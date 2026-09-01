<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
  Copy, 
  Wallet, 
  TrendingUp, 
  Users, 
  Trophy, 
  Sparkles, 
  Award, 
  ShieldCheck, 
  Gift, 
  ArrowUpRight, 
  Check, 
  Package
} from '@lucide/vue';

const props = defineProps({
  referral_links: Object,
  wallet: Object,
  binary_legs: Object,
  rewards: Array,
  packages: Array,
  steping_status: Object
});

const copySuccessMsg = ref('');

const copyToClipboard = (text, type) => {
  navigator.clipboard.writeText(text);
  copySuccessMsg.value = `Link Referral ${type} berhasil disalin!`;
  setTimeout(() => {
    copySuccessMsg.value = '';
  }, 3000);
};

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
};
</script>

<template>
  <Head title="Dashboard Member Area - XSELLER" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Copy Link Toast Alert Banner -->
      <div v-if="copySuccessMsg" class="p-3 bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 rounded-xl text-xs font-semibold flex items-center gap-2 animate-bounce">
        <Check class="w-4 h-4 text-emerald-500" />
        <span>{{ copySuccessMsg }}</span>
      </div>

      <!-- 1. Link Referral Banner Card -->
      <div class="bg-gradient-to-r from-[#f0f7fb] to-[#e6f9f8] border border-[#04bdb2]/30 rounded-3xl p-5 md:p-6 shadow-sm relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-start gap-4">
          <div class="p-3 bg-[#04bdb2]/10 text-[#009c94] rounded-2xl shrink-0 hidden sm:block">
            <span class="text-xl font-bold">🔗</span>
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h3 class="text-sm font-extrabold text-[#1653a1] tracking-tight">Link Referral Anda</h3>
              <span class="px-2 py-0.5 text-[9px] font-bold bg-[#04bdb2]/20 text-[#009c94] rounded-md">Referral Kemitraan</span>
            </div>
            <p class="text-xs text-slate-600 mt-1 font-medium">Bagikan link ini untuk mendaftarkan mitra baru secara langsung ke jaringan Anda.</p>
          </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
          <button 
            @click="copyToClipboard(referral_links?.default || referral_links?.url, 'Referral')"
            class="px-4 py-2 bg-gradient-to-r from-[#1653a1] to-[#04bdb2] hover:opacity-95 text-white text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 cursor-pointer shadow-md"
          >
            <Copy class="w-3.5 h-3.5" />
            <span>Copy Link Referral</span>
          </button>
        </div>
      </div>

      <!-- 2. Main Metrics Grid (Saldo Wallet & Total Bonus Cair) -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Saldo Wallet Card -->
        <div class="bg-gradient-to-br from-[#0b1f3a] via-[#103f80] to-[#1653a1] text-white rounded-3xl p-6 relative overflow-hidden shadow-lg space-y-4 border border-[#04bdb2]/30">
          <div class="flex items-center justify-between">
            <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#a9fff7] flex items-center gap-1.5">
              <Wallet class="w-3.5 h-3.5 text-[#04bdb2]" />
              SALDO WALLET
            </span>
          </div>

          <div>
            <h2 class="text-3xl md:text-4xl font-black text-white tracking-tight">{{ formatRupiah(wallet?.saldo || 2500000) }}</h2>
          </div>

          <div class="pt-2 flex items-center justify-between border-t border-white/10">
            <span class="text-[11px] text-slate-300 font-medium">Status Wallet: Terverifikasi</span>
            <button class="px-3.5 py-1.5 bg-[#04bdb2] hover:bg-[#009c94] text-white text-xs font-bold rounded-xl shadow-md flex items-center gap-1.5 transition-all cursor-pointer">
              <span>Tarik Dana</span>
              <ArrowUpRight class="w-4 h-4" />
            </button>
          </div>
        </div>

        <!-- Total Bonus Cair Card -->
        <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-4">
          <div class="flex items-center justify-between">
            <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400 flex items-center gap-1.5">
              <TrendingUp class="w-3.5 h-3.5 text-[#1653a1]" />
              TOTAL BONUS CAIR
            </span>
            <div class="w-6 h-6 rounded-full bg-[#f0f7fb] flex items-center justify-center text-[#1653a1]">
              <TrendingUp class="w-3.5 h-3.5" />
            </div>
          </div>

          <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">{{ formatRupiah(wallet?.total_bonus_cair || 400000) }}</h2>
          </div>

          <!-- Bonus Breakdown -->
          <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 text-xs">
            <div class="p-2 bg-[#f4f8fb] rounded-xl">
              <p class="text-[10px] text-slate-400 font-bold uppercase">Sponsor (20%)</p>
              <p class="font-bold text-[#1653a1] mt-0.5">{{ formatRupiah(wallet?.bonus_sponsor || 300000) }}</p>
            </div>
            <div class="p-2 bg-[#f4f8fb] rounded-xl">
              <p class="text-[10px] text-slate-400 font-bold uppercase">Generasi (Tier)</p>
              <p class="font-bold text-[#009c94] mt-0.5">{{ formatRupiah(wallet?.bonus_generasi || 100000) }}</p>
            </div>
            <div class="p-2 bg-[#f4f8fb] rounded-xl">
              <p class="text-[10px] text-slate-400 font-bold uppercase">Bonus RO</p>
              <p class="font-bold text-amber-600 mt-0.5">{{ formatRupiah(wallet?.bonus_ro || 0) }}</p>
            </div>
            <div class="p-2 bg-[#f4f8fb] rounded-xl">
              <p class="text-[10px] text-slate-400 font-bold uppercase">Bonus TPR</p>
              <p class="font-bold text-rose-600 mt-0.5">{{ formatRupiah(wallet?.bonus_tpr || 0) }}</p>
            </div>
          </div>
        </div>

      </div>



      <!-- 4. Membership Packages & TPR Plan PRD 2026 Overview -->
      <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
          <div class="flex items-center gap-2">
            <Package class="w-5 h-5 text-[#1653a1]" />
            <div>
              <h3 class="text-base font-extrabold text-slate-900 tracking-tight">Rincian Paket Join & Fitur TPR (PRD 2026)</h3>
              <p class="text-xs text-slate-500">Ketentuan alokasi bonus sponsor 20%, tier generasi 1-15, dan profit share program TPR.</p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 pt-2">
          <div 
            v-for="pkg in packages" 
            :key="pkg.name"
            :class="[
              pkg.is_current ? 'border-[#04bdb2] bg-[#e6f9f8]/60 shadow-xs' : 'border-slate-100 bg-slate-50/60',
              'p-4 rounded-2xl border space-y-3 flex flex-col justify-between'
            ]"
          >
            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <span class="text-xs font-black text-slate-900 uppercase">{{ pkg.name }}</span>
                <span v-if="pkg.is_current" class="px-2 py-0.5 text-[9px] font-bold bg-[#04bdb2]/20 text-[#009c94] rounded-md">Aktif</span>
              </div>
              <h4 class="text-lg font-extrabold text-[#1653a1]">{{ formatRupiah(pkg.price) }}</h4>

              <ul class="space-y-1.5 text-[11px] text-slate-600 pt-2 border-t border-slate-200/60">
                <li class="flex items-center gap-1">
                  <span class="text-[#009c94] font-bold">•</span>
                  <span>Sponsor: <strong class="text-slate-800">{{ formatRupiah(pkg.sponsor_bonus) }}</strong></span>
                </li>
                <li class="flex items-center gap-1">
                  <span class="text-[#1653a1] font-bold">•</span>
                  <span>Max Gen: <strong class="text-slate-800">{{ pkg.max_tier }}</strong></span>
                </li>
                <li class="flex items-center gap-1">
                  <span class="text-[#04bdb2] font-bold">•</span>
                  <span>Team Poin: <strong class="text-slate-800">{{ pkg.team_poin }} Poin</strong></span>
                </li>
              </ul>
            </div>

            <div class="pt-2 border-t border-slate-200/60">
              <span class="text-[10px] font-semibold text-amber-600 block truncate">{{ pkg.tpr }}</span>
            </div>
          </div>
        </div>
      </div>


    </div>
  </AdminLayout>
</template>
