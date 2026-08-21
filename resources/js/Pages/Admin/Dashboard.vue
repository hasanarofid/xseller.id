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
  <Head title="Dashboard System Binary - XSELLER" />

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
              <h3 class="text-sm font-extrabold text-[#1653a1] tracking-tight">Link Referral Anda (Otomatis Kiri/Kanan)</h3>
              <span class="px-2 py-0.5 text-[9px] font-bold bg-[#04bdb2]/20 text-[#009c94] rounded-md">Otomatis Placement</span>
            </div>
            <p class="text-xs text-slate-600 mt-1 font-medium">Bagikan link ini. Member baru otomatis akan diletakkan di kaki paling bawah sesuai pilihan Anda.</p>
          </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
          <button 
            @click="copyToClipboard(referral_links?.left, 'Kiri')"
            class="px-4 py-2 bg-white hover:bg-slate-50 border border-[#1653a1]/30 text-[#1653a1] text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 cursor-pointer shadow-xs"
          >
            <Copy class="w-3.5 h-3.5" />
            <span>Copy Kiri</span>
          </button>
          <button 
            @click="copyToClipboard(referral_links?.right, 'Kanan')"
            class="px-4 py-2 bg-gradient-to-r from-[#1653a1] to-[#04bdb2] hover:opacity-95 text-white text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 cursor-pointer shadow-md"
          >
            <Copy class="w-3.5 h-3.5" />
            <span>Copy Kanan</span>
          </button>
        </div>
      </div>

      <!-- 2. Main Metrics Grid (Saldo Wallet, Total Bonus Cair, Perkembangan Kaki Binary) -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Saldo Wallet Card -->
        <div class="bg-gradient-to-br from-[#0b1f3a] via-[#103f80] to-[#1653a1] text-white rounded-3xl p-6 relative overflow-hidden shadow-lg space-y-4 border border-[#04bdb2]/30">
          <div class="flex items-center justify-between">
            <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#a9fff7] flex items-center gap-1.5">
              <Wallet class="w-3.5 h-3.5 text-[#04bdb2]" />
              SALDO WALLET
            </span>
            <span class="px-2.5 py-0.5 text-[10px] font-bold bg-[#04bdb2]/20 text-[#a9fff7] rounded-full border border-[#04bdb2]/30">
              VOUCHER Aktif: {{ wallet?.voucher_aktif || 2 }} Pcs
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
              <p class="text-[10px] text-slate-400 font-bold uppercase">Pasangan/Tier</p>
              <p class="font-bold text-[#009c94] mt-0.5">{{ formatRupiah(wallet?.bonus_pasangan || 100000) }}</p>
            </div>
            <div class="p-2 bg-[#f4f8fb] rounded-xl">
              <p class="text-[10px] text-slate-400 font-bold uppercase">Titik RO</p>
              <p class="font-bold text-amber-600 mt-0.5">{{ formatRupiah(wallet?.bonus_titik || 0) }}</p>
            </div>
            <div class="p-2 bg-[#f4f8fb] rounded-xl">
              <p class="text-[10px] text-slate-400 font-bold uppercase">Reward</p>
              <p class="font-bold text-rose-600 mt-0.5">{{ formatRupiah(wallet?.bonus_reward || 0) }}</p>
            </div>
          </div>
        </div>

        <!-- Perkembangan Kaki Binary Card -->
        <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-4">
          <div class="flex items-center justify-between">
            <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400 flex items-center gap-1.5">
              <Users class="w-3.5 h-3.5 text-[#1653a1]" />
              PERKEMBANGAN KAKI BINARY
            </span>
            <span class="px-2.5 py-0.5 text-[10px] font-bold bg-[#e6f9f8] text-[#009c94] rounded-full border border-[#04bdb2]/30">
              2 KAKI BINARY
            </span>
          </div>

          <div class="grid grid-cols-2 gap-3 pt-2">
            <!-- Left Leg -->
            <div class="p-4 bg-[#f4f8fb] rounded-2xl border border-slate-100 space-y-2 text-center">
              <span class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider block">KAKI KIRI (LEFT)</span>
              <h3 class="text-2xl font-black text-slate-900">{{ binary_legs?.left?.members || 3 }} <span class="text-xs font-semibold text-slate-400">Orang</span></h3>
              <div class="px-2 py-1 bg-[#e6f9f8] text-[#009c94] border border-[#04bdb2]/30 rounded-lg text-[10px] font-bold">
                MENUNGGU: {{ binary_legs?.left?.pending_points || 1 }} Poin
              </div>
            </div>

            <!-- Right Leg -->
            <div class="p-4 bg-[#f4f8fb] rounded-2xl border border-slate-100 space-y-2 text-center">
              <span class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider block">KAKI KANAN (RIGHT)</span>
              <h3 class="text-2xl font-black text-slate-900">{{ binary_legs?.right?.members || 2 }} <span class="text-xs font-semibold text-slate-400">Orang</span></h3>
              <div class="px-2 py-1 bg-[#e6f9f8] text-[#009c94] border border-[#04bdb2]/30 rounded-lg text-[10px] font-bold">
                MENUNGGU: {{ binary_legs?.right?.pending_points || 0 }} Poin
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- 3. Pencapaian Reward Jaringan Card Row -->
      <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-4">
          <div>
            <div class="flex items-center gap-2">
              <Trophy class="w-5 h-5 text-[#04bdb2]" />
              <h3 class="text-base font-extrabold text-slate-900 tracking-tight uppercase">Pencapaian Reward Jaringan</h3>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Sistem reward dihitung otomatis dari keseimbangan volume kaki Kiri & Kanan Anda.</p>
          </div>
          <div class="px-3.5 py-1.5 bg-[#f0f7fb] text-[#1653a1] border border-[#1653a1]/20 text-xs font-bold rounded-full self-start sm:self-auto">
            Total Reward Cair: Rp 0
          </div>
        </div>

        <!-- Reward Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
          
          <!-- Silver Reward -->
          <div class="p-4 bg-slate-50/70 border border-slate-100 rounded-2xl space-y-3 relative overflow-hidden flex flex-col justify-between">
            <div class="space-y-1">
              <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-slate-600 uppercase tracking-wider">SILVER REWARD</span>
                <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-slate-600">
                  <Sparkles class="w-3.5 h-3.5" />
                </div>
              </div>
              <p class="text-[10px] text-slate-400 font-bold uppercase">HADIAH</p>
              <h4 class="text-xs font-black text-slate-800 leading-tight">HP Android / Rp 1 Juta</h4>
              <p class="text-[10px] text-slate-500">Syarat: 10 : 10</p>
            </div>

            <div class="space-y-2 pt-2 border-t border-slate-200/60">
              <div class="space-y-1 text-[10px] text-slate-500">
                <div class="flex justify-between font-bold"><span>Kiri:</span> <span>3/10</span></div>
                <div class="w-full h-1.5 bg-slate-200 rounded-full overflow-hidden">
                  <div class="h-full bg-[#1653a1] rounded-full" style="width: 30%"></div>
                </div>
                <div class="flex justify-between font-bold pt-1"><span>Kanan:</span> <span>2/10</span></div>
                <div class="w-full h-1.5 bg-slate-200 rounded-full overflow-hidden">
                  <div class="h-full bg-[#04bdb2] rounded-full" style="width: 20%"></div>
                </div>
              </div>

              <button class="w-full py-1.5 bg-slate-100 text-slate-400 text-[10px] font-bold rounded-lg uppercase tracking-wider cursor-not-allowed">
                MENUNGGU
              </button>
            </div>
          </div>

          <!-- Gold Reward -->
          <div class="p-4 bg-slate-50/70 border border-slate-100 rounded-2xl space-y-3 relative overflow-hidden flex flex-col justify-between">
            <div class="space-y-1">
              <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-amber-600 uppercase tracking-wider">GOLD REWARD</span>
                <div class="w-6 h-6 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
                  <Award class="w-3.5 h-3.5" />
                </div>
              </div>
              <p class="text-[10px] text-slate-400 font-bold uppercase">HADIAH</p>
              <h4 class="text-xs font-black text-slate-800 leading-tight">Laptop / Rp 5 Juta</h4>
              <p class="text-[10px] text-slate-500">Syarat: 50 : 50</p>
            </div>

            <div class="space-y-2 pt-2 border-t border-slate-200/60">
              <div class="space-y-1 text-[10px] text-slate-500">
                <div class="flex justify-between font-bold"><span>Kiri:</span> <span>3/50</span></div>
                <div class="w-full h-1.5 bg-slate-200 rounded-full overflow-hidden">
                  <div class="h-full bg-amber-500 rounded-full" style="width: 6%"></div>
                </div>
                <div class="flex justify-between font-bold pt-1"><span>Kanan:</span> <span>2/50</span></div>
                <div class="w-full h-1.5 bg-slate-200 rounded-full overflow-hidden">
                  <div class="h-full bg-amber-500 rounded-full" style="width: 4%"></div>
                </div>
              </div>

              <button class="w-full py-1.5 bg-slate-100 text-slate-400 text-[10px] font-bold rounded-lg uppercase tracking-wider cursor-not-allowed">
                MENUNGGU
              </button>
            </div>
          </div>

          <!-- Platinum Reward -->
          <div class="p-4 bg-slate-50/70 border border-slate-100 rounded-2xl space-y-3 relative overflow-hidden flex flex-col justify-between">
            <div class="space-y-1">
              <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-[#1653a1] uppercase tracking-wider">PLATINUM REWARD</span>
                <div class="w-6 h-6 rounded-full bg-[#f0f7fb] flex items-center justify-center text-[#1653a1]">
                  <ShieldCheck class="w-3.5 h-3.5" />
                </div>
              </div>
              <p class="text-[10px] text-slate-400 font-bold uppercase">HADIAH</p>
              <h4 class="text-xs font-black text-slate-800 leading-tight">Motor / Rp 25 Juta</h4>
              <p class="text-[10px] text-slate-500">Syarat: 250 : 250</p>
            </div>

            <div class="space-y-2 pt-2 border-t border-slate-200/60">
              <div class="space-y-1 text-[10px] text-slate-500">
                <div class="flex justify-between font-bold"><span>Kiri:</span> <span>3/250</span></div>
                <div class="w-full h-1.5 bg-slate-200 rounded-full overflow-hidden">
                  <div class="h-full bg-[#1653a1] rounded-full" style="width: 2%"></div>
                </div>
                <div class="flex justify-between font-bold pt-1"><span>Kanan:</span> <span>2/250</span></div>
                <div class="w-full h-1.5 bg-slate-200 rounded-full overflow-hidden">
                  <div class="h-full bg-[#04bdb2] rounded-full" style="width: 1%"></div>
                </div>
              </div>

              <button class="w-full py-1.5 bg-slate-100 text-slate-400 text-[10px] font-bold rounded-lg uppercase tracking-wider cursor-not-allowed">
                MENUNGGU
              </button>
            </div>
          </div>

          <!-- Diamond Reward -->
          <div class="p-4 bg-slate-50/70 border border-slate-100 rounded-2xl space-y-3 relative overflow-hidden flex flex-col justify-between">
            <div class="space-y-1">
              <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-rose-600 uppercase tracking-wider">DIAMOND REWARD</span>
                <div class="w-6 h-6 rounded-full bg-rose-100 flex items-center justify-center text-rose-600">
                  <Trophy class="w-3.5 h-3.5" />
                </div>
              </div>
              <p class="text-[10px] text-slate-400 font-bold uppercase">HADIAH</p>
              <h4 class="text-xs font-black text-slate-800 leading-tight">Mobil / Rp 150 Juta</h4>
              <p class="text-[10px] text-slate-500">Syarat: 1000 : 1000</p>
            </div>

            <div class="space-y-2 pt-2 border-t border-slate-200/60">
              <div class="space-y-1 text-[10px] text-slate-500">
                <div class="flex justify-between font-bold"><span>Kiri:</span> <span>3/1000</span></div>
                <div class="w-full h-1.5 bg-slate-200 rounded-full overflow-hidden">
                  <div class="h-full bg-rose-500 rounded-full" style="width: 1%"></div>
                </div>
                <div class="flex justify-between font-bold pt-1"><span>Kanan:</span> <span>2/1000</span></div>
                <div class="w-full h-1.5 bg-slate-200 rounded-full overflow-hidden">
                  <div class="h-full bg-rose-500 rounded-full" style="width: 1%"></div>
                </div>
              </div>

              <button class="w-full py-1.5 bg-slate-100 text-slate-400 text-[10px] font-bold rounded-lg uppercase tracking-wider cursor-not-allowed">
                MENUNGGU
              </button>
            </div>
          </div>

          <!-- Crown Reward -->
          <div class="p-4 bg-slate-50/70 border border-slate-100 rounded-2xl space-y-3 relative overflow-hidden flex flex-col justify-between">
            <div class="space-y-1">
              <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-[#009c94] uppercase tracking-wider">CROWN REWARD</span>
                <div class="w-6 h-6 rounded-full bg-[#e6f9f8] flex items-center justify-center text-[#009c94]">
                  <Gift class="w-3.5 h-3.5" />
                </div>
              </div>
              <p class="text-[10px] text-slate-400 font-bold uppercase">HADIAH</p>
              <h4 class="text-xs font-black text-slate-800 leading-tight">Rumah Mewah / Rp 750 Juta</h4>
              <p class="text-[10px] text-slate-500">Syarat: 5000 : 5000</p>
            </div>

            <div class="space-y-2 pt-2 border-t border-slate-200/60">
              <div class="space-y-1 text-[10px] text-slate-500">
                <div class="flex justify-between font-bold"><span>Kiri:</span> <span>3/5000</span></div>
                <div class="w-full h-1.5 bg-slate-200 rounded-full overflow-hidden">
                  <div class="h-full bg-[#04bdb2] rounded-full" style="width: 1%"></div>
                </div>
                <div class="flex justify-between font-bold pt-1"><span>Kanan:</span> <span>2/5000</span></div>
                <div class="w-full h-1.5 bg-slate-200 rounded-full overflow-hidden">
                  <div class="h-full bg-[#04bdb2] rounded-full" style="width: 1%"></div>
                </div>
              </div>

              <button class="w-full py-1.5 bg-slate-100 text-slate-400 text-[10px] font-bold rounded-lg uppercase tracking-wider cursor-not-allowed">
                MENUNGGU
              </button>
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
