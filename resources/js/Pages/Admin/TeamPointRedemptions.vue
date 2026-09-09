<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Award, CheckCircle2, XCircle, Clock, Coins } from '@lucide/vue';

const props = defineProps({
  redemptions: Array,
  summary: Object,
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success);
const flashError   = computed(() => page.props.flash?.error);

const formatRp = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);

const approveForm = useForm({});
const rejectForm  = useForm({ admin_notes: '' });

const approve = (id) => {
  approveForm.post(route('admin.team-point-redemptions.approve', id), { preserveScroll: true });
};

const reject = (id, notes = 'Ditolak oleh Admin') => {
  rejectForm.admin_notes = notes;
  rejectForm.post(route('admin.team-point-redemptions.reject', id), { preserveScroll: true });
};

const statusColor = (status) => ({
  pending:  'bg-amber-100 text-amber-800 border-amber-300',
  approved: 'bg-emerald-100 text-emerald-800 border-emerald-300',
  rejected: 'bg-rose-100 text-rose-800 border-rose-300',
}[status] || 'bg-slate-100 text-slate-600 border-slate-200');
</script>

<template>
  <Head title="Klaim Reward Team Poin - Admin XSELLER" />

  <AdminLayout>
    <div class="space-y-6 max-w-6xl mx-auto">

      <!-- Header -->
      <div class="bg-gradient-to-r from-amber-600 to-orange-500 rounded-3xl p-6 md:p-8 text-white shadow-xl">
        <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 rounded-full text-xs font-black uppercase tracking-wider mb-3">
          <Award class="w-3.5 h-3.5" />
          <span>Admin Panel</span>
        </div>
        <h1 class="text-2xl md:text-3xl font-black tracking-tight">Approval Klaim Reward Team Poin</h1>
        <p class="text-sm text-amber-100 font-medium mt-1">Kelola permintaan konversi Team Poin dari seluruh member jaringan.</p>
        <div class="flex gap-4 mt-4">
          <div class="px-4 py-2 bg-white/10 rounded-xl text-center">
            <span class="text-xl font-black">{{ summary.total_pending }}</span>
            <span class="text-[10px] block font-bold text-amber-200 uppercase">Pending</span>
          </div>
          <div class="px-4 py-2 bg-white/10 rounded-xl text-center">
            <span class="text-xl font-black">{{ summary.total_approved }}</span>
            <span class="text-[10px] block font-bold text-amber-200 uppercase">Approved</span>
          </div>
        </div>
      </div>

      <!-- Flash Alerts -->
      <div v-if="flashSuccess" class="p-4 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-2xl text-xs font-semibold flex items-center gap-2">
        <CheckCircle2 class="w-4 h-4 shrink-0" />
        <span>{{ flashSuccess }}</span>
      </div>
      <div v-if="flashError" class="p-4 bg-rose-50 border border-rose-300 text-rose-800 rounded-2xl text-xs font-semibold flex items-center gap-2">
        <XCircle class="w-4 h-4 shrink-0" />
        <span>{{ flashError }}</span>
      </div>

      <!-- Redemption List -->
      <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100">
          <h2 class="text-base font-black text-slate-900 uppercase tracking-tight">Daftar Permintaan Klaim</h2>
          <p class="text-xs text-slate-500 font-medium mt-0.5">Klik Approve untuk menyetujui dan transfer saldo ke member.</p>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="border-b border-slate-100 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-wider">
                <th class="py-3 px-5">Member</th>
                <th class="py-3 px-5">Poin Klaim</th>
                <th class="py-3 px-5">Reward</th>
                <th class="py-3 px-5">Tanggal Ajuan</th>
                <th class="py-3 px-5">Status</th>
                <th class="py-3 px-5 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
              <tr v-for="r in redemptions" :key="r.id" class="hover:bg-slate-50/70 transition-colors">
                <td class="py-4 px-5">
                  <p class="font-black text-slate-900">{{ r.user_name }}</p>
                  <p class="text-[10px] text-slate-400 font-mono">@{{ r.user_username }}</p>
                </td>
                <td class="py-4 px-5 font-black text-amber-600 text-sm">{{ r.points_used }} Poin</td>
                <td class="py-4 px-5 font-black text-emerald-600">{{ formatRp(r.reward_amount) }}</td>
                <td class="py-4 px-5 text-slate-500 font-mono text-[11px]">{{ r.created_at }}</td>
                <td class="py-4 px-5">
                  <span :class="['px-2.5 py-0.5 rounded-md text-[10px] font-black border', statusColor(r.status)]">
                    {{ r.status.toUpperCase() }}
                  </span>
                </td>
                <td class="py-4 px-5 text-right">
                  <div v-if="r.status === 'pending'" class="flex items-center justify-end gap-2">
                    <button
                      @click="approve(r.id)"
                      :disabled="approveForm.processing"
                      class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-black rounded-xl transition-all disabled:opacity-50 cursor-pointer flex items-center gap-1"
                    >
                      <CheckCircle2 class="w-3 h-3" /> Approve
                    </button>
                    <button
                      @click="reject(r.id)"
                      :disabled="rejectForm.processing"
                      class="px-3 py-1.5 bg-rose-100 hover:bg-rose-200 text-rose-700 text-[10px] font-black rounded-xl transition-all disabled:opacity-50 cursor-pointer flex items-center gap-1"
                    >
                      <XCircle class="w-3 h-3" /> Tolak
                    </button>
                  </div>
                  <span v-else class="text-slate-400 font-medium text-[10px]">Sudah Diproses</span>
                </td>
              </tr>

              <tr v-if="!redemptions || redemptions.length === 0">
                <td colspan="6" class="py-12 text-center text-slate-400 text-xs font-medium italic">
                  Belum ada permintaan klaim Team Poin.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </AdminLayout>
</template>
