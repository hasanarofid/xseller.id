<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
  Crown, 
  Sparkles, 
  Send, 
  CheckCircle2, 
  AlertCircle, 
  Check, 
  X, 
  Clock, 
  Lock, 
  FileText,
  KeyRound,
  ArrowRight
} from '@lucide/vue';

const props = defineProps({
  is_eligible: Boolean,
  is_admin: Boolean,
  user_package: String,
  allowed_options: Array,
  requests: Array,
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

const form = useForm({
  amount: props.allowed_options.length > 0 ? props.allowed_options[0].amount : '',
  proof_of_transfer: null,
});

const handleProofFile = (e) => {
  if (e.target.files && e.target.files[0]) {
    form.proof_of_transfer = e.target.files[0];
  }
};

const submitTprRequest = () => {
  form.post(route('admin.tpr.store'), {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      form.reset('proof_of_transfer');
    },
  });
};

const approveForm = useForm({});
const rejectForm = useForm({ notes: '' });

const approveTpr = (id) => {
  if (confirm('Apakah Anda yakin ingin MENYETUJUI pengajuan Trade Promotion Program (TPR) ini?')) {
    approveForm.post(route('admin.tpr.approve', id), { preserveScroll: true });
  }
};

const rejectTpr = (id) => {
  const notes = prompt('Masukkan alasan penolakan pengajuan TPR:');
  if (notes !== null) {
    rejectForm.notes = notes;
    rejectForm.post(route('admin.tpr.reject', id), { preserveScroll: true });
  }
};

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
};
</script>

