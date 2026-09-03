<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BonusLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityController extends Controller
{
    /**
     * Display the Activity & Bonus Breakdown page.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $tab = $request->input('tab', 'sponsor');

        if (!in_array($tab, ['sponsor', 'generasi', 'ro', 'tpr', 'incentive', 'penarikan'])) {
            $tab = 'sponsor';
        }

        // Summary totals for cards
        $bonusSponsor = BonusLog::where('user_id', $user->id)->where('category', 'sponsor')->sum('amount');
        $bonusGenerasi = BonusLog::where('user_id', $user->id)->where('category', 'generasi')->sum('amount');
        $bonusRO = BonusLog::where('user_id', $user->id)->where('category', 'ro')->sum('amount');
        $bonusTPR = BonusLog::where('user_id', $user->id)->where('category', 'tpr')->sum('amount');
        $bonusIncentive = BonusLog::where('user_id', $user->id)->where('category', 'incentive')->sum('amount');

        // Tab Info Descriptions
        $tabDescriptions = [
            'sponsor' => 'Bonus Sponsor (Direct Referral 20%): Diberikan setiap kali Anda mereferensikan secara langsung member baru yang diaktifkan dengan VOUCHER.',
            'generasi' => 'Bonus Generasi (Tier Allocation): Diberikan dari alokasi pembagian tier generasi (Generasi 1 s/d Generasi 15) dari pendaftaran member di jaringan Anda.',
            'ro' => 'Bonus Repeat Order (RO): Diberikan dari setiap transaksi Repeat Order (RO) di jaringan Anda (Bonus Sponsor RO Rp 20.000 + Matching Bonus).',
            'tpr' => 'Bonus TPR: Diberikan dari alokasi program Trade Promotion Program (TPR) bulanan.',
            'incentive' => 'Diberikan atas pencapaian kamu dalam menjalankan bisnis Xseller yang mengacu pada total Income kamu',
            'penarikan' => 'Histori Penarikan Saldo: Rincian transaksi pencairan saldo dari e-wallet ke rekening bank Anda.',
        ];

        $totalIncome = $bonusSponsor + $bonusGenerasi + $bonusRO + $bonusTPR + $bonusIncentive;

        // Fetch logs for active tab
        $logs = BonusLog::with('sourceUser')
            ->where('user_id', $user->id)
            ->where('category', $tab)
            ->latest()
            ->get()
            ->map(function ($log) {
                $source = $log->sourceUser ? '@' . $log->sourceUser->username : '-';
                $code = $log->transaction_code ?? ('B' . str_pad($log->id, 3, '0', STR_PAD_LEFT));

                return [
                    'id' => $log->id,
                    'transaction_code' => $code,
                    'created_at' => $log->created_at->format('j/n/Y, H.i.s'),
                    'source' => $source,
                    'description' => $log->description,
                    'amount' => '+Rp ' . number_format($log->amount, 0, ',', '.'),
                    'qualified' => $log->qualified_amount ? 'Rp ' . number_format($log->qualified_amount, 0, ',', '.') : '-',
                    'incentive' => 'Rp ' . number_format($log->amount, 0, ',', '.'),
                    'status' => 'Klaim',
                    'date' => $log->created_at->format('d/m/Y'),
                ];
            });

        return Inertia::render('Admin/Activities', [
            'metrics' => [
                'bonus_sponsor' => (float) $bonusSponsor,
                'bonus_generasi' => (float) $bonusGenerasi,
                'bonus_ro' => (float) $bonusRO,
                'bonus_tpr' => (float) $bonusTPR,
                'bonus_incentive' => (float) $bonusIncentive,
            ],
            'user_income' => (float) $totalIncome,
            'active_tab' => $tab,
            'tab_description' => $tabDescriptions[$tab] ?? '',
            'logs' => $logs,
        ]);
    }
}
