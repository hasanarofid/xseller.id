<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class WithdrawalController extends Controller
{
    /**
     * Display the Withdrawal (WD) management page.
     */
    public function index()
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');

        // Calculate total approved and pending withdrawals
        $baseQuery = Withdrawal::query();
        if (!$isAdmin) {
            $baseQuery->where('user_id', $user->id);
        }

        $totalCair = (clone $baseQuery)->where('status', 'approved')->sum('amount');
        $totalProses = (clone $baseQuery)->where('status', 'pending')->sum('amount');

        // Fetch withdrawals list
        $withdrawalsQuery = Withdrawal::with('user')->latest();
        if (!$isAdmin) {
            $withdrawalsQuery->where('user_id', $user->id);
        }

        $withdrawals = $withdrawalsQuery->get()->map(function ($w) {
            return [
                'id' => $w->id,
                'user_name' => $w->user ? $w->user->name : 'Member',
                'user_username' => $w->user ? $w->user->username : 'member',
                'bank_name' => $w->bank_name,
                'bank_account_number' => $w->bank_account_number,
                'bank_account_name' => $w->bank_account_name,
                'amount' => (float) $w->amount,
                'fee' => (float) ($w->fee ?? 10000),
                'status' => $w->status, // 'pending', 'approved', 'rejected'
                'proof_of_transfer' => $w->proof_of_transfer ? (str_starts_with($w->proof_of_transfer, 'http') ? $w->proof_of_transfer : asset('storage/' . $w->proof_of_transfer)) : null,
                'admin_notes' => $w->admin_notes,
                'created_at' => $w->created_at->format('j/n/Y, H:i.s'),
            ];
        });

        return Inertia::render('Admin/Withdrawals', [
            'wallet' => [
                'saldo' => (float) ($user->saldo ?? 0),
                'min_withdrawal' => 50000,
                'admin_fee' => 10000,
                'total_cair' => (float) $totalCair,
                'total_proses' => (float) $totalProses,
            ],
            'user_bank' => [
                'bank_name' => $user->bank_name ?? 'Bank Mandiri',
                'bank_account_number' => $user->bank_account_number ?? '',
                'bank_account_name' => $user->bank_account_name ?? '',
            ],
            'withdrawals' => $withdrawals,
            'is_admin' => $isAdmin,
        ]);
    }

    /**
     * Submit a new withdrawal request (WD).
     */
    public function store(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string',
            'bank_account_number' => 'required|string',
            'bank_account_name' => 'required|string',
            'amount' => 'required|numeric|min:50000',
        ]);

        $user = auth()->user();
        $minWithdrawal = 50000;
        $fee = 10000;

        if ($request->amount < $minWithdrawal) {
            return back()->with('error', 'Nominal penarikan minimum adalah Rp ' . number_format($minWithdrawal, 0, ',', '.') . '!');
        }

        if (($user->saldo ?? 0) < $request->amount) {
            return back()->with('error', 'Saldo E-Wallet Anda tidak mencukupi untuk penarikan sebesar Rp ' . number_format($request->amount, 0, ',', '.') . '!');
        }

        DB::transaction(function () use ($user, $request, $fee) {
            // Reserve money from user saldo
            $user->decrement('saldo', $request->amount);

            // Save user bank profile
            $user->update([
                'bank_name' => $request->bank_name,
                'bank_account_number' => $request->bank_account_number,
                'bank_account_name' => $request->bank_account_name,
            ]);

            // Create withdrawal request
            $withdrawal = Withdrawal::create([
                'user_id' => $user->id,
                'bank_name' => $request->bank_name,
                'bank_account_number' => $request->bank_account_number,
                'bank_account_name' => $request->bank_account_name,
                'amount' => $request->amount,
                'fee' => $fee,
                'status' => 'pending',
            ]);

            // Record mutation transaction
            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'out',
                'category' => 'withdrawal',
                'amount' => $request->amount,
                'description' => 'Permohonan Penarikan Saldo (WD #' . $withdrawal->id . ') ke ' . $request->bank_name . ' (' . $request->bank_account_number . ') - Biaya Potongan: Rp ' . number_format($fee, 0, ',', '.'),
            ]);
        });

        return back()->with('success', 'Permohonan penarikan saldo (WD) sebesar Rp ' . number_format($request->amount, 0, ',', '.') . ' berhasil dikirim! Potongan biaya admin Rp 10.000.');
    }

    /**
     * Admin approves a withdrawal request.
     */
    public function approve(Request $request, Withdrawal $withdrawal)
    {
        $admin = auth()->user();

        if (!$admin->hasRole('admin')) {
            return back()->with('error', 'Hanya Admin yang dapat menyetujui permohonan penarikan saldo!');
        }

        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Permohonan penarikan ini sudah diproses sebelumnya.');
        }

        $request->validate([
            'proof_of_transfer' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        $proofPath = $withdrawal->proof_of_transfer;
        if ($request->hasFile('proof_of_transfer')) {
            $proofPath = $request->file('proof_of_transfer')->store('proofs', 'public');
        }

        $withdrawal->update([
            'status' => 'approved',
            'proof_of_transfer' => $proofPath,
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Penarikan saldo #' . $withdrawal->id . ' sebesar Rp ' . number_format($withdrawal->amount, 0, ',', '.') . ' berhasil disetujui!');
    }

    /**
     * Admin rejects a withdrawal request and refunds user balance.
     */
    public function reject(Request $request, Withdrawal $withdrawal)
    {
        $admin = auth()->user();

        if (!$admin->hasRole('admin')) {
            return back()->with('error', 'Hanya Admin yang dapat menolak permohonan penarikan saldo!');
        }

        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Permohonan penarikan ini sudah diproses sebelumnya.');
        }

        DB::transaction(function () use ($withdrawal, $request) {
            $withdrawal->update([
                'status' => 'rejected',
                'admin_notes' => $request->notes ?? 'Penarikan ditolak oleh Admin.',
                'processed_at' => now(),
            ]);

            // Refund balance back to user
            $withdrawal->user->increment('saldo', $withdrawal->amount);

            // Record refund mutation
            WalletTransaction::create([
                'user_id' => $withdrawal->user_id,
                'type' => 'in',
                'category' => 'withdrawal_refund',
                'amount' => $withdrawal->amount,
                'description' => 'Pengembalian dana penarikan saldo (WD #' . $withdrawal->id . ' ditolak)',
            ]);
        });

        return back()->with('success', 'Penarikan saldo #' . $withdrawal->id . ' ditolak dan dana telah dikembalikan ke saldo user.');
    }
}