<template>
  <Head title="Fitur TPR (Trade Promotion Program) - XSELLER" />

  <AdminLayout>
    <div class="space-y-6 max-w-7xl mx-auto">
      
      <!-- Flash Alert Notifications -->
      <div v-if="flashSuccess" class="p-4 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-2xl text-xs font-semibold flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2">
          <CheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0" />
          <span>{{ flashSuccess }}</span>
        </div>
      </div>

      <div v-if="flashError" class="p-4 bg-rose-50 border border-rose-300 text-rose-800 rounded-2xl text-xs font-semibold flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2">
          <AlertCircle class="w-4 h-4 text-rose-500 shrink-0" />
          <span>{{ flashError }}</span>
        </div>
      </div>

      <!-- Header Card Banner -->
      <div class="bg-gradient-to-r from-amber-600 via-amber-700 to-yellow-600 rounded-3xl p-6 md:p-8 text-white shadow-lg space-y-4 relative overflow-hidden">
        <div class="absolute right-0 top-0 bottom-0 opacity-10 flex items-center pr-10 pointer-events-none">
          <Crown class="w-64 h-64 text-white" />
        </div>

        <div class="flex items-center justify-between flex-wrap gap-3">
          <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-white/20 backdrop-blur rounded-full text-[10px] font-extrabold uppercase tracking-wider text-amber-100">
            <Sparkles class="w-3.5 h-3.5" />
            <span>TRADE PROMOTION PROGRAM (FITUR TPR)</span>
          </div>

          <!-- Status Indicator Badge -->
          <div 
            :class="[
              is_eligible ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white',
              'px-3.5 py-1 text-xs font-black rounded-full uppercase tracking-wider flex items-center gap-1.5 shadow-xs'
            ]"
          >
            <CheckCircle2 v-if="is_eligible" class="w-3.5 h-3.5" />
            <Lock v-else class="w-3.5 h-3.5" />
            <span>FITUR TPR: {{ is_eligible ? 'ON (AKTIF)' : 'OFF (NON-AKTIF)' }}</span>
          </div>
        </div>

        <h2 class="text-2xl md:text-3xl font-black tracking-tight">
          Fitur Trade Promotion Program (TPR)
        </h2>

        <p class="text-xs text-amber-100 max-w-2xl font-medium leading-relaxed">
          Fitur eksklusif dimana kamu bisa menitipkan produk kamu ke perusahaan atau conciate sale dan kamu berhak mendapatkan bagi hasil selama 3 bulan kedepan.
        </p>
      </div>

      <!-- Access Restricted Notice if Fitur TPR is OFF -->
      <div v-if="!is_eligible" class="bg-white border border-rose-200 rounded-3xl p-8 text-center space-y-5 shadow-sm max-w-2xl mx-auto">
        <div class="w-20 h-20 rounded-full bg-rose-50 border border-rose-200 text-rose-500 mx-auto flex items-center justify-center shadow-xs">
          <Lock class="w-10 h-10" />
        </div>

        <div class="space-y-2">
          <span class="px-3 py-1 bg-rose-100 text-rose-700 rounded-full text-[10px] font-black uppercase tracking-wider">
            FITUR TPR SAAT INI: OFF (NON-AKTIF)
          </span>
          <h3 class="text-xl font-black text-slate-900">Diperlukan Paket Rp 4.300.000 atau Rp 10.500.000</h3>
          <p class="text-xs text-slate-600 font-medium leading-relaxed max-w-lg mx-auto">
            Untuk mendapatkan alokasi bulanan (Profit Share 7% / 9%), Anda harus membeli/mengaktifkan 
            <strong class="text-slate-900">Paket Rp 4.300.000 (Business)</strong> atau 
            <strong class="text-slate-900">Paket Rp 10.500.000 (Partner)</strong> terlebih dahulu. Paket Anda saat ini: <span class="font-extrabold text-rose-600 uppercase">{{ user_package }}</span>.
          </p>
        </div>

        <div class="pt-2 flex items-center justify-center gap-3">
          <Link 
            :href="route('admin.voucher-wallet.index')" 
            class="px-6 py-3 bg-[#1653a1] hover:bg-[#103f80] text-white text-xs font-black rounded-2xl shadow-md transition-all inline-flex items-center gap-2 cursor-pointer"
          >
            <KeyRound class="w-4 h-4 text-[#a9fff7]" />
            <span>Beli / Konversi Voucher Paket (4.3m / 10.5m)</span>
            <ArrowRight class="w-4 h-4" />
          </Link>
        </div>
      </div>

      <!-- Main Layout Grid if Fitur TPR is ON or Admin -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- LEFT: Request Form (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
          <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm space-y-5">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-4">
              <div class="p-2 bg-amber-50 text-amber-600 rounded-xl">
                <Crown class="w-5 h-5" />
              </div>
              <h3 class="text-xs font-black text-slate-900 uppercase tracking-tight">FORMULIR REQUEST FITUR TPR</h3>
            </div>

            <form @submit.prevent="submitTprRequest" class="space-y-4">
              <!-- Select Package -->
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                  PILIH PAKET FITUR TPR
                </label>
                <select 
                  v-model="form.amount"
                  required
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-amber-500"
                >
                  <option v-for="opt in allowed_options" :key="opt.amount" :value="opt.amount">
                    {{ opt.package_name }} - {{ opt.description }}
                  </option>
                </select>
              </div>

              <!-- Upload Transfer Proof -->
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                  UPLOAD BUKTI TRANSFER DANA FITUR TPR
                </label>
                <input 
                  type="file" 
                  @change="handleProofFile"
                  accept="image/*,.pdf"
                  required
                  class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 cursor-pointer"
                />
                <p class="text-[10px] text-slate-400 mt-1">Struk/bukti transfer dana ke rekening resmi perusahaan.</p>
                <p v-if="form.errors.proof_of_transfer" class="text-xs text-rose-500 font-medium mt-1">{{ form.errors.proof_of_transfer }}</p>
              </div>

              <button 
                type="submit"
                :disabled="form.processing"
                class="w-full py-3 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-2xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
              >
                <Send class="w-4 h-4" />
                <span>Kirim Request Fitur TPR</span>
              </button>
            </form>
          </div>
        </div>

        <!-- RIGHT: Requests Directory (7 Cols) -->
        <div class="lg:col-span-7 space-y-6">
          <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
              <div class="flex items-center gap-2">
                <FileText class="w-4 h-4 text-amber-600" />
                <h3 class="text-xs font-black text-slate-900 uppercase tracking-tight">
                  {{ is_admin ? 'DAFTAR REQUEST FITUR TPR SEMUA MEMBER (ADMIN)' : 'RIWAYAT REQUEST FITUR TPR ANDA' }}
                </h3>
              </div>
              <span class="px-2.5 py-1 text-[10px] font-extrabold bg-slate-100 text-slate-600 rounded-full">
                {{ requests.length }} Request
              </span>
            </div>

            <!-- List / Empty State -->
            <div v-if="requests.length === 0" class="p-12 text-center border-2 border-dashed border-slate-100 rounded-3xl bg-slate-50/50">
              <p class="text-xs text-slate-400 italic font-medium">
                Belum ada pengajuan Fitur TPR di sistem.
              </p>
            </div>

            <div v-else class="space-y-3">
              <div 
                v-for="item in requests" 
                :key="item.id"
                class="p-4 bg-slate-50/70 border border-slate-100 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50 transition-colors"
              >
                <div class="space-y-1">
                  <div class="flex items-center gap-2">
                    <span class="font-bold text-slate-800 text-xs">{{ item.user_name }}</span>
                    <span class="text-[10px] text-slate-400 font-medium">@{{ item.user_username }}</span>
                    <span 
                      :class="[
                        item.status === 'approved' ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 
                        item.status === 'rejected' ? 'bg-rose-100 text-rose-700 border-rose-200' : 
                        'bg-amber-100 text-amber-700 border-amber-200',
                        'px-2 py-0.5 text-[9px] font-extrabold rounded-md border uppercase tracking-wider ml-1'
                      ]"
                    >
                      {{ item.status === 'approved' ? 'AKTIF (DISETUJUI)' : item.status === 'rejected' ? 'DITOLAK' : 'PENDING' }}
                    </span>
                  </div>

                  <p class="text-xs text-slate-700 font-semibold">
                    {{ item.package_name }} — Profit Share {{ item.monthly_share_percent }}%/Bulan ({{ formatRupiah(item.monthly_share_amount) }}/bln)
                  </p>

                  <p class="text-[10px] text-slate-400">
                    Diajukan: {{ item.created_at }}
                    <a v-if="item.proof_of_transfer" :href="item.proof_of_transfer" target="_blank" class="text-amber-600 font-bold hover:underline block mt-0.5">
                      📄 Lihat Struk Transfer
                    </a>
                  </p>
                </div>

                <!-- Right Amount & Admin Buttons -->
                <div class="flex flex-col items-end gap-2 shrink-0">
                  <span class="text-sm font-black text-slate-900 font-mono tracking-tight">
                    {{ formatRupiah(item.amount) }}
                  </span>

                  <div v-if="is_admin && item.status === 'pending'" class="flex items-center gap-1.5 pt-1">
                    <button 
                      @click="approveTpr(item.id)"
                      class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold rounded-lg shadow-sm transition-colors flex items-center gap-1 cursor-pointer"
                    >
                      <Check class="w-3 h-3" />
                      <span>Setujui</span>
                    </button>

                    <button 
                      @click="rejectTpr(item.id)"
                      class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white text-[10px] font-bold rounded-lg shadow-sm transition-colors flex items-center gap-1 cursor-pointer"
                    >
                      <X class="w-3 h-3" />
                      <span>Tolak</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>

      </div>

    </div>
  </AdminLayout>
</template>
