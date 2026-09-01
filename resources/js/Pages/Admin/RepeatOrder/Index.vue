<script setup>
import { ref } from 'vue';
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
  CreditCard
} from '@lucide/vue';

const props = defineProps({
  ro_stats: Object,
  available_ro_vouchers: Array,
  repeat_orders: Array,
  user_saldo: Number,
  user_package: String,
  is_admin: Boolean,
});

const page = usePage();
const flashSuccess = ref(page.props.flash?.success || null);
const flashError = ref(page.props.flash?.error || null);

// Form Claim Repeat Order using Voucher RO
const claimForm = useForm({
  voucher_code: props.available_ro_vouchers.length > 0 ? props.available_ro_vouchers[0].code : '',
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
  is_produce: false,
  target_username: '',
});

const submitBuyVoucher = () => {
  buyVoucherForm.post(route('admin.repeat-order.buy-voucher'), {
    preserveScroll: true,
    onSuccess: () => {
      buyVoucherModalOpen.value = false;
      buyVoucherForm.reset();
    },
  });
};

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);
};
</script>

<template>
  <Head title="Repeat Order (RO) - XSELLER" />

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
            <span>Program Repeat Order (RO)</span>
          </div>
          <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white">Repeat Order & Perolehan Poin RO</h1>
          <p class="text-xs md:text-sm text-slate-200 max-w-2xl font-medium">
            Lakukan Repeat Order senilai <span class="font-bold text-[#a9fff7]">Rp 125.000 (Voucher RO)</span>. Setiap transaksi Repeat Order memberikan <span class="font-bold text-white">1 Poin RO</span> untuk Anda dan <span class="font-bold text-[#a9fff7]">Bonus Tier 1 (Rp 20.000)</span> untuk Sponsor langsung Anda!
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
        <!-- Card 1: Total Poin RO -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between relative overflow-hidden group hover:border-[#04bdb2]/50 transition-all">
          <div class="space-y-1">
            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Poin RO Terkumpul</span>
            <div class="flex items-baseline gap-2">
              <span class="text-3xl font-black text-slate-900">{{ ro_stats.total_ro_points }}</span>
              <span class="text-xs font-extrabold text-[#04bdb2]">Poin RO</span>
            </div>
            <p class="text-[10px] text-slate-500">Konversi Rp 500.000 / 35 Poin</p>
          </div>
          <div class="w-12 h-12 rounded-2xl bg-[#04bdb2]/10 border border-[#04bdb2]/30 text-[#04bdb2] flex items-center justify-center font-bold shadow-xs">
            <Award class="w-6 h-6" />
          </div>
        </div>

        <!-- Card 2: Stok Voucher RO Tersedia -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between relative overflow-hidden group hover:border-[#1653a1]/50 transition-all">
          <div class="space-y-1">
            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Stok Voucher RO</span>
            <div class="flex items-baseline gap-2">
              <span class="text-3xl font-black text-[#1653a1]">{{ ro_stats.available_ro_vouchers_count }}</span>
              <span class="text-xs font-bold text-slate-500">Voucher</span>
            </div>
            <p class="text-[10px] text-slate-500">Voucher RO Aktif Rp 125.000</p>
          </div>
          <div class="w-12 h-12 rounded-2xl bg-[#1653a1]/10 border border-[#1653a1]/30 text-[#1653a1] flex items-center justify-center font-bold shadow-xs">
            <KeyRound class="w-6 h-6" />
          </div>
        </div>

        <!-- Card 3: Total Bonus Sponsor RO -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between relative overflow-hidden group hover:border-amber-500/50 transition-all">
          <div class="space-y-1">
            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Bonus Sponsor RO Diterima</span>
            <div class="flex items-baseline gap-1">
              <span class="text-2xl font-black text-amber-600">{{ formatRupiah(ro_stats.total_ro_bonus) }}</span>
            </div>
            <p class="text-[10px] text-slate-500">Rp 20.000 / Transaksi RO Bawahan</p>
          </div>
          <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-600 flex items-center justify-center font-bold shadow-xs">
            <TrendingUp class="w-6 h-6" />
          </div>
        </div>
      </div>

      <!-- Action Section: Claim RO Form & Buy Voucher Button -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Form Klaim Repeat Order -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200/80 p-6 md:p-8 shadow-sm space-y-6">
          <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-[#1653a1] to-[#04bdb2] text-white flex items-center justify-center shadow-md">
                <RotateCcw class="w-5 h-5" />
              </div>
              <div>
                <h2 class="text-base font-black text-slate-900">Klaim Repeat Order (RO)</h2>
                <p class="text-xs text-slate-500 font-medium">Gunakan Voucher RO untuk klaim 1 Poin RO & alokasi bonus sponsor Rp 20.000</p>
              </div>
            </div>
          </div>

          <form @submit.prevent="submitClaimRo" class="space-y-5">
            <div>
              <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                Pilih Kode Voucher RO (Rp 125.000)
              </label>
              
              <div v-if="available_ro_vouchers.length > 0">
                <select 
                  v-model="claimForm.voucher_code"
                  class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-800 focus:ring-2 focus:ring-[#04bdb2] focus:bg-white transition-all"
                  required
                >
                  <option v-for="v in available_ro_vouchers" :key="v.id" :value="v.code">
                    {{ v.code }} - ({{ v.package_name || 'Voucher RO Rp 125.000' }})
                  </option>
                </select>
              </div>

              <div v-else class="p-4 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-800 space-y-2">
                <p class="font-bold">⚠️ Anda belum memiliki Voucher RO aktif.</p>
                <p class="text-[11px]">Silakan beli Voucher RO terlebih dahulu menggunakan saldo wallet Anda.</p>
              </div>
            </div>

            <!-- Benefit Summary List -->
            <div class="p-4 bg-[#f0f7fb] border border-[#04bdb2]/30 rounded-2xl space-y-2 text-xs text-slate-700">
              <span class="font-black text-[#1653a1] block uppercase text-[10px] tracking-wider">Rincian Manfaat Repeat Order:</span>
              <ul class="space-y-1.5 list-disc list-inside text-[11px] font-medium text-slate-600">
                <li><strong class="text-slate-900">+1 Poin RO</strong> ditambahkan ke akumulasi Poin RO Anda.</li>
                <li><strong class="text-slate-900">Rp 20.000</strong> ditransfer sebagai Bonus Tier 1 ke Sponsor langsung Anda.</li>
                <li>Mendapatkan tambahan 1 paket produk sesuai ketentuan program Repeat Order.</li>
              </ul>
            </div>

            <button 
              type="submit" 
              :disabled="claimForm.processing || available_ro_vouchers.length === 0"
              class="w-full py-3.5 px-6 bg-gradient-to-r from-[#1653a1] to-[#04bdb2] hover:from-[#103f80] hover:to-[#009c94] text-white font-black text-xs uppercase tracking-wider rounded-2xl shadow-lg shadow-[#1653a1]/20 transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
              <RotateCcw class="w-4 h-4" />
              <span>Proses Repeat Order Sekarang</span>
            </button>
          </form>
        </div>

        <!-- Right: Buy Voucher RO Widget -->
        <div class="bg-gradient-to-b from-white to-slate-50/80 rounded-3xl border border-slate-200/80 p-6 shadow-sm flex flex-col justify-between space-y-6">
          <div class="space-y-4">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center font-bold">
                <Gift class="w-5 h-5" />
              </div>
              <div>
                <h3 class="text-sm font-black text-slate-900">Beli Voucher RO</h3>
                <p class="text-[11px] text-slate-500 font-medium">Nominal Rp 125.000 / Voucher</p>
              </div>
            </div>

            <div class="p-4 bg-white border border-slate-200/80 rounded-2xl space-y-2 shadow-2xs">
              <div class="flex items-center justify-between text-xs">
                <span class="text-slate-500 font-medium">Saldo Wallet Anda:</span>
                <span class="font-black text-slate-900">{{ formatRupiah(user_saldo) }}</span>
              </div>
              <div class="flex items-center justify-between text-xs">
                <span class="text-slate-500 font-medium">Harga 1 Voucher RO:</span>
                <span class="font-black text-[#1653a1]">Rp 125.000</span>
              </div>
            </div>

            <p class="text-[11px] text-slate-500 font-medium leading-relaxed">
              Voucher RO dapat digunakan untuk klaim Repeat Order sendiri atau ditransfer ke anggota tim mitra Anda.
            </p>
          </div>

          <button 
            @click="buyVoucherModalOpen = true"
            class="w-full py-3 px-4 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-2xl shadow-md transition-all cursor-pointer flex items-center justify-center gap-2"
          >
            <Plus class="w-4 h-4 text-[#04bdb2]" />
            <span>Beli Voucher RO (Rp 125.000)</span>
          </button>
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

    <!-- Modal Buy Voucher RO -->
    <div v-if="buyVoucherModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-5 shadow-2xl border border-slate-100 animate-fade-in">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <h3 class="text-base font-black text-slate-900">Beli Voucher Repeat Order</h3>
          <button @click="buyVoucherModalOpen = false" class="text-slate-400 hover:text-slate-700">
            <XCircle class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitBuyVoucher" class="space-y-4">
          <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-2 text-xs">
            <div class="flex items-center justify-between">
              <span class="text-slate-500 font-medium">Nama Paket Voucher:</span>
              <span class="font-bold text-slate-800">Repeat Order (RO)</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-slate-500 font-medium">Harga Voucher:</span>
              <span class="font-black text-[#1653a1]">Rp 125.000</span>
            </div>
            <div class="flex items-center justify-between border-t border-slate-200 pt-2">
              <span class="text-slate-500 font-medium">Saldo Wallet Anda:</span>
              <span class="font-black text-slate-900">{{ formatRupiah(user_saldo) }}</span>
            </div>
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
              Konfirmasi Beli (Rp 125.000)
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
