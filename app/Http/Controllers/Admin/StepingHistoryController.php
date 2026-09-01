<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StepingHistoryController extends Controller
{
    /**
     * Display Steping Qualification History for current user.
     */
    public function index()
    {
        $user = auth()->user();

        // Get all direct referrals sponsored by user
        $referrals = User::where('parent_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($ref) {
                $pkg = strtolower($ref->package_name ?? '');
                $isPro = str_contains($pkg, 'pro') || str_contains($pkg, '4.300') || str_contains($pkg, '4300') || str_contains($pkg, 'ultimate');

                return [
                    'id' => $ref->id,
                    'name' => $ref->name,
                    'username' => $ref->username,
                    'package_name' => $ref->package_name ?: 'Starter',
                    'is_pro_referral' => $isPro,
                    'created_at' => $ref->created_at ? $ref->created_at->format('d/m/Y H:i') : '-',
                ];
            });

        // Count direct referrals with Pro package or higher
        $proReferralCount = $referrals->where('is_pro_referral', true)->count();
        $totalReferrals = $referrals->count();

        // Base max tier according to package
        $pkgName = strtolower($user->package_name ?? '');

        if (str_contains($pkgName, 'ultimate')) {
            $baseMaxTier = 15;
        } elseif (str_contains($pkgName, 'pro')) {
            $baseMaxTier = 12;
        } elseif (str_contains($pkgName, 'medium')) {
            $baseMaxTier = 8;
        } elseif (str_contains($pkgName, 'basic')) {
            $baseMaxTier = 5;
        } else {
            // Starter / Steping
            $baseMaxTier = 3;
        }

        // Calculate active tier with steping rules
        $activeTier = $baseMaxTier;

        // Steping milestones definitions
        $milestones = [
            ['tier' => 4, 'required_pro' => 2, 'unlocked' => false],
            ['tier' => 5, 'required_pro' => 4, 'unlocked' => false],
            ['tier' => 6, 'required_pro' => 8, 'unlocked' => false],
            ['tier' => 7, 'required_pro' => 10, 'unlocked' => false],
            ['tier' => 9, 'required_pro' => 12, 'unlocked' => false],
            ['tier' => 11, 'required_pro' => 14, 'unlocked' => false],
            ['tier' => 13, 'required_pro' => 17, 'unlocked' => false],
            ['tier' => 15, 'required_pro' => 20, 'unlocked' => false],
        ];

        foreach ($milestones as &$m) {
            if ($baseMaxTier >= $m['tier'] || $proReferralCount >= $m['required_pro']) {
                $m['unlocked'] = true;
                if ($m['tier'] > $activeTier) {
                    $activeTier = $m['tier'];
                }
            }
        }

        // Find next milestone
        $nextMilestone = null;
        foreach ($milestones as $m) {
            if (!$m['unlocked']) {
                $nextMilestone = $m;
                break;
            }
        }

        return Inertia::render('Admin/StepingHistory/Index', [
            'steping_summary' => [
                'user_package' => $user->package_name ?: 'Starter',
                'base_tier' => $baseMaxTier,
                'active_tier' => $activeTier,
                'pro_referral_count' => $proReferralCount,
                'total_referral_count' => $totalReferrals,
                'next_tier' => $nextMilestone ? $nextMilestone['tier'] : 15,
                'required_pro_referrals' => $nextMilestone ? $nextMilestone['required_pro'] : 20,
                'remaining_pro_referrals' => $nextMilestone ? max(0, $nextMilestone['required_pro'] - $proReferralCount) : 0,
            ],
            'milestones' => $milestones,
            'referrals' => $referrals,
            'is_admin' => $user->hasRole('admin'),
        ]);
    }
}
