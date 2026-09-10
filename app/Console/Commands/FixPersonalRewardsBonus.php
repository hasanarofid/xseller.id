<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\BonusLog;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class FixPersonalRewardsBonus extends Command
{
    protected $signature = 'fix:personal-rewards {username?} {--force : Jalankan tanpa konfirmasi (untuk web runner)}';
    protected $description = 'Fix retroactive Personal RO (Konversi) and Personal PO (Reward Cash) for users';

    public function handle(): int
    {
        $username = $this->argument('username');

        $query = User::query();
        if ($username) {
            $query->where('username', $username);
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->error("No users found.");
            return 1;
        }

        if (!$this->option('force') && !$this->confirm("Are you sure you want to fix personal rewards for {$users->count()} users?")) {
            return 0;
        }

        $fixedRo = 0;
        $fixedPo = 0;

        foreach ($users as $user) {
            DB::transaction(function () use ($user, &$fixedRo, &$fixedPo) {
                // 1. Fix Personal RO Konversi (Rp 500k per 35 points)
                if ($user->ro_points >= 35) {
                    $multiplier = floor($user->ro_points / 35);
                    $totalExpectedReward = $multiplier * 500000;

                    // Check how many times they already got it (description like Reward Konversi % Poin RO)
                    // Or just sum the amount from reward_ro / category 'ro' with description Reward Konversi
                    $alreadyReceived = BonusLog::where('user_id', $user->id)
                        ->where('category', 'ro')
                        ->where('description', 'like', 'Reward Konversi%')
                        ->sum('amount');

                    $missingReward = $totalExpectedReward - $alreadyReceived;

                    if ($missingReward > 0) {
                        $user->increment('saldo', $missingReward);
                        $user->increment('total_bonus', $missingReward);

                        BonusLog::create([
                            'transaction_code' => 'RO' . sprintf('%04d', BonusLog::count() + 1),
                            'user_id' => $user->id,
                            'category' => 'ro',
                            'source_user_id' => $user->id,
                            'description' => "Reward Konversi Poin RO (Retroactive Fix)",
                            'amount' => $missingReward,
                        ]);

                        WalletTransaction::create([
                            'user_id' => $user->id,
                            'type' => 'in',
                            'category' => 'reward_ro',
                            'amount' => $missingReward,
                            'description' => "Reward Konversi Poin RO (Retroactive Fix)",
                        ]);

                        $fixedRo++;
                        $this->info("Fixed RO Reward for @{$user->username}: +Rp " . number_format($missingReward, 0, ',', '.'));
                    }
                }

                // 2. Fix Personal PO Reward
                if ($user->po_points >= 35) {
                    $milestones = [
                        35 => 1000000,
                        90 => 3000000,
                        300 => 12000000,
                        2000 => 70000000,
                        5000 => 150000000,
                    ];

                    $totalExpectedPoReward = 0;
                    foreach ($milestones as $pts => $reward) {
                        if ($user->po_points >= $pts) {
                            $totalExpectedPoReward += $reward;
                        }
                    }

                    // Check how much they already received
                    $alreadyReceivedPo = BonusLog::where('user_id', $user->id)
                        ->where('category', 'po')
                        ->where('description', 'like', 'Reward Personal PO%')
                        ->sum('amount');

                    $missingPoReward = $totalExpectedPoReward - $alreadyReceivedPo;

                    if ($missingPoReward > 0) {
                        $user->increment('saldo', $missingPoReward);
                        $user->increment('total_bonus', $missingPoReward);

                        BonusLog::create([
                            'transaction_code' => 'PO' . sprintf('%04d', BonusLog::count() + 1),
                            'user_id' => $user->id,
                            'category' => 'po',
                            'source_user_id' => $user->id,
                            'description' => "Reward Personal PO (Retroactive Fix)",
                            'amount' => $missingPoReward,
                            'qualified_amount' => $missingPoReward, // Simplification
                        ]);

                        WalletTransaction::create([
                            'user_id' => $user->id,
                            'type' => 'in',
                            'category' => 'reward_po',
                            'amount' => $missingPoReward,
                            'description' => "Reward Personal PO (Retroactive Fix)",
                        ]);

                        $fixedPo++;
                        $this->info("Fixed PO Reward for @{$user->username}: +Rp " . number_format($missingPoReward, 0, ',', '.'));
                    }
                }
            });
        }

        $this->info("Done! Fixed RO for {$fixedRo} users and PO for {$fixedPo} users.");
        return 0;
    }
}
