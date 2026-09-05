<script setup>
import { ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
  ShoppingBag, 
  Award, 
  CheckCircle2, 
  XCircle, 
  Layers, 
  CreditCard, 
  TrendingUp, 
  Package, 
  Plus,
  Send 
} from '@lucide/vue';

const props = defineProps({
  po_stats: Object,
  available_po_vouchers: Array,
  purchase_orders: Array,
  user_saldo: Number,
  company_bank: Object,
  is_admin: Boolean,
  products: Array,
});

const page = usePage();

// Claim form
const claimForm = useForm({
  voucher_code: props.available_po_vouchers.length > 0 ? props.available_po_vouchers[0].code : '',
});

const submitClaimPo = () => {
  claimForm.post(route('admin.purchase-order.store'), {
    preserveScroll: true,
    onSuccess: () => {
      claimForm.reset();
    },
  });
};

// Buy modal form
const buyVoucherModalOpen = ref(false);
const buyVoucherForm = useForm({
  package_type: 'star_seller',
  quantity: 1,
  is_produce: false,
  target_username: '',
});

const submitBuyVoucher = () => {
  buyVoucherForm.post(route('admin.purchase-order.buy-voucher'), {
    preserveScroll: true,
    onSuccess: () => {
      buyVoucherModalOpen.value = false;
      buyVoucherForm.reset({ package_type: 'star_seller', quantity: 1, is_produce: false, target_username: '' });
    },
  });
};

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);
};
</script>

