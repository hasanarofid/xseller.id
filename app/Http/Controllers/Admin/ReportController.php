<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\BonusLog;
use App\Models\Withdrawal;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    /**
     * Display the Reports dashboard.
     */
    public function index(Request $request)
    {
        $type = $request->input('type', 'member');
        if (!in_array($type, ['member', 'bonus', 'pencairan', 'topup'])) {
            $type = 'member';
        }

        $data = $this->getReportData($type);

        return Inertia::render('Admin/Reports', [
            'active_type' => $type,
            'report_data' => $data,
        ]);
    }

    /**
     * Get array data based on report type.
     */
    private function getReportData($type)
    {
        if ($type === 'member') {
            return User::with('parent')->orderBy('id', 'asc')->get()->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'username' => $u->username ?? 'user' . $u->id,
                    'email' => $u->email ?? '-',
                    'sponsor' => $u->parent ? 'USR' . str_pad($u->parent->id, 3, '0', STR_PAD_LEFT) : '-',
                    'left_count' => (int) ($u->left_count ?? 0),
                    'right_count' => (int) ($u->right_count ?? 0),
                    'saldo' => (float) ($u->saldo ?? 0),
                    'created_at' => $u->created_at ? $u->created_at->format('d/m/Y H:i') : '-',
                ];
            });
        }

        if ($type === 'bonus') {
            return BonusLog::with(['user', 'sourceUser'])->latest()->get()->map(function ($b) {
                return [
                    'id' => $b->id,
                    'name' => $b->user ? $b->user->name : 'Member',
                    'username' => $b->user ? $b->user->username : 'user',
                    'email' => $b->user ? $b->user->email : '-',
                    'category' => ucfirst($b->category),
                    'source' => $b->sourceUser ? '@' . $b->sourceUser->username : '-',
                    'description' => $b->description,
                    'amount' => (float) $b->amount,
                    'created_at' => $b->created_at ? $b->created_at->format('d/m/Y H:i') : '-',
                ];
            });
        }

        if ($type === 'pencairan') {
            return Withdrawal::with('user')->latest()->get()->map(function ($w) {
                return [
                    'id' => $w->id,
                    'name' => $w->user ? $w->user->name : 'Member',
                    'username' => $w->user ? $w->user->username : 'user',
                    'email' => $w->user ? $w->user->email : '-',
                    'bank_name' => $w->bank_name,
                    'bank_account_number' => $w->bank_account_number,
                    'bank_account_name' => $w->bank_account_name,
                    'amount' => (float) $w->amount,
                    'status' => strtoupper($w->status),
                    'created_at' => $w->created_at ? $w->created_at->format('d/m/Y H:i') : '-',
                ];
            });
        }

        if ($type === 'topup') {
            return WalletTransaction::with('user')->latest()->get()->map(function ($t) {
                return [
                    'id' => $t->id,
                    'name' => $t->user ? $t->user->name : 'Member',
                    'username' => $t->user ? $t->user->username : 'user',
                    'email' => $t->user ? $t->user->email : '-',
                    'category' => ucfirst($t->category),
                    'description' => $t->description,
                    'type' => $t->type === 'in' ? 'MASUK' : 'KELUAR',
                    'amount' => (float) $t->amount,
                    'created_at' => $t->created_at ? $t->created_at->format('d/m/Y H:i') : '-',
                ];
            });
        }

        return [];
    }

    /**
     * Export data to native Excel spreadsheet format (.xlsx).
     */
    public function exportExcel(Request $request)
    {
        $type = $request->input('type', 'member');
        $filename = "Laporan_" . ucfirst($type) . "_" . date('Y-m-d') . ".xlsx";
        $data = $this->getReportData($type);

        $headers = [
            "Content-Type" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        $html .= '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
        $html .= '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Laporan</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
        $html .= '<style>';
        $html .= 'th { background-color: #0d131d; color: #ffffff; font-weight: bold; text-align: left; padding: 10px; border: 1px solid #000000; }';
        $html .= 'td { padding: 8px; border: 1px solid #cccccc; vertical-align: middle; }';
        $html .= '.title { font-size: 16px; font-weight: bold; color: #0d131d; margin-bottom: 15px; }';
        $html .= '.number { text-align: right; font-weight: bold; }';
        $html .= '</style></head><body>';

        $html .= '<div class="title">LAPORAN ' . strtoupper($type) . ' - XSELLER.ID (' . date('d/m/Y H:i') . ')</div>';
        $html .= '<table>';

        if ($type === 'member') {
            $html .= '<thead><tr><th>ID</th><th>NAMA LENGKAP</th><th>USERNAME</th><th>EMAIL</th><th>SPONSOR</th><th>SALDO WALLET (RP)</th><th>TGL DAFTAR</th></tr></thead><tbody>';
            foreach ($data as $row) {
                $html .= '<tr>';
                $html .= '<td>USR' . str_pad($row['id'], 3, '0', STR_PAD_LEFT) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['name']) . '</td>';
                $html .= '<td>@' . htmlspecialchars($row['username']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['email'] ?? '-') . '</td>';
                $html .= '<td>' . htmlspecialchars($row['sponsor']) . '</td>';
                $html .= '<td class="number">Rp ' . number_format($row['saldo'], 0, ',', '.') . '</td>';
                $html .= '<td>' . $row['created_at'] . '</td>';
                $html .= '</tr>';
            }
        } elseif ($type === 'bonus') {
            $html .= '<thead><tr><th>ID</th><th>NAMA MEMBER</th><th>USERNAME</th><th>JENIS BONUS</th><th>SUMBER MEMBER</th><th>DESKRIPSI</th><th>NOMINAL (RP)</th><th>TANGGAL</th></tr></thead><tbody>';
            foreach ($data as $row) {
                $html .= '<tr>';
                $html .= '<td>B' . str_pad($row['id'], 3, '0', STR_PAD_LEFT) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['name']) . '</td>';
                $html .= '<td>@' . htmlspecialchars($row['username']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['category']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['source']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['description']) . '</td>';
                $html .= '<td class="number">Rp ' . number_format($row['amount'], 0, ',', '.') . '</td>';
                $html .= '<td>' . $row['created_at'] . '</td>';
                $html .= '</tr>';
            }
        } elseif ($type === 'pencairan') {
            $html .= '<thead><tr><th>ID</th><th>NAMA MEMBER</th><th>USERNAME</th><th>BANK TUJUAN</th><th>NO REKENING</th><th>PEMILIK REKENING</th><th>NOMINAL WD (RP)</th><th>STATUS</th><th>TANGGAL</th></tr></thead><tbody>';
            foreach ($data as $row) {
                $html .= '<tr>';
                $html .= '<td>WD' . str_pad($row['id'], 3, '0', STR_PAD_LEFT) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['name']) . '</td>';
                $html .= '<td>@' . htmlspecialchars($row['username']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['bank_name']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['bank_account_number']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['bank_account_name']) . '</td>';
                $html .= '<td class="number">Rp ' . number_format($row['amount'], 0, ',', '.') . '</td>';
                $html .= '<td>' . htmlspecialchars($row['status']) . '</td>';
                $html .= '<td>' . $row['created_at'] . '</td>';
                $html .= '</tr>';
            }
        } elseif ($type === 'topup') {
            $html .= '<thead><tr><th>ID</th><th>NAMA MEMBER</th><th>USERNAME</th><th>KATEGORI</th><th>TIPE</th><th>DESKRIPSI</th><th>NOMINAL (RP)</th><th>TANGGAL</th></tr></thead><tbody>';
            foreach ($data as $row) {
                $html .= '<tr>';
                $html .= '<td>TX' . str_pad($row['id'], 3, '0', STR_PAD_LEFT) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['name']) . '</td>';
                $html .= '<td>@' . htmlspecialchars($row['username']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['category']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['type']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['description']) . '</td>';
                $html .= '<td class="number">Rp ' . number_format($row['amount'], 0, ',', '.') . '</td>';
                $html .= '<td>' . $row['created_at'] . '</td>';
                $html .= '</tr>';
            }
        }

        $html .= '</tbody></table></body></html>';

        return response($html, 200, $headers);
    }

    /**
     * Export data to PDF / Printable View.
     */
    public function exportPdf(Request $request)
    {
        $type = $request->input('type', 'member');
        $data = $this->getReportData($type);
        $title = "Laporan " . ucfirst($type);
        $date = date('d F Y, H:i');

        return response()->view('reports.pdf', compact('title', 'type', 'data', 'date'));
    }
}
