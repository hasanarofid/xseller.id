<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BonusLog;
use App\Models\RepeatOrder;
use App\Models\User;
use App\Models\Voucher;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class RepeatOrderController extends Controller
{
    /**
     * Display the Repeat Order page.
     */
    public function index()
    {
        $user = auth()->user();

        // Get user's available active RO vouchers (Regular RO & Cashback RO)
        $availableRoVouchers = Voucher::where('user_id', $user->id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereIn('voucher_type', ['ro', 'ro_cashback', 'ro_cash'])
                  ->orWhere('package_name', 'LIKE', '%Repeat Order%')
                  ->orWhere('package_name', 'LIKE', '%Cashback RO%')
                  ->orWhere('package_name', 'LIKE', '%Cash RO%')
                  ->orWhere('package_name', 'LIKE', '%RO%')
                  ->orWhere('package_name', 'LIKE', '%125%')
                  ->orWhere('package_name', 'LIKE', '%4.375%');
            })
            ->get(['id', 'code', 'package_name', 'voucher_type'])
            ->map(function ($v) {
                $isCashback = in_array($v->voucher_type, ['ro_cashback', 'ro_cash'])
                    || str_contains(strtolower($v->package_name ?? ''), 'cash')
                    || str_contains($v->package_name ?? '', '4.375');

                $pkgName = $isCashback
                    ? 'Voucher Cash RO (Rp 4.375.000)'
                    : 'Voucher RO (Rp 125.000)';

                return [
                    'id' => $v->id,
                    'code' => $v->code,
                    'package_name' => $pkgName,
                    'voucher_type' => $isCashback ? 'ro_cashback' : 'ro',
                    'type_label' => $isCashback ? 'Voucher Cash RO (35 Poin)' : 'Voucher RO (1 Poin)',
                    'points' => $isCashback ? 35 : 1,
                ];
            });

        // Count active RO vouchers
        $regularRoCount = $availableRoVouchers->where('voucher_type', 'ro')->count();
        $cashbackRoCount = $availableRoVouchers->where('voucher_type', 'ro_cashback')->count();
        $activeRoVoucherCount = $availableRoVouchers->count();

        // Get user's RO history
        $repeatOrders = RepeatOrder::where('user_id', $user->id)
            ->orWhere('sponsor_id', $user->id)
            ->with(['user', 'sponsor', 'voucher'])
            ->latest()
            ->get()
            ->map(function ($ro) use ($user) {
                $isUser = $ro->user_id === $user->id;
                return [
                    'id' => $ro->id,
                    'user_name' => $ro->user ? $ro->user->name . ' (@' . $ro->user->username . ')' : '-',
                    'sponsor_name' => $ro->sponsor ? $ro->sponsor->name . ' (@' . $ro->sponsor->username . ')' : '-',
                    'voucher_code' => $ro->voucher_code,
                    'ro_points' => $ro->ro_points,
                    'sponsor_bonus' => (float) $ro->sponsor_bonus,
                    'is_user' => $isUser,
                    'type_label' => $isUser 
                        ? ($ro->ro_points >= 35 ? 'Klaim Cash RO (35 Poin)' : 'Repeat Order Klaim (1 Poin)') 
                        : ($ro->ro_points >= 35 ? 'Bonus Sponsor Cash RO' : 'Bonus Sponsor RO'),
                    'created_at' => $ro->created_at->format('d/m/Y H:i'),
                ];
            });

        // Calculate total RO points for user
        $totalRoPoints = (int) ($user->ro_points ?? 0);

        // Calculate total sponsor bonus earned from RO
        $totalRoBonus = (float) BonusLog::where('user_id', $user->id)
            ->where('description', 'LIKE', '%Repeat Order%')
            ->sum('amount');

        // Calculate matching RO bonus
        $matchingRoBonus = (float) BonusLog::where('user_id', $user->id)
            ->where(function($q) {
                $q->where('description', 'LIKE', '%Matching%RO%')
                  ->orWhere('category', 'ro_matching');
            })
            ->sum('amount');

        $settings = \App\Models\Setting::all()->pluck('value', 'key');
        $companyBanks = json_decode($settings['company_banks'] ?? '[]', true);
        $companyBank = (is_array($companyBanks) && count($companyBanks) > 0) 
            ? $companyBanks[0] 
            : [
                'bank_name' => 'Bank BRI',
                'account_number' => '806401000095564',
                'account_name' => 'PT.Xseller Punya Kita',
            ];

        $roProducts = \App\Models\Product::where('type', 'ro')->where('is_active', true)->get();

        return Inertia::render('Admin/RepeatOrder/Index', [
            'ro_stats' => [
                'total_ro_points' => $totalRoPoints,
                'available_ro_vouchers_count' => $activeRoVoucherCount,
                'regular_ro_vouchers_count' => $regularRoCount,
                'cashback_ro_vouchers_count' => $cashbackRoCount,
                'total_ro_bonus' => $totalRoBonus,
                'matching_ro_bonus' => $matchingRoBonus,
            ],
            'available_ro_vouchers' => $availableRoVouchers,
            'repeat_orders' => $repeatOrders,
            'user_saldo' => (float) ($user->saldo ?? 0),
            'user_package' => $user->package_name ?? 'Starter',
            'company_bank' => $companyBank,
            'is_admin' => $user->hasRole('admin'),
            'products' => $roProducts,
        ]);
    }

    /**
     * Process Repeat Order claim using Voucher RO or Voucher Cash RO.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $userPackage = strtolower($user->package_name ?? '');
        $isAdmin = $user->hasRole('admin');

        $isSeller = str_contains($userPackage, 'seller') && !str_contains($userPackage, 'star');
        $isStarter = str_contains($userPackage, 'starter');

        if (!$isSeller && !$isStarter && !$isAdmin) {
            return back()->with('error', 'Fitur RO hanya tersedia untuk member Paket Seller (Rp 125.000)!');
        }

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
            return back()->with('error', 'Voucher RO tidak ditemukan, sudah digunakan, atau bukan milik Anda!');
        }

        $isCashback = in_array($voucher->voucher_type, ['ro_cashback', 'ro_cash'])
            || str_contains(strtolower($voucher->package_name ?? ''), 'cash')
            || str_contains($voucher->package_name ?? '', '4.375');

        DB::transaction(function () use ($user, $voucher, $isCashback) {
            // 1. Mark voucher as used
            $voucher->update([
                'status' => 'used',
                'used_by_id' => $user->id,
                'used_at' => now(),
            ]);

            $sponsor = $user->parent;

            if ($isCashback) {
                // MODE CEPAT: VOUCHER CASHBACK RO (Rp 4.375.000)
                // User langsung dapat 35 Poin RO + Cashback Rp 500.000
                $user->increment('ro_points', 35);
                $rewardCashback = 500000;
                $user->increment('saldo', $rewardCashback);
                $user->increment('total_bonus', $rewardCashback);

                BonusLog::create([
                    'transaction_code' => 'ROC' . sprintf('%04d', BonusLog::count() + 1),
                    'user_id' => $user->id,
                    'category' => 'ro',
                    'source_user_id' => $user->id,
                    'description' => "Reward Cashback Voucher Cash RO (35 Poin RO)",
                    'amount' => $rewardCashback,
                ]);

                WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'in',
                    'category' => 'reward_ro',
                    'amount' => $rewardCashback,
                    'description' => "Reward Cashback Voucher Cash RO (35 Poin RO)",
                ]);

                // Sponsor langsung dapat Sponsor RO Rp 700.000 (20.000 x 35) + Matching RO Rp 100.000
                $sponsorBonus = 700000;
                $matchingBonus = 100000;

                if ($sponsor) {
                    // Bonus Sponsor RO Rp 700.000
                    $sponsor->increment('saldo', $sponsorBonus);
                    $sponsor->increment('total_bonus', $sponsorBonus);

                    BonusLog::create([
                        'transaction_code' => 'RO' . sprintf('%04d', BonusLog::count() + 1),
                        'user_id' => $sponsor->id,
                        'category' => 'ro',
                        'source_user_id' => $user->id,
                        'description' => "Bonus Repeat Order (Voucher Cash RO 35 Pkt) dari @{$user->username}",
                        'amount' => $sponsorBonus,
                    ]);

                    WalletTransaction::create([
                        'user_id' => $sponsor->id,
                        'type' => 'in',
                        'category' => 'bonus_sponsor',
                        'amount' => $sponsorBonus,
                        'description' => "Bonus Repeat Order (Voucher Cash RO 35 Pkt) dari @{$user->username}",
                    ]);

                    // Matching Bonus RO Rp 100.000
                    $sponsor->increment('saldo', $matchingBonus);
                    $sponsor->increment('total_bonus', $matchingBonus);

                    BonusLog::create([
                        'transaction_code' => 'ROMB' . sprintf('%04d', BonusLog::count() + 1),
                        'user_id' => $sponsor->id,
                        'category' => 'ro_matching',
                        'source_user_id' => $user->id,
                        'description' => "Matching Bonus RO dari @{$user->username} (Voucher Cash RO 35 Poin)",
                        'amount' => $matchingBonus,
                    ]);

                    WalletTransaction::create([
                        'user_id' => $sponsor->id,
                        'type' => 'in',
                        'category' => 'ro_matching',
                        'amount' => $matchingBonus,
                        'description' => "Matching Bonus RO dari @{$user->username} (Voucher Cash RO)",
                    ]);
                }

                RepeatOrder::create([
                    'user_id' => $user->id,
                    'voucher_id' => $voucher->id,
                    'voucher_code' => $voucher->code,
                    'sponsor_id' => $sponsor ? $sponsor->id : null,
                    'sponsor_bonus' => $sponsorBonus,
                    'ro_points' => 35,
                ]);
            } else {
                // MODE SATU PER SATU: VOUCHER RO REGULAR (Rp 125.000)
                $user->increment('ro_points', 1);
                $user->refresh();
                $newRoPoints = (int) $user->ro_points;

                $sponsorBonus = 20000;
                if ($sponsor) {
                    $sponsor->increment('saldo', $sponsorBonus);
                    $sponsor->increment('total_bonus', $sponsorBonus);

                    BonusLog::create([
                        'transaction_code' => 'RO' . sprintf('%04d', BonusLog::count() + 1),
                        'user_id' => $sponsor->id,
                        'category' => 'ro',
                        'source_user_id' => $user->id,
                        'description' => "Bonus Repeat Order dari @{$user->username} (Tier 1)",
                        'amount' => $sponsorBonus,
                    ]);

                    WalletTransaction::create([
                        'user_id' => $sponsor->id,
                        'type' => 'in',
                        'category' => 'bonus_sponsor',
                        'amount' => $sponsorBonus,
                        'description' => "Bonus Repeat Order dari @{$user->username} (Tier 1)",
                    ]);
                }

                RepeatOrder::create([
                    'user_id' => $user->id,
                    'voucher_id' => $voucher->id,
                    'voucher_code' => $voucher->code,
                    'sponsor_id' => $sponsor ? $sponsor->id : null,
                    'sponsor_bonus' => $sponsorBonus,
                    'ro_points' => 1,
                ]);

                if ($newRoPoints > 0 && ($newRoPoints % 35 === 0)) {
                    // Konversi 35 Poin RO ke member
                    $rewardRo = 500000;
                    
                    $user->increment('saldo', $rewardRo);
                    $user->increment('total_bonus', $rewardRo);

                    BonusLog::create([
                        'transaction_code' => 'RO' . sprintf('%04d', BonusLog::count() + 1),
                        'user_id' => $user->id,
                        'category' => 'ro',
                        'source_user_id' => $user->id,
                        'description' => "Reward Konversi {$newRoPoints} Poin RO",
                        'amount' => $rewardRo,
                    ]);

                    WalletTransaction::create([
                        'user_id' => $user->id,
                        'type' => 'in',
                        'category' => 'reward_ro',
                        'amount' => $rewardRo,
                        'description' => "Reward Konversi {$newRoPoints} Poin RO",
                    ]);

                    // Matching Bonus RO: Rp 100.000 ke sponsor saat member capai kelipatan 35 Poin RO
                    if ($sponsor) {
                        $matchingBonus = 100000;
        
                        $sponsor->increment('saldo', $matchingBonus);
                        $sponsor->increment('total_bonus', $matchingBonus);
        
                        BonusLog::create([
                            'transaction_code' => 'ROMB' . sprintf('%04d', BonusLog::count() + 1),
                            'user_id' => $sponsor->id,
                            'category' => 'ro_matching',
                            'source_user_id' => $user->id,
                            'description' => "Matching Bonus RO dari @{$user->username} (Capai {$newRoPoints} Poin RO = 20% × Rp 500.000)",
                            'amount' => $matchingBonus,
                        ]);
        
                        WalletTransaction::create([
                            'user_id' => $sponsor->id,
                            'type' => 'in',
                            'category' => 'ro_matching',
                            'amount' => $matchingBonus,
                            'description' => "Matching Bonus RO dari @{$user->username} (Capai {$newRoPoints} Poin RO)",
                        ]);
                    }
                }
            }
        });

        if ($isCashback) {
            return back()->with('success', 'Berhasil aktivasi Voucher Cash RO! Anda mendapatkan 35 Poin RO dan Cashback Rp 500.000. Sponsor langsung menerima Bonus Sponsor Rp 700.000 & Matching Bonus Rp 100.000.');
        }

        return back()->with('success', 'Berhasil melakukan Repeat Order! Anda mendapatkan 1 Poin RO dan Sponsor Anda menerima Bonus Tier 1 (Rp 20.000).');
    }

    /**
     * Purchase or produce Voucher RO (Rp 125.000) or Voucher Cash RO (Rp 4.375.000).
     */
    public function buyVoucher(Request $request)
    {
        $user = auth()->user();
        $userPackage = strtolower($user->package_name ?? '');
        $isAdmin = $user->hasRole('admin');

        $isSeller = str_contains($userPackage, 'seller') && !str_contains($userPackage, 'star');
        $isStarter = str_contains($userPackage, 'starter');

        if (!$isSeller && !$isStarter && !$isAdmin) {
            return back()->with('error', 'Fitur RO hanya tersedia untuk member Paket Seller (Rp 125.000)!');
        }

        $request->validate([
            'voucher_type' => 'nullable|string|in:ro,ro_cashback',
            'quantity' => 'nullable|integer|min:1|max:35',
        ]);

        $voucherType = $request->input('voucher_type', 'ro');
        $qty = max(1, min(35, (int) $request->input('quantity', 1)));

        if ($voucherType === 'ro_cashback') {
            $unitPrice = 4375000;
            $pkgName = 'Cashback RO (Rp 4.375.000)';
            $type = 'ro_cashback';
            $prefix = 'ROC';
        } else {
            $unitPrice = 125000;
            $pkgName = 'Repeat Order (Rp 125.000)';
            $type = 'ro';
            $prefix = 'RO';
        }

        $totalPrice = $unitPrice * $qty;
        $user = auth()->user();

        if ($request->boolean('is_produce') && $user->hasRole('admin')) {
            // Admin produce free Voucher RO
            $targetUser = $user;
            if ($request->filled('target_username')) {
                $targetUser = User::where('username', $request->target_username)->first();
                if (!$targetUser) {
                    return back()->with('error', 'Username penerima tidak ditemukan!');
                }
            }

            DB::transaction(function () use ($targetUser, $qty, $pkgName, $type, $prefix) {
                for ($i = 0; $i < $qty; $i++) {
                    $code = $prefix . '-' . rand(1000, 9999) . '-' . strtoupper(Str::random(3));
                    Voucher::create([
                        'code' => $code,
                        'user_id' => $targetUser->id,
                        'package_name' => $pkgName,
                        'voucher_type' => $type,
                        'status' => 'active',
                    ]);
                }
            });

            return back()->with('success', "Berhasil memproduksi {$qty} Voucher {$pkgName} untuk @" . $targetUser->username . "!");
        }

        // Member purchase using wallet balance
        if (($user->saldo ?? 0) < $totalPrice) {
            return back()->with('error', "Saldo wallet Anda tidak mencukupi untuk membeli {$qty} {$pkgName} (Total: Rp " . number_format($totalPrice, 0, ',', '.') . ")!");
        }

        DB::transaction(function () use ($user, $totalPrice, $qty, $pkgName, $type, $prefix) {
            $user->decrement('saldo', $totalPrice);

            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'out',
                'category' => 'purchase_voucher',
                'amount' => $totalPrice,
                'description' => "Pembelian {$qty} Voucher {$pkgName}",
            ]);

            for ($i = 0; $i < $qty; $i++) {
                $code = $prefix . '-' . rand(1000, 9999) . '-' . strtoupper(Str::random(3));
                Voucher::create([
                    'code' => $code,
                    'user_id' => $user->id,
                    'package_name' => $pkgName,
                    'voucher_type' => $type,
                    'status' => 'active',
                ]);
            }
        });

        return back()->with('success', "Berhasil membeli {$qty} Voucher {$pkgName}!");
    }
}
