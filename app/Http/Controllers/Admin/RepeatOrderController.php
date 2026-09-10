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

        // Get user's available active RO vouchers
        $availableRoVouchers = Voucher::where('user_id', $user->id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->where('voucher_type', 'ro')
                  ->orWhere('package_name', 'LIKE', '%Repeat Order%')
                  ->orWhere('package_name', 'LIKE', '%RO%')
                  ->orWhere('package_name', 'LIKE', '%125%');
            })
            ->get(['id', 'code', 'package_name'])
            ->map(function ($v) {
                $pkgName = $v->package_name ?: 'Seller (Rp 125.000)';
                $pkgName = str_replace(
                    ['Starter (Rp 125.000)', 'Starter', 'Basic (Rp 550.000)', 'Basic'],
                    ['Seller (Rp 125.000)', 'Seller', 'Star Seller (Rp 550.000)', 'Star Seller'],
                    $pkgName
                );
                return [
                    'id' => $v->id,
                    'code' => $v->code,
                    'package_name' => $pkgName,
                ];
            });

        // Count active RO vouchers
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
                    'type_label' => $isUser ? 'Repeat Order Klaim' : 'Bonus Sponsor RO',
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
     * Process Repeat Order claim using Voucher RO.
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

        DB::transaction(function () use ($user, $voucher) {
            // 1. Mark voucher as used
            $voucher->update([
                'status' => 'used',
                'used_by_id' => $user->id,
                'used_at' => now(),
            ]);

            // 2. Increment user RO Poin
            $user->increment('ro_points', 1);
            $user->refresh(); // reload fresh ro_points after increment
            $newRoPoints = (int) $user->ro_points;

            // 3. Distribute Tier 1 Bonus (Rp 20.000) to Direct Sponsor
            $sponsor = $user->parent;
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

            // 4. Create RepeatOrder record
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
                // (20% × Rp 500.000 konversi reward per 35 poin)
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
        });

        return back()->with('success', 'Berhasil melakukan Repeat Order! Anda mendapatkan 1 Poin RO dan Sponsor Anda menerima Bonus Tier 1 (Rp 20.000).');
    }

    /**
     * Purchase or produce Voucher RO (Rp 125.000 / pcs, quantity 1 to 35).
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
            'quantity' => 'nullable|integer|min:1|max:35',
        ]);

        $qty = max(1, min(35, (int) $request->input('quantity', 1)));
        $unitPrice = 125000;
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

            DB::transaction(function () use ($targetUser, $qty) {
                for ($i = 0; $i < $qty; $i++) {
                    $code = 'RO-' . rand(1000, 9999) . '-' . strtoupper(Str::random(3));
                    Voucher::create([
                        'code' => $code,
                        'user_id' => $targetUser->id,
                        'package_name' => 'Repeat Order (Rp 125.000)',
                        'voucher_type' => 'ro',
                        'status' => 'active',
                    ]);
                }
            });

            return back()->with('success', "Berhasil memproduksi {$qty} Voucher RO untuk @" . $targetUser->username . "!");
        }

        // Member purchase using wallet balance
        if (($user->saldo ?? 0) < $totalPrice) {
            return back()->with('error', "Saldo wallet Anda tidak mencukupi untuk membeli {$qty} Voucher RO (Total: Rp " . number_format($totalPrice, 0, ',', '.') . ")!");
        }

        DB::transaction(function () use ($user, $totalPrice, $qty) {
            $user->decrement('saldo', $totalPrice);

            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'out',
                'category' => 'purchase_voucher',
                'amount' => $totalPrice,
                'description' => "Pembelian {$qty} Voucher Repeat Order (Rp 125.000 / pcs)",
            ]);

            for ($i = 0; $i < $qty; $i++) {
                $code = 'RO-' . rand(1000, 9999) . '-' . strtoupper(Str::random(3));
                Voucher::create([
                    'code' => $code,
                    'user_id' => $user->id,
                    'package_name' => 'Repeat Order (Rp 125.000)',
                    'voucher_type' => 'ro',
                    'status' => 'active',
                ]);
            }
        });

        return back()->with('success', "Berhasil membeli {$qty} Voucher RO!");
    }
}
