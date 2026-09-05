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

        if (!in_array($tab, ['sponsor', 'generasi', 'ro', 'po', 'pal', 'team_point', 'tpr', 'incentive', 'penarikan'])) {
            $tab = 'sponsor';
        }

        // Summary totals for cards
        $bonusSponsor = BonusLog::where('user_id', $user->id)->where('category', 'sponsor')->sum('amount');
        $bonusGenerasi = BonusLog::where('user_id', $user->id)->where('category', 'generasi')->sum('amount');
        $bonusRO = BonusLog::where('user_id', $user->id)->where('category', 'ro')->sum('amount');
        $bonusPO = BonusLog::where('user_id', $user->id)->where('category', 'po')->sum('amount');
        $bonusPAL = BonusLog::where('user_id', $user->id)->where('category', 'pal')->sum('amount');
        $bonusTPR = BonusLog::where('user_id', $user->id)->where('category', 'tpr')->sum('amount');
        $bonusIncentive = BonusLog::where('user_id', $user->id)->where('category', 'incentive')->sum('amount');
        $totalTeamPoints = (int) ($user->team_points ?? 0);

        // Tab Info Descriptions
        $tabDescriptions = [
            'sponsor' => 'Bonus Sponsor (Direct Referral 20%): Diberikan setiap kali Anda mereferensikan secara langsung member baru yang diaktifkan dengan VOUCHER.',
            'generasi' => 'Bonus Generasi (Tier Allocation): Diberikan dari alokasi pembagian tier generasi (Generasi 1 s/d Generasi 15) dari pendaftaran member di jaringan Anda.',
            'ro' => 'Bonus Repeat Order (RO): Diberikan dari setiap transaksi Repeat Order (RO) di jaringan Anda (Bonus Sponsor RO Rp 20.000 + Matching Bonus).',
            'po' => 'Bonus PO (Purchase Order): Diberikan dari alokasi 15 Generasi Tier transaksi Purchase Order (PO) di jaringan Anda.',
            'pal' => 'PAL Bonus (Personal Allocation Level): Bonus yang didapatkan dari Generasi 1 yang melakukan klaim Personal Poin PO (Rp 50.000 untuk Star Seller, Rp 200.000 untuk Affiliate).',
            'team_point' => 'Team Poin: Poin perolehan tim terakumulasi dari hasil pendaftaran member di jaringan Anda sesuai alokasi paket (Star Seller +1, Affiliate +4, Business +8, Partner +12).',
            'tpr' => 'Bonus TPR: Diberikan dari alokasi program Trade Promotion Program (TPR) bulanan.',
            'incentive' => 'Diberikan atas pencapaian kamu dalam menjalankan bisnis Xseller yang mengacu pada total Income kamu',
            'penarikan' => 'Histori Penarikan Saldo: Rincian transaksi pencairan saldo dari e-wallet ke rekening bank Anda.',
        ];

        $totalIncome = $bonusSponsor + $bonusGenerasi + $bonusRO + $bonusPO + $bonusPAL + $bonusTPR + $bonusIncentive;

        // Fetch logs for active tab
        if ($tab === 'penarikan') {
            $logs = \App\Models\Withdrawal::where('user_id', $user->id)
                ->latest()
                ->get()
                ->map(function ($w) {
                    return [
                        'id' => $w->id,
                        'transaction_code' => 'WD' . str_pad($w->id, 4, '0', STR_PAD_LEFT),
                        'created_at' => $w->created_at->format('j/n/Y, H.i.s'),
                        'source' => $w->bank_name . ' (' . $w->account_number . ')',
                        'description' => "Penarikan Saldo KE " . $w->account_name . " [" . strtoupper($w->status) . "]",
                        'amount' => '-Rp ' . number_format($w->amount, 0, ',', '.'),
                        'qualified' => '-',
                        'incentive' => 'Rp ' . number_format($w->amount, 0, ',', '.'),
                        'status' => ucfirst($w->status),
                        'date' => $w->created_at->format('d/m/Y'),
                    ];
                });
        } elseif ($tab === 'team_point') {
            $logs = BonusLog::with('sourceUser')
                ->where('user_id', $user->id)
                ->whereIn('category', ['sponsor', 'generasi', 'tier'])
                ->latest()
                ->get()
                ->map(function ($log) {
                    $source = $log->sourceUser ? '@' . $log->sourceUser->username : '-';
                    $code = $log->transaction_code ?? ('TP' . str_pad($log->id, 3, '0', STR_PAD_LEFT));
                    $pkg = $log->sourceUser ? ($log->sourceUser->package_name ?? 'Basic') : 'Basic';
                    $pts = str_contains(strtolower($pkg), 'partner') ? 12 : (str_contains(strtolower($pkg), 'business') ? 8 : (str_contains(strtolower($pkg), 'affiliate') ? 4 : 1));

                    return [
                        'id' => $log->id,
                        'transaction_code' => $code,
                        'created_at' => $log->created_at->format('j/n/Y, H.i.s'),
                        'source' => $source,
                        'description' => "Perolehan Team Poin dari member @{$source} (Paket {$pkg})",
                        'amount' => '+' . $pts . ' Poin',
                        'qualified' => '-',
                        'incentive' => $pts . ' Poin',
                        'status' => 'Klaim',
                        'date' => $log->created_at->format('d/m/Y'),
                    ];
                });
        } else {
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
        }

        return Inertia::render('Admin/Activities', [
            'metrics' => [
                'bonus_sponsor' => (float) $bonusSponsor,
                'bonus_generasi' => (float) $bonusGenerasi,
                'bonus_ro' => (float) $bonusRO,
                'bonus_po' => (float) $bonusPO,
                'bonus_pal' => (float) $bonusPAL,
                'bonus_tpr' => (float) $bonusTPR,
                'bonus_incentive' => (float) $bonusIncentive,
                'total_team_points' => $totalTeamPoints,
            ],
            'user_income' => (float) $totalIncome,
            'active_tab' => $tab,
            'tab_description' => $tabDescriptions[$tab] ?? '',
            'logs' => $logs,
        ]);
    }
}