<template>
  <Head title="Purchase Order (PO) - XSELLER" />

  <AdminLayout>
    <div class="space-y-6 max-w-7xl mx-auto">
      
      <!-- Page Title & Header Banner -->
      <div class="bg-gradient-to-r from-[#0b1f3a] via-[#103f80] to-[#1653a1] rounded-3xl p-6 md:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none">
          <ShoppingBag class="w-72 h-72 text-white" />
        </div>
        
        <div class="relative z-10 space-y-2">
          <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 backdrop-blur border border-white/20 rounded-full text-xs text-[#a9fff7] font-extrabold uppercase tracking-wider">
            <ShoppingBag class="w-3.5 h-3.5" />
            <span>Program Purchase Order (PO)</span>
          </div>
          <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white">Purchase Order & Poin Personal Reward</h1>
          <p class="text-xs md:text-sm text-slate-200 max-w-2xl font-medium">
            Paket khusus <span class="font-bold text-[#a9fff7]">Star Seller (Rp 550.000)</span> dan <span class="font-bold text-white">Affiliate (Rp 2.100.000)</span>. Dapatkan akumulasi <span class="font-bold text-[#a9fff7]">Poin Personal Reward</span> dan alokasi 15 Generasi Tier.
          </p>
        </div>
      </div>

      <!-- Flash Messages -->
      <div v-if="$page.props.flash?.success" class="p-4 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-2xl flex items-center justify-between text-xs font-bold shadow-sm">
        <div class="flex items-center gap-2">
          <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
          <span>{{ $page.props.flash?.success }}</span>
        </div>
      </div>

      <div v-if="$page.props.flash?.error" class="p-4 bg-rose-50 border border-rose-300 text-rose-800 rounded-2xl flex items-center justify-between text-xs font-bold shadow-sm">
        <div class="flex items-center gap-2">
          <XCircle class="w-4 h-4 text-rose-600 shrink-0" />
          <span>{{ $page.props.flash?.error }}</span>
        </div>
      </div>

      <!-- Stats Overview Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Card 1: Total Personal Poin PO -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between relative overflow-hidden group hover:border-[#04bdb2]/50 transition-all">
          <div class="space-y-1">
            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Personal Poin PO</span>
            <div class="flex items-baseline gap-2">
              <span class="text-3xl font-black text-slate-900">{{ po_stats.total_po_points }}</span>
              <span class="text-xs font-extrabold text-[#04bdb2]">Poin PO</span>
            </div>
            <p class="text-[10px] text-slate-500">Reward Cash up to Rp 150.000.000</p>
          </div>
          <div class="w-12 h-12 rounded-2xl bg-[#04bdb2]/10 border border-[#04bdb2]/30 text-[#04bdb2] flex items-center justify-center font-bold shadow-xs">
            <Award class="w-6 h-6" />
          </div>
        </div>

        <!-- Card 2: Stok Voucher PO Tersedia -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between relative overflow-hidden group hover:border-[#1653a1]/50 transition-all">
          <div class="space-y-1">
            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Stok Voucher PO</span>
            <div class="flex items-baseline gap-2">
              <span class="text-3xl font-black text-[#1653a1]">{{ po_stats.total_po_vouchers_count }}</span>
              <span class="text-xs font-bold text-slate-500">Voucher</span>
            </div>
            <p class="text-[10px] text-slate-500">Star Seller ({{ po_stats.star_seller_count }}) | Affiliate ({{ po_stats.affiliate_count }})</p>
          </div>
          <div class="w-12 h-12 rounded-2xl bg-[#1653a1]/10 border border-[#1653a1]/30 text-[#1653a1] flex items-center justify-center font-bold shadow-xs">
            <ShoppingBag class="w-6 h-6" />
          </div>
        </div>

        <!-- Card 3: PAL Bonus (Personal Allocation Level) -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between relative overflow-hidden group hover:border-emerald-500/50 transition-all">
          <div class="space-y-1">
            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">PAL Bonus (Gen 1 PO)</span>
            <div class="flex items-baseline gap-1">
              <span class="text-2xl font-black text-emerald-600">Active</span>
            </div>
            <p class="text-[10px] text-slate-500">Star Seller +50k | Affiliate +200k</p>
          </div>
          <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 flex items-center justify-center font-bold shadow-xs">
            <TrendingUp class="w-6 h-6" />
          </div>
        </div>
      </div>

      <!-- Main Action Section: Client Mockup Style Card & Buy Voucher Widget -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Client Mockup Style Activation Card -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200/80 p-6 md:p-8 shadow-sm space-y-6">
          
          <div class="bg-white p-6 md:p-8 rounded-3xl text-center space-y-6 max-w-md mx-auto border border-slate-100 shadow-xs">
            <div class="space-y-1">
              <h2 class="text-xl font-black tracking-tight text-slate-900 uppercase">PURCHASE ORDER</h2>
              <p class="text-xs text-slate-500 font-medium">Kamu bisa melakukan PO sesuai dengan Voucher PO yang tersedia</p>
            </div>

            <!-- Pill Box: Voucher PO tersedia (Select or Zero Display) -->
            <div v-if="available_po_vouchers.length > 0" class="border-2 border-slate-900 rounded-full py-2.5 px-5 bg-white shadow-xs">
              <select 
                v-model="claimForm.voucher_code"
                class="w-full bg-transparent text-sm font-bold text-slate-900 focus:outline-none border-none cursor-pointer py-1"
              >
                <option v-for="v in available_po_vouchers" :key="v.id" :value="v.code">
                  {{ v.code }} - {{ v.package_name || 'Voucher PO' }}
                </option>
              </select>
            </div>

            <div v-else class="border-2 border-slate-900 rounded-full py-3 px-6 flex items-center justify-between text-sm font-bold text-slate-900 bg-white shadow-xs">
              <span>Voucher PO tersedia</span>
              <span class="text-base font-black text-[#1653a1]">0</span>
            </div>

            <!-- Subtext: Voucher PO counts -->
            <div v-if="po_stats.total_po_vouchers_count > 0" class="text-xs font-semibold text-slate-600 space-y-1 text-left px-2">
              <p>kamu memiliki Voucher PO Star Seller = <span class="font-extrabold text-slate-900">{{ po_stats.star_seller_count }}</span></p>
              <p>kamu memiliki Voucher PO Affiliate = <span class="font-extrabold text-slate-900">{{ po_stats.affiliate_count }}</span></p>
            </div>
            <p v-else class="text-xs font-semibold text-slate-500">
              kamu tidak memiliki Voucher PO
            </p>

            <!-- Button: AKTIVASI PO (If > 0) vs BELI (If === 0) -->
            <form v-if="available_po_vouchers.length > 0" @submit.prevent="submitClaimPo" class="pt-2">
              <button 
                type="submit" 
                :disabled="claimForm.processing"
                class="w-full py-3.5 px-8 bg-[#04bdb2] hover:bg-[#009c94] text-white font-black text-xs uppercase tracking-widest rounded-full shadow-md shadow-[#04bdb2]/30 transition-all cursor-pointer"
              >
                AKTIVASI PO
              </button>
            </form>

            <div v-else class="pt-2">
              <button 
                @click="buyVoucherModalOpen = true"
                class="w-full py-3.5 px-8 bg-[#04bdb2] hover:bg-[#009c94] text-white font-black text-xs uppercase tracking-widest rounded-full shadow-md shadow-[#04bdb2]/30 transition-all cursor-pointer"
              >
                BELI
              </button>
            </div>
          </div>

          <!-- Benefit Summary List -->
          <div class="p-4 bg-[#f0f7fb] border border-[#04bdb2]/30 rounded-2xl space-y-2 text-xs text-slate-700">
            <span class="font-black text-[#1653a1] block uppercase text-[10px] tracking-wider">Ketentuan Paket Purchase Order:</span>
            <ul class="space-y-1.5 list-disc list-inside text-[11px] font-medium text-slate-600">
              <li><strong class="text-slate-900">PO Star Seller (Rp 550.000):</strong> +2 Personal Poin PO + Alokasi Tier 15 Generasi (Rp 10.000/gen).</li>
              <li><strong class="text-slate-900">PO Affiliate (Rp 2.100.000):</strong> +8 Personal Poin PO + Alokasi Tier 15 Generasi (Rp 50.000/gen).</li>
            </ul>
          </div>
        </div>

        <!-- Right: Buy Voucher PO Widget Card -->
        <div class="bg-gradient-to-b from-white to-slate-50/80 rounded-3xl border border-slate-200/80 p-6 shadow-sm flex flex-col justify-between space-y-6">
          <div class="space-y-4">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl bg-[#04bdb2]/10 text-[#04bdb2] flex items-center justify-center font-bold">
                <ShoppingBag class="w-5 h-5" />
              </div>
              <div>
                <h3 class="text-sm font-black text-slate-900">Beli Voucher PO</h3>
                <p class="text-[11px] text-slate-500 font-medium">Star Seller & Affiliate (1 s/d 35 pcs)</p>
              </div>
            </div>

            <div class="p-4 bg-white border border-slate-200/80 rounded-2xl space-y-2 shadow-2xs">
              <div class="flex items-center justify-between text-xs">
                <span class="text-slate-500 font-medium">PO Star Seller:</span>
                <span class="font-black text-[#1653a1]">Rp 550.000</span>
              </div>
              <div class="flex items-center justify-between text-xs">
                <span class="text-slate-500 font-medium">PO Affiliate:</span>
                <span class="font-black text-[#1653a1]">Rp 2.100.000</span>
              </div>
              <div class="flex items-center justify-between border-t border-slate-200 pt-2 text-xs">
                <span class="text-slate-500 font-medium">Saldo Wallet Anda:</span>
                <span class="font-black text-slate-900">{{ formatRupiah(user_saldo) }}</span>
              </div>
            </div>

            <p class="text-[11px] text-slate-500 font-medium leading-relaxed">
              Pilih paket Voucher PO yang diinginkan (1 s/d 35 Pcs) menggunakan saldo e-wallet atau transfer bank.
            </p>
          </div>

          <button 
            @click="buyVoucherModalOpen = true"
            class="w-full py-3 px-4 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-2xl shadow-md transition-all cursor-pointer flex items-center justify-center gap-2"
          >
            <Plus class="w-4 h-4 text-[#04bdb2]" />
            <span>Beli Voucher PO (1-35 pcs)</span>
          </button>
        </div>

      </div>

      <!-- PRODUK PO CATALOG SECTION (Matching Client Image 2 Mockup) -->
      <div class="bg-white rounded-3xl border border-slate-200/80 p-6 md:p-8 shadow-sm space-y-6">
        <div class="text-center space-y-1">
          <h2 class="text-2xl md:text-3xl font-black text-[#5c3a21] uppercase tracking-wide">PRODUK PO</h2>
          <p class="text-xs text-slate-500 font-medium">Katalog Pilihan Produk Paket Purchase Order (PO)</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 pt-4">
          <!-- Item 1: HERBAQUEENA -->
          <div class="bg-slate-50/60 border border-slate-200/70 rounded-3xl p-5 text-center space-y-3 flex flex-col justify-between hover:shadow-md transition-all">
            <div class="space-y-3">
              <div class="w-full aspect-square rounded-2xl bg-white border border-slate-200/60 overflow-hidden flex items-center justify-center p-2 shadow-2xs">
                <div class="w-full h-full bg-emerald-900/10 rounded-xl flex items-center justify-center font-black text-emerald-900 text-sm">
                  🌿 HERBAQUEENA
                </div>
              </div>
              <div class="text-left space-y-0.5">
                <h3 class="text-base font-black text-[#5c3a21] uppercase tracking-tight">HERBAQUEENA</h3>
                <p class="text-sm font-extrabold text-slate-900">Rp 550.000</p>
                <p class="text-xs text-slate-500 font-bold">Jumlah : 3</p>
              </div>
            </div>
            <div class="pt-2 flex justify-start">
              <span class="px-3 py-1 bg-[#5c3a21] text-white text-xs font-black rounded-full flex items-center gap-1.5 shadow-2xs">
                <span>Poin</span>
                <span class="w-4 h-4 rounded-full bg-white text-[#5c3a21] text-[10px] font-black flex items-center justify-center">2</span>
              </span>
            </div>
          </div>

          <!-- Item 2: XSELLER BEE -->
          <div class="bg-slate-50/60 border border-slate-200/70 rounded-3xl p-5 text-center space-y-3 flex flex-col justify-between hover:shadow-md transition-all">
            <div class="space-y-3">
              <div class="w-full aspect-square rounded-2xl bg-white border border-slate-200/60 overflow-hidden flex items-center justify-center p-2 shadow-2xs">
                <div class="w-full h-full bg-amber-500/10 rounded-xl flex items-center justify-center font-black text-amber-800 text-sm">
                  🍯 XSELLER BEE (MADU)
                </div>
              </div>
              <div class="text-left space-y-0.5">
                <h3 class="text-base font-black text-[#5c3a21] uppercase tracking-tight">XSELLER BEE</h3>
                <p class="text-sm font-extrabold text-slate-900">Rp 550.000</p>
                <p class="text-xs text-slate-500 font-bold">Jumlah : 12</p>
              </div>
            </div>
            <div class="pt-2 flex justify-start">
              <span class="px-3 py-1 bg-[#5c3a21] text-white text-xs font-black rounded-full flex items-center gap-1.5 shadow-2xs">
                <span>Poin</span>
                <span class="w-4 h-4 rounded-full bg-white text-[#5c3a21] text-[10px] font-black flex items-center justify-center">2</span>
              </span>
            </div>
          </div>

          <!-- Item 3: Logo X Placeholder -->
          <div class="bg-slate-50/60 border border-slate-200/70 rounded-3xl p-5 text-center space-y-3 flex flex-col justify-between hover:shadow-md transition-all">
            <div class="space-y-3">
              <div class="w-full aspect-square rounded-2xl bg-white border border-slate-200/60 overflow-hidden flex items-center justify-center p-4 shadow-2xs">
                <div class="w-full h-full bg-[#04bdb2]/10 rounded-xl flex items-center justify-center font-black text-[#009c94] text-2xl">
                  X
                </div>
              </div>
              <div class="text-left space-y-0.5">
                <h3 class="text-base font-black text-slate-400 uppercase tracking-tight">COMING SOON</h3>
                <p class="text-xs text-slate-400 font-bold">Produk PO Baru</p>
              </div>
            </div>
            <div class="pt-2 flex justify-start">
              <span class="px-3 py-1 bg-[#5c3a21] text-white text-xs font-black rounded-full flex items-center gap-1.5 shadow-2xs">
                <span>Poin</span>
                <span class="w-4 h-4 rounded-full bg-white text-[#5c3a21] text-[10px] font-black flex items-center justify-center">1</span>
              </span>
            </div>
          </div>

          <!-- Item 4: Logo X Placeholder -->
          <div class="bg-slate-50/60 border border-slate-200/70 rounded-3xl p-5 text-center space-y-3 flex flex-col justify-between hover:shadow-md transition-all">
            <div class="space-y-3">
              <div class="w-full aspect-square rounded-2xl bg-white border border-slate-200/60 overflow-hidden flex items-center justify-center p-4 shadow-2xs">
                <div class="w-full h-full bg-[#04bdb2]/10 rounded-xl flex items-center justify-center font-black text-[#009c94] text-2xl">
                  X
                </div>
              </div>
              <div class="text-left space-y-0.5">
                <h3 class="text-base font-black text-slate-400 uppercase tracking-tight">COMING SOON</h3>
                <p class="text-xs text-slate-400 font-bold">Produk PO Baru</p>
              </div>
            </div>
            <div class="pt-2 flex justify-start">
              <span class="px-3 py-1 bg-[#5c3a21] text-white text-xs font-black rounded-full flex items-center gap-1.5 shadow-2xs">
                <span>Poin</span>
                <span class="w-4 h-4 rounded-full bg-white text-[#5c3a21] text-[10px] font-black flex items-center justify-center">1</span>
              </span>
            </div>
          </div>

          <!-- Item 5: Logo X Placeholder -->
          <div class="bg-slate-50/60 border border-slate-200/70 rounded-3xl p-5 text-center space-y-3 flex flex-col justify-between hover:shadow-md transition-all">
            <div class="space-y-3">
              <div class="w-full aspect-square rounded-2xl bg-white border border-slate-200/60 overflow-hidden flex items-center justify-center p-4 shadow-2xs">
                <div class="w-full h-full bg-[#04bdb2]/10 rounded-xl flex items-center justify-center font-black text-[#009c94] text-2xl">
                  X
                </div>
              </div>
              <div class="text-left space-y-0.5">
                <h3 class="text-base font-black text-slate-400 uppercase tracking-tight">COMING SOON</h3>
                <p class="text-xs text-slate-400 font-bold">Produk PO Baru</p>
              </div>
            </div>
            <div class="pt-2 flex justify-start">
              <span class="px-3 py-1 bg-[#5c3a21] text-white text-xs font-black rounded-full flex items-center gap-1.5 shadow-2xs">
                <span>Poin</span>
                <span class="w-4 h-4 rounded-full bg-white text-[#5c3a21] text-[10px] font-black flex items-center justify-center">1</span>
              </span>
            </div>
          </div>

          <!-- Item 6: Logo X Placeholder -->
          <div class="bg-slate-50/60 border border-slate-200/70 rounded-3xl p-5 text-center space-y-3 flex flex-col justify-between hover:shadow-md transition-all">
            <div class="space-y-3">
              <div class="w-full aspect-square rounded-2xl bg-white border border-slate-200/60 overflow-hidden flex items-center justify-center p-4 shadow-2xs">
                <div class="w-full h-full bg-[#04bdb2]/10 rounded-xl flex items-center justify-center font-black text-[#009c94] text-2xl">
                  X
                </div>
              </div>
              <div class="text-left space-y-0.5">
                <h3 class="text-base font-black text-slate-400 uppercase tracking-tight">COMING SOON</h3>
                <p class="text-xs text-slate-400 font-bold">Produk PO Baru</p>
              </div>
            </div>
            <div class="pt-2 flex justify-start">
              <span class="px-3 py-1 bg-[#5c3a21] text-white text-xs font-black rounded-full flex items-center gap-1.5 shadow-2xs">
                <span>Poin</span>
                <span class="w-4 h-4 rounded-full bg-white text-[#5c3a21] text-[10px] font-black flex items-center justify-center">1</span>
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Transaction History Table -->
      <div class="bg-white rounded-3xl border border-slate-200/80 p-6 md:p-8 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
          <div>
            <h3 class="text-base font-black text-slate-900">Riwayat Purchase Order</h3>
            <p class="text-xs text-slate-500 font-medium">Daftar transaksi pembelian produk tambahan (PO)</p>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="border-b border-slate-200/80 bg-slate-50/50 text-[10px] uppercase tracking-wider text-slate-400 font-black">
                <th class="py-3 px-4">Tanggal</th>
                <th class="py-3 px-4">Nama Paket PO</th>
                <th class="py-3 px-4 text-center">Poin PO Perolehan</th>
                <th class="py-3 px-4 text-right">Nominal Transaksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-semibold text-slate-700">
              <tr v-for="po in purchase_orders" :key="po.id" class="hover:bg-slate-50/60 transition-colors">
                <td class="py-3.5 px-4 whitespace-nowrap text-slate-500 font-medium">{{ po.created_at }}</td>
                <td class="py-3.5 px-4 whitespace-nowrap font-bold text-slate-800">{{ po.package_name }}</td>
                <td class="py-3.5 px-4 whitespace-nowrap text-center">
                  <span class="font-extrabold text-[#04bdb2] bg-[#04bdb2]/10 px-2.5 py-0.5 rounded-md">+{{ po.po_points }} Poin</span>
                </td>
                <td class="py-3.5 px-4 whitespace-nowrap text-right font-black text-slate-900">
                  {{ formatRupiah(po.amount) }}
                </td>
              </tr>

              <tr v-if="purchase_orders.length === 0">
                <td colspan="4" class="py-8 text-center text-slate-400 font-medium">
                  Belum ada riwayat transaksi Purchase Order.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>

    <!-- Modal Buy Voucher PO -->
    <div v-if="buyVoucherModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-5 shadow-2xl border border-slate-100 animate-fade-in">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <h3 class="text-base font-black text-slate-900">Beli Voucher Purchase Order (PO)</h3>
          <button @click="buyVoucherModalOpen = false" class="text-slate-400 hover:text-slate-700">
            <XCircle class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitBuyVoucher" class="space-y-4">
          <!-- Package Selection -->
          <div>
            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">
              Pilih Paket Voucher PO:
            </label>
            <select 
              v-model="buyVoucherForm.package_type" 
              class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-800"
            >
              <option value="star_seller">PO Star Seller (Rp 550.000)</option>
              <option value="affiliate">PO Affiliate (Rp 2.100.000)</option>
            </select>
          </div>

          <!-- Quantity Selection -->
          <div>
            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">
              Jumlah Voucher PO (1 s/d 35 Pcs):
            </label>
            <input 
              type="number" 
              v-model.number="buyVoucherForm.quantity" 
              min="1" 
              max="35" 
              class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-[#04bdb2]"
              required
            />
          </div>

          <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-2 text-xs">
            <div class="flex items-center justify-between">
              <span class="text-slate-500 font-medium">Harga Per Voucher:</span>
              <span class="font-bold text-slate-800">
                {{ buyVoucherForm.package_type === 'affiliate' ? 'Rp 2.100.000' : 'Rp 550.000' }}
              </span>
            </div>
            <div class="flex items-center justify-between font-bold">
              <span class="text-slate-700">Total Pembayaran ({{ buyVoucherForm.quantity || 1 }} Pcs):</span>
              <span class="font-black text-[#1653a1] text-sm">
                {{ formatRupiah((buyVoucherForm.quantity || 1) * (buyVoucherForm.package_type === 'affiliate' ? 2100000 : 550000)) }}
              </span>
            </div>
            <div class="flex items-center justify-between border-t border-slate-200 pt-2 text-slate-500">
              <span>Saldo Wallet Anda:</span>
              <span class="font-bold text-slate-900">{{ formatRupiah(user_saldo) }}</span>
            </div>
          </div>

          <!-- Company Bank Transfer Info -->
          <div class="p-3 bg-[#f0f7fb] border border-[#1653a1]/30 rounded-2xl text-xs space-y-1">
            <span class="font-extrabold text-[#1653a1] block text-[10px] uppercase tracking-wider">Rekening Transfer Bank Perusahaan:</span>
            <p class="font-black text-slate-900">{{ company_bank?.bank_name || 'Bank BRI' }} - {{ company_bank?.account_number || '806401000095564' }}</p>
            <p class="text-[11px] text-slate-600 font-medium">a.n {{ company_bank?.account_name || 'PT.Xseller Punya Kita' }}</p>
          </div>

          <div v-if="is_admin" class="space-y-3 pt-2">
            <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
              <input type="checkbox" v-model="buyVoucherForm.is_produce" class="rounded text-[#04bdb2] focus:ring-[#04bdb2]" />
              <span>Produksi Gratis (Mode Admin)</span>
            </label>

            <div v-if="buyVoucherForm.is_produce">
              <label class="block text-[11px] font-bold text-slate-600 mb-1">Target Username (Opsional):</label>
              <input 
                type="text" 
                v-model="buyVoucherForm.target_username" 
                placeholder="Kosongkan untuk akun sendiri"
                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs"
              />
            </div>
          </div>

          <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
            <button 
              type="button" 
              @click="buyVoucherModalOpen = false" 
              class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors cursor-pointer"
            >
              Batal
            </button>
            <button 
              type="submit" 
              :disabled="buyVoucherForm.processing"
              class="px-5 py-2.5 bg-[#1653a1] hover:bg-[#103f80] text-white font-black text-xs rounded-xl shadow-md transition-all cursor-pointer"
            >
              Konfirmasi Beli ({{ formatRupiah((buyVoucherForm.quantity || 1) * (buyVoucherForm.package_type === 'affiliate' ? 2100000 : 550000)) }})
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
