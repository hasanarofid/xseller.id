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

        // Get user's active vouchers
        $activeVouchersRaw = Voucher::where('user_id', $currentUser->id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('voucher_type')->orWhere('voucher_type', 'activation');
            })
            ->get();

        $vouchers = $activeVouchersRaw->map(function ($v) {
            return [
                'code' => $v->code,
                'package_name' => $v->package_name,
                'label' => $v->code . ' (Paket ' . ($v->package_name ?: 'Seller') . ')',
            ];
        });

        // Calculate voucher stock count per package type
        $voucherStocks = [
            'seller' => 0,
            'star_seller' => 0,
            'affiliate' => 0,
            'business' => 0,
            'partner' => 0,
        ];

        foreach ($activeVouchersRaw as $v) {
            $pkg = strtolower($v->package_name ?? '');
            if (str_contains($pkg, 'partner') || str_contains($pkg, 'ultimate') || str_contains($pkg, '10.500') || str_contains($pkg, '10500')) {
                $voucherStocks['partner']++;
            } elseif (str_contains($pkg, 'business') || str_contains($pkg, 'pro') || str_contains($pkg, '4.300') || str_contains($pkg, '4300')) {
                $voucherStocks['business']++;
            } elseif (str_contains($pkg, 'affiliate') || str_contains($pkg, 'medium') || str_contains($pkg, '2.100') || str_contains($pkg, '2100')) {
                $voucherStocks['affiliate']++;
            } elseif (str_contains($pkg, 'star') || str_contains($pkg, 'basic') || str_contains($pkg, '550')) {
                $voucherStocks['star_seller']++;
            } else {
                $voucherStocks['seller']++;
            }
        }

        // List of all active users to choose as Sponsor Langsung
        $allUsers = User::select('id', 'name', 'username', 'email')->get()->map(function ($u) {
            return [
                'username' => $u->username ?: strtolower(explode(' ', $u->name)[0]),
                'name' => $u->name,
                'label' => '@' . ($u->username ?: strtolower(explode(' ', $u->name)[0])) . ' (' . $u->name . ')',
            ];
        });

        return Inertia::render('Admin/Activation/Index', [
            'vouchers' => $vouchers,
            'voucher_stocks' => $voucherStocks,
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
        $alloc = $this->calculatePackageAllocation($packageName);
        $sponsorBonus = $alloc['gen_1'];
        $teamPoints = $alloc['team_points'];

        DB::transaction(function () use ($request, $voucher, $sponsorUser, $packageName, $alloc, $sponsorBonus, $teamPoints) {
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

            // 1. Direct Sponsor Bonus (Generasi 1) & Team Points for Sponsor
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
            }

            if ($teamPoints > 0) {
                $sponsorUser->increment('team_points', $teamPoints);
            }

            // 2. Multi-tier Allocation for Generasi 2 up to Generasi 15 Uplines
            $currentUpline = $sponsorUser;
            for ($gen = 2; $gen <= 15; $gen++) {
                if (!$currentUpline->parent_id) {
                    break;
                }

                $upline = User::find($currentUpline->parent_id);
                if (!$upline) {
                    break;
                }

                $genAmount = $alloc['gen_2_15'];
                // Only distribute generation bonus if upline active tier reaches or exceeds $gen
                if ($genAmount > 0 && $upline->getActiveTier() >= $gen) {
                    $upline->increment('saldo', $genAmount);
                    $upline->increment('total_bonus', $genAmount);

                    BonusLog::create([
                        'transaction_code' => 'T' . sprintf('%03d', BonusLog::count() + 1),
                        'user_id' => $upline->id,
                        'category' => 'tier',
                        'source_user_id' => $newUser->id,
                        'description' => "Bonus Tier Generasi {$gen}: Pendaftaran @{$newUser->username} (Paket {$packageName})",
                        'amount' => $genAmount,
                    ]);

                    WalletTransaction::create([
                        'user_id' => $upline->id,
                        'type' => 'in',
                        'category' => 'bonus_tier',
                        'amount' => $genAmount,
                        'description' => "Bonus Tier Generasi {$gen} dari pendaftaran member baru @{$newUser->username} (Paket {$packageName})",
                    ]);
                }

                if ($teamPoints > 0) {
                    $upline->increment('team_points', $teamPoints);
                }

                $currentUpline = $upline;
            }
        });

        return redirect()->route('admin.pohon-jaringan', ['focus_id' => $sponsorUser->id])
            ->with('success', "Member baru @{$request->username} ({$request->name}) berhasil diautentikasi & diaktifkan di bawah Sponsor @{$sponsorUser->username}! Tier bonus & Team Poin berhasil didistribusikan.");
    }

    /**
     * Calculate Tier Allocation & Team Points by package name.
     */
    private function calculatePackageAllocation(string $packageName): array
    {
        $pkg = strtolower($packageName);

        if (str_contains($pkg, '125') || str_contains($pkg, 'seller') || str_contains($pkg, 'starter')) {
            return [
                'gen_1' => 20000,
                'gen_2_15' => 0,
                'team_points' => 0,
            ];
        }
        if (str_contains($pkg, '550') || str_contains($pkg, 'star') || str_contains($pkg, 'basic')) {
            return [
                'gen_1' => 100000,
                'gen_2_15' => 5000,
                'team_points' => 1,
            ];
        }
        if (str_contains($pkg, '2.100') || str_contains($pkg, '2100') || str_contains($pkg, 'affiliate') || str_contains($pkg, 'medium')) {
            return [
                'gen_1' => 300000,
                'gen_2_15' => 15000,
                'team_points' => 4,
            ];
        }
        if (str_contains($pkg, '4.300') || str_contains($pkg, '4300') || str_contains($pkg, 'business') || str_contains($pkg, 'pro')) {
            return [
                'gen_1' => 600000,
                'gen_2_15' => 30000,
                'team_points' => 8,
            ];
        }
        if (str_contains($pkg, '10.500') || str_contains($pkg, '10500') || str_contains($pkg, 'partner') || str_contains($pkg, 'ultimate')) {
            return [
                'gen_1' => 1500000,
                'gen_2_15' => 100000,
                'team_points' => 12,
            ];
        }

        // Default fallback
        return [
            'gen_1' => 100000,
            'gen_2_15' => 5000,
            'team_points' => 1,
        ];
    }
}
