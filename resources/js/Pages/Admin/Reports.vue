<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { 
  FileSpreadsheet, 
  FileText, 
  FileDown, 
  Users, 
  Award, 
  Banknote, 
  Wallet 
} from '@lucide/vue';

const props = defineProps({
  active_type: String,
  report_data: Array,
});

const switchTab = (type) => {
  router.get(
    route('admin.reports.index'),
    { type: type },
    { preserveState: true, preserveScroll: true }
  );
};

const exportExcel = () => {
  window.open(route('admin.reports.export-excel', { type: props.active_type }), '_blank');
};

const exportPdf = () => {
  window.open(route('admin.reports.export-pdf', { type: props.active_type }), '_blank');
};

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
};

const reportTabs = [
  { type: 'member', label: 'Laporan Member' },
  { type: 'bonus', label: 'Laporan Bonus' },
  { type: 'pencairan', label: 'Laporan Pencairan' },
  { type: 'topup', label: 'Laporan Topup' },
];
</script>

<template>
  <Head title="Menu Laporan - XSELLER" />

  <AdminLayout>
    <div class="space-y-6">
      
      <!-- 1. TOP HEADER & EXPORT ACTION BUTTONS (Matching Mockup) -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h2 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight">
            Menu Laporan
          </h2>
          <p class="text-xs text-slate-500 font-medium mt-0.5">
            Export data transaksi dan member ke Excel atau PDF.
          </p>
        </div>

        <!-- Export Buttons (Green Excel & Red PDF) -->
        <div class="flex items-center gap-2.5 shrink-0">
          <!-- Excel Button -->
          <button 
            @click="exportExcel"
            class="px-4 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200/80 rounded-2xl text-xs font-extrabold shadow-sm transition-all flex items-center gap-2 cursor-pointer"
          >
            <FileSpreadsheet class="w-4 h-4 text-emerald-600" />
            <span>Excel</span>
          </button>

          <!-- PDF Button -->
          <button 
            @click="exportPdf"
            class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200/80 rounded-2xl text-xs font-extrabold shadow-sm transition-all flex items-center gap-2 cursor-pointer"
          >
            <FileText class="w-4 h-4 text-rose-600" />
            <span>PDF</span>
          </button>
        </div>
      </div>

      <!-- 2. REPORT NAVIGATION TABS (Pills Matching Mockup) -->
      <div class="flex items-center gap-2.5 overflow-x-auto pb-1 max-w-full">
        <button 
          v-for="t in reportTabs" 
          :key="t.type"
          @click="switchTab(t.type)"
          :class="[
            active_type === t.type 
              ? 'bg-[#4f46e5] text-white font-extrabold shadow-md' 
              : 'bg-white text-slate-700 hover:bg-slate-50 font-bold border border-slate-200/80',
            'px-5 py-2.5 text-xs rounded-2xl transition-all cursor-pointer whitespace-nowrap'
          ]"
        >
          {{ t.label }}
        </button>
      </div>

      <!-- 3. MAIN TABLE DATA CONTAINER CARD (Matching Mockup) -->
      <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm">
        <div class="overflow-x-auto">
          
          <!-- TAB 1: LAPORAN MEMBER -->
          <table v-if="active_type === 'member'" class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="border-b border-slate-100 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                <th class="py-3.5 px-4">MEMBER</th>
                <th class="py-3.5 px-4">SPONSOR</th>
                <th class="py-3.5 px-4">SALDO WALLET</th>
                <th class="py-3.5 px-4 text-right">TGL DAFTAR</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
              <tr v-for="row in report_data" :key="row.id" class="hover:bg-slate-50/80 transition-colors">
                <!-- Member -->
                <td class="py-3.5 px-4 space-y-0.5">
                  <h4 class="font-extrabold text-slate-900 text-xs leading-tight">{{ row.name }}</h4>
                  <p class="text-[11px] text-slate-400 font-mono">@{{ row.username }}</p>
                </td>

                <!-- Sponsor -->
                <td class="py-3.5 px-4 font-mono text-slate-600 font-bold">
                  {{ row.sponsor }}
                </td>

                <!-- Saldo Wallet -->
                <td class="py-3.5 px-4 font-black text-slate-900 font-mono text-xs">
                  {{ formatRupiah(row.saldo) }}
                </td>

                <!-- Tgl Daftar -->
                <td class="py-3.5 px-4 text-right text-slate-400 font-mono text-xs">
                  {{ row.created_at }}
                </td>
              </tr>

              <tr v-if="report_data.length === 0">
                <td colspan="4" class="py-12 text-center text-slate-400 text-xs italic">
                  Belum ada data member terdaftar.
                </td>
              </tr>
            </tbody>
          </table>

          <!-- TAB 2: LAPORAN BONUS -->
          <table v-else-if="active_type === 'bonus'" class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="border-b border-slate-100 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                <th class="py-3.5 px-4">MEMBER</th>
                <th class="py-3.5 px-4">JENIS BONUS</th>
                <th class="py-3.5 px-4">SUMBER MEMBER</th>
                <th class="py-3.5 px-4">DESKRIPSI</th>
                <th class="py-3.5 px-4">NOMINAL BONUS</th>
                <th class="py-3.5 px-4 text-right">TANGGAL</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
              <tr v-for="row in report_data" :key="row.id" class="hover:bg-slate-50/80 transition-colors">
                <td class="py-3.5 px-4 space-y-0.5">
                  <h4 class="font-extrabold text-slate-900 text-xs">{{ row.name }}</h4>
                  <p class="text-[11px] text-slate-400 font-mono">@{{ row.username }}</p>
                </td>
                <td class="py-3.5 px-4">
                  <span class="px-2.5 py-0.5 text-[9px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-md uppercase tracking-wider">
                    {{ row.category }}
                  </span>
                </td>
                <td class="py-3.5 px-4 font-mono font-bold text-slate-700">{{ row.source }}</td>
                <td class="py-3.5 px-4 text-slate-800 font-medium">{{ row.description }}</td>
                <td class="py-3.5 px-4 font-black text-emerald-600 font-mono text-xs">+{{ formatRupiah(row.amount) }}</td>
                <td class="py-3.5 px-4 text-right text-slate-400 font-mono text-xs">{{ row.created_at }}</td>
              </tr>
            </tbody>
          </table>

          <!-- TAB 3: LAPORAN PENCAIRAN -->
          <table v-else-if="active_type === 'pencairan'" class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="border-b border-slate-100 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                <th class="py-3.5 px-4">MEMBER</th>
                <th class="py-3.5 px-4">REKENING TUJUAN</th>
                <th class="py-3.5 px-4">NOMINAL WD</th>
                <th class="py-3.5 px-4">STATUS</th>
                <th class="py-3.5 px-4 text-right">TANGGAL</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
              <tr v-for="row in report_data" :key="row.id" class="hover:bg-slate-50/80 transition-colors">
                <td class="py-3.5 px-4 space-y-0.5">
                  <h4 class="font-extrabold text-slate-900 text-xs">{{ row.name }}</h4>
                  <p class="text-[11px] text-slate-400 font-mono">@{{ row.username }}</p>
                </td>
                <td class="py-3.5 px-4 font-medium text-slate-700">
                  {{ row.bank_name }} - <strong class="font-mono text-slate-900">{{ row.bank_account_number }}</strong> a.n {{ row.bank_account_name }}
                </td>
                <td class="py-3.5 px-4 font-black text-slate-900 font-mono text-xs">{{ formatRupiah(row.amount) }}</td>
                <td class="py-3.5 px-4">
                  <span 
                    :class="[
                      row.status === 'APPROVED' ? 'bg-emerald-100 text-emerald-700 border-emerald-200' :
                      row.status === 'REJECTED' ? 'bg-rose-100 text-rose-700 border-rose-200' :
                      'bg-amber-100 text-amber-700 border-amber-200',
                      'px-2.5 py-0.5 text-[9px] font-extrabold rounded-md border uppercase tracking-wider'
                    ]"
                  >
                    {{ row.status }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-right text-slate-400 font-mono text-xs">{{ row.created_at }}</td>
              </tr>
            </tbody>
          </table>

          <!-- TAB 4: LAPORAN TOPUP -->
          <table v-else-if="active_type === 'topup'" class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="border-b border-slate-100 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                <th class="py-3.5 px-4">MEMBER</th>
                <th class="py-3.5 px-4">KATEGORI</th>
                <th class="py-3.5 px-4">DESKRIPSI</th>
                <th class="py-3.5 px-4">NOMINAL</th>
                <th class="py-3.5 px-4 text-right">TANGGAL</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
              <tr v-for="row in report_data" :key="row.id" class="hover:bg-slate-50/80 transition-colors">
                <td class="py-3.5 px-4 space-y-0.5">
                  <h4 class="font-extrabold text-slate-900 text-xs">{{ row.name }}</h4>
                  <p class="text-[11px] text-slate-400 font-mono">@{{ row.username }}</p>
                </td>
                <td class="py-3.5 px-4">
                  <span class="px-2.5 py-0.5 text-[9px] font-extrabold bg-slate-100 text-slate-700 border border-slate-200 rounded-md uppercase tracking-wider">
                    {{ row.category }}
                  </span>
                </td>
                <td class="py-3.5 px-4 font-medium text-slate-800">{{ row.description }}</td>
                <td class="py-3.5 px-4 font-black text-slate-900 font-mono text-xs">
                  {{ formatRupiah(row.amount) }}
                </td>
                <td class="py-3.5 px-4 text-right text-slate-400 font-mono text-xs">{{ row.created_at }}</td>
              </tr>
            </tbody>
          </table>

        </div>
      </div>

    </div>
  </AdminLayout>
</template>
