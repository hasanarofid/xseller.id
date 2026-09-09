<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BonusLog;
use App\Models\TeamPointRedemption;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TeamPointRedemptionController extends Controller
{
    /**
     * Admin: List all pending team point redemption requests.
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user->hasRole('admin')) {
            abort(403);
        }

        $redemptions = TeamPointRedemption::with('user')
            ->latest()
            ->get()
            ->map(function ($r) {
                return [
                    'id'            => $r->id,
                    'user_name'     => $r->user?->name ?? '-',
                    'user_username' => $r->user?->username ?? '-',
                    'points_used'   => $r->points_used,
                    'reward_amount' => (float) $r->reward_amount,
                    'status'        => $r->status,
                    'admin_notes'   => $r->admin_notes,
                    'created_at'    => $r->created_at->format('j/n/Y, H:i.s'),
                    'processed_at'  => $r->processed_at?->format('j/n/Y, H:i.s'),
                ];
            });

        $summary = [
            'total_pending'  => TeamPointRedemption::where('status', 'pending')->count(),
            'total_approved' => TeamPointRedemption::where('status', 'approved')->count(),
        ];

        return Inertia::render('Admin/TeamPointRedemptions', [
            'redemptions' => $redemptions,
            'summary'     => $summary,
        ]);
    }

    /**
     * Member: Submit a Team Point redemption request.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $currentPoints = (int) ($user->team_points ?? 0);

        // Cek poin mencukupi (minimal 35)
        $bracket = TeamPointRedemption::rewardForPoints($currentPoints);
        if (!$bracket) {
            return back()->with('error', 'Poin Team Anda belum mencukupi. Minimal 35 Poin untuk klaim reward.');
        }

        // Cek apakah sudah ada request pending
        $hasPending = TeamPointRedemption::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            return back()->with('error', 'Anda masih memiliki permintaan Klaim Team Poin yang sedang menunggu approval Admin.');
        }

        TeamPointRedemption::create([
            'user_id'       => $user->id,
            'points_used'   => $bracket['points'],
            'reward_amount' => $bracket['reward'],
            'status'        => 'pending',
        ]);

        return back()->with('success', "Berhasil mengajukan klaim reward {$bracket['points']} Team Poin = Rp " . number_format($bracket['reward'], 0, ',', '.') . "! Menunggu approval Admin.");
    }

    /**
     * Admin: Approve a redemption request.
     */
    public function approve(Request $request, TeamPointRedemption $redemption)
    {
        $admin = auth()->user();
        if (!$admin->hasRole('admin')) {
            abort(403);
        }

        if ($redemption->status !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah diproses sebelumnya.');
        }

        $member = $redemption->user;

        DB::transaction(function () use ($redemption, $member) {
            $pointsUsed   = $redemption->points_used;
            $rewardAmount = $redemption->reward_amount;

            // Kurangi team_points sesuai bracket, sisakan jika > 350 (HOLD)
            $currentPoints = (int) ($member->team_points ?? 0);
            $remaining = $currentPoints - $pointsUsed;

            // Rule: reset poin jika total akumulasi ≤ 350; lebih dari itu, sisa di-hold
            $newPoints = $remaining >= 0 ? $remaining : 0;
            $member->update(['team_points' => $newPoints]);

            // Tambah saldo member
            $member->increment('saldo', $rewardAmount);
            $member->increment('total_bonus', $rewardAmount);

            // Catat wallet transaction
            WalletTransaction::create([
                'user_id'     => $member->id,
                'type'        => 'in',
                'category'    => 'reward',
                'amount'      => $rewardAmount,
                'description' => "Reward Team Poin {$pointsUsed} Poin - disetujui Admin",
            ]);

            // Catat bonus log
            BonusLog::create([
                'transaction_code' => 'TP' . sprintf('%04d', BonusLog::count() + 1),
                'user_id'          => $member->id,
                'category'         => 'reward',
                'source_user_id'   => null,
                'description'      => "Reward Klaim Team Poin ({$pointsUsed} Poin) = Rp " . number_format($rewardAmount, 0, ',', '.'),
                'amount'           => $rewardAmount,
            ]);

            // Update redemption status
            $redemption->update([
                'status'       => 'approved',
                'processed_at' => now(),
            ]);
        });

        return back()->with('success', "Reward Team Poin untuk @{$member->username} sebesar Rp " . number_format($redemption->reward_amount, 0, ',', '.') . " berhasil disetujui dan saldo sudah masuk!");
    }

    /**
     * Admin: Reject a redemption request.
     */
    public function reject(Request $request, TeamPointRedemption $redemption)
    {
        $admin = auth()->user();
        if (!$admin->hasRole('admin')) {
            abort(403);
        }

        if ($redemption->status !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah diproses sebelumnya.');
        }

        $redemption->update([
            'status'       => 'rejected',
            'admin_notes'  => $request->input('admin_notes', 'Ditolak oleh Admin'),
            'processed_at' => now(),
        ]);

        return back()->with('success', "Permintaan klaim Team Poin @{$redemption->user?->username} berhasil ditolak.");
    }
}
