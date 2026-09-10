<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\IncentiveService;
use Illuminate\Support\Facades\DB;

class FixIncentiveBonus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:incentive-bonus {username?} {--force : Jalankan tanpa konfirmasi (untuk web runner)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix retroactive incentive promotion bonuses for users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->argument('username');

        $query = User::query();
        if ($username) {
            $query->where('username', $username);
        }

        $users = $query->get();

        foreach ($users as $user) {
            DB::transaction(function () use ($user) {
                IncentiveService::check($user);
            });
            $this->info("Checked incentive for user: " . $user->username);
        }

        $this->info('Fix incentive bonus completed.');
    }
}
