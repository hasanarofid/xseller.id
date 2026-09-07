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

        // Team Point Matrix Rules per package
        $teamPointRules = [
            ['package_name' => 'Star Seller (Rp 550.000)', 'team_points' => 1, 'max_gen' => 5],
            ['package_name' => 'Affiliate (Rp 2.100.000)', 'team_points' => 4, 'max_gen' => 8],
            ['package_name' => 'Business (Rp 4.300.000)', 'team_points' => 8, 'max_gen' => 12],
            ['package_name' => 'Partner (Rp 10.500.000)', 'team_points' => 12, 'max_gen' => 15],
        ];

        // Team Point History Logs
        $teamPointLogs = \App\Models\BonusLog::with('sourceUser')
            ->where('user_id', $user->id)
            ->whereIn('category', ['sponsor', 'generasi', 'tier'])
            ->latest()
            ->get()
            ->filter(function ($log) {
                $pkg = $log->sourceUser ? ($log->sourceUser->package_name ?? '') : '';
                $pkgLower = strtolower($pkg);
                if (str_contains($pkgLower, 'seller') && !str_contains($pkgLower, 'star')) {
                    return false;
                }
                if (str_contains($pkgLower, '125') || str_contains($pkgLower, 'starter')) {
                    return false;
                }
                return true;
            })
            ->map(function ($log) {
                $rawSource = $log->sourceUser ? $log->sourceUser->username : '';
                $source = $rawSource ? '@' . ltrim($rawSource, '@') : '-';
                $pkg = $log->sourceUser ? ($log->sourceUser->package_name ?? 'Star Seller') : 'Star Seller';
                $pkgLower = strtolower($pkg);
                $pts = 0;
                if (str_contains($pkgLower, 'partner') || str_contains($pkgLower, '10.500') || str_contains($pkgLower, '10500')) {
                    $pts = 12;
                } elseif (str_contains($pkgLower, 'business') || str_contains($pkgLower, '4.300') || str_contains($pkgLower, '4300')) {
                    $pts = 8;
                } elseif (str_contains($pkgLower, 'affiliate') || str_contains($pkgLower, '2.100') || str_contains($pkgLower, '2100')) {
                    $pts = 4;
                } elseif (str_contains($pkgLower, 'star') || str_contains($pkgLower, '550')) {
                    $pts = 1;
                }

                return [
                    'id' => $log->id,
                    'created_at' => $log->created_at->format('d/m/Y H:i'),
                    'source_username' => $source,
                    'source_name' => $log->sourceUser ? $log->sourceUser->name : 'Mitra',
                    'package_name' => $pkg,
                    'points_earned' => $pts,
                ];
            })
            ->values();

        return Inertia::render('Admin/StepingHistory/Index', [
            'steping_summary' => [
                'user_package' => $user->package_name ?: 'Starter',
                'base_tier' => $baseMaxTier,
                'active_tier' => $activeTier,
                'total_referral_count' => $totalReferrals,
                'next_tier' => $nextMilestone ? $nextMilestone['tier'] : 15,
                'required_referrals' => $nextMilestone ? $nextMilestone['required_referrals'] : 32,
                'remaining_referrals' => $nextMilestone ? max(0, $nextMilestone['required_referrals'] - $totalReferrals) : 0,
                'total_team_points' => (int) ($user->team_points ?? 0),
            ],
            'milestones' => $milestones,
            'referrals' => $referrals,
            'team_point_rules' => $teamPointRules,
            'team_point_logs' => $teamPointLogs,
            'is_admin' => $user->hasRole('admin'),
        ]);
    }
}
