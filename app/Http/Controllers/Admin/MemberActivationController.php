<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Http\Request;
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

        // List of all active users to choose as Sponsor / Parent Placement
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
            'default_parent' => $currentUser->username ?: 'admin',
        ]);
    }

    /**
     * Process member activation and placement in binary tree.
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|alpha_dash|max:50|unique:users,username',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'sponsor_username' => 'required|string|exists:users,username',
            'parent_username' => 'required|string|exists:users,username',
            'position' => 'required|in:left,right',
            'voucher_code' => 'required|string|exists:vouchers,code',
        ]);

        $currentUser = auth()->user() ?: User::first();

        // Verify voucher
        $voucher = Voucher::where('code', $request->voucher_code)
            ->where('user_id', $currentUser->id)
            ->where('status', 'active')
            ->first();

        if (!$voucher) {
            throw ValidationException::withMessages([
                'voucher_code' => 'VOUCHER Aktivasi tidak valid, telah digunakan, atau bukan milik Anda.',
            ]);
        }

        $parentUser = User::where('username', $request->parent_username)->first();
        if (!$parentUser) {
            throw ValidationException::withMessages([
                'parent_username' => 'Username Parent Placement tidak ditemukan.',
            ]);
        }

        // Check if parent position is already occupied
        $existingChild = User::where('parent_id', $parentUser->id)
            ->where('position', $request->position)
            ->exists();

        if ($existingChild) {
            $posText = $request->position === 'left' ? 'Kiri (Left)' : 'Kanan (Right)';
            throw ValidationException::withMessages([
                'position' => "Posisi Kaki {$posText} pada parent @{$parentUser->username} sudah terisi! Silakan pilih posisi lain atau tentukan parent placement baru.",
            ]);
        }

        // Create new member
        $newUser = User::create([
            'name' => $request->name,
            'username' => strtolower($request->username),
            'email' => $request->email,
            'password' => bcrypt('password'),
            'parent_id' => $parentUser->id,
            'position' => $request->position,
            'package_name' => $voucher->package_name ?: 'Basic',
            'left_count' => 0,
            'right_count' => 0,
            'left_points' => 0,
            'right_points' => 0,
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

        // Update binary leg counters for parent
        if ($request->position === 'left') {
            $parentUser->increment('left_count');
        } else {
            $parentUser->increment('right_count');
        }

        return redirect()->route('admin.pohon-jaringan', ['focus_id' => $newUser->id])
            ->with('success', "Member baru @{$newUser->username} ({$newUser->name}) berhasil didaftarkan & diaktifkan!");
    }
}
