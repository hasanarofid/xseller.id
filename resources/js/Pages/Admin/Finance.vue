<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { 
  Wallet, 
  TrendingUp, 
  ArrowRightLeft, 
  CheckCircle2, 
  AlertCircle, 
  Send, 
  ShieldCheck, 
  CreditCard,
  Building2
} from '@lucide/vue';

const props = defineProps({
  wallet: Object,
  transactions: Array,
  is_admin: Boolean,
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

// Form Cashout Bonus
const cashoutForm = useForm({});
const submitCashout = () => {
  cashoutForm.post(route('admin.finance.cashout'), {
    preserveScroll: true,
  });
};

// Form Generate / Create Saldo Wallet Admin & Member
const generateForm = useForm({
  username: '',
  amount: '',
});
const submitGenerateSaldo = () => {
  generateForm.post(route('admin.finance.generate-saldo'), {
    preserveScroll: true,
    onSuccess: () => generateForm.reset(),
  });
};

// Form Transfer Saldo ke Member
const transferForm = useForm({
  recipient_username: '',
  amount: '',
  security_pin: '123456',
});
const submitTransfer = () => {
  transferForm.post(route('admin.finance.transfer'), {
    preserveScroll: true,
    onSuccess: () => {
      transferForm.recipient_username = '';
      transferForm.amount = '';
    },
  });
};

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
};
</script>

<template>
  <Head title="Keuangan & Mutasi Saldo - XSELLER" />

  <AdminLayout>
    <div class="space-y-6">
      
      <!-- Flash Alert Notifications -->
      <div v-if="flashSuccess" class="p-4 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-2xl text-xs font-semibold flex items-center justify-between shadow-sm animate-fade-in">
        <div class="flex items-center gap-2">
          <CheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0" />
          <span>{{ flashSuccess }}</span>
        </div>
      </div>

      <div v-if="flashError" class="p-4 bg-rose-50 border border-rose-300 text-rose-800 rounded-2xl text-xs font-semibold flex items-center justify-between shadow-sm animate-fade-in">
        <div class="flex items-center gap-2">
          <AlertCircle class="w-4 h-4 text-rose-500 shrink-0" />
          <span>{{ flashError }}</span>
        </div>
      </div>

      <!-- Main Layout Grid (Left: Balance & Forms, Right: History) -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- LEFT COLUMN: Wallet Cards & Action Forms (6 Cols) -->
        <div class="lg:col-span-6 space-y-6">
          
          <!-- 1. SALDO E-WALLET AKTIF CARD (Dark Midnight Card matching Mockup) -->
          <div class="bg-[#0d131d] text-white rounded-3xl p-6 shadow-xl relative overflow-hidden border border-slate-800 flex items-center justify-between gap-4">
            <div class="space-y-2">
              <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-400 flex items-center gap-1.5">
                SALDO E-WALLET AKTIF
              </span>
              <h2 class="text-3xl md:text-4xl font-black text-white tracking-tight">
                {{ formatRupiah(wallet?.saldo || 2500000) }}
              </h2>
              <p class="text-[11px] text-slate-400 font-medium">
                Batas penarikan harian maksimum: {{ formatRupiah(wallet?.max_daily_withdrawal || 50000000) }}
              </p>
            </div>

            <div class="w-14 h-14 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
              <Wallet class="w-7 h-7" />
            </div>
          </div>

          <!-- 2. TOTAL BONUS CAIR CARD (Forest Green Card matching Mockup) -->
          <div class="bg-[#064e3b] text-white rounded-3xl p-6 shadow-xl relative overflow-hidden border border-emerald-800/60 flex items-center justify-between gap-4">
            <div class="space-y-2">
              <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-200">
                TOTAL BONUS CAIR
              </span>
              <h2 class="text-3xl font-black text-white tracking-tight">
                {{ formatRupiah(wallet?.total_bonus_cair || 0) }}
              </h2>
              <div class="pt-1">
                <button 
                  @click="submitCashout"
                  :disabled="cashoutForm.processing"
                  class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 active:bg-emerald-700 text-white text-xs font-extrabold rounded-xl shadow-md transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
                >
                  <span>Cairkan ke E-Wallet</span>
                </button>
              </div>
            </div>

            <div class="w-14 h-14 rounded-2xl bg-white/10 text-emerald-200 flex items-center justify-center shrink-0">
              <TrendingUp class="w-7 h-7" />
            </div>
          </div>

          <!-- 3. GENERATE / CREATE SALDO WALLET CARD (Only visible if admin) -->
          <div v-if="is_admin" class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-start gap-3">
              <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl shrink-0">
                <CreditCard class="w-5 h-5" />
              </div>
              <div>
                <h3 class="text-xs font-extrabold text-slate-900 uppercase tracking-wide">GENERATE / CREATE SALDO WALLET</h3>
                <p class="text-xs text-slate-500 mt-0.5">Isi atau tambahkan saldo wallet member / admin setelah konfirmasi transfer.</p>
              </div>
            </div>

            <form @submit.prevent="submitGenerateSaldo" class="space-y-3 pt-1">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                  TARGET USERNAME (KOSONGKAN JIKA UNTUK ADMIN)
                </label>
                <input 
                  v-model="generateForm.username"
                  type="text"
                  placeholder="cth: budisantoso"
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs font-semibold focus:outline-none focus:border-emerald-500"
                />
              </div>

              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                  NOMINAL SALDO (RP)
                </label>
                <input 
                  v-model.number="generateForm.amount"
                  type="number"
                  required
                  min="1000"
                  step="1000"
                  placeholder="cth: 2600000"
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs font-semibold focus:outline-none focus:border-emerald-500"
                />
              </div>

              <button 
                type="submit"
                :disabled="generateForm.processing"
                class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold rounded-2xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
              >
                <span>Generate Saldo Wallet</span>
              </button>
            </form>
          </div>

          <!-- 4. KIRIM SALDO KE MEMBER CARD -->
          <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-start gap-3">
              <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl shrink-0">
                <ArrowRightLeft class="w-5 h-5" />
              </div>
              <div>
                <h3 class="text-xs font-extrabold text-slate-900 uppercase tracking-wide">KIRIM SALDO KE MEMBER</h3>
                <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">
                  Transfer saldo secara instan ke sesama member di jaringan tanpa biaya admin.
                </p>
              </div>
            </div>

            <form @submit.prevent="submitTransfer" class="space-y-3 pt-1">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                  USERNAME PENERIMA
                </label>
                <input 
                  v-model="transferForm.recipient_username"
                  type="text"
                  required
                  placeholder="@ cth: siti"
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 text-xs font-medium focus:outline-none focus:border-emerald-500"
                />
              </div>

              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                  JUMLAH SALDO (RP)
                </label>
                <input 
                  v-model="transferForm.amount"
                  type="number"
                  required
                  min="1000"
                  step="1000"
                  placeholder="cth: 50000"
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 text-xs font-medium focus:outline-none focus:border-emerald-500"
                />
              </div>

              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                  PIN KEAMANAN AKUN
                </label>
                <input 
                  v-model="transferForm.security_pin"
                  type="password"
                  required
                  placeholder="default: 123456 / 111111"
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 text-xs font-medium focus:outline-none focus:border-emerald-500"
                />
              </div>

              <button 
                type="submit"
                :disabled="transferForm.processing"
                class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold rounded-2xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
              >
                <Send class="w-4 h-4" />
                <span>Kirim Saldo Sekarang</span>
              </button>
            </form>
          </div>

        </div>

        <!-- RIGHT COLUMN: Financial History (6 Cols) -->
        <div class="lg:col-span-6 space-y-6">
          
          <!-- 5. RIWAYAT KEUANGAN & PENCAIRAN BONUS CARD -->
          <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
              <h3 class="text-xs font-extrabold text-slate-900 uppercase tracking-wide">
                RIWAYAT KEUANGAN & PENCAIRAN BONUS (SEMUA MEMBER)
              </h3>
              <span class="px-2.5 py-1 text-[10px] font-extrabold bg-slate-100 text-slate-600 rounded-full">
                {{ transactions.length }} Catatan
              </span>
            </div>

            <!-- Mutation History List -->
            <div class="space-y-3">
              <div 
                v-for="item in transactions" 
                :key="item.id" 
                class="p-4 bg-slate-50/70 border border-slate-100 rounded-2xl flex items-center justify-between gap-4 hover:bg-slate-50 transition-colors"
              >
                <div class="flex items-center gap-3">
                  <span 
                    :class="[
                      item.type === 'KELUAR' ? 'bg-rose-100 text-rose-600 border-rose-200' : 'bg-emerald-100 text-emerald-600 border-emerald-200',
                      'px-2 py-1 text-[9px] font-extrabold rounded-md border uppercase tracking-wider shrink-0'
                    ]"
                  >
                    {{ item.type }}
                  </span>
                  <div>
                    <h4 class="text-xs font-bold text-slate-800 leading-tight">{{ item.description }}</h4>
                    <p class="text-[10px] text-slate-400 mt-0.5 font-medium">{{ item.created_at }}</p>
                  </div>
                </div>

                <div class="shrink-0 text-right">
                  <span 
                    :class="[
                      item.is_income ? 'text-emerald-600' : 'text-rose-600',
                      'font-black text-xs font-mono tracking-tight block'
                    ]"
                  >
                    {{ item.amount }}
                  </span>
                </div>
              </div>

              <div v-if="transactions.length === 0" class="py-12 text-center text-slate-400 text-xs italic">
                Belum ada riwayat transaksi keuangan.
              </div>
            </div>
          </div>

        </div>

      </div>

    </div>
  </AdminLayout>
</template>
