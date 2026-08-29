<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NetworkDataController extends Controller
{
    /**
     * Display the Network Member Directory page.
     */
    public function index(Request $request)
    {
        $currentUser = auth()->user();
        $search = $request->input('search');

        $query = User::with('parent')->withCount('children')->orderBy('id', 'asc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $members = $query->get()->map(function ($u) use ($currentUser) {
            $idCode = 'USR' . str_pad($u->id, 3, '0', STR_PAD_LEFT);
            $sponsor = $u->parent ? '@' . $u->parent->username : 'FOUNDER';

            return [
                'id' => $u->id,
                'id_code' => $idCode,
                'name' => $u->name,
                'username' => $u->username ?? 'user' . $u->id,
                'email' => $u->email,
                'sponsor' => $sponsor,
                'g1_count' => (int) ($u->children_count ?? 0),
                'total_team' => $this->calculateTotalTeamCount($u->id),
                'saldo' => (float) ($u->saldo ?? 0),
                'is_self' => $u->id === $currentUser->id,
            ];
        });

        return Inertia::render('Admin/NetworkData', [
            'members' => $members,
            'filters' => [
                'search' => $search ?? '',
            ],
            'is_admin' => $currentUser->hasRole('admin'),
            'is_impersonating' => session()->has('impersonator_id'),
        ]);
    }

    /**
     * Switch perspective / login as member for testing.
     */
    public function impersonate(User $user)
    {
        $admin = auth()->user();

        if (!$admin->hasRole('admin') && !session()->has('impersonator_id')) {
            return back()->with('error', 'Hanya Admin yang dapat menggunakan opsi pengujian login!');
        }

        if ($user->id === $admin->id) {
            return back()->with('error', 'Anda sudah berada di akun Anda sendiri.');
        }

        // Store original admin ID if not already impersonating
        if (!session()->has('impersonator_id')) {
            session(['impersonator_id' => $admin->id]);
        }

        Auth::login($user);

        return redirect()->route('admin.dashboard')->with('success', 'Berhasil berganti perspektif login ke @' . $user->username . '!');
    }

    /**
     * Return back to original admin account.
     */
    public function stopImpersonating()
    {
        if (!session()->has('impersonator_id')) {
            return redirect()->route('admin.dashboard');
        }

        $adminId = session('impersonator_id');
        $admin = User::find($adminId);

        if ($admin) {
            Auth::login($admin);
        }

        session()->forget('impersonator_id');

        return redirect()->route('admin.network-data.index')->with('success', 'Kembali ke akun Admin utama.');
    }

    /**
     * Calculate total team members count up to 15 generations depth.
     */
    private function calculateTotalTeamCount($rootUserId): int
    {
        $count = 0;
        $currentIds = [$rootUserId];

        for ($gen = 1; $gen <= 15; $gen++) {
            if (empty($currentIds)) break;
            $downlineIds = User::whereIn('parent_id', $currentIds)->pluck('id')->toArray();
            $count += count($downlineIds);
            $currentIds = $downlineIds;
        }

        return $count;
    }
}
