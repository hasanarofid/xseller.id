<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TprRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TprController extends Controller
{
    /**
     * Display Open TPR Feature page.
     */
    public function index()
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');
        $userPackage = strtolower($user->package_name ?? '');

        // Determine if member is eligible for TPR Feature (Package 4,3 Juta or 10,5 Juta or Admin)
        $isPro = str_contains($userPackage, '4.300') || str_contains($userPackage, '4300') || str_contains($userPackage, 'pro');
        $isUltimate = str_contains($userPackage, '10.500') || str_contains($userPackage, '10500') || str_contains($userPackage, 'ultimate');
        $isEligible = $isPro || $isUltimate || $isAdmin;

        // Determine allowed package option for member request
        $allowedOptions = [];
        if ($isPro || $isAdmin) {
            $allowedOptions[] = [
                'package_name' => 'Paket Rp 4.300.000',
                'amount' => 4300000,
                'monthly_share_percent' => 7,
                'monthly_share_amount' => 301000,
                'duration_months' => 3,
                'description' => 'Profit Share 7% per bulan selama 3 bulan (Rp 301.000 / bulan)',
            ];
        }
        if ($isUltimate || $isAdmin) {
            $allowedOptions[] = [
                'package_name' => 'Paket Rp 10.500.000',
                'amount' => 10500000,
                'monthly_share_percent' => 9,
                'monthly_share_amount' => 945000,
                'duration_months' => 3,
                'description' => 'Profit Share 9% per bulan selama 3 bulan (Rp 945.000 / bulan)',
            ];
        }

        // Fetch TPR Requests list
        $query = TprRequest::with('user')->latest();
        if (!$isAdmin) {
            $query->where('user_id', $user->id);
        }

        $requests = $query->get()->map(function ($r) {
            return [
                'id' => $r->id,
                'user_name' => $r->user ? $r->user->name : 'Member',
                'user_username' => $r->user ? $r->user->username : 'member',
                'package_name' => $r->package_name,
                'amount' => (float) $r->amount,
                'monthly_share_percent' => (float) $r->monthly_share_percent,
                'monthly_share_amount' => (float) $r->monthly_share_amount,
                'status' => $r->status,
                'proof_of_transfer' => $r->proof_of_transfer ? (str_starts_with($r->proof_of_transfer, 'http') ? $r->proof_of_transfer : asset('storage/' . $r->proof_of_transfer)) : null,
                'admin_notes' => $r->admin_notes,
                'created_at' => $r->created_at->format('j/n/Y, H:i'),
                'approved_at' => $r->approved_at ? $r->approved_at->format('j/n/Y, H:i') : null,
            ];
        });

        return Inertia::render('Admin/Tpr/Index', [
            'is_eligible' => $isEligible,
            'is_admin' => $isAdmin,
            'user_package' => $user->package_name ?? 'Basic',
            'allowed_options' => $allowedOptions,
            'requests' => $requests,
        ]);
    }

    /**
     * Submit a new TPR Request with proof of transfer.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $userPackage = strtolower($user->package_name ?? '');
        $isAdmin = $user->hasRole('admin');

        $isPro = str_contains($userPackage, '4.300') || str_contains($userPackage, '4300') || str_contains($userPackage, 'pro');
        $isUltimate = str_contains($userPackage, '10.500') || str_contains($userPackage, '10500') || str_contains($userPackage, 'ultimate');

        if (!$isPro && !$isUltimate && !$isAdmin) {
            return back()->with('error', 'Fitur TPR hanya tersedia untuk member Paket Rp 4.300.000 dan Rp 10.500.000!');
        }

        $request->validate([
            'amount' => 'required|numeric|in:4300000,10500000',
            'proof_of_transfer' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        $amount = (float) $request->amount;
        if ($amount == 4300000) {
            $packageName = 'Paket Rp 4.300.000';
            $percent = 7;
            $shareAmount = 301000;
        } else {
            $packageName = 'Paket Rp 10.500.000';
            $percent = 9;
            $shareAmount = 945000;
        }

        $proofPath = $request->file('proof_of_transfer')->store('tpr_proofs', 'public');

        TprRequest::create([
            'user_id' => $user->id,
            'package_name' => $packageName,
            'amount' => $amount,
            'monthly_share_percent' => $percent,
            'monthly_share_amount' => $shareAmount,
            'proof_of_transfer' => $proofPath,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Pengajuan Trade Promotion Program (TPR) sebesar Rp ' . number_format($amount, 0, ',', '.') . ' berhasil dikirim! Menunggu verifikasi Admin.');
    }

    /**
     * Admin approves a TPR Request.
     */
    public function approve(TprRequest $tprRequest)
    {
        $admin = auth()->user();

        if (!$admin->hasRole('admin')) {
            return back()->with('error', 'Hanya Admin yang dapat menyetujui pengajuan TPR!');
        }

        if ($tprRequest->status !== 'pending') {
            return back()->with('error', 'Pengajuan TPR ini sudah diproses sebelumnya.');
        }

        $tprRequest->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Pengajuan TPR #' . $tprRequest->id . ' berhasil disetujui! Bagi hasil bulanan selama 3 bulan diaktifkan.');
    }

    /**
     * Admin rejects a TPR Request.
     */
    public function reject(Request $request, TprRequest $tprRequest)
    {
        $admin = auth()->user();

        if (!$admin->hasRole('admin')) {
            return back()->with('error', 'Hanya Admin yang dapat menolak pengajuan TPR!');
        }

        if ($tprRequest->status !== 'pending') {
            return back()->with('error', 'Pengajuan TPR ini sudah diproses sebelumnya.');
        }

        $tprRequest->update([
            'status' => 'rejected',
            'admin_notes' => $request->notes ?? 'Pengajuan TPR ditolak oleh Admin.',
        ]);

        return back()->with('success', 'Pengajuan TPR #' . $tprRequest->id . ' ditolak.');
    }
}
