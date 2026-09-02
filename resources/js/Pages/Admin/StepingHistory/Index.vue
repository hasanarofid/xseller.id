<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { 
  Layers, 
  CheckCircle2, 
  XCircle, 
  TrendingUp, 
  Users, 
  Award, 
  Lock, 
  Unlock, 
  ShieldCheck, 
  Sparkles,
  ChevronRight
} from '@lucide/vue';

const props = defineProps({
  steping_summary: Object,
  milestones: Array,
  referrals: Array,
  is_admin: Boolean,
});

const page = usePage();
</script>

<template>
  <Head title="Riwayat Steping Tier - XSELLER" />

  <AdminLayout>
    <div class="space-y-6 max-w-7xl mx-auto">
      
      <!-- Page Header Banner -->
      <div class="bg-gradient-to-r from-[#0b1f3a] via-[#103f80] to-[#1653a1] rounded-3xl p-6 md:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none">
          <Layers class="w-72 h-72 text-white" />
        </div>
        
        <div class="relative z-10 space-y-2">
          <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 backdrop-blur border border-white/20 rounded-full text-xs text-[#a9fff7] font-extrabold uppercase tracking-wider">
            <Layers class="w-3.5 h-3.5" />
            <span>Mekanisme Steping Tier</span>
          </div>
          <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white">Riwayat & Status Steping Tier</h1>
          <p class="text-xs md:text-sm text-slate-200 max-w-2xl font-medium">
            Semua Paket Join dapat menambah kedalaman Tier (Generasi) tanpa overwrite paket melalui pencapaian Direct Referral Paket Rp 125.000.
          </p>
        </div>
      </div>

      <!-- Stats Overview Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        <!-- Card 1: Paket Join -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm space-y-1">
          <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Paket Join Anda</span>
          <div class="flex items-baseline gap-1.5">
            <span class="text-xl font-black text-slate-900">{{ steping_summary.user_package }}</span>
          </div>
          <p class="text-[10px] text-slate-500 font-medium">Base Tier: Tier {{ steping_summary.base_tier }}</p>
        </div>

        <!-- Card 2: Tier Aktif Saat Ini -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm space-y-1">
          <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Kedalaman Tier Aktif</span>
          <div class="flex items-baseline gap-1.5">
            <span class="text-2xl font-black text-[#04bdb2]">Tier {{ steping_summary.active_tier }}</span>
            <span class="text-xs font-bold text-slate-500">Generasi</span>
          </div>
          <p class="text-[10px] text-emerald-600 font-semibold">Maksimal: Tier 15 Generasi</p>
        </div>

        <!-- Card 3: Total Referrals -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm space-y-1">
          <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Direct Referral Total</span>
          <div class="flex items-baseline gap-1.5">
            <span class="text-2xl font-black text-amber-600">{{ steping_summary.total_referral_count }}</span>
            <span class="text-xs font-bold text-slate-500">Mitra Direct</span>
          </div>
          <p class="text-[10px] text-slate-500 font-medium">Paket Rp 125.000 / Join</p>
        </div>

        <!-- Card 4: Next Target Tier -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm space-y-1">
          <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Target Next Tier</span>
          <div class="flex items-baseline gap-1.5">
            <span class="text-2xl font-black text-[#1653a1]">Tier {{ steping_summary.next_tier }}</span>
          </div>
          <p class="text-[10px] text-slate-500 font-medium">
            Butuh +{{ steping_summary.remaining_referrals }} Direct Referral lagi
          </p>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Matriks Kualifikasi Steping Table (7 Cols) -->
        <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-4">
          <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
              <h3 class="text-base font-black text-slate-900">Matriks Kualifikasi Steping Tier</h3>
              <p class="text-xs text-slate-500 font-medium">Syarat penambahan kedalaman bonus tier generasi</p>
            </div>
            <span class="px-3 py-1 text-[10px] font-black bg-[#f0f7fb] text-[#1653a1] rounded-full uppercase tracking-wider">
              Paket {{ steping_summary.user_package }}
            </span>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
              <thead>
                <tr class="border-b border-slate-100 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-wider">
                  <th class="py-3 px-3">Target Tier</th>
                  <th class="py-3 px-3">Syarat Direct Referral (Paket 125.000)</th>
                  <th class="py-3 px-3 text-right">Status Kualifikasi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 font-semibold text-slate-700">
                <tr class="bg-emerald-50/40">
                  <td class="py-3 px-3 font-black text-slate-900">Tier 1 - {{ steping_summary.base_tier }} (Base)</td>
                  <td class="py-3 px-3 text-slate-500 font-medium">Tanpa Syarat (Default Paket {{ steping_summary.user_package }})</td>
                  <td class="py-3 px-3 text-right">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[9px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                      <Unlock class="w-3 h-3 text-emerald-600" />
                      TERBUKA
                    </span>
                  </td>
                </tr>

                <tr v-for="m in milestones" :key="m.tier" class="hover:bg-slate-50/70 transition-colors">
                  <td class="py-3 px-3 font-bold text-slate-900">Go to Tier {{ m.tier }}</td>
                  <td class="py-3 px-3 text-slate-600">
                    {{ m.required_referrals }} Direct Referral Paket Rp 125.000
                  </td>
                  <td class="py-3 px-3 text-right">
                    <span 
                      v-if="m.unlocked" 
                      class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[9px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300"
                    >
                      <Unlock class="w-3 h-3 text-emerald-600" />
                      TERBUKA
                    </span>
                    <span 
                      v-else 
                      class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[9px] font-black bg-slate-100 text-slate-500 border border-slate-200"
                    >
                      <Lock class="w-3 h-3 text-slate-400" />
                      TERKUNCI
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Direct Referral List Table (5 Cols) -->
        <div class="lg:col-span-5 bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-4">
          <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
              <h3 class="text-base font-black text-slate-900">Daftar Direct Referral Anda</h3>
              <p class="text-xs text-slate-500 font-medium">Mitra yang disponsori langsung oleh Anda</p>
            </div>
            <span class="px-2.5 py-1 text-[10px] font-black bg-slate-100 text-slate-700 rounded-full">
              {{ steping_summary.total_referral_count }} Member
            </span>
          </div>

          <div class="space-y-3">
            <div 
              v-for="ref in referrals" 
              :key="ref.id" 
              class="p-3.5 bg-slate-50/70 border border-slate-100 rounded-2xl flex items-center justify-between gap-3 hover:bg-slate-50 transition-colors"
            >
              <div>
                <h4 class="text-xs font-bold text-slate-900 leading-tight">{{ ref.name }}</h4>
                <p class="text-[10px] text-slate-400 font-mono mt-0.5">@{{ ref.username }} • {{ ref.created_at }}</p>
              </div>

              <div class="text-right shrink-0">
                <span class="bg-emerald-100 text-emerald-900 border-emerald-300 font-extrabold px-2.5 py-1 text-[10px] rounded-lg border inline-block">
                  {{ ref.package_name }}
                </span>
              </div>
            </div>

            <div v-if="referrals.length === 0" class="py-8 text-center text-slate-400 text-xs font-medium">
              Belum ada mitra direct referral yang didaftarkan.
            </div>
          </div>
        </div>

      </div>

    </div>
  </AdminLayout>
</template>
