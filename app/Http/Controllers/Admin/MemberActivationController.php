<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BonusLog;
use App\Models\User;
use App\Models\Voucher;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class MemberActivationController extends Controller
{
    /**
     * Display member activation form.
     */
    public function index()
    {
        $currentUser = auth()->user() ?: User::first();

        // Get user's active vouchers / PIN
        $vouchers = Voucher::where('user_id', $currentUser->id)
            ->where('status', 'active')
            ->get()
            ->map(function ($v) {
                return [
                    'code' => $v->code,
                    'package_name' => $v->package_name,
                    'label' => $v->code . ' (Paket ' . $v->package_name . ')',
                ];
            });

        // List of all active users to choose as Sponsor Langsung (Matahari System)
        $allUsers = User::select('id', 'name', 'username', 'email')->get()->map(function ($u) {
            return [
                'username' => $u->username ?: strtolower(explode(' ', $u->name)[0]),
                'name' => $u->name,
                'label' => '@' . ($u->username ?: strtolower(explode(' ', $u->name)[0])) . ' (' . $u->name . ')',
            ];
        });

        return Inertia::render('Admin/Activation/Index', [
            'vouchers' => $vouchers,
            'users' => $allUsers,
            'default_sponsor' => $currentUser->username ?: 'admin',
        ]);
    }

    /**
     * Process member activation in Matahari System (Direct Sponsor).
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|alpha_dash|max:50|unique:users,username',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'sponsor_username' => 'required|string|exists:users,username',
            'voucher_code' => 'required|string|exists:vouchers,code',
        ]);

        $currentUser = auth()->user() ?: User::first();

        // Verify voucher / PIN
        $voucher = Voucher::where('code', $request->voucher_code)
            ->where('user_id', $currentUser->id)
            ->where('status', 'active')
            ->first();

        if (!$voucher) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Voucher Activation (PIN) tidak valid, telah digunakan, atau bukan milik Anda.',
            ]);
        }

        $sponsorUser = User::where('username', $request->sponsor_username)->first();
        if (!$sponsorUser) {
            throw ValidationException::withMessages([
                'sponsor_username' => 'Username Sponsor Langsung tidak ditemukan.',
            ]);
        }

        $packageName = $voucher->package_name ?: 'Basic';
        $sponsorBonus = $this->calculateSponsorBonus($packageName);

        DB::transaction(function () use ($request, $voucher, $sponsorUser, $packageName, $sponsorBonus) {
            // Create new member in Matahari system (parent_id = sponsor_id)
            $newUser = User::create([
                'name' => $request->name,
                'username' => strtolower($request->username),
                'email' => $request->email,
                'password' => bcrypt('password'),
                'parent_id' => $sponsorUser->id,
                'package_name' => $packageName,
            ]);
            $newUser->assignRole('client');

            try {
                $newUser->notify(new \App\Notifications\WelcomeRegisterNotification($newUser, 'password'));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Gagal mengirim email aktivasi member: ' . $e->getMessage());
            }

            // Mark voucher as used
            $voucher->update([
                'status' => 'used',
                'used_by_id' => $newUser->id,
                'used_at' => now(),
            ]);

            // Add Direct Referral Bonus to Sponsor Langsung
            if ($sponsorBonus > 0) {
                $sponsorUser->increment('saldo', $sponsorBonus);
                $sponsorUser->increment('total_bonus', $sponsorBonus);

                BonusLog::create([
                    'transaction_code' => 'B' . sprintf('%03d', BonusLog::count() + 1),
                    'user_id' => $sponsorUser->id,
                    'category' => 'sponsor',
                    'source_user_id' => $newUser->id,
                    'description' => "Bonus Direct Referral: Pendaftaran @{$newUser->username} (Paket {$packageName})",
                    'amount' => $sponsorBonus,
                ]);

                WalletTransaction::create([
                    'user_id' => $sponsorUser->id,
                    'type' => 'in',
                    'category' => 'bonus_sponsor',
                    'amount' => $sponsorBonus,
                    'description' => "Bonus Direct Referral dari pendaftaran member baru @{$newUser->username} (Paket {$packageName})",
                ]);

                try {
                    $sponsorUser->notify(new \App\Notifications\BonusReceivedNotification($sponsorBonus, 'Direct Referral', "Pendaftaran member @{$newUser->username}"));
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Gagal mengirim notifikasi bonus sponsor: ' . $e->getMessage());
                }
            }
        });

        return redirect()->route('admin.pohon-jaringan', ['focus_id' => $sponsorUser->id])
            ->with('success', "Member baru @{$request->username} ({$request->name}) berhasil diautentikasi & diaktifkan di bawah Sponsor @{$sponsorUser->username}!");
    }

    /**
     * Calculate Direct Referral bonus by package name/price.
     */
    private function calculateSponsorBonus(string $packageName): float
    {
        $pkg = strtolower($packageName);

        if (str_contains($pkg, '125') || str_contains($pkg, 'starter')) {
            return 25000;
        }
        if (str_contains($pkg, '550') || str_contains($pkg, 'basic')) {
            return 100000;
        }
        if (str_contains($pkg, '2.100') || str_contains($pkg, '2100') || str_contains($pkg, 'medium')) {
            return 300000;
        }
        if (str_contains($pkg, '4.300') || str_contains($pkg, '4300') || str_contains($pkg, 'pro')) {
            return 600000;
        }
        if (str_contains($pkg, '10.500') || str_contains($pkg, '10500') || str_contains($pkg, 'ultimate')) {
            return 1500000;
        }

        // Default 20% fallback for custom package names
        return 100000;
    }
}
