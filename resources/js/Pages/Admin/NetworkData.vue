<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { 
  Users, 
  Search, 
  LogIn, 
  UserCheck, 
  CheckCircle2, 
  AlertCircle,
  ShieldCheck,
  LogOut
} from '@lucide/vue';

const props = defineProps({
  members: Array,
  filters: Object,
  is_admin: Boolean,
  is_impersonating: Boolean,
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

const search = ref(props.filters?.search || '');

// Search filter with debounce/watcher
watch(search, (val) => {
  router.get(
    route('admin.network-data.index'),
    { search: val },
    { preserveState: true, replace: true }
  );
});

const impersonate = (id) => {
  router.post(route('admin.network-data.impersonate', id));
};

const stopImpersonating = () => {
  router.post(route('admin.network-data.stop-impersonating'));
};

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
};
</script>

<template>
  <Head title="Direktori Semua Member Jaringan - XSELLER" />

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

      <!-- Impersonation Active Alert Banner -->
      <div v-if="is_impersonating" class="p-4 bg-amber-50 border border-amber-300 text-amber-900 rounded-2xl text-xs font-bold flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2">
          <ShieldCheck class="w-5 h-5 text-amber-600 shrink-0" />
          <span>Anda saat ini sedang berada dalam mode pengujian / perspektif login member lain.</span>
        </div>
        <button 
          @click="stopImpersonating"
          class="px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all flex items-center gap-1.5 cursor-pointer"
        >
          <LogOut class="w-3.5 h-3.5" />
          <span>Kembali ke Admin Utama</span>
        </button>
      </div>

      <!-- Main Directory Card Container (White Card matching Mockup) -->
      <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-6">
        
        <!-- Header & Search Bar Row -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div class="flex items-start gap-3">
            <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-2xl shrink-0">
              <Users class="w-6 h-6" />
            </div>
            <div>
              <h2 class="text-base md:text-lg font-black text-slate-900 tracking-tight">
                Direktori Semua Member Jaringan
              </h2>
              <p class="text-xs text-slate-500 font-medium mt-0.5">
                Cari, tinjau, dan ganti perspektif login untuk melihat bonus dan team mitra member lain.
              </p>
            </div>
          </div>

          <!-- Live Search Input -->
          <div class="relative w-full md:w-72">
            <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input 
              v-model="search"
              type="text"
              placeholder="Cari Username / Nama..."
              class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 placeholder-slate-400 text-xs font-medium focus:outline-none focus:border-emerald-500 transition-colors"
            />
          </div>
        </div>

        <!-- Directory Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="border-b border-slate-100 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                <th class="py-3 px-4">ID & USERNAME</th>
                <th class="py-3 px-4">NAMA LENGKAP / EMAIL</th>
                <th class="py-3 px-4">SPONSOR LANGSUNG</th>
                <th class="py-3 px-4">POIN (KIRI / KANAN)</th>
                <th class="py-3 px-4">SALDO DOMPET</th>
                <th v-if="is_admin" class="py-3 px-4 text-right">OPSI PENGUJIAN</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr 
                v-for="m in members" 
                :key="m.id" 
                class="hover:bg-slate-50/80 transition-colors font-medium"
              >
                <!-- ID & Username -->
                <td class="py-3.5 px-4 space-y-0.5">
                  <span class="text-[10px] font-mono font-bold text-slate-400 block">{{ m.id_code }}</span>
                  <span class="font-extrabold text-emerald-600 font-mono text-xs">@{{ m.username }}</span>
                </td>

                <!-- Nama Lengkap / Email -->
                <td class="py-3.5 px-4 space-y-0.5">
                  <h4 class="font-extrabold text-slate-900 text-xs leading-tight">{{ m.name }}</h4>
                  <p class="text-[11px] text-slate-400 font-medium">{{ m.email }}</p>
                </td>

                <!-- Sponsor Langsung -->
                <td class="py-3.5 px-4">
                  <span 
                    v-if="m.sponsor === 'FOUNDER'"
                    class="px-2.5 py-0.5 text-[9px] font-extrabold bg-rose-50 text-rose-600 border border-rose-200 rounded-md uppercase tracking-wider"
                  >
                    FOUNDER
                  </span>
                  <span v-else class="font-extrabold text-slate-700 font-mono text-xs">
                    {{ m.sponsor }}
                  </span>
                </td>

                <!-- Poin (Kiri / Kanan) -->
                <td class="py-3.5 px-4">
                  <span class="font-extrabold text-slate-700 text-[11px] font-mono">
                    L: {{ m.left_count }} ({{ m.left_points }} P) &nbsp;|&nbsp; R: {{ m.right_count }} ({{ m.right_points }} P)
                  </span>
                </td>

                <!-- Saldo Dompet -->
                <td class="py-3.5 px-4">
                  <span class="font-black text-slate-900 text-xs">
                    {{ formatRupiah(m.saldo) }}
                  </span>
                </td>

                <!-- Opsi Pengujian (Login Ke Sini) -->
                <td v-if="is_admin" class="py-3.5 px-4 text-right">
                  <span 
                    v-if="m.is_self"
                    class="px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-extrabold rounded-full uppercase tracking-wider inline-block"
                  >
                    AKUN ANDA
                  </span>
                  <button 
                    v-else
                    @click="impersonate(m.id)"
                    class="px-3.5 py-1.5 bg-[#0d131d] hover:bg-slate-800 active:bg-slate-900 text-white text-[11px] font-bold rounded-xl shadow-sm transition-all inline-flex items-center gap-1.5 cursor-pointer"
                  >
                    <LogIn class="w-3.5 h-3.5 text-emerald-400" />
                    <span>Login Ke Sini</span>
                  </button>
                </td>
              </tr>

              <tr v-if="members.length === 0">
                <td :colspan="is_admin ? 6 : 5" class="py-12 text-center text-slate-400 text-xs italic">
                  Tidak ada member jaringan yang ditemukan.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>

    </div>
  </AdminLayout>
</template>
