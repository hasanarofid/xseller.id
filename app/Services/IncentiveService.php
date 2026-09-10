<?php

namespace App\Services;

use App\Models\BonusLog;
use App\Models\User;
use App\Models\WalletTransaction;

class IncentiveService
{
    /**
     * Check and award Incentive Promotion bonus if user qualifies.
     */
    public static function check(User $user)
    {
        // 1. Calculate total income exactly as in ActivityController
        $bonusSponsor = BonusLog::where('user_id', $user->id)->where('category', 'sponsor')->sum('amount');
        $bonusGenerasi = BonusLog::where('user_id', $user->id)->where('category', 'tier')->sum('amount');
        $bonusRO = BonusLog::where('user_id', $user->id)->whereIn('category', ['ro', 'ro_matching'])->sum('amount');
        $bonusPO = BonusLog::where('user_id', $user->id)->where('category', 'po')->sum('amount');
        $bonusPAL = BonusLog::where('user_id', $user->id)->where('category', 'pal')->sum('amount');
        $bonusTPR = BonusLog::where('user_id', $user->id)->where('category', 'tpr')->sum('amount');
        $bonusIncentive = BonusLog::where('user_id', $user->id)->where('category', 'incentive')->sum('amount');

        $totalIncome = $bonusSponsor + $bonusGenerasi + $bonusRO + $bonusPO + $bonusPAL + $bonusTPR + $bonusIncentive;
        
        $milestones = [
            ['income' => 5000000, 'reward' => 250000],
            ['income' => 15000000, 'reward' => 1500000],
            ['income' => 50000000, 'reward' => 5000000],
            ['income' => 100000000, 'reward' => 10000000],
            ['income' => 400000000, 'reward' => 40000000],
        ];

        // Ensure milestones are sorted ascending by income
        usort($milestones, fn($a, $b) => $a['income'] <=> $b['income']);

        foreach ($milestones as $m) {
            if ($totalIncome >= $m['income']) {
                // Check if user already got this exact incentive milestone
                $alreadyGot = BonusLog::where('user_id', $user->id)
                    ->where('category', 'incentive')
                    ->where('qualified_amount', $m['income'])
                    ->exists();

                if (!$alreadyGot) {
                    // Give incentive
                    $user->increment('saldo', $m['reward']);
                    $user->increment('total_bonus', $m['reward']);
                    
                    BonusLog::create([
                        'transaction_code' => 'INC' . sprintf('%03d', BonusLog::count() + 1),
                        'user_id' => $user->id,
                        'category' => 'incentive',
                        'description' => "Bonus Incentive Promotion pencapaian Income Rp " . number_format($m['income'], 0, ',', '.'),
                        'amount' => $m['reward'],
                        'qualified_amount' => $m['income'],
                    ]);
                    
                    WalletTransaction::create([
                        'user_id' => $user->id,
                        'type' => 'in',
                        'category' => 'bonus',
                        'amount' => $m['reward'],
                        'description' => "Bonus Incentive Promotion (Pencapaian Rp " . number_format($m['income'], 0, ',', '.') . ")",
                    ]);
                    
                    // Re-calculate totalIncome after giving incentive to check the next milestone properly
                    $totalIncome += $m['reward'];
                }
            }
        }
    }
}
