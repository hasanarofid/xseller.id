<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the admin binary MLM dashboard home based on XSELLER PRD 2026.
     */
    public function index()
    {
        $user = auth()->user();

        return Inertia::render('Admin/Dashboard', [
            'referral_links' => [
                'default' => url('/register?sponsor=' . ($user ? ($user->username ?: $user->id) : 1)),
                'url' => url('/register?sponsor=' . ($user ? ($user->username ?: $user->id) : 1)),
            ],
            'wallet' => [
                'saldo' => 2500000,
                'voucher_aktif' => 2, // Strict terminology: VOUCHER (no PIN)
                'total_bonus_cair' => 400000,
                'bonus_sponsor' => 300000,
                'bonus_pasangan' => 100000,
                'bonus_titik' => 0,
                'bonus_reward' => 0,
            ],
            'binary_legs' => [
                'left' => [
                    'members' => 3,
                    'pending_points' => 1,
                ],
                'right' => [
                    'members' => 2,
                    'pending_points' => 0,
                ],
            ],
            'rewards' => [
                [
                    'key' => 'silver',
                    'title' => 'SILVER REWARD',
                    'prize' => 'HP Android / Rp 1 Juta',
                    'target_left' => 10,
                    'target_right' => 10,
                    'current_left' => 3,
                    'current_right' => 2,
                    'status' => 'MENUNGGU',
                    'icon' => 'Sparkles'
                ],
                [
                    'key' => 'gold',
                    'title' => 'GOLD REWARD',
                    'prize' => 'Laptop / Rp 5 Juta',
                    'target_left' => 50,
                    'target_right' => 50,
                    'current_left' => 3,
                    'current_right' => 2,
                    'status' => 'MENUNGGU',
                    'icon' => 'Award'
                ],
                [
                    'key' => 'platinum',
                    'title' => 'PLATINUM REWARD',
                    'prize' => 'Motor / Rp 25 Juta',
                    'target_left' => 250,
                    'target_right' => 250,
                    'current_left' => 3,
                    'current_right' => 2,
                    'status' => 'MENUNGGU',
                    'icon' => 'Shield'
                ],
                [
                    'key' => 'diamond',
                    'title' => 'DIAMOND REWARD',
                    'prize' => 'Mobil / Rp 150 Juta',
                    'target_left' => 1000,
                    'target_right' => 1000,
                    'current_left' => 3,
                    'current_right' => 2,
                    'status' => 'MENUNGGU',
                    'icon' => 'Trophy'
                ],
                [
                    'key' => 'crown',
                    'title' => 'CROWN REWARD',
                    'prize' => 'Rumah Mewah / Rp 750 Juta',
                    'target_left' => 5000,
                    'target_right' => 5000,
                    'current_left' => 3,
                    'current_right' => 2,
                    'status' => 'MENUNGGU',
                    'icon' => 'Gift'
                ],
            ],
            'packages' => [
                [
                    'name' => 'Starter (Steping)',
                    'price' => 125000,
                    'sponsor_bonus' => 20000,
                    'team_poin' => 0,
                    'max_tier' => 'Tier 3 (Steping s/d Tier 15)',
                    'tpr' => 'Non-TPR',
                    'is_current' => false
                ],
                [
                    'name' => 'Basic',
                    'price' => 550000,
                    'sponsor_bonus' => 100000,
                    'team_poin' => 1,
                    'max_tier' => 'Tier 5 Generasi',
                    'tpr' => 'Non-TPR',
                    'is_current' => false
                ],
                [
                    'name' => 'Medium',
                    'price' => 2100000,
                    'sponsor_bonus' => 400000,
                    'team_poin' => 4,
                    'max_tier' => 'Tier 8 Generasi',
                    'tpr' => 'Non-TPR',
                    'is_current' => false
                ],
                [
                    'name' => 'Pro',
                    'price' => 4300000,
                    'sponsor_bonus' => 800000,
                    'team_poin' => 8,
                    'max_tier' => 'Tier 12 Generasi',
                    'tpr' => 'Profit Share 7% / bulan (3 bulan)',
                    'is_current' => false
                ],
                [
                    'name' => 'Ultimate',
                    'price' => 10500000,
                    'sponsor_bonus' => 2000000,
                    'team_poin' => 12,
                    'max_tier' => 'Tier 15 Generasi',
                    'tpr' => 'Profit Share 9% / bulan (3 bulan)',
                    'is_current' => str_contains(strtolower($user->package_name ?? ''), 'ultimate')
                ],
            ],
            'steping_status' => [
                'current_tier' => $user->getActiveTier(),
                'total_referrals' => User::where('parent_id', $user->id)->count(),
                'next_tier' => 15,
                'required_referrals' => 32,
            ]
        ]);
    }
}
