<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
  User, 
  Search, 
  RotateCcw, 
  UserPlus, 
  ChevronRight,
  Users,
  Sparkles,
  ShieldCheck,
  Layers,
  ArrowRight
} from '@lucide/vue';

const props = defineProps({
  focus_user: Object,
  direct_downlines: Array,
  generations: Array,
  all_users: Array
});

const selectedUserSearch = ref(props.focus_user?.id || '');

const focusUser = (userId) => {
  if (!userId) return;
  router.get(route('admin.pohon-jaringan'), { focus_id: userId }, { preserveState: true });
};

const resetFocus = () => {
  selectedUserSearch.value = '';
  router.get(route('admin.pohon-jaringan'));
};

const getBadgeColor = (pkg) => {
  const p = (pkg || '').toLowerCase();
  if (p.includes('ultimate') || p.includes('10.500')) return 'bg-amber-100 text-amber-800 border-amber-300';
  if (p.includes('pro') || p.includes('4.300')) return 'bg-purple-100 text-purple-800 border-purple-300';
  if (p.includes('medium') || p.includes('2.100')) return 'bg-indigo-100 text-indigo-800 border-indigo-300';
  if (p.includes('basic') || p.includes('550')) return 'bg-blue-100 text-blue-800 border-blue-300';
  return 'bg-slate-100 text-slate-700 border-slate-300';
};
</script>

