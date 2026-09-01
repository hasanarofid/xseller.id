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
  Send 
} from '@lucide/vue';

const props = defineProps({
  po_stats: Object,
  po_packages: Array,
  purchase_orders: Array,
  user_saldo: Number,
  is_admin: Boolean,
});

const page = usePage();
const selectedPackageId = ref(props.po_packages[0]?.id || 'po_550');

const poForm = useForm({
  package_id: selectedPackageId.value,
});

const submitPo = () => {
  poForm.package_id = selectedPackageId.value;
  poForm.post(route('admin.purchase-order.store'), {
    preserveScroll: true,
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
            Beli paket produk tambahan (PO) untuk mendapatkan akumulasi <span class="font-bold text-[#a9fff7]">Poin Personal Reward</span> dan mendistribusikan <span class="font-bold text-white">Bonus Alokasi 15 Generasi Tier</span> hingga Rp 50.000 / generasi.
          </p>
        </div>
      </div>

      <!-- Flash Messages -->
      <div v-if="$page.props.flash.success" class="p-4 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-2xl flex items-center justify-between text-xs font-bold shadow-sm">
        <div class="flex items-center gap-2">
          <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
          <span>{{ $page.props.flash.success }}</span>
        </div>
      </div>

      <div v-if="$page.props.flash.error" class="p-4 bg-rose-50 border border-rose-300 text-rose-800 rounded-2xl flex items-center justify-between text-xs font-bold shadow-sm">
        <div class="flex items-center gap-2">
          <XCircle class="w-4 h-4 text-rose-600 shrink-0" />
          <span>{{ $page.props.flash.error }}</span>
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

        <!-- Card 2: Total Order PO -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between relative overflow-hidden group hover:border-[#1653a1]/50 transition-all">
          <div class="space-y-1">
            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Total Transaksi PO</span>
            <div class="flex items-baseline gap-2">
              <span class="text-3xl font-black text-[#1653a1]">{{ po_stats.total_orders }}</span>
              <span class="text-xs font-bold text-slate-500">Kali</span>
            </div>
            <p class="text-[10px] text-slate-500">Transaksi Pembelian Produk PO</p>
          </div>
          <div class="w-12 h-12 rounded-2xl bg-[#1653a1]/10 border border-[#1653a1]/30 text-[#1653a1] flex items-center justify-center font-bold shadow-xs">
            <ShoppingBag class="w-6 h-6" />
          </div>
        </div>

        <!-- Card 3: Saldo Wallet Anda -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between relative overflow-hidden group hover:border-amber-500/50 transition-all">
          <div class="space-y-1">
            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Saldo Wallet Anda</span>
            <div class="flex items-baseline gap-1">
              <span class="text-2xl font-black text-amber-600">{{ formatRupiah(user_saldo) }}</span>
            </div>
            <p class="text-[10px] text-slate-500">Gunakan saldo untuk transaksi PO</p>
          </div>
          <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-600 flex items-center justify-center font-bold shadow-xs">
            <CreditCard class="w-6 h-6" />
          </div>
        </div>
      </div>

      <!-- PO Packages Selection Grid -->
      <div class="bg-white rounded-3xl border border-slate-200/80 p-6 md:p-8 shadow-sm space-y-6">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
          <div>
            <h2 class="text-base font-black text-slate-900">Pilih Paket Purchase Order (PO)</h2>
            <p class="text-xs text-slate-500 font-medium">Pilih nominal PO untuk mendapatkan Poin PO & alokasi 15 generasi tier</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div 
            v-for="pkg in po_packages" 
            :key="pkg.id"
            @click="selectedPackageId = pkg.id"
            :class="[
              selectedPackageId === pkg.id 
                ? 'border-2 border-[#04bdb2] bg-[#f0f7fb] shadow-md ring-2 ring-[#04bdb2]/20' 
                : 'border border-slate-200 hover:border-slate-300 bg-white shadow-xs',
              'p-6 rounded-3xl cursor-pointer transition-all flex flex-col justify-between space-y-4 relative overflow-hidden'
            ]"
          >
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="px-3 py-1 bg-[#1653a1]/10 text-[#1653a1] text-[10px] font-black uppercase tracking-wider rounded-full">
                  {{ pkg.name }}
                </span>
                <span class="text-xs font-black text-[#04bdb2] bg-[#04bdb2]/10 px-2.5 py-1 rounded-full">
                  +{{ pkg.po_points }} Poin PO
                </span>
              </div>

              <div class="space-y-1">
                <span class="text-2xl font-black text-slate-900">{{ formatRupiah(pkg.amount) }}</span>
                <p class="text-xs text-slate-600 font-medium leading-relaxed">{{ pkg.description }}</p>
              </div>

              <div class="p-3 bg-white/80 border border-slate-200/60 rounded-2xl text-[11px] space-y-1.5 font-medium text-slate-700">
                <div class="flex items-center justify-between">
                  <span class="text-slate-500">Alokasi Tier 15 Generasi:</span>
                  <span class="font-bold text-slate-900">{{ formatRupiah(pkg.tier_allocation) }} / gen</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-slate-500">Bonus Poin Personal:</span>
                  <span class="font-extrabold text-[#009c94]">+{{ pkg.po_points }} Poin Reward</span>
                </div>
              </div>
            </div>

            <div class="pt-2 flex items-center justify-between">
              <span class="text-xs font-bold" :class="selectedPackageId === pkg.id ? 'text-[#1653a1]' : 'text-slate-400'">
                {{ selectedPackageId === pkg.id ? '✓ Paket Dipilih' : 'Klik untuk Pilih' }}
              </span>
              <div 
                :class="[
                  selectedPackageId === pkg.id ? 'bg-[#04bdb2] text-white' : 'bg-slate-100 text-slate-400',
                  'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold'
                ]"
              >
                ✓
              </div>
            </div>
          </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-between flex-wrap gap-4">
          <div class="text-xs">
            <span class="text-slate-500 font-medium">Total Pembayaran: </span>
            <span class="text-lg font-black text-[#1653a1] ml-2">
              {{ formatRupiah(po_packages.find(p => p.id === selectedPackageId)?.amount || 0) }}
            </span>
          </div>

          <button 
            @click="submitPo" 
            :disabled="poForm.processing"
            class="py-3 px-8 bg-gradient-to-r from-[#1653a1] to-[#04bdb2] hover:from-[#103f80] hover:to-[#009c94] text-white font-black text-xs uppercase tracking-wider rounded-2xl shadow-lg shadow-[#1653a1]/20 transition-all cursor-pointer flex items-center gap-2"
          >
            <ShoppingBag class="w-4 h-4" />
            <span>Proses Purchase Order Sekarang</span>
          </button>
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
  </AdminLayout>
</template>
