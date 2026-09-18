<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
  RotateCcw, 
  KeyRound, 
  Award, 
  Gift, 
  ShoppingBag, 
  CheckCircle2, 
  XCircle, 
  Clock, 
  Plus, 
  Send, 
  ShieldCheck, 
  UserCheck,
  TrendingUp,
  CreditCard,
  Zap,
  Sparkles
} from '@lucide/vue';

const props = defineProps({
  ro_stats: Object,
  available_ro_vouchers: Array,
  repeat_orders: Array,
  user_saldo: Number,
  user_package: String,
  company_bank: Object,
  is_admin: Boolean,
  products: Array,
});

const page = usePage();
const flashSuccess = ref(page.props.flash?.success || null);
const flashError = ref(page.props.flash?.error || null);

// Form Claim Repeat Order using Voucher RO or Voucher Cash RO
const claimForm = useForm({
  voucher_code: props.available_ro_vouchers.length > 0 ? props.available_ro_vouchers[0].code : '',
});

const selectedClaimVoucher = computed(() => {
  return props.available_ro_vouchers.find(v => v.code === claimForm.voucher_code) || props.available_ro_vouchers[0];
});

const submitClaimRo = () => {
  claimForm.post(route('admin.repeat-order.store'), {
    preserveScroll: true,
    onSuccess: () => {
      claimForm.reset();
    },
  });
};

// Form Buy / Produce Voucher RO
const buyVoucherModalOpen = ref(false);
const buyVoucherForm = useForm({
  voucher_type: 'ro', // 'ro' or 'ro_cashback'
  quantity: 1,
  is_produce: false,
  target_username: '',
});

const buyUnitPrice = computed(() => {
  return buyVoucherForm.voucher_type === 'ro_cashback' ? 4375000 : 125000;
});

const buyTotalCost = computed(() => {
  return buyUnitPrice.value * (buyVoucherForm.quantity || 1);
});

const submitBuyVoucher = () => {
  buyVoucherForm.post(route('admin.repeat-order.buy-voucher'), {
    preserveScroll: true,
    onSuccess: () => {
      buyVoucherModalOpen.value = false;
      buyVoucherForm.reset({ voucher_type: 'ro', quantity: 1, is_produce: false, target_username: '' });
    },
  });
};

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);
};
</script>

