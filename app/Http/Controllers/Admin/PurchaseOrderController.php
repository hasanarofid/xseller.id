<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BonusLog;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PurchaseOrderController extends Controller
{
    /**
     * Display the Purchase Order page.
     */
    public function index()
    {
        $user = auth()->user();

        // Get PO history
        $purchaseOrders = PurchaseOrder::where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($po) {
                return [
                    'id' => $po->id,
                    'package_name' => $po->package_name,
                    'amount' => (float) $po->amount,
                    'po_points' => (int) $po->po_points,
                    'created_at' => $po->created_at->format('d/m/Y H:i'),
                ];
            });

        // Calculate total personal PO points
        $totalPoPoints = (int) ($user->po_points ?? 0);

        // Define available PO package options
        $poPackages = [
            [
                'id' => 'po_550',
                'name' => 'PO Paket Basic (Rp 550.000)',
                'amount' => 550000,
                'po_points' => 2,
                'tier_allocation' => 10000, // Rp 10.000 / gen for 15 generations
                'description' => '+3 Botol/Pouch/Box Produk + 2 Poin PO + Alokasi 15 Generasi (Rp 10.000/gen)',
            ],
            [
                'id' => 'po_2100',
                'name' => 'PO Paket Medium (Rp 2.100.000)',
                'amount' => 2100000,
                'po_points' => 8,
                'tier_allocation' => 50000, // Rp 50.000 / gen for 15 generations
                'description' => '+10 Botol/Pouch/Box Produk + 8 Poin PO + Alokasi 15 Generasi (Rp 50.000/gen)',
            ],
        ];

        return Inertia::render('Admin/PurchaseOrder/Index', [
            'po_stats' => [
                'total_po_points' => $totalPoPoints,
                'total_orders' => $purchaseOrders->count(),
                'total_spent' => (float) $purchaseOrders->sum('amount'),
            ],
            'po_packages' => $poPackages,
            'purchase_orders' => $purchaseOrders,
            'user_saldo' => (float) ($user->saldo ?? 0),
            'is_admin' => $user->hasRole('admin'),
        ]);
    }

    /**
     * Process Purchase Order (PO).
     */
    public function store(Request $request)
    {
        $request->validate([
            'package_id' => 'required|string|in:po_550,po_2100',
        ]);

        $user = auth()->user();

        $pkgConfig = [
            'po_550' => [
                'name' => 'PO Paket Basic (Rp 550.000)',
                'amount' => 550000,
                'po_points' => 2,
                'tier_amount' => 10000,
            ],
            'po_2100' => [
                'name' => 'PO Paket Medium (Rp 2.100.000)',
                'amount' => 2100000,
                'po_points' => 8,
                'tier_amount' => 50000,
            ],
        ];

        $selected = $pkgConfig[$request->package_id];
        $amount = $selected['amount'];

        if (($user->saldo ?? 0) < $amount) {
            return back()->with('error', 'Saldo wallet Anda tidak mencukupi untuk melakukan Purchase Order ini!');
        }

        DB::transaction(function () use ($user, $selected, $amount) {
            // 1. Deduct user saldo
            $user->decrement('saldo', $amount);

            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'out',
                'category' => 'purchase_order',
                'amount' => $amount,
                'description' => "Transaksi {$selected['name']}",
            ]);

            // 2. Increment Personal Poin PO
            $user->increment('po_points', $selected['po_points']);

            // 3. Create PurchaseOrder record
            PurchaseOrder::create([
                'user_id' => $user->id,
                'package_name' => $selected['name'],
                'amount' => $amount,
                'po_points' => $selected['po_points'],
            ]);

            // 4. Distribute 15 Generasi Tier Allocation to Uplines
            $currentUpline = $user;
            for ($gen = 1; $gen <= 15; $gen++) {
                if (!$currentUpline->parent_id) {
                    break;
                }

                $upline = User::find($currentUpline->parent_id);
                if (!$upline) {
                    break;
                }

                $tierBonus = $selected['tier_amount'];
                $upline->increment('saldo', $tierBonus);
                $upline->increment('total_bonus', $tierBonus);

                BonusLog::create([
                    'transaction_code' => 'PO' . sprintf('%04d', BonusLog::count() + 1),
                    'user_id' => $upline->id,
                    'category' => 'tier',
                    'source_user_id' => $user->id,
                    'description' => "Bonus Tier PO Generasi {$gen} dari @{$user->username} ({$selected['name']})",
                    'amount' => $tierBonus,
                ]);

                WalletTransaction::create([
                    'user_id' => $upline->id,
                    'type' => 'in',
                    'category' => 'bonus_tier',
                    'amount' => $tierBonus,
                    'description' => "Bonus Tier PO Generasi {$gen} dari @{$user->username}",
                ]);

                $currentUpline = $upline;
            }
        });

        return back()->with('success', "Berhasil melakukan Purchase Order ({$selected['name']})! Anda mendapatkan +{$selected['po_points']} Poin PO dan alokasi 15 Generasi berhasil didistribusikan.");
    }
}
