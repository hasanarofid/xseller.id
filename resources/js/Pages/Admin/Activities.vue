<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { 
  UserPlus, 
  ArrowLeftRight, 
  TrendingUp, 
  Trophy, 
  Activity, 
  Info, 
  Award,
  CheckCircle2
} from '@lucide/vue';

const props = defineProps({
  metrics: Object,
  user_income: Number,
  active_tab: String,
  tab_description: String,
  logs: Array,
});

const switchTab = (tabKey) => {
  router.get(
    route('admin.activities.index'),
    { tab: tabKey },
    { preserveState: true, preserveScroll: true }
  );
};

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
};

const tabList = [
  { key: 'sponsor', label: 'Bonus Sponsor' },
  { key: 'generasi', label: 'Bonus Generasi' },
  { key: 'ro', label: 'Bonus RO' },
  { key: 'tpr', label: 'Bonus TPR' },
  { key: 'incentive', label: 'Incentive' },
  { key: 'penarikan', label: 'Penarikan' },
];
</script>

<template>
  <Head title="Aktivitas & Rincian Mutasi Bonus - XSELLER" />

  <AdminLayout>
    <div class="space-y-6">

      <!-- 2. MAIN TABLE CONTAINER CARD (Matching Mockup) -->
      <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-5">
        
        <!-- Header Row with Filter Tabs -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
          <div class="flex items-start gap-3">
            <div class="p-2 bg-emerald-50 text-emerald-600 rounded-xl shrink-0 mt-0.5">
              <Activity class="w-5 h-5" />
            </div>
            <div>
              <h2 class="text-base font-black text-slate-900 uppercase tracking-tight">RINCIAN MUTASI BONUS ANDA</h2>
              <p class="text-xs text-slate-500 font-medium mt-0.5">
                Berikut adalah Rincian detail Bonus {{ active_tab === 'incentive' ? 'insentif' : 'anda' }} kamu
              </p>
            </div>
          </div>

          <!-- Filter Navigation Tabs (Matching Mockup Right Pill Filter) -->
          <div class="p-1 bg-slate-100/80 rounded-2xl flex items-center gap-1 self-start md:self-auto overflow-x-auto max-w-full">
            <button 
              v-for="t in tabList" 
              :key="t.key"
              @click="switchTab(t.key)"
              :class="[
                active_tab === t.key 
                  ? 'bg-white text-slate-900 font-extrabold shadow-sm' 
                  : 'text-slate-600 hover:text-slate-900 font-medium',
                'px-4 py-1.5 text-xs rounded-xl transition-all cursor-pointer whitespace-nowrap'
              ]"
            >
              {{ t.label }}
            </button>
          </div>
        </div>

        <!-- Info Box Banner (Matching Mockup Green Info Banner) -->
        <div class="p-4 bg-emerald-50/70 border border-emerald-200/70 text-emerald-900 rounded-2xl text-xs font-medium leading-relaxed flex items-start gap-2.5">
          <span class="text-emerald-600 font-bold shrink-0">ℹ️</span>
          <span>{{ tab_description }}</span>
        </div>

        <!-- Sub-banner Income for Incentive Tab -->
        <div v-if="active_tab === 'incentive'" class="text-center py-2">
          <h3 class="text-sm font-black text-slate-900 tracking-tight">
            INCOME SAAT INI : <span class="text-emerald-600">{{ formatRupiah(user_income || 0) }}</span>
          </h3>
        </div>

        <!-- Table List -->
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <!-- Special Headers for Incentive Tab as in Mockup WhatsApp Image 2026-09-03 at 13.15.33.jpeg -->
              <tr v-if="active_tab === 'incentive'" class="border-b border-slate-100 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                <th class="py-3 px-4">Qualified</th>
                <th class="py-3 px-4">Incentive</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4 text-right">Date</th>
              </tr>
              <!-- Standard Headers for Other Tabs -->
              <tr v-else class="border-b border-slate-100 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                <th class="py-3 px-4">ID TRANSAKSI</th>
                <th class="py-3 px-4">TANGGAL & WAKTU</th>
                <th class="py-3 px-4">PEMBERI / SUMBER</th>
                <th class="py-3 px-4">DESKRIPSI TRANSAKSI</th>
                <th class="py-3 px-4 text-right">JUMLAH</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
              <!-- Incentive Rows -->
              <template v-if="active_tab === 'incentive'">
                <tr 
                  v-for="item in logs" 
                  :key="item.id" 
                  class="hover:bg-slate-50/80 transition-colors"
                >
                  <td class="py-3.5 px-4 font-extrabold text-slate-900">
                    {{ item.qualified || '-' }}
                  </td>
                  <td class="py-3.5 px-4 font-black text-emerald-600">
                    {{ item.incentive || item.amount }}
                  </td>
                  <td class="py-3.5 px-4 font-bold text-slate-700">
                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-md text-[10px]">{{ item.status || 'Klaim' }}</span>
                  </td>
                  <td class="py-3.5 px-4 text-right text-slate-500 font-mono">
                    {{ item.date || item.created_at }}
                  </td>
                </tr>
              </template>

              <!-- Standard Rows -->
              <template v-else>
                <tr 
                  v-for="item in logs" 
                  :key="item.id" 
                  class="hover:bg-slate-50/80 transition-colors"
                >
                  <td class="py-3.5 px-4">
                    <span class="font-extrabold text-slate-400 font-mono text-xs">{{ item.transaction_code }}</span>
                  </td>
                  <td class="py-3.5 px-4 text-slate-500 font-mono text-xs">
                    {{ item.created_at }}
                  </td>
                  <td class="py-3.5 px-4 font-extrabold text-emerald-600 font-mono text-xs">
                    {{ item.source }}
                  </td>
                  <td class="py-3.5 px-4 font-bold text-slate-800">
                    {{ item.description }}
                  </td>
                  <td class="py-3.5 px-4 text-right font-black text-emerald-600 font-mono text-xs tracking-tight">
                    {{ item.amount }}
                  </td>
                </tr>
              </template>

              <tr v-if="logs.length === 0">
                <td :colspan="active_tab === 'incentive' ? 4 : 5" class="py-12 text-center text-slate-400 text-xs italic">
                  Belum ada catatan mutasi bonus untuk kategori ini.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>

    </div>
  </AdminLayout>
</template>