<template>
  <Head title="Repeat Order (RO) & Cashback RO - XSELLER" />

  <AdminLayout>
    <div class="space-y-6 max-w-7xl mx-auto">
      
      <!-- Page Title & Header Banner -->
      <div class="bg-gradient-to-r from-[#0b1f3a] via-[#103f80] to-[#1653a1] rounded-3xl p-6 md:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none">
          <RotateCcw class="w-72 h-72 text-white" />
        </div>
        
        <div class="relative z-10 space-y-2">
          <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 backdrop-blur border border-white/20 rounded-full text-xs text-[#a9fff7] font-extrabold uppercase tracking-wider">
            <RotateCcw class="w-3.5 h-3.5" />
            <span>Program Repeat Order (RO) & Cashback RO</span>
          </div>
          <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white">Repeat Order (RO) & Cashback RO</h1>
          <p class="text-xs md:text-sm text-slate-200 max-w-3xl font-medium leading-relaxed">
            Tersedia 2 pilihan transaksi RO: <span class="font-bold text-[#a9fff7]">Voucher RO (Rp 125.000 / 1 Poin)</span> untuk transaksi satuan, atau mode cepat <span class="font-bold text-amber-300">Voucher Cash RO (Rp 4.375.000 / 35 Poin)</span> yang langsung memberikan <span class="text-white font-bold">Cashback Rp 500.000</span> ke User serta <span class="font-bold text-[#a9fff7]">Bonus Sponsor Rp 700.000 + Matching Rp 100.000</span> ke Sponsor!
          </p>
        </div>
      </div>

      <!-- Flash Messages -->
      <div v-if="$page.props.flash?.success" class="p-4 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-2xl flex items-center justify-between text-xs font-bold shadow-sm animate-fade-in">
        <div class="flex items-center gap-2">
          <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
          <span>{{ $page.props.flash?.success }}</span>
        </div>
      </div>

      <div v-if="$page.props.flash?.error" class="p-4 bg-rose-50 border border-rose-300 text-rose-800 rounded-2xl flex items-center justify-between text-xs font-bold shadow-sm animate-fade-in">
        <div class="flex items-center gap-2">
          <XCircle class="w-4 h-4 text-rose-600 shrink-0" />
          <span>{{ $page.props.flash?.error }}</span>
        </div>
      </div>

      <!-- Stats Overview Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Card 1: Total Poin RO -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between relative overflow-hidden group hover:border-[#04bdb2]/50 transition-all">
          <div class="space-y-1">
            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Poin RO Terkumpul</span>
            <div class="flex items-baseline gap-2">
              <span class="text-3xl font-black text-slate-900">{{ ro_stats.total_ro_points }}</span>
              <span class="text-xs font-extrabold text-[#04bdb2]">Poin RO</span>
            </div>
            <p class="text-[10px] text-slate-500">Konversi Rp 500.000 / 35 Poin</p>
          </div>
          <div class="w-11 h-11 rounded-2xl bg-[#04bdb2]/10 border border-[#04bdb2]/30 text-[#04bdb2] flex items-center justify-center font-bold shadow-xs">
            <Award class="w-5 h-5" />
          </div>
        </div>

        <!-- Card 2: Stok Voucher RO Tersedia -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between relative overflow-hidden group hover:border-[#1653a1]/50 transition-all">
          <div class="space-y-1">
            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Stok Voucher RO</span>
            <div class="flex items-baseline gap-2">
              <span class="text-3xl font-black text-[#1653a1]">{{ ro_stats.available_ro_vouchers_count }}</span>
              <span class="text-xs font-bold text-slate-500">Voucher</span>
            </div>
            <p class="text-[10px] text-slate-500">
              <span class="font-bold text-[#1653a1]">{{ ro_stats.regular_ro_vouchers_count || 0 }} RO</span> &middot; 
              <span class="font-bold text-amber-600">{{ ro_stats.cashback_ro_vouchers_count || 0 }} Cash RO</span>
            </p>
          </div>
          <div class="w-11 h-11 rounded-2xl bg-[#1653a1]/10 border border-[#1653a1]/30 text-[#1653a1] flex items-center justify-center font-bold shadow-xs">
            <KeyRound class="w-5 h-5" />
          </div>
        </div>

        <!-- Card 3: Total Bonus Sponsor RO -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between relative overflow-hidden group hover:border-amber-500/50 transition-all">
          <div class="space-y-1">
            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Bonus Sponsor RO Diterima</span>
            <div class="flex items-baseline gap-1">
              <span class="text-2xl font-black text-amber-600">{{ formatRupiah(ro_stats.total_ro_bonus) }}</span>
            </div>
            <p class="text-[10px] text-slate-500">Rp 20.000 / RO & Rp 700.000 / Cash RO</p>
          </div>
          <div class="w-11 h-11 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-600 flex items-center justify-center font-bold shadow-xs">
            <TrendingUp class="w-5 h-5" />
          </div>
        </div>

        <!-- Card 4: Matching Bonus RO -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between relative overflow-hidden group hover:border-emerald-500/50 transition-all">
          <div class="space-y-1">
            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Matching Bonus RO</span>
            <div class="flex items-baseline gap-1">
              <span class="text-2xl font-black text-emerald-600">{{ formatRupiah(ro_stats.matching_ro_bonus || 0) }}</span>
            </div>
            <p class="text-[10px] text-slate-500">Rp 100k per kelipatan 35 Poin RO</p>
          </div>
          <div class="w-11 h-11 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 flex items-center justify-center font-bold shadow-xs">
            <ShieldCheck class="w-5 h-5" />
          </div>
        </div>
      </div>

      <!-- Action Section: Claim RO Form & Buy Voucher Button -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Client Mockup Style Activation Card -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200/80 p-6 md:p-8 shadow-sm space-y-6">
          
          <div class="space-y-4 max-w-md mx-auto text-center pt-2">
            <!-- Header Icon & Title -->
            <div class="space-y-1">
              <div class="w-12 h-12 rounded-2xl bg-[#04bdb2]/10 border border-[#04bdb2]/30 text-[#04bdb2] flex items-center justify-center font-bold mx-auto shadow-sm">
                <RotateCcw class="w-6 h-6" />
              </div>
              <h2 class="text-xl font-black tracking-tight text-slate-900 uppercase">AKTIVASI REPEAT ORDER</h2>
              <p class="text-xs text-slate-500 font-medium">
                Pilih Voucher RO atau Voucher Cash RO yang tersedia di gudang Anda
              </p>
            </div>

            <!-- Pill Box: Voucher RO tersedia -->
            <div class="border-2 border-slate-900 rounded-full py-3 px-6 flex items-center justify-between text-sm font-bold text-slate-900 bg-white shadow-xs">
              <span>Total Voucher RO Anda</span>
              <span class="text-base font-black text-[#1653a1]">{{ ro_stats.available_ro_vouchers_count }} Pcs</span>
            </div>

            <!-- Subtext: Total Value Calculation -->
            <div v-if="ro_stats.available_ro_vouchers_count > 0" class="flex items-center justify-center gap-4 text-xs font-semibold">
              <span class="px-3 py-1 bg-slate-100 rounded-full text-slate-700">
                Voucher RO: <strong>{{ ro_stats.regular_ro_vouchers_count || 0 }}</strong>
              </span>
              <span class="px-3 py-1 bg-amber-50 text-amber-800 border border-amber-200 rounded-full font-bold">
                Voucher Cash RO: <strong>{{ ro_stats.cashback_ro_vouchers_count || 0 }}</strong>
              </span>
            </div>
            <p v-else class="text-xs font-semibold text-slate-500">
              Anda belum memiliki stok Voucher RO / Cash RO di gudang.
            </p>

            <!-- If user has vouchers: Show Select & AKTIVASI RO Button -->
            <form v-if="available_ro_vouchers.length > 0" @submit.prevent="submitClaimRo" class="space-y-4 pt-2">
              <div class="text-left">
                <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-1">
                  Pilih Kode Voucher untuk Diaktivasi:
                </label>
                <select 
                  v-model="claimForm.voucher_code"
                  class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-800 focus:ring-2 focus:ring-[#04bdb2] transition-all"
                  required
                >
                  <option v-for="v in available_ro_vouchers" :key="v.id" :value="v.code">
                    {{ v.code }} - {{ v.package_name }} (+{{ v.points }} Poin)
                  </option>
                </select>
              </div>

              <!-- Selected Voucher Dynamic Highlight Box -->
              <div v-if="selectedClaimVoucher" class="p-4 rounded-2xl text-left text-xs border space-y-1.5" :class="selectedClaimVoucher.voucher_type === 'ro_cashback' ? 'bg-amber-50/70 border-amber-300 text-amber-900' : 'bg-teal-50/70 border-teal-200 text-teal-950'">
                <div class="flex items-center justify-between font-black">
                  <span class="flex items-center gap-1.5">
                    <Sparkles class="w-4 h-4" :class="selectedClaimVoucher.voucher_type === 'ro_cashback' ? 'text-amber-600' : 'text-[#04bdb2]'" />
                    {{ selectedClaimVoucher.voucher_type === 'ro_cashback' ? 'MODE CEPAT: VOUCHER CASH RO' : 'MODE SATUAN: VOUCHER RO' }}
                  </span>
                  <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider" :class="selectedClaimVoucher.voucher_type === 'ro_cashback' ? 'bg-amber-200 text-amber-900' : 'bg-teal-200 text-teal-900'">
                    +{{ selectedClaimVoucher.points }} Poin
                  </span>
                </div>
                <p class="text-[11px] leading-relaxed">
                  <template v-if="selectedClaimVoucher.voucher_type === 'ro_cashback'">
                    Aktivasi ini langsung memberikan <strong>+35 Poin RO & Cashback Rp 500.000</strong> ke Anda. Sponsor langsung mendapatkan <strong>Bonus Sponsor Rp 700.000 + Matching Bonus Rp 100.000</strong>.
                  </template>
                  <template v-else>
                    Aktivasi ini memberikan <strong>+1 Poin RO</strong> untuk Anda dan <strong>Bonus Sponsor Rp 20.000</strong> untuk Sponsor langsung Anda.
                  </template>
                </p>
              </div>

              <button 
                type="submit" 
                :disabled="claimForm.processing"
                class="w-full py-3.5 px-8 bg-[#04bdb2] hover:bg-[#009c94] text-white font-black text-xs uppercase tracking-widest rounded-full shadow-md shadow-[#04bdb2]/30 transition-all cursor-pointer"
              >
                {{ claimForm.processing ? 'MEMPROSES...' : 'AKTIVASI RO SEKARANG' }}
              </button>
            </form>

            <!-- If user HAS NO vouchers (count === 0): Show BELI Button -->
            <div v-else class="pt-2">
              <button 
                @click="buyVoucherModalOpen = true"
                class="w-full py-3.5 px-8 bg-[#04bdb2] hover:bg-[#009c94] text-white font-black text-xs uppercase tracking-widest rounded-full shadow-md shadow-[#04bdb2]/30 transition-all cursor-pointer"
              >
                BELI VOUCHER RO SEKARANG
              </button>
            </div>
          </div>

          <!-- Benefit Summary List -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
            <!-- Summary RO Regular -->
            <div class="p-4 bg-[#f0f7fb] border border-[#04bdb2]/30 rounded-2xl space-y-2 text-xs text-slate-700">
              <span class="font-black text-[#1653a1] block uppercase text-[11px] tracking-wider flex items-center gap-1.5">
                <RotateCcw class="w-3.5 h-3.5 text-[#04bdb2]" />
                1. Voucher RO (Rp 125.000)
              </span>
              <ul class="space-y-1 list-disc list-inside text-[11px] font-medium text-slate-600">
                <li><strong class="text-slate-900">+1 Poin RO</strong> untuk member.</li>
                <li><strong class="text-slate-900">Rp 20.000</strong> Bonus Sponsor Tier 1.</li>
                <li>Setiap 35 Poin RO: Reward Rp 500k + Matching Rp 100k.</li>
              </ul>
            </div>

            <!-- Summary Cash RO -->
            <div class="p-4 bg-amber-50/70 border border-amber-300/80 rounded-2xl space-y-2 text-xs text-amber-950">
              <span class="font-black text-amber-800 block uppercase text-[11px] tracking-wider flex items-center gap-1.5">
                <Zap class="w-3.5 h-3.5 text-amber-600" />
                2. Voucher Cash RO (Rp 4.375.000)
              </span>
              <ul class="space-y-1 list-disc list-inside text-[11px] font-medium text-amber-900">
                <li><strong class="text-slate-900">+35 Poin RO Langsung</strong>.</li>
                <li><strong class="text-slate-900">Cashback Rp 500.000</strong> langsung ke User.</li>
                <li><strong class="text-slate-900">Sponsor Rp 700.000 + Matching Rp 100.000</strong>.</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Right: Buy Voucher RO Widget -->
        <div class="bg-gradient-to-b from-white to-slate-50/80 rounded-3xl border border-slate-200/80 p-6 shadow-sm flex flex-col justify-between space-y-6">
          <div class="space-y-4">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center font-bold">
                <Gift class="w-5 h-5" />
              </div>
              <div>
                <h3 class="text-sm font-black text-slate-900">Beli Voucher RO / Cash RO</h3>
                <p class="text-[11px] text-slate-500 font-medium">Beli voucher menggunakan saldo wallet Anda</p>
              </div>
            </div>

            <div class="p-4 bg-white border border-slate-200/80 rounded-2xl space-y-2.5 shadow-2xs">
              <div class="flex items-center justify-between text-xs">
                <span class="text-slate-500 font-medium">Saldo Wallet Anda:</span>
                <span class="font-black text-slate-900">{{ formatRupiah(user_saldo) }}</span>
              </div>
              <div class="border-t border-slate-100 pt-2 space-y-1.5">
                <div class="flex items-center justify-between text-xs">
                  <span class="text-slate-600 font-medium">Voucher RO:</span>
                  <span class="font-black text-[#1653a1]">Rp 125.000 / pcs</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                  <span class="text-slate-600 font-medium">Voucher Cash RO:</span>
                  <span class="font-black text-amber-600">Rp 4.375.000 / pcs</span>
                </div>
              </div>
            </div>

            <div class="p-3 bg-[#f0f7fb] border border-[#1653a1]/20 rounded-2xl text-[11px] text-slate-600 space-y-1">
              <span class="font-bold text-[#1653a1] block">Info Rekening Perusahaan:</span>
              <p class="font-black text-slate-900">{{ company_bank?.bank_name || 'Bank BRI' }} - {{ company_bank?.account_number || '806401000095564' }}</p>
              <p class="text-[10px] text-slate-500">a.n {{ company_bank?.account_name || 'PT.Xseller Punya Kita' }}</p>
            </div>
          </div>

          <button 
            @click="buyVoucherModalOpen = true"
            class="w-full py-3.5 px-4 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-2xl shadow-md transition-all cursor-pointer flex items-center justify-center gap-2"
          >
            <Plus class="w-4 h-4 text-[#04bdb2]" />
            <span>Beli Voucher RO / Cash RO</span>
          </button>
        </div>

      </div>

      <!-- PRODUK RO CATALOG SECTION (Matching Client Image 1 Mockup) -->
      <div class="bg-white rounded-3xl border border-slate-200/80 p-6 md:p-8 shadow-sm space-y-6">
        <div class="text-center space-y-1">
          <h2 class="text-2xl md:text-3xl font-black text-[#5c3a21] uppercase tracking-wide">PRODUK RO</h2>
          <p class="text-xs text-slate-500 font-medium">Katalog Pilihan Produk Paket Repeat Order (RO) Rp 125.000</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6 pt-4">
          <div 
            v-for="item in (products && products.length > 0 ? products : [])" 
            :key="item.id || item.name" 
            class="bg-slate-50/60 border border-slate-200/70 rounded-3xl p-5 text-center space-y-3 flex flex-col justify-between hover:shadow-md transition-all"
          >
            <div class="space-y-3">
              <div class="w-full aspect-square rounded-2xl bg-white border border-slate-200/60 overflow-hidden flex items-center justify-center p-2 shadow-2xs">
                <img v-if="item.image" :src="item.image" :alt="item.name" class="w-full h-full object-contain" />
                <div v-else class="w-full h-full bg-amber-900/10 rounded-xl flex items-center justify-center font-black text-amber-900 text-sm">
                  💊 {{ item.name }}
                </div>
              </div>
              <div class="text-left space-y-0.5">
                <h3 class="text-base font-black text-[#5c3a21] uppercase tracking-tight">{{ item.name }}</h3>
                <p class="text-sm font-extrabold text-slate-900">{{ formatRupiah(item.price) }}</p>
                <p class="text-xs text-slate-500 font-bold">Jumlah : {{ item.quantity }}</p>
                <p v-if="item.description" class="text-[10px] text-slate-400 font-medium line-clamp-2 mt-0.5">{{ item.description }}</p>
              </div>
            </div>
            <div class="pt-2 flex justify-start">
              <span class="px-3 py-1 bg-[#5c3a21] text-white text-xs font-black rounded-full flex items-center gap-1.5 shadow-2xs">
                <span>Poin</span>
                <span class="w-4 h-4 rounded-full bg-white text-[#5c3a21] text-[10px] font-black flex items-center justify-center">{{ item.points || 1 }}</span>
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Transaction History Table -->
      <div class="bg-white rounded-3xl border border-slate-200/80 p-6 md:p-8 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
          <div>
            <h3 class="text-base font-black text-slate-900">Riwayat Transaksi Repeat Order</h3>
            <p class="text-xs text-slate-500 font-medium">Catatan klaim RO & bonus sponsor RO yang masuk</p>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="border-b border-slate-200/80 bg-slate-50/50 text-[10px] uppercase tracking-wider text-slate-400 font-black">
                <th class="py-3 px-4">Tanggal</th>
                <th class="py-3 px-4">Kategori</th>
                <th class="py-3 px-4">Kode Voucher</th>
                <th class="py-3 px-4">User Klaim</th>
                <th class="py-3 px-4">Sponsor (Penerima Bonus)</th>
                <th class="py-3 px-4 text-center">Poin RO</th>
                <th class="py-3 px-4 text-right">Bonus Sponsor</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-semibold text-slate-700">
              <tr v-for="ro in repeat_orders" :key="ro.id" class="hover:bg-slate-50/60 transition-colors">
                <td class="py-3.5 px-4 whitespace-nowrap text-slate-500 font-medium">{{ ro.created_at }}</td>
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <span 
                    :class="[
                      ro.is_user ? 'bg-teal-50 text-[#009c94] border-teal-200' : 'bg-amber-50 text-amber-700 border-amber-200',
                      'px-2.5 py-0.5 rounded-full border text-[10px] font-extrabold uppercase'
                    ]"
                  >
                    {{ ro.type_label }}
                  </span>
                </td>
                <td class="py-3.5 px-4 whitespace-nowrap font-mono font-bold text-slate-800">{{ ro.voucher_code }}</td>
                <td class="py-3.5 px-4 whitespace-nowrap">{{ ro.user_name }}</td>
                <td class="py-3.5 px-4 whitespace-nowrap text-slate-600">{{ ro.sponsor_name }}</td>
                <td class="py-3.5 px-4 whitespace-nowrap text-center">
                  <span class="font-extrabold text-[#04bdb2] bg-[#04bdb2]/10 px-2 py-0.5 rounded-md">+{{ ro.ro_points }}</span>
                </td>
                <td class="py-3.5 px-4 whitespace-nowrap text-right font-black text-slate-900">
                  {{ formatRupiah(ro.sponsor_bonus) }}
                </td>
              </tr>

              <tr v-if="repeat_orders.length === 0">
                <td colspan="7" class="py-8 text-center text-slate-400 font-medium">
                  Belum ada riwayat transaksi Repeat Order.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>

    <!-- Modal Buy / Produce Voucher RO & Cash RO -->
    <div v-if="buyVoucherModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-5 shadow-2xl border border-slate-100 animate-fade-in">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <h3 class="text-base font-black text-slate-900">Beli Voucher Repeat Order</h3>
          <button @click="buyVoucherModalOpen = false" class="text-slate-400 hover:text-slate-700">
            <XCircle class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitBuyVoucher" class="space-y-4">
          <!-- Voucher Type Radio Cards -->
          <div class="space-y-2">
            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider">
              Pilih Jenis Voucher RO:
            </label>
            <div class="grid grid-cols-2 gap-3">
              <label 
                class="border-2 rounded-2xl p-3.5 flex flex-col justify-between cursor-pointer transition-all"
                :class="buyVoucherForm.voucher_type === 'ro' ? 'border-[#04bdb2] bg-teal-50/40 text-slate-900' : 'border-slate-200 hover:border-slate-300 text-slate-600'"
              >
                <div class="flex items-center justify-between">
                  <span class="text-xs font-black">Voucher RO</span>
                  <input type="radio" v-model="buyVoucherForm.voucher_type" value="ro" class="text-[#04bdb2] focus:ring-[#04bdb2]" />
                </div>
                <div class="mt-2">
                  <p class="text-sm font-black text-[#1653a1]">Rp 125.000</p>
                  <p class="text-[10px] text-slate-500 font-medium">Satuan &middot; +1 Poin RO</p>
                </div>
              </label>

              <label 
                class="border-2 rounded-2xl p-3.5 flex flex-col justify-between cursor-pointer transition-all"
                :class="buyVoucherForm.voucher_type === 'ro_cashback' ? 'border-amber-500 bg-amber-50/40 text-slate-900' : 'border-slate-200 hover:border-slate-300 text-slate-600'"
              >
                <div class="flex items-center justify-between">
                  <span class="text-xs font-black">Voucher Cash RO</span>
                  <input type="radio" v-model="buyVoucherForm.voucher_type" value="ro_cashback" class="text-amber-500 focus:ring-amber-500" />
                </div>
                <div class="mt-2">
                  <p class="text-sm font-black text-amber-600">Rp 4.375.000</p>
                  <p class="text-[10px] text-slate-500 font-medium">35 RO + Rp 500k Cashback</p>
                </div>
              </label>
            </div>
          </div>

          <!-- Quantity Selection -->
          <div>
            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">
              Jumlah Voucher (1 s/d 35 Pcs):
            </label>
            <input 
              type="number" 
              v-model.number="buyVoucherForm.quantity" 
              min="1" 
              max="35" 
              class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-[#04bdb2]"
              required
            />
          </div>

          <!-- Admin Produce Toggle -->
          <div v-if="is_admin" class="p-3 bg-amber-50/60 border border-amber-200/80 rounded-2xl space-y-2 text-xs">
            <label class="flex items-center gap-2 cursor-pointer font-bold text-amber-900">
              <input type="checkbox" v-model="buyVoucherForm.is_produce" class="rounded text-amber-600 focus:ring-amber-500" />
              <span>Mode Admin: Produksi Voucher Gratis</span>
            </label>
            <div v-if="buyVoucherForm.is_produce" class="pt-1">
              <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">
                Target Username Penerima (Opsional, kosongkan jika untuk Admin):
              </label>
              <input 
                type="text" 
                v-model="buyVoucherForm.target_username" 
                placeholder="Masukkan username penerima..." 
                class="w-full px-3 py-2 bg-white border border-amber-200 rounded-xl text-xs font-bold text-slate-900"
              />
            </div>
          </div>

          <!-- Payment Calculation Box -->
          <div v-if="!buyVoucherForm.is_produce" class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-2 text-xs">
            <div class="flex items-center justify-between">
              <span class="text-slate-500 font-medium">Harga Per Voucher:</span>
              <span class="font-bold text-slate-800">{{ formatRupiah(buyUnitPrice) }}</span>
            </div>
            <div class="flex items-center justify-between font-bold">
              <span class="text-slate-700">Total Pembayaran ({{ buyVoucherForm.quantity || 1 }} Pcs):</span>
              <span class="font-black text-[#1653a1] text-sm">{{ formatRupiah(buyTotalCost) }}</span>
            </div>
            <div class="flex items-center justify-between border-t border-slate-200 pt-2 text-slate-500">
              <span>Saldo Wallet Anda:</span>
              <span class="font-bold" :class="(user_saldo < buyTotalCost) ? 'text-rose-600' : 'text-slate-900'">
                {{ formatRupiah(user_saldo) }}
              </span>
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
              :disabled="buyVoucherForm.processing || (!buyVoucherForm.is_produce && user_saldo < buyTotalCost)"
              class="px-5 py-2.5 bg-[#1653a1] hover:bg-[#103f80] disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-black text-xs rounded-xl shadow-md transition-all cursor-pointer"
            >
              {{ buyVoucherForm.processing ? 'Memproses...' : (buyVoucherForm.is_produce ? 'Produksi Gratis' : `Konfirmasi Beli (${formatRupiah(buyTotalCost)})`) }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
