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
                return [
                    'id' => $ref->id,
                    'name' => $ref->name,
                    'username' => $ref->username,
                    'package_name' => $ref->package_name ?: 'Starter (125.000)',
                    'created_at' => $ref->created_at ? $ref->created_at->format('d/m/Y H:i') : '-',
                ];
            });

        $totalReferrals = $referrals->count();
        $baseMaxTier = $user->getBaseTier();
        $activeTier = $baseMaxTier;

        // Steping milestones definitions per 02 Sept 2026 revision
        $milestones = [
            ['tier' => 4,  'required_referrals' => 4,  'unlocked' => false],
            ['tier' => 5,  'required_referrals' => 8,  'unlocked' => false],
            ['tier' => 6,  'required_referrals' => 12, 'unlocked' => false],
            ['tier' => 7,  'required_referrals' => 16, 'unlocked' => false],
            ['tier' => 9,  'required_referrals' => 20, 'unlocked' => false],
            ['tier' => 11, 'required_referrals' => 24, 'unlocked' => false],
            ['tier' => 13, 'required_referrals' => 28, 'unlocked' => false],
            ['tier' => 15, 'required_referrals' => 32, 'unlocked' => false],
        ];

        foreach ($milestones as &$m) {
            if ($baseMaxTier >= $m['tier'] || $totalReferrals >= $m['required_referrals']) {
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
                'total_referral_count' => $totalReferrals,
                'next_tier' => $nextMilestone ? $nextMilestone['tier'] : 15,
                'required_referrals' => $nextMilestone ? $nextMilestone['required_referrals'] : 32,
                'remaining_referrals' => $nextMilestone ? max(0, $nextMilestone['required_referrals'] - $totalReferrals) : 0,
            ],
            'milestones' => $milestones,
            'referrals' => $referrals,
            'is_admin' => $user->hasRole('admin'),
        ]);
    }
}
