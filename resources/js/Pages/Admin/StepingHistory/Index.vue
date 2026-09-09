<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
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
  team_point_rules: Array,
  team_point_logs: Array,
  is_admin: Boolean,
  pending_redemption: Object, // null jika tidak ada pending request
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success);
const flashError   = computed(() => page.props.flash?.error);

// Reward bracket table
const rewardBrackets = [
  { points: 35,   reward: 500000 },
  { points: 90,   reward: 1500000 },
  { points: 300,  reward: 6000000 },
  { points: 2000, reward: 35000000 },
  { points: 5000, reward: 80000000 },
];

// Tentukan bracket tertinggi yang bisa diklaim sekarang
const eligibleBracket = computed(() => {
  const pts = props.steping_summary?.total_team_points ?? 0;
  for (let i = rewardBrackets.length - 1; i >= 0; i--) {
    if (pts >= rewardBrackets[i].points) return rewardBrackets[i];
  }
  return null;
});

const formatRp = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);

const redeemForm = useForm({});
</script>

<template>
  <Head title="Riwayat Steping Tier & Team Poin - XSELLER" />

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
            <span>Mekanisme Steping Tier & Team Poin</span>
          </div>
          <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white">Riwayat Steping Tier & Team Poin</h1>
          <p class="text-xs md:text-sm text-slate-200 max-w-2xl font-medium">
            Kelola kedalaman Tier Generasi dan pantau statistik akumulasi <span class="font-bold text-[#a9fff7]">Team Poin</span> dari pertumbuhan jaringan Anda.
          </p>
        </div>
      </div>

      <!-- Flash Alerts -->
      <div v-if="flashSuccess" class="p-4 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-2xl text-xs font-semibold flex items-center gap-2 shadow-sm">
        <CheckCircle2 class="w-4 h-4 shrink-0" />
        <span>{{ flashSuccess }}</span>
      </div>
      <div v-if="flashError" class="p-4 bg-rose-50 border border-rose-300 text-rose-800 rounded-2xl text-xs font-semibold flex items-center gap-2 shadow-sm">
        <XCircle class="w-4 h-4 shrink-0" />
        <span>{{ flashError }}</span>
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

        <!-- Card 3: Total Team Poin -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm space-y-1">
          <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Total Team Poin</span>
          <div class="flex items-baseline gap-1.5">
            <span class="text-2xl font-black text-[#1653a1]">{{ steping_summary.total_team_points || 0 }}</span>
            <span class="text-xs font-bold text-slate-500">Poin</span>
          </div>
          <p class="text-[10px] text-slate-500 font-medium">Alokasi Generasi Tim</p>
        </div>

        <!-- Card 4: Next Target Tier -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm space-y-1">
          <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Target Next Tier</span>
          <div class="flex items-baseline gap-1.5">
            <span class="text-2xl font-black text-amber-600">Tier {{ steping_summary.next_tier }}</span>
          </div>
          <p class="text-[10px] text-slate-500 font-medium">
            Butuh +{{ steping_summary.remaining_referrals }} Direct Referral lagi
          </p>
        </div>
      </div>

      <!-- Main Content Grid 1: Stepping Tier Matrix & Direct Referrals -->
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

      <!-- SECTION: RIWAYAT & ALOKASI TEAM POIN (Under Stepping) -->
      <div class="bg-white rounded-3xl border border-slate-200/80 p-6 md:p-8 shadow-sm space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
          <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#04bdb2]/10 text-[#009c94] rounded-full text-[10px] font-black uppercase tracking-wider mb-1">
              <Award class="w-3.5 h-3.5" />
              <span>Program Team Poin</span>
            </div>
            <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight">RIWAYAT TEAM POIN</h2>
            <p class="text-xs text-slate-500 font-medium">
              Rincian perolehan Team Poin dari perekrutan dan pertumbuhan jaringan tim Anda sesuai alokasi generasi & paket.
            </p>
          </div>

          <div class="px-4 py-2 bg-[#f0f7fb] border border-[#1653a1]/30 rounded-2xl text-right">
            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">AKUMULASI TEAM POIN</span>
            <span class="text-2xl font-black text-[#1653a1]">{{ steping_summary.total_team_points || 0 }} <span class="text-xs font-bold text-slate-500">Poin</span></span>
          </div>
        </div>

        <!-- Matriks Ketentuan Alokasi Team Poin -->
        <div class="p-4 bg-slate-50/80 border border-slate-200/80 rounded-2xl space-y-3">
          <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Matriks Alokasi Perolehan Team Poin Per Paket:</h4>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-xs">
            <div v-for="r in team_point_rules" :key="r.package_name" class="p-3 bg-white border border-slate-200 rounded-xl space-y-1 shadow-2xs">
              <span class="font-extrabold text-slate-900 block text-[11px]">{{ r.package_name }}</span>
              <div class="flex items-center justify-between text-slate-600">
                <span>Perolehan:</span>
                <span class="font-black text-[#04bdb2]">+{{ r.team_points }} Poin</span>
              </div>
              <div class="flex items-center justify-between text-slate-500 text-[10px]">
                <span>Kedalaman Max:</span>
                <span class="font-bold text-slate-700">{{ r.max_gen }} Generasi</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Table Log Riwayat Team Poin -->
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="border-b border-slate-100 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-wider">
                <th class="py-3 px-4">Tanggal & Waktu</th>
                <th class="py-3 px-4">Username Sumber</th>
                <th class="py-3 px-4">Nama Mitra</th>
                <th class="py-3 px-4">Paket Join</th>
                <th class="py-3 px-4 text-right">Team Poin Diperoleh</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-semibold text-slate-700">
              <tr v-for="item in team_point_logs" :key="item.id" class="hover:bg-slate-50/70 transition-colors">
                <td class="py-3.5 px-4 text-slate-500 font-mono">{{ item.created_at }}</td>
                <td class="py-3.5 px-4 font-black text-[#1653a1] font-mono">{{ item.source_username }}</td>
                <td class="py-3.5 px-4 font-bold text-slate-900">{{ item.source_name }}</td>
                <td class="py-3.5 px-4">
                  <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 rounded-md text-[10px] font-extrabold border border-emerald-300">
                    {{ item.package_name }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-right font-black text-[#04bdb2] text-sm">
                  +{{ item.points_earned }} Poin
                </td>
              </tr>

              <tr v-if="!team_point_logs || team_point_logs.length === 0">
                <td colspan="5" class="py-8 text-center text-slate-400 text-xs font-medium italic">
                  Belum ada riwayat perolehan Team Poin.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- SECTION: REWARD TEAM POIN KLAIM -->
      <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-3xl p-6 md:p-8 shadow-sm space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-amber-200/70 pb-5">
          <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-[10px] font-black uppercase tracking-wider mb-1">
              <Award class="w-3.5 h-3.5" />
              <span>Reward Konversi Team Poin</span>
            </div>
            <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight">KLAIM REWARD TEAM POIN</h2>
            <p class="text-xs text-slate-600 font-medium mt-0.5">
              Kumpulkan Team Poin dari jaringan Anda, lalu klaim reward tunai sesuai bracket pencapaian.
            </p>
          </div>
          <div class="px-5 py-3 bg-white border border-amber-300 rounded-2xl text-center shadow-sm">
            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Team Poin Anda</span>
            <span class="text-3xl font-black text-amber-600">{{ steping_summary.total_team_points || 0 }}</span>
            <span class="text-xs font-bold text-slate-500 block">Poin</span>
          </div>
        </div>

        <!-- Bracket Reward Table -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
          <div
            v-for="b in rewardBrackets"
            :key="b.points"
            :class="[
              'p-4 rounded-2xl border-2 text-center space-y-1 transition-all',
              (steping_summary.total_team_points || 0) >= b.points
                ? 'bg-amber-400 border-amber-500 text-white shadow-md'
                : 'bg-white border-slate-200 text-slate-500'
            ]"
          >
            <span class="block text-lg font-black">{{ b.points }} Poin</span>
            <span class="block text-xs font-bold">{{ formatRp(b.reward) }}</span>
            <span
              v-if="(steping_summary.total_team_points || 0) >= b.points"
              class="inline-block text-[9px] font-black uppercase px-2 py-0.5 bg-white/30 rounded-full"
            >✓ ELIGIBLE</span>
          </div>
        </div>

        <!-- Klaim Status / Button -->
        <div class="pt-2">
          <!-- Sudah ada pending request -->
          <div v-if="pending_redemption" class="flex items-center gap-3 p-4 bg-amber-100 border border-amber-300 rounded-2xl">
            <div class="p-2 bg-amber-500 rounded-xl shrink-0">
              <Award class="w-4 h-4 text-white" />
            </div>
            <div>
              <p class="text-xs font-black text-amber-900">Permintaan Klaim Sedang Diproses</p>
              <p class="text-[11px] text-amber-800 font-medium mt-0.5">
                Anda sudah mengajukan klaim <strong>{{ pending_redemption.points_used }} Poin</strong> = <strong>{{ formatRp(pending_redemption.reward_amount) }}</strong>. Menunggu persetujuan Admin.
              </p>
            </div>
          </div>

          <!-- Eligible dan belum ada pending -->
          <div v-else-if="eligibleBracket" class="flex flex-col sm:flex-row items-start sm:items-center gap-4 p-5 bg-white border border-amber-300 rounded-2xl shadow-sm">
            <div class="flex-1">
              <p class="text-sm font-black text-slate-900">
                Selamat! 🎉 Anda bisa klaim reward <span class="text-amber-600">{{ eligibleBracket.points }} Team Poin</span>
              </p>
              <p class="text-xs text-slate-600 font-medium mt-0.5">
                Reward tunai sebesar <strong class="text-emerald-600">{{ formatRp(eligibleBracket.reward) }}</strong> akan diproses ke saldo e-wallet Anda setelah disetujui Admin.
              </p>
            </div>
            <button
              @click="redeemForm.post(route('admin.team-point-redemptions.store'))"
              :disabled="redeemForm.processing"
              class="shrink-0 px-6 py-3 bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white text-sm font-black rounded-2xl shadow-md transition-all disabled:opacity-50 flex items-center gap-2 cursor-pointer"
            >
              <Award class="w-4 h-4" />
              Klaim {{ formatRp(eligibleBracket.reward) }}
            </button>
          </div>

          <!-- Belum eligible -->
          <div v-else class="flex items-center gap-3 p-4 bg-slate-50 border border-slate-200 rounded-2xl">
            <div class="p-2 bg-slate-200 rounded-xl shrink-0">
              <Lock class="w-4 h-4 text-slate-500" />
            </div>
            <div>
              <p class="text-xs font-black text-slate-700">Belum Eligible untuk Klaim</p>
              <p class="text-[11px] text-slate-500 font-medium mt-0.5">
                Kumpulkan minimal <strong>35 Team Poin</strong> untuk mulai klaim reward. Saat ini Anda memiliki <strong>{{ steping_summary.total_team_points || 0 }} Poin</strong>.
              </p>
            </div>
          </div>
        </div>
      </div>

    </div>
  </AdminLayout>
</template>
