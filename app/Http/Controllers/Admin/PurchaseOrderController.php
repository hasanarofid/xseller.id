<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BonusLog;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Voucher;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PurchaseOrderController extends Controller
{
    /**
     * Display the Purchase Order page.
     */
    public function index()
    {
        $user = auth()->user();

        // Get user's available active PO vouchers
        $availablePoVouchers = Voucher::where('user_id', $user->id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->where('voucher_type', 'LIKE', '%po%')
                  ->orWhere('package_name', 'LIKE', '%PO%')
                  ->orWhere('package_name', 'LIKE', '%Star Seller%')
                  ->orWhere('package_name', 'LIKE', '%Affiliate%')
                  ->orWhere('package_name', 'LIKE', '%550%')
                  ->orWhere('package_name', 'LIKE', '%2.100%')
                  ->orWhere('package_name', 'LIKE', '%2100%');
            })
            ->get(['id', 'code', 'package_name', 'voucher_type']);

        // Count Star Seller & Affiliate Vouchers
        $starSellerCount = $availablePoVouchers->filter(function ($v) {
            return str_contains(strtolower($v->package_name), 'star seller') 
                || str_contains(strtolower($v->package_name), '550') 
                || $v->voucher_type === 'po_star_seller';
        })->count();

        $affiliateCount = $availablePoVouchers->filter(function ($v) {
            return str_contains(strtolower($v->package_name), 'affiliate') 
                || str_contains(strtolower($v->package_name), '2.100') 
                || str_contains(strtolower($v->package_name), '2100') 
                || $v->voucher_type === 'po_affiliate';
        })->count();

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

        // Company Bank Details
        $settings = \App\Models\Setting::all()->pluck('value', 'key');
        $companyBanks = json_decode($settings['company_banks'] ?? '[]', true);
        $companyBank = (is_array($companyBanks) && count($companyBanks) > 0) 
            ? $companyBanks[0] 
            : [
                'bank_name' => 'Bank BRI',
                'account_number' => '806401000095564',
                'account_name' => 'PT.Xseller Punya Kita',
            ];

        $poProducts = \App\Models\Product::where('type', 'po')->where('is_active', true)->get();

        return Inertia::render('Admin/PurchaseOrder/Index', [
            'po_stats' => [
                'total_po_points' => $totalPoPoints,
                'total_orders' => $purchaseOrders->count(),
                'total_spent' => (float) $purchaseOrders->sum('amount'),
                'star_seller_count' => $starSellerCount,
                'affiliate_count' => $affiliateCount,
                'total_po_vouchers_count' => $availablePoVouchers->count(),
            ],
            'available_po_vouchers' => $availablePoVouchers,
            'purchase_orders' => $purchaseOrders,
            'user_saldo' => (float) ($user->saldo ?? 0),
            'company_bank' => $companyBank,
            'is_admin' => $user->hasRole('admin'),
            'products' => $poProducts,
        ]);
    }

    /**
     * Process Purchase Order (PO) Activation using Voucher PO.
     */
    public function store(Request $request)
    {
        $request->validate([
            'voucher_code' => 'required|string|exists:vouchers,code',
        ]);

        $user = auth()->user();

        // Verify voucher ownership & status
        $voucher = Voucher::where('code', $request->voucher_code)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$voucher) {
            return back()->with('error', 'Voucher PO tidak ditemukan, telah digunakan, atau bukan milik Anda!');
        }

        // Determine package configuration based on voucher name/type
        $pkgName = $voucher->package_name;
        $vType = $voucher->voucher_type;

        $isAffiliate = str_contains(strtolower($pkgName), 'affiliate') 
            || str_contains(strtolower($pkgName), '2.100') 
            || str_contains(strtolower($pkgName), '2100') 
            || $vType === 'po_affiliate';

        if ($isAffiliate) {
            $packageName = 'PO Paket Affiliate (Rp 2.100.000)';
            $amount = 2100000;
            $poPoints = 8;
            $tierAmount = 50000;
        } else {
            $packageName = 'PO Paket Star Seller (Rp 550.000)';
            $amount = 550000;
            $poPoints = 2;
            $tierAmount = 10000;
        }

        DB::transaction(function () use ($user, $voucher, $packageName, $amount, $poPoints, $tierAmount) {
            // 1. Mark voucher as used
            $voucher->update([
                'status' => 'used',
                'used_by_id' => $user->id,
                'used_at' => now(),
            ]);

            // 2. Increment Personal Poin PO
            $user->increment('po_points', $poPoints);

            // 3. Create PurchaseOrder record
            PurchaseOrder::create([
                'user_id' => $user->id,
                'package_name' => $packageName,
                'amount' => $amount,
                'po_points' => $poPoints,
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

                $upline->increment('saldo', $tierAmount);
                $upline->increment('total_bonus', $tierAmount);

                BonusLog::create([
                    'transaction_code' => 'PO' . sprintf('%04d', BonusLog::count() + 1),
                    'user_id' => $upline->id,
                    'category' => 'tier',
                    'source_user_id' => $user->id,
                    'description' => "Bonus Tier PO Generasi {$gen} dari @{$user->username} ({$packageName})",
                    'amount' => $tierAmount,
                ]);

                WalletTransaction::create([
                    'user_id' => $upline->id,
                    'type' => 'in',
                    'category' => 'bonus_tier',
                    'amount' => $tierAmount,
                    'description' => "Bonus Tier PO Generasi {$gen} dari @{$user->username}",
                ]);

                $currentUpline = $upline;
            }
        });

        return back()->with('success', "Berhasil melakukan Aktivasi Purchase Order ({$packageName})! Anda mendapatkan +{$poPoints} Poin PO dan alokasi 15 Generasi berhasil didistribusikan.");
    }

    /**
     * Purchase or produce Voucher PO (Star Seller Rp 550.000 / Affiliate Rp 2.100.000, 1-35 pcs).
     */
    public function buyVoucher(Request $request)
    {
        $request->validate([
            'package_type' => 'required|string|in:star_seller,affiliate',
            'quantity' => 'nullable|integer|min:1|max:35',
        ]);

        $user = auth()->user();
        $qty = max(1, min(35, (int) $request->input('quantity', 1)));

        if ($request->package_type === 'affiliate') {
            $unitPrice = 2100000;
            $pkgName = 'PO Paket Affiliate (Rp 2.100.000)';
            $vType = 'po_affiliate';
        } else {
            $unitPrice = 550000;
            $pkgName = 'PO Paket Star Seller (Rp 550.000)';
            $vType = 'po_star_seller';
        }

        $totalPrice = $unitPrice * $qty;

        if ($request->boolean('is_produce') && $user->hasRole('admin')) {
            // Admin produce free Voucher PO
            $targetUser = $user;
            if ($request->filled('target_username')) {
                $targetUser = User::where('username', $request->target_username)->first();
                if (!$targetUser) {
                    return back()->with('error', 'Username penerima tidak ditemukan!');
                }
            }

            DB::transaction(function () use ($targetUser, $pkgName, $vType, $qty) {
                for ($i = 0; $i < $qty; $i++) {
                    $code = 'PO-' . rand(1000, 9999) . '-' . strtoupper(Str::random(3));
                    Voucher::create([
                        'code' => $code,
                        'user_id' => $targetUser->id,
                        'package_name' => $pkgName,
                        'voucher_type' => $vType,
                        'status' => 'active',
                    ]);
                }
            });

            return back()->with('success', "Berhasil memproduksi {$qty} Voucher {$pkgName} untuk @" . $targetUser->username . "!");
        }

        // Member purchase using wallet balance
        if (($user->saldo ?? 0) < $totalPrice) {
            return back()->with('error', "Saldo wallet Anda tidak mencukupi untuk membeli {$qty} Voucher {$pkgName} (Total: Rp " . number_format($totalPrice, 0, ',', '.') . ")!");
        }

        DB::transaction(function () use ($user, $pkgName, $vType, $totalPrice, $qty) {
            $user->decrement('saldo', $totalPrice);

            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'out',
                'category' => 'purchase_voucher',
                'amount' => $totalPrice,
                'description' => "Pembelian {$qty} Voucher {$pkgName}",
            ]);

            for ($i = 0; $i < $qty; $i++) {
                $code = 'PO-' . rand(1000, 9999) . '-' . strtoupper(Str::random(3));
                Voucher::create([
                    'code' => $code,
                    'user_id' => $user->id,
                    'package_name' => $pkgName,
                    'voucher_type' => $vType,
                    'status' => 'active',
                ]);
            }
        });

        return back()->with('success', "Berhasil membeli {$qty} Voucher {$pkgName}!");
    }
}
