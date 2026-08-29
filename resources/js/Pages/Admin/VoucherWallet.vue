<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { 
  KeyRound, 
  Wallet, 
  Send, 
  Sparkles, 
  CheckCircle2, 
  AlertCircle, 
  ArrowRightLeft, 
  ShieldCheck,
  Clock,
  UserCheck,
  Building2
} from '@lucide/vue';

const props = defineProps({
  wallet: Object,
  voucher_price: Number,
  company_bank: Object,
  vouchers: Array,
  available_vouchers: Array,
  transfers: Array,
  is_admin: Boolean,
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

// Form Beli PIN / Voucher
const buyForm = useForm({});
const submitBuy = () => {
  buyForm.post(route('admin.voucher-wallet.buy'), {
    preserveScroll: true,
  });
};

// Form Produksi PIN Admin
const produceForm = useForm({
  username: '',
});
const submitProduce = () => {
  produceForm.post(route('admin.voucher-wallet.produce'), {
    preserveScroll: true,
    onSuccess: () => produceForm.reset(),
  });
};

// Form Transfer PIN
const transferForm = useForm({
  voucher_id: '',
  recipient_username: '',
});
const submitTransfer = () => {
  transferForm.post(route('admin.voucher-wallet.transfer'), {
    preserveScroll: true,
    onSuccess: () => transferForm.reset(),
  });
};

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
};
</script>

<template>
  <Head title="PIN / Voucher Wallet - XSELLER" />

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

      <!-- Main Layout Grid (Left: Actions, Right: Warehouse & History) -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- LEFT COLUMN: Forms & Actions (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
          
          <!-- 1. BELI PIN AKTIVASI CARD -->
          <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-start gap-3">
              <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl shrink-0">
                <KeyRound class="w-5 h-5" />
              </div>
              <div>
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">BELI PIN AKTIVASI</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                  PIN Aktivasi digunakan untuk mengaktifkan pendaftaran downline baru. Harga: <strong class="text-slate-800">{{ formatRupiah(voucher_price || 100000) }}</strong> per PIN.
                </p>
              </div>
            </div>

            <!-- Manual Bank Transfer Info Box -->
            <div class="p-4 bg-emerald-50/60 border border-emerald-200/80 rounded-2xl space-y-2">
              <div class="flex items-center gap-2">
                <Building2 class="w-4 h-4 text-emerald-600 shrink-0" />
                <span class="text-xs font-black text-emerald-900 uppercase tracking-tight">REKENING PEMBAYARAN MANUAL</span>
              </div>
              <div class="text-xs text-slate-700 font-medium space-y-1">
                <p><strong>Bank:</strong> {{ company_bank?.bank_name || 'Bank BRI' }}</p>
                <p><strong>No. Rekening:</strong> <span class="font-mono font-black text-slate-900 select-all">{{ company_bank?.account_number || '806401000095564' }}</span></p>
                <p><strong>Atas Nama:</strong> <strong class="text-slate-900">{{ company_bank?.account_name || 'PT.Xseller Punya Kita' }}</strong></p>
              </div>
            </div>

            <!-- Saldo Box -->
            <div class="p-4 bg-slate-50/80 border border-slate-100 rounded-2xl space-y-1">
              <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Saldo Wallet Tersedia:</span>
              <p class="text-xl font-black text-slate-900 tracking-tight">{{ formatRupiah(wallet?.saldo || 2500000) }}</p>
            </div>

            <button 
              @click="submitBuy"
              :disabled="buyForm.processing"
              class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold rounded-2xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
            >
              <KeyRound class="w-4 h-4" />
              <span>Beli 1 PIN ({{ formatRupiah(voucher_price || 100000) }})</span>
            </button>
          </div>

          <!-- 2. FITUR KHUSUS ADMIN CARD (Purple/Midnight Banner Card matching Mockup) -->
          <div v-if="is_admin" class="bg-[#1e1b4b] text-white rounded-3xl p-6 shadow-lg space-y-4 relative overflow-hidden border border-indigo-900">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <ShieldCheck class="w-4 h-4 text-indigo-300" />
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-indigo-200">FITUR KHUSUS ADMIN</h3>
              </div>
              <span class="px-2 py-0.5 text-[9px] font-bold bg-indigo-500/20 text-indigo-300 rounded-md border border-indigo-500/30">Admin Mode</span>
            </div>

            <p class="text-xs text-indigo-200 leading-relaxed font-medium">
              Sebagai Admin, Anda dapat memproduksi PIN Aktivasi secara gratis dan mendistribusikannya ke member mana saja.
            </p>

            <form @submit.prevent="submitProduce" class="space-y-3 pt-1">
              <div>
                <label class="block text-[10px] font-bold text-indigo-300 uppercase tracking-wider mb-1">
                  KIRIM KE USERNAME (OPSIONAL)
                </label>
                <input 
                  v-model="produceForm.username"
                  type="text"
                  placeholder="cth: budi (kosongkan jika untuk diri sendiri)"
                  class="w-full px-3.5 py-2.5 bg-slate-900/60 border border-indigo-500/40 rounded-xl text-white placeholder-indigo-300/40 text-xs focus:outline-none focus:border-indigo-400"
                />
              </div>

              <button 
                type="submit"
                :disabled="produceForm.processing"
                class="w-full py-3 bg-[#4f46e5] hover:bg-indigo-600 active:bg-indigo-700 text-white text-xs font-bold rounded-2xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
              >
                <Sparkles class="w-4 h-4 text-amber-300" />
                <span>Produksi PIN Gratis</span>
              </button>
            </form>
          </div>

          <!-- 3. TRANSFER PIN AKTIVASI CARD -->
          <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-start gap-3">
              <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl shrink-0">
                <ArrowRightLeft class="w-5 h-5" />
              </div>
              <div>
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">TRANSFER PIN AKTIVASI</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                  Kirim PIN yang Anda miliki ke member downline Anda agar mereka bisa melakukan registrasi mandiri.
                </p>
              </div>
            </div>

            <form @submit.prevent="submitTransfer" class="space-y-4 pt-1">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                  PILIH PIN AKTIVASI
                </label>
                <select 
                  v-model="transferForm.voucher_id"
                  required
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-xs font-medium focus:outline-none focus:border-emerald-500"
                >
                  <option value="" disabled>-- Pilih PIN --</option>
                  <option v-for="v in available_vouchers" :key="v.id" :value="v.id">
                    {{ v.code }} (Tersedia)
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                  USERNAME PENERIMA
                </label>
                <input 
                  v-model="transferForm.recipient_username"
                  type="text"
                  required
                  placeholder="cth: budisantoso"
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 text-xs font-medium focus:outline-none focus:border-emerald-500"
                />
              </div>

              <button 
                type="submit"
                :disabled="transferForm.processing"
                class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold rounded-2xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
              >
                <Send class="w-4 h-4" />
                <span>Transfer PIN</span>
              </button>
            </form>
          </div>

        </div>

        <!-- RIGHT COLUMN: Warehouse & Transfer History (7 Cols) -->
        <div class="lg:col-span-7 space-y-6">
          
          <!-- 4. GUDANG PIN WALLET ANDA TABLE CARD -->
          <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
              <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">GUDANG PIN WALLET ANDA</h3>
              <span class="px-2.5 py-1 text-[10px] font-extrabold bg-slate-100 text-slate-600 rounded-full">
                Total: {{ vouchers.length }} PIN
              </span>
            </div>

            <!-- Table Container -->
            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs border-collapse">
                <thead>
                  <tr class="border-b border-slate-100 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                    <th class="py-2.5 px-3">KODE PIN</th>
                    <th class="py-2.5 px-3">TANGGAL DIBUAT</th>
                    <th class="py-2.5 px-3">STATUS</th>
                    <th class="py-2.5 px-3">KETERANGAN PENGGUNAAN</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                  <tr v-for="item in vouchers" :key="item.id" class="hover:bg-slate-50/80 transition-colors">
                    <td class="py-3 px-3">
                      <span class="font-extrabold text-emerald-600 tracking-wide font-mono text-xs">{{ item.code }}</span>
                    </td>
                    <td class="py-3 px-3 text-slate-500">{{ item.created_at }}</td>
                    <td class="py-3 px-3">
                      <span 
                        :class="[
                          item.status === 'TERSEDIA' ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-slate-200 text-slate-600 border-slate-300',
                          'px-2.5 py-0.5 text-[9px] font-extrabold rounded-md border uppercase tracking-wider inline-block'
                        ]"
                      >
                        {{ item.status }}
                      </span>
                    </td>
                    <td class="py-3 px-3 text-[11px] text-slate-500 italic">
                      {{ item.keterangan }}
                    </td>
                  </tr>
                  <tr v-if="vouchers.length === 0">
                    <td colspan="4" class="py-8 text-center text-slate-400 italic">
                      Belum ada PIN Aktivasi di gudang Anda.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- 5. RIWAYAT TRANSFER PIN WALLET CARD -->
          <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
              <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">RIWAYAT TRANSFER PIN WALLET</h3>
              <span class="px-2.5 py-1 text-[10px] font-extrabold bg-slate-100 text-slate-600 rounded-full">
                {{ transfers.length }} Transaksi
              </span>
            </div>

            <!-- Transfer List -->
            <div class="space-y-3">
              <div 
                v-for="item in transfers" 
                :key="item.id" 
                class="p-3.5 bg-slate-50/70 border border-slate-100 rounded-2xl flex items-center justify-between gap-4 hover:bg-slate-50 transition-colors"
              >
                <div class="flex items-center gap-3">
                  <span 
                    :class="[
                      item.type === 'DIKIRIM' ? 'bg-rose-100 text-rose-600 border-rose-200' : 'bg-emerald-100 text-emerald-600 border-emerald-200',
                      'px-2 py-1 text-[9px] font-extrabold rounded-md border uppercase tracking-wider shrink-0'
                    ]"
                  >
                    {{ item.type }}
                  </span>
                  <div>
                    <h4 class="text-xs font-bold text-slate-800 leading-tight">{{ item.keterangan }}</h4>
                    <p class="text-[10px] text-slate-400 mt-0.5 font-medium">{{ item.created_at }}</p>
                  </div>
                </div>

                <div class="shrink-0 text-right">
                  <span class="font-extrabold text-slate-700 font-mono text-xs">{{ item.voucher_code }}</span>
                </div>
              </div>

              <div v-if="transfers.length === 0" class="py-8 text-center text-slate-400 text-xs italic">
                Belum ada riwayat transfer PIN.
              </div>
            </div>
          </div>

        </div>

      </div>

    </div>
  </AdminLayout>
</template>
