<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherTransfer;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class VoucherWalletController extends Controller
{
    /**
     * Display the Voucher Wallet Management page.
     */
    public function index()
    {
        $user = auth()->user();

        // Count active vouchers
        $voucherCount = Voucher::where('user_id', $user->id)->where('status', 'active')->count();

        // Get all vouchers owned by user
        $vouchers = Voucher::where('user_id', $user->id)
            ->with('usedBy')
            ->latest()
            ->get()
            ->map(function ($v) {
                $isAvailable = $v->status === 'active';
                $usedByUsername = $v->usedBy ? $v->usedBy->username : 'member';
                $usedDate = $v->used_at ? $v->used_at->format('j/n/Y') : $v->updated_at->format('j/n/Y');

                return [
                    'id' => $v->id,
                    'code' => $v->code,
                    'package_name' => $v->package_name ?: 'Basic',
                    'voucher_type' => $v->voucher_type ?: 'activation',
                    'created_at' => $v->created_at->format('j/n/Y'),
                    'status' => $isAvailable ? 'TERSEDIA' : 'TERPAKAI',
                    'keterangan' => $isAvailable
                        ? 'Tersedia di gudang voucher'
                        : 'Diaktifkan oleh member ID: @' . $usedByUsername . ' pada ' . $usedDate,
                ];
            });

        // Get available vouchers for transfer select options
        $availableVouchers = Voucher::where('user_id', $user->id)
            ->where('status', 'active')
            ->get()
            ->map(function ($v) {
                return [
                    'id' => $v->id,
                    'code' => $v->code,
                    'label' => $v->code . ' - ' . ($v->package_name ?: 'Voucher'),
                ];
            });

        // Get transfer history
        $transfers = VoucherTransfer::where('sender_id', $user->id)
            ->orWhere('recipient_id', $user->id)
            ->with(['sender', 'recipient'])
            ->latest()
            ->get()
            ->map(function ($t) use ($user) {
                $isSender = $t->sender_id === $user->id;
                $targetName = $isSender 
                    ? ($t->recipient ? $t->recipient->name . ' (@' . $t->recipient->username . ')' : 'Member')
                    : ($t->sender ? $t->sender->name . ' (@' . $t->sender->username . ')' : 'Member');

                return [
                    'id' => $t->id,
                    'type' => $isSender ? 'DIKIRIM' : 'DITERIMA',
                    'keterangan' => $isSender ? 'Mengirim ke ' . $targetName : 'Menerima dari ' . $targetName,
                    'created_at' => $t->created_at->format('j/n/Y, H:i.s'),
                    'voucher_code' => $t->voucher_code,
                ];
            });

        // Company Bank Settings
        $settings = \App\Models\Setting::all()->pluck('value', 'key');
        $companyBanks = json_decode($settings['company_banks'] ?? '[]', true);
        $companyBank = (is_array($companyBanks) && count($companyBanks) > 0) 
            ? $companyBanks[0] 
            : [
                'bank_name' => 'Bank BRI',
                'account_number' => '806401000095564',
                'account_name' => 'PT.Xseller Punya Kita',
            ];

        // Voucher Package Catalog for Conversion
        $convertPackages = [
            // Activation Vouchers
            ['key' => 'starter', 'group' => 'Voucher Activation', 'name' => 'Voucher Activation Starter (Rp 125.000)', 'price' => 125000, 'type' => 'activation'],
            ['key' => 'basic', 'group' => 'Voucher Activation', 'name' => 'Voucher Activation Basic (Rp 550.000)', 'price' => 550000, 'type' => 'activation'],
            ['key' => 'medium', 'group' => 'Voucher Activation', 'name' => 'Voucher Activation Medium (Rp 2.100.000)', 'price' => 2100000, 'type' => 'activation'],
            ['key' => 'pro', 'group' => 'Voucher Activation', 'name' => 'Voucher Activation Pro (Rp 4.300.000)', 'price' => 4300000, 'type' => 'activation'],
            ['key' => 'ultimate', 'group' => 'Voucher Activation', 'name' => 'Voucher Activation Ultimate (Rp 10.500.000)', 'price' => 10500000, 'type' => 'activation'],

            // RO Voucher
            ['key' => 'ro', 'group' => 'Voucher RO', 'name' => 'Voucher RO (Rp 125.000)', 'price' => 125000, 'type' => 'ro'],

            // PO Vouchers
            ['key' => 'po_star_seller', 'group' => 'Voucher PO', 'name' => 'Voucher PO Star Seller (Rp 550.000)', 'price' => 550000, 'type' => 'po_star_seller'],
            ['key' => 'po_affiliate', 'group' => 'Voucher PO', 'name' => 'Voucher PO Affiliate (Rp 2.100.000)', 'price' => 2100000, 'type' => 'po_affiliate'],
        ];

        return Inertia::render('Admin/VoucherWallet', [
            'wallet' => [
                'saldo' => (float) ($user->saldo ?? 0),
                'total_bonus' => (float) ($user->total_bonus ?? 0),
                'voucher_count' => $voucherCount,
            ],
            'convert_packages' => $convertPackages,
            'company_bank' => $companyBank,
            'vouchers' => $vouchers,
            'available_vouchers' => $availableVouchers,
            'transfers' => $transfers,
            'is_admin' => $user->hasRole('admin'),
        ]);
    }

    /**
     * Convert Saldo Wallet into Vouchers (Activation, RO, PO).
     */
    public function buy(Request $request)
    {
        $request->validate([
            'package_key' => 'required|string',
            'quantity' => 'nullable|integer|min:1|max:35',
        ]);

        $user = auth()->user();
        $qty = max(1, min(35, (int) $request->input('quantity', 1)));

        $catalog = [
            'starter' => ['name' => 'Starter (Rp 125.000)', 'price' => 125000, 'type' => 'activation', 'prefix' => 'PIN'],
            'basic' => ['name' => 'Basic (Rp 550.000)', 'price' => 550000, 'type' => 'activation', 'prefix' => 'PIN'],
            'medium' => ['name' => 'Medium (Rp 2.100.000)', 'price' => 2100000, 'type' => 'activation', 'prefix' => 'PIN'],
            'pro' => ['name' => 'Pro (Rp 4.300.000)', 'price' => 4300000, 'type' => 'activation', 'prefix' => 'PIN'],
            'ultimate' => ['name' => 'Ultimate (Rp 10.500.000)', 'price' => 10500000, 'type' => 'activation', 'prefix' => 'PIN'],
            'ro' => ['name' => 'Repeat Order (Rp 125.000)', 'price' => 125000, 'type' => 'ro', 'prefix' => 'RO'],
            'po_star_seller' => ['name' => 'PO Star Seller (Rp 550.000)', 'price' => 550000, 'type' => 'po_star_seller', 'prefix' => 'PO'],
            'po_affiliate' => ['name' => 'PO Affiliate (Rp 2.100.000)', 'price' => 2100000, 'type' => 'po_affiliate', 'prefix' => 'PO'],
        ];

        $pkg = $catalog[$request->package_key] ?? $catalog['basic'];
        $totalPrice = $pkg['price'] * $qty;

        if (($user->saldo ?? 0) < $totalPrice) {
            return back()->with('error', "Saldo wallet Anda tidak mencukupi untuk konversi {$qty} Voucher {$pkg['name']} (Total: Rp " . number_format($totalPrice, 0, ',', '.') . ")!");
        }

        DB::transaction(function () use ($user, $pkg, $totalPrice, $qty) {
            $user->decrement('saldo', $totalPrice);

            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'out',
                'category' => 'purchase_voucher',
                'amount' => $totalPrice,
                'description' => "Konversi Saldo Wallet ke {$qty} Voucher {$pkg['name']}",
            ]);

            for ($i = 0; $i < $qty; $i++) {
                $code = $pkg['prefix'] . '-' . rand(1000, 9999) . '-' . strtoupper(Str::random(3));

                Voucher::create([
                    'code' => $code,
                    'user_id' => $user->id,
                    'package_name' => $pkg['name'],
                    'voucher_type' => $pkg['type'],
                    'status' => 'active',
                ]);
            }
        });

        return back()->with('success', "Berhasil mengonversi Saldo Wallet menjadi {$qty} Voucher {$pkg['name']}!");
    }

    /**
     * Admin action to produce free voucher.
     */
    public function produce(Request $request)
    {
        $admin = auth()->user();

        if (!$admin->hasRole('admin')) {
            return back()->with('error', 'Hanya Admin yang berhak memproduksi voucher gratis!');
        }

        $request->validate([
            'package_key' => 'nullable|string',
            'quantity' => 'nullable|integer|min:1|max:35',
        ]);

        $qty = max(1, min(35, (int) $request->input('quantity', 1)));
        $targetUser = $admin;

        if ($request->filled('username')) {
            $targetUser = User::where('username', $request->username)->first();
            if (!$targetUser) {
                return back()->with('error', 'Username penerima tidak ditemukan!');
            }
        }

        $catalog = [
            'starter' => ['name' => 'Starter (Rp 125.000)', 'type' => 'activation', 'prefix' => 'PIN'],
            'basic' => ['name' => 'Basic (Rp 550.000)', 'type' => 'activation', 'prefix' => 'PIN'],
            'medium' => ['name' => 'Medium (Rp 2.100.000)', 'type' => 'activation', 'prefix' => 'PIN'],
            'pro' => ['name' => 'Pro (Rp 4.300.000)', 'type' => 'activation', 'prefix' => 'PIN'],
            'ultimate' => ['name' => 'Ultimate (Rp 10.500.000)', 'type' => 'activation', 'prefix' => 'PIN'],
            'ro' => ['name' => 'Repeat Order (Rp 125.000)', 'type' => 'ro', 'prefix' => 'RO'],
            'po_star_seller' => ['name' => 'PO Star Seller (Rp 550.000)', 'type' => 'po_star_seller', 'prefix' => 'PO'],
            'po_affiliate' => ['name' => 'PO Affiliate (Rp 2.100.000)', 'type' => 'po_affiliate', 'prefix' => 'PO'],
        ];

        $pkgKey = $request->input('package_key', 'basic');
        $pkg = $catalog[$pkgKey] ?? $catalog['basic'];

        DB::transaction(function () use ($targetUser, $pkg, $qty) {
            for ($i = 0; $i < $qty; $i++) {
                $code = $pkg['prefix'] . '-' . rand(1000, 9999) . '-' . strtoupper(Str::random(3));

                Voucher::create([
                    'code' => $code,
                    'user_id' => $targetUser->id,
                    'package_name' => $pkg['name'],
                    'voucher_type' => $pkg['type'],
                    'status' => 'active',
                ]);
            }
        });

        return back()->with('success', "Berhasil memproduksi {$qty} Voucher {$pkg['name']} gratis untuk @" . $targetUser->username . "!");
    }

    /**
     * Transfer voucher to another member by username.
     */
    public function transfer(Request $request)
    {
        $request->validate([
            'voucher_id' => 'required|exists:vouchers,id',
            'recipient_username' => 'required|string',
        ]);

        $sender = auth()->user();

        $voucher = Voucher::where('id', $request->voucher_id)
            ->where('user_id', $sender->id)
            ->where('status', 'active')
            ->first();

        if (!$voucher) {
            return back()->with('error', 'Voucher tidak valid atau sudah terpakai!');
        }

        $recipient = User::where('username', $request->recipient_username)->first();

        if (!$recipient) {
            return back()->with('error', 'Username penerima @' . $request->recipient_username . ' tidak ditemukan!');
        }

        if ($recipient->id === $sender->id) {
            return back()->with('error', 'Tidak dapat mentransfer voucher ke akun sendiri!');
        }

        DB::transaction(function () use ($voucher, $sender, $recipient) {
            $voucher->update([
                'user_id' => $recipient->id,
            ]);

            VoucherTransfer::create([
                'voucher_id' => $voucher->id,
                'voucher_code' => $voucher->code,
                'sender_id' => $sender->id,
                'recipient_id' => $recipient->id,
            ]);
        });

        return back()->with('success', 'Berhasil mentransfer voucher ' . $voucher->code . ' ke @' . $recipient->username . '!');
    }
}
