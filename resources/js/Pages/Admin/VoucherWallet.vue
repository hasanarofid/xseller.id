<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
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
  Building2,
  ChevronDown,
  RefreshCw,
  Plus
} from '@lucide/vue';

const props = defineProps({
  wallet: Object,
  convert_packages: Array,
  company_bank: Object,
  vouchers: Array,
  available_vouchers: Array,
  transfers: Array,
  is_admin: Boolean,
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

// Form Convert Saldo Wallet to Voucher
const buyForm = useForm({
  package_key: props.convert_packages.length > 0 ? props.convert_packages[0].key : 'starter',
  quantity: 1,
});

const selectedPackage = computed(() => {
  return props.convert_packages.find(p => p.key === buyForm.package_key) || props.convert_packages[0];
});

const totalCost = computed(() => {
  return (selectedPackage.value?.price || 125000) * (buyForm.quantity || 1);
});

const submitBuy = () => {
  buyForm.post(route('admin.voucher-wallet.buy'), {
    preserveScroll: true,
  });
};

// Form Produksi Voucher Admin
const produceForm = useForm({
  package_key: 'basic',
  quantity: 1,
  username: '',
});

const submitProduce = () => {
  produceForm.post(route('admin.voucher-wallet.produce'), {
    preserveScroll: true,
    onSuccess: () => produceForm.reset({ package_key: 'basic', quantity: 1, username: '' }),
  });
};

// Form Transfer Voucher
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
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
};
</script>

<template>
  <Head title="Gudang Voucher & Saldo Wallet - XSELLER" />

  <AdminLayout>
    <div class="space-y-6 max-w-7xl mx-auto">
      
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

      <!-- Main Layout Grid (Left: Actions & Convert, Right: Gudang Voucher & History) -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- LEFT COLUMN: Forms & Actions (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
          
          <!-- 1. BELI VOUCHER / CONVERT TO CARD -->
          <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm space-y-5">
            <div class="flex items-start gap-3">
              <div class="p-2.5 bg-teal-50 text-[#04bdb2] rounded-2xl shrink-0 border border-[#04bdb2]/20">
                <KeyRound class="w-5 h-5" />
              </div>
              <div>
                <h3 class="text-base font-black text-slate-900 uppercase tracking-tight">BELI VOUCHER</h3>
                <p class="text-xs text-slate-500 mt-0.5 leading-relaxed font-medium">
                  Kamu bisa membeli Voucher Activation, Voucher RO dan Voucher PO
                </p>
              </div>
            </div>

            <!-- Rekening Pembayaran Manual Info Box -->
            <div class="p-4 bg-emerald-50/60 border border-emerald-200/80 rounded-2xl space-y-2">
              <div class="flex items-center gap-2">
                <Building2 class="w-4 h-4 text-emerald-700 shrink-0" />
                <span class="text-xs font-black text-emerald-950 uppercase tracking-tight">REKENING PEMBAYARAN MANUAL</span>
              </div>
              <div class="text-xs text-slate-700 font-medium space-y-1">
                <p><strong>Bank:</strong> {{ company_bank?.bank_name || 'Bank BRI' }}</p>
                <p><strong>No. Rekening:</strong> <span class="font-mono font-black text-slate-900 select-all">{{ company_bank?.account_number || '806401000095564' }}</span></p>
                <p><strong>Atas Nama:</strong> <strong class="text-slate-900">{{ company_bank?.account_name || 'PT.Xseller Punya Kita' }}</strong></p>
              </div>
            </div>

            <!-- Saldo Wallet Box -->
            <div class="p-4 bg-[#f0f7fb] border border-[#04bdb2]/30 rounded-2xl space-y-1">
              <span class="text-[10px] font-extrabold text-[#1653a1] uppercase tracking-wider block">SALDO WALLET TERSEDIA:</span>
              <p class="text-2xl font-black text-slate-900 tracking-tight">{{ formatRupiah(wallet?.saldo) }}</p>
              <p class="text-[10px] text-slate-500 font-medium">Saldo terisi setelah transfer manual & dikonfirmasi admin.</p>
            </div>

            <!-- Form Convert To -->
            <form @submit.prevent="submitBuy" class="space-y-4 pt-1">
              <div>
                <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">
                  PILIH JENIS VOUCHER (SEMUA PAKET):
                </label>
                <select 
                  v-model="buyForm.package_key"
                  class="w-full px-3.5 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 text-xs font-bold focus:ring-2 focus:ring-[#04bdb2]"
                >
                  <optgroup v-for="group in ['Voucher Activation', 'Voucher RO', 'Voucher PO']" :key="group" :label="group">
                    <option 
                      v-for="pkg in convert_packages.filter(p => p.group === group)" 
                      :key="pkg.key" 
                      :value="pkg.key"
                    >
                      {{ pkg.name }}
                    </option>
                  </optgroup>
                </select>
              </div>

              <div>
                <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">
                  JUMLAH VOUCHER (1 s/d 35 PCS):
                </label>
                <input 
                  type="number"
                  v-model.number="buyForm.quantity"
                  min="1"
                  max="35"
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 text-sm font-bold focus:ring-2 focus:ring-[#04bdb2]"
                  required
                />
              </div>

              <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-xl text-xs flex items-center justify-between font-bold">
                <span class="text-slate-500">Total Biaya Konversi:</span>
                <span class="text-slate-900 font-black text-sm">{{ formatRupiah(totalCost) }}</span>
              </div>

              <button 
                type="submit"
                :disabled="buyForm.processing"
                class="w-full py-3.5 px-6 bg-[#04bdb2] hover:bg-[#009c94] text-white text-xs font-black uppercase tracking-widest rounded-2xl shadow-md shadow-[#04bdb2]/30 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
              >
                <span>CONVERT TO</span>
                <ChevronDown class="w-4 h-4" />
              </button>
            </form>
          </div>

          <!-- 2. FITUR KHUSUS ADMIN CARD -->
          <div v-if="is_admin" class="bg-[#1e1b4b] text-white rounded-3xl p-6 shadow-lg space-y-4 relative overflow-hidden border border-indigo-900">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <ShieldCheck class="w-4 h-4 text-indigo-300" />
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-indigo-200">PRODUKSI VOUCHER (ADMIN MODE)</h3>
              </div>
              <span class="px-2 py-0.5 text-[9px] font-bold bg-indigo-500/20 text-indigo-300 rounded-md border border-indigo-500/30">Admin Mode</span>
            </div>

            <p class="text-xs text-indigo-200 leading-relaxed font-medium">
              Memproduksi voucher gratis (Activation, RO, PO) untuk dikirim ke member mana saja.
            </p>

            <form @submit.prevent="submitProduce" class="space-y-3 pt-1">
              <div>
                <label class="block text-[10px] font-bold text-indigo-300 uppercase tracking-wider mb-1">
                  PILIH PAKET VOUCHER
                </label>
                <select 
                  v-model="produceForm.package_key"
                  class="w-full px-3 py-2 bg-slate-900/60 border border-indigo-500/40 rounded-xl text-white text-xs"
                >
                  <option v-for="pkg in convert_packages" :key="pkg.key" :value="pkg.key" class="bg-slate-900">
                    {{ pkg.name }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-[10px] font-bold text-indigo-300 uppercase tracking-wider mb-1">
                  KIRIM KE USERNAME (OPSIONAL)
                </label>
                <input 
                  v-model="produceForm.username"
                  type="text"
                  placeholder="cth: budi (kosongkan jika untuk diri sendiri)"
                  class="w-full px-3.5 py-2 bg-slate-900/60 border border-indigo-500/40 rounded-xl text-white placeholder-indigo-300/40 text-xs focus:outline-none focus:border-indigo-400"
                />
              </div>

              <button 
                type="submit"
                :disabled="produceForm.processing"
                class="w-full py-3 bg-[#4f46e5] hover:bg-indigo-600 active:bg-indigo-700 text-white text-xs font-bold rounded-2xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
              >
                <Sparkles class="w-4 h-4 text-amber-300" />
                <span>Produksi Voucher Gratis</span>
              </button>
            </form>
          </div>

          <!-- 3. TRANSFER VOUCHER CARD -->
          <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-start gap-3">
              <div class="p-2.5 bg-teal-50 text-[#04bdb2] rounded-2xl shrink-0 border border-[#04bdb2]/20">
                <ArrowRightLeft class="w-5 h-5" />
              </div>
              <div>
                <h3 class="text-base font-black text-slate-900 uppercase tracking-tight">TRANSFER VOUCHER</h3>
                <p class="text-xs text-slate-500 mt-0.5 leading-relaxed font-medium">
                  Kirim voucher yang tersedia ke team kamu, baik Voucher Activation, Voucher RO atau Voucher PO agar mereka bisa melakukan aktivasi secara mandiri.
                </p>
              </div>
            </div>

            <form @submit.prevent="submitTransfer" class="space-y-4 pt-1">
              <div>
                <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-1">
                  PILIH VOUCHER
                </label>
                <select 
                  v-model="transferForm.voucher_id"
                  required
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 text-xs font-bold focus:outline-none focus:border-[#04bdb2]"
                >
                  <option value="" disabled>..TIPE VOUCHER..</option>
                  <option v-for="v in available_vouchers" :key="v.id" :value="v.id">
                    {{ v.label }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-1">
                  USERNAME PENERIMA
                </label>
                <input 
                  v-model="transferForm.recipient_username"
                  type="text"
                  required
                  placeholder="cth: budisantoso"
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 placeholder-slate-400 text-xs font-bold focus:outline-none focus:border-[#04bdb2]"
                />
              </div>

              <button 
                type="submit"
                :disabled="transferForm.processing || available_vouchers.length === 0"
                class="w-full py-3 bg-[#04bdb2] hover:bg-[#009c94] text-white text-xs font-black uppercase tracking-wider rounded-2xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
              >
                <Send class="w-4 h-4" />
                <span>TRANSFER VOUCHER</span>
              </button>
            </form>
          </div>

        </div>

        <!-- RIGHT COLUMN: Warehouse & Transfer History (7 Cols) -->
        <div class="lg:col-span-7 space-y-6">
          
          <!-- 4. GUDANG VOUCHER KAMU TABLE CARD -->
          <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
              <h3 class="text-base font-black text-slate-900 uppercase tracking-tight">GUDANG VOUCHER KAMU</h3>
              <span class="px-3 py-1 text-[10px] font-extrabold bg-slate-100 text-slate-700 rounded-full">
                Total: {{ vouchers.length }} Voucher
              </span>
            </div>

            <!-- Table Container -->
            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs border-collapse">
                <thead>
                  <tr class="border-b border-slate-100 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider bg-slate-50/50">
                    <th class="py-3 px-3">KODE PIN / VOUCHER</th>
                    <th class="py-3 px-3">NAMA VOUCHER</th>
                    <th class="py-3 px-3">TANGGAL DIBUAT</th>
                    <th class="py-3 px-3">STATUS</th>
                    <th class="py-3 px-3">KETERANGAN</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-semibold text-slate-700">
                  <tr v-for="item in vouchers" :key="item.id" class="hover:bg-slate-50/80 transition-colors">
                    <td class="py-3.5 px-3">
                      <span class="font-extrabold text-emerald-600 tracking-wide font-mono text-xs">{{ item.code }}</span>
                    </td>
                    <td class="py-3.5 px-3 text-slate-900 font-bold">{{ item.package_name }}</td>
                    <td class="py-3.5 px-3 text-slate-500 font-medium">{{ item.created_at }}</td>
                    <td class="py-3.5 px-3">
                      <span 
                        :class="[
                          item.status === 'TERSEDIA' ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-slate-200 text-slate-600 border-slate-300',
                          'px-2.5 py-0.5 text-[9px] font-extrabold rounded-md border uppercase tracking-wider inline-block'
                        ]"
                      >
                        {{ item.status }}
                      </span>
                    </td>
                    <td class="py-3.5 px-3 text-[11px] text-slate-500 font-medium">
                      {{ item.keterangan }}
                    </td>
                  </tr>
                  <tr v-if="vouchers.length === 0">
                    <td colspan="5" class="py-8 text-center text-slate-400 font-medium">
                      Belum ada voucher di gudang Anda.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- 5. RIWAYAT TRANSFER VOUCHER CARD -->
          <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
              <h3 class="text-base font-black text-slate-900 uppercase tracking-tight">RIWAYAT TRANSFER VOUCHER</h3>
              <span class="px-3 py-1 text-[10px] font-extrabold bg-slate-100 text-slate-700 rounded-full">
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
                      'px-2.5 py-1 text-[9px] font-extrabold rounded-md border uppercase tracking-wider shrink-0'
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

              <div v-if="transfers.length === 0" class="py-8 text-center text-slate-400 text-xs font-medium">
                Belum ada riwayat transfer voucher.
              </div>
            </div>
          </div>

        </div>

      </div>

    </div>
  </AdminLayout>
</template>