<template>
  <Head title="Team Mitra - XSELLER" />

  <AdminLayout>
    <div class="space-y-6">
      
      <!-- Top Search & Focus Control Bar -->
      <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Search User Select -->
        <div class="relative flex-1 max-w-xl flex items-center gap-2">
          <div class="relative w-full">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
              <Search class="w-4 h-4" />
            </span>
            <select
              v-model="selectedUserSearch"
              @change="focusUser(selectedUserSearch)"
              class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-8 py-2.5 text-xs font-semibold text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors appearance-none cursor-pointer"
            >
              <option v-for="u in all_users" :key="u.id" :value="u.id">
                {{ u.label }}
              </option>
            </select>
            <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400 text-xs">
              ▼
            </span>
          </div>
        </div>

        <!-- Reset Button -->
        <button 
          @click="resetFocus"
          class="px-4 py-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all flex items-center gap-2 shadow-sm shrink-0 cursor-pointer"
        >
          <RotateCcw class="w-3.5 h-3.5 text-slate-500" />
          <span>Reset Fokus ke Saya</span>
        </button>
      </div>

      <!-- Member Focus Header Summary Card -->
      <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-1">
          <div class="flex items-center gap-2">
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">
              {{ focus_user?.name }}
            </h2>
            <span class="text-xs font-bold text-slate-400 font-mono">{{ focus_user?.username }}</span>
            <span :class="['px-2.5 py-0.5 text-[10px] font-extrabold rounded-md border uppercase tracking-wider', getBadgeColor(focus_user?.package_name)]">
              Paket {{ focus_user?.package_name }}
            </span>
          </div>
          <p class="text-xs text-slate-500 font-medium pt-0.5">
            Struktur Team Mitra (Direct Referral Level System)
          </p>
        </div>

        <!-- Stat Badges -->
        <div class="flex items-center gap-3 shrink-0 flex-wrap">
          <div class="px-5 py-3 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-sm">
              <Users class="w-5 h-5" />
            </div>
            <div>
              <span class="text-[9px] font-extrabold text-emerald-800 uppercase tracking-wider block">SPONSOR LANGSUNG</span>
              <span class="text-sm font-black text-emerald-700 font-mono">{{ focus_user?.total_direct || 0 }} Member</span>
            </div>
          </div>

          <div class="px-5 py-3 bg-indigo-50 border border-indigo-200 rounded-2xl flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-sm">
              <Layers class="w-5 h-5" />
            </div>
            <div>
              <span class="text-[9px] font-extrabold text-indigo-800 uppercase tracking-wider block">TOTAL ANGGOTA TIM</span>
              <span class="text-sm font-black text-indigo-700 font-mono">{{ focus_user?.total_team || 0 }} Member</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Layout Grid (Left: Direct Downline Table, Right: Generation Tier Summary) -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- LEFT: Direct Downlines List (8 Cols) -->
        <div class="lg:col-span-8 space-y-4">
          <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
              <div class="flex items-center gap-2">
                <Users class="w-5 h-5 text-emerald-600" />
                <h3 class="text-xs font-black text-slate-900 uppercase tracking-tight">
                  DAFTAR MITRA SPONSOR LANGSUNG (GENERASI 1)
                </h3>
              </div>
              <span class="px-2.5 py-1 text-[10px] font-extrabold bg-slate-100 text-slate-600 rounded-full">
                {{ direct_downlines.length }} Mitra
              </span>
            </div>

            <!-- Table / Empty State -->
            <div v-if="direct_downlines.length === 0" class="p-12 text-center border-2 border-dashed border-slate-100 rounded-3xl bg-slate-50/50">
              <p class="text-xs text-slate-400 italic font-medium">
                Belum ada mitra sponsor langsung (Generasi 1) terdaftar di bawah akun ini.
              </p>
            </div>

            <div v-else class="overflow-x-auto">
              <table class="w-full text-left text-xs">
                <thead>
                  <tr class="border-b border-slate-100 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                    <th class="pb-3 pl-2">Nama Member</th>
                    <th class="pb-3">Paket Join</th>
                    <th class="pb-3 text-center">Tim G2</th>
                    <th class="pb-3">Tanggal Bergabung</th>
                    <th class="pb-3 text-right pr-2">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr v-for="item in direct_downlines" :key="item.id" class="hover:bg-slate-50/80 transition-colors">
                    <td class="py-3.5 pl-2 font-bold text-slate-800">
                      <div>{{ item.name }}</div>
                      <div class="text-[10px] text-slate-400 font-normal">{{ item.username }}</div>
                    </td>
                    <td class="py-3.5">
                      <span :class="['px-2 py-0.5 text-[9px] font-extrabold rounded border uppercase tracking-wider', getBadgeColor(item.package_name)]">
                        {{ item.package_name }}
                      </span>
                    </td>
                    <td class="py-3.5 text-center font-bold font-mono text-slate-700">
                      {{ item.direct_count }} Mitra
                    </td>
                    <td class="py-3.5 text-slate-500 font-medium text-[11px]">
                      {{ item.joined_at }}
                    </td>
                    <td class="py-3.5 text-right pr-2">
                      <button 
                        @click="focusUser(item.id)"
                        class="px-2.5 py-1 bg-slate-100 hover:bg-emerald-600 hover:text-white text-slate-700 text-[10px] font-bold rounded-lg transition-colors inline-flex items-center gap-1 cursor-pointer"
                      >
                        <span>Lihat Tim</span>
                        <ArrowRight class="w-3 h-3" />
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

          </div>
        </div>

        <!-- RIGHT: Generation Depth Breakdown (4 Cols) -->
        <div class="lg:col-span-4 space-y-4">
          <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
              <div class="flex items-center gap-2">
                <Layers class="w-4 h-4 text-indigo-600" />
                <h3 class="text-xs font-black text-slate-900 uppercase tracking-tight">KEDALAMAN GENERASI (1 - 15)</h3>
              </div>
            </div>

            <div class="space-y-2 max-h-[480px] overflow-y-auto pr-1">
              <div 
                v-for="gen in generations" 
                :key="gen.generation"
                class="p-3 bg-slate-50/70 border border-slate-100 rounded-xl flex items-center justify-between"
              >
                <span class="text-xs font-bold text-slate-700">{{ gen.label }}</span>
                <span :class="[gen.count > 0 ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-400', 'px-2.5 py-0.5 text-xs font-extrabold rounded-full font-mono']">
                  {{ gen.count }} Member
                </span>
              </div>
            </div>

          </div>
        </div>

      </div>

    </div>
  </AdminLayout>
</template>
