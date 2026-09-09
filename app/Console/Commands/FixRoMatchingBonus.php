<?php

namespace App\Console\Commands;

use App\Models\BonusLog;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixRoMatchingBonus extends Command
{
    protected $signature = 'fix:ro-matching-bonus {member_username : Username member yang sudah capai 35 poin RO}';
    protected $description = 'Koreksi Matching Bonus RO (Rp 100.000) ke sponsor yang terlewat';

    public function handle(): int
    {
        $memberUsername = $this->argument('member_username');
        $member = User::where('username', $memberUsername)->first();

        if (!$member) {
            $this->error("User @{$memberUsername} tidak ditemukan!");
            return 1;
        }

        $roPoints = (int) ($member->ro_points ?? 0);
        $sponsor = $member->parent;

        $this->info("Member: @{$member->username} | Poin RO: {$roPoints}");

        if (!$sponsor) {
            $this->warn("Member tidak memiliki sponsor (parent_id kosong). Batalkan.");
            return 1;
        }

        $this->info("Sponsor: @{$sponsor->username}");

        // Hitung berapa kali kelipatan 35 yang sudah dicapai
        $milestone = intdiv($roPoints, 35);
        if ($milestone === 0) {
            $this->warn("Poin RO belum mencapai 35. Batalkan.");
            return 1;
        }

        // Cek sudah berapa kali matching bonus dikirim ke sponsor dari member ini
        $alreadySent = BonusLog::where('user_id', $sponsor->id)
            ->where('category', 'ro_matching')
            ->where('source_user_id', $member->id)
            ->count();

        $toSend = $milestone - $alreadySent;

        if ($toSend <= 0) {
            $this->info("Matching Bonus sudah terkirim semua ({$alreadySent}x). Tidak perlu koreksi.");
            return 0;
        }

        $this->warn("Perlu kirim {$toSend}x Matching Bonus RO (masing-masing Rp 100.000) ke @{$sponsor->username}");

        if (!$this->confirm("Konfirmasi kirim koreksi?")) {
            return 0;
        }

        DB::transaction(function () use ($toSend, $sponsor, $member) {
            $totalBonus = $toSend * 100000;

            $sponsor->increment('saldo', $totalBonus);
            $sponsor->increment('total_bonus', $totalBonus);

            for ($i = 0; $i < $toSend; $i++) {
                $milestone = $i + 1;
                BonusLog::create([
                    'transaction_code' => 'ROMB' . sprintf('%04d', BonusLog::count() + 1),
                    'user_id' => $sponsor->id,
                    'category' => 'ro_matching',
                    'source_user_id' => $member->id,
                    'description' => "Matching Bonus RO dari @{$member->username} (Koreksi - Capai " . ($milestone * 35) . " Poin RO)",
                    'amount' => 100000,
                ]);

                WalletTransaction::create([
                    'user_id' => $sponsor->id,
                    'type' => 'in',
                    'category' => 'ro_matching',
                    'amount' => 100000,
                    'description' => "Matching Bonus RO dari @{$member->username} (Koreksi)",
                ]);
            }
        });

        $this->info("✅ Koreksi selesai! Rp " . number_format($toSend * 100000, 0, ',', '.') . " telah ditambahkan ke saldo @{$sponsor->username}");

        return 0;
    }
}
