<script setup>
import { ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { 
  LayoutDashboard, 
  Settings as SettingsIcon, 
  FileText, 
  Users, 
  Menu, 
  X, 
  LogOut, 
  ChevronDown,
  ChevronLeft,
  ChevronRight,
  Bell,
  Wallet,
  KeyRound,
  GitFork,
  UserPlus,
  ArrowUpRight,
  UserCheck,
  CheckCircle2,
  Activity,
  Crown,
  Download
} from '@lucide/vue';

const page = usePage();
const user = page.props.auth?.user || { name: 'President Director (Admin)', email: 'admin@xseller.id' };

const isSidebarOpen = ref(false);
const isSidebarCollapsed = ref(false);
const isUserMenuOpen = ref(false);
const isNotificationsOpen = ref(false);

const toastStack = ref([]);

const closeToast = (id) => {
  toastStack.value = toastStack.value.filter(t => t.id !== id);
};

const closeAllToasts = () => {
  toastStack.value = [];
};

const navigation = [
  { name: 'Dashboard', href: route('admin.dashboard'), icon: LayoutDashboard, current: route().current('admin.dashboard') },
  { name: 'Team Mitra', href: route('admin.pohon-jaringan'), icon: GitFork, current: route().current('admin.pohon-jaringan') },
  { name: 'Aktivasi Member', href: route('admin.activation.index'), icon: UserPlus, current: route().current('admin.activation.index') },
  { name: 'PIN Wallet', href: route('admin.voucher-wallet.index'), icon: KeyRound, current: route().current('admin.voucher-wallet.index') },
  { name: 'Keuangan', href: route('admin.finance.index'), icon: Wallet, current: route().current('admin.finance.index') },
  { name: 'Penarikan Saldo', href: route('admin.withdrawals.index'), icon: ArrowUpRight, current: route().current('admin.withdrawals.index') },
  { name: 'Fitur TPR (Bagi Hasil)', href: route('admin.tpr.index'), icon: Crown, current: route().current('admin.tpr.index') },
  { name: 'Data Jaringan', href: route('admin.network-data.index'), icon: Users, current: route().current('admin.network-data.index') },
  { name: 'Aktivitas', href: route('admin.activities.index'), icon: Activity, current: route().current('admin.activities.index') },
  { name: 'Laporan', href: route('admin.reports.index'), icon: FileText, current: route().current('admin.reports.index') },
  { name: 'Pengaturan Profil', href: route('profile.edit'), icon: UserCheck, current: route().current('profile.edit') },
  { name: 'Pengaturan Sistem', href: route('admin.settings.index'), icon: SettingsIcon, current: route().current('admin.settings.index'), special: 'amber' },
];

const logout = () => {
  router.post(route('logout'));
};
</script>

<template>
  <div class="min-h-screen bg-[#f4f8fb] text-slate-800 font-sans antialiased relative overflow-hidden flex flex-col justify-between">
    
    <div>
      <!-- Floating Toast Notification Stack -->
      <div v-if="toastStack.length > 0" class="fixed top-4 right-4 z-50 flex flex-col items-end gap-2 max-w-xs">
        <button 
          @click="closeAllToasts" 
          class="px-3 py-1 bg-white/90 border border-slate-200 hover:bg-slate-50 text-slate-700 text-[11px] font-semibold rounded-full shadow-md backdrop-blur flex items-center gap-1.5 transition-all cursor-pointer"
        >
          <CheckCircle2 class="w-3.5 h-3.5 text-[#04bdb2]" />
          <span>Tutup Semua</span>
        </button>

        <div 
          v-for="toast in toastStack" 
          :key="toast.id"
          class="w-full p-3 bg-[#e6f9f8] border border-[#04bdb2]/40 text-[#009c94] rounded-2xl shadow-lg backdrop-blur-md flex items-center justify-between gap-3 text-xs font-bold animate-fade-in transition-all"
        >
          <div class="flex items-center gap-2">
            <div class="w-5 h-5 rounded-full bg-[#04bdb2] text-white flex items-center justify-center shrink-0">
              <CheckCircle2 class="w-3.5 h-3.5" />
            </div>
            <span>{{ toast.text }}</span>
          </div>
          <button @click="closeToast(toast.id)" class="text-[#04bdb2] hover:text-[#1653a1] p-0.5 cursor-pointer">
            <X class="w-3.5 h-3.5" />
          </button>
        </div>
      </div>

      <!-- Mobile Sidebar Backdrop -->
      <div 
        v-if="isSidebarOpen" 
        @click="isSidebarOpen = false" 
        class="fixed inset-0 z-40 bg-slate-900/60 lg:hidden transition-opacity"
      ></div>

      <!-- Left Sidebar -->
      <aside 
        :class="[
          isSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
          isSidebarCollapsed ? 'lg:w-20' : 'lg:w-64',
          'fixed top-0 bottom-0 left-0 z-40 bg-white border-r border-slate-200/80 transition-all duration-300 ease-in-out lg:fixed flex flex-col justify-between shadow-sm'
        ]"
      >
        <div>
          <!-- Sidebar Brand Header Mobile -->
          <div class="flex items-center h-16 px-5 border-b border-slate-100 lg:hidden justify-between">
            <div class="flex items-center gap-2.5">
              <ApplicationLogo class="h-7 w-auto" />
              <span class="font-extrabold text-[#1653a1] text-sm tracking-tight uppercase">XSELLER</span>
            </div>
            <button @click="isSidebarOpen = false" class="p-2 text-slate-500 hover:text-slate-800">
              <X class="w-5 h-5" />
            </button>
          </div>

          <!-- Sidebar User Profile Summary Card -->
          <div v-if="!isSidebarCollapsed" class="p-4 flex flex-col items-center text-center space-y-2 border-b border-slate-100">
            <div class="w-14 h-14 rounded-full bg-gradient-to-tr from-[#1653a1] to-[#04bdb2] text-white font-extrabold flex items-center justify-center text-xl shadow-md border-2 border-white">
              {{ user.name ? user.name.charAt(0).toUpperCase() : 'P' }}
            </div>
            <div>
              <h3 class="text-xs font-black text-slate-800 tracking-tight leading-tight">{{ user.name }}</h3>
              <p class="text-[10px] text-slate-400 font-medium">@{{ user.username || 'admin' }}</p>
            </div>
            <div class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full border border-[#04bdb2]/40 text-[#009c94] bg-[#04bdb2]/10 text-[9px] font-extrabold uppercase tracking-wider">
              <Crown class="w-3 h-3 text-[#04bdb2]" />
              <span>MEMBER</span>
            </div>

            <!-- Dompet Saya Card Widget -->
            <div class="w-full mt-2 p-3 bg-[#f0f7fb] border border-[#04bdb2]/20 rounded-2xl text-left space-y-1.5 shadow-xs">
              <span class="text-[9px] font-extrabold text-[#1653a1] uppercase tracking-wider block">DOMPET SAYA</span>
              <p class="text-sm font-black text-slate-900 leading-tight">Rp 2.500.000</p>
              <div class="grid grid-cols-2 gap-1 pt-1.5 border-t border-slate-200/80 text-[9px]">
                <div>
                  <span class="text-slate-400 font-medium block">VOUCHER:</span>
                  <span class="font-bold text-slate-800">2 Pcs</span>
                </div>
                <div>
                  <span class="text-slate-400 font-medium block">TOTAL BONUS:</span>
                  <span class="font-bold text-[#009c94]">Rp 400.000</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Navigation Menu -->
          <nav class="px-3 py-3 space-y-1.5 overflow-y-auto max-h-[calc(100vh-280px)]">
            <template v-for="item in navigation" :key="item.name">
              <a 
                v-if="item.external"
                :href="item.href"
                :class="[
                  item.special === 'blue' ? 'bg-[#f0f7fb] border border-[#1653a1]/30 text-[#1653a1] font-bold hover:bg-[#e2f1fc]' : 'text-slate-600 hover:bg-slate-100 font-medium',
                  isSidebarCollapsed ? 'lg:justify-center lg:px-0' : 'px-3.5',
                  'group flex items-center py-2.5 text-xs rounded-2xl transition-all duration-200'
                ]"
                :title="isSidebarCollapsed ? item.name : ''"
              >
                <component 
                  :is="item.icon" 
                  :class="[
                    item.special === 'blue' ? 'text-[#1653a1]' : 'text-slate-400',
                    isSidebarCollapsed ? 'lg:mr-0' : 'mr-3',
                    'w-4 h-4 flex-shrink-0 transition-transform duration-200 group-hover:scale-110'
                  ]" 
                />
                <span :class="[isSidebarCollapsed ? 'lg:hidden' : 'block', 'whitespace-nowrap']">{{ item.name }}</span>
              </a>

              <Link 
                v-else
                :href="item.href"
                :class="[
                  item.current 
                    ? (item.special === 'amber' ? 'bg-gradient-to-r from-amber-500 to-amber-600 text-white font-black shadow-sm' : 'bg-gradient-to-r from-[#1653a1] to-[#04bdb2] text-white font-bold shadow-md shadow-[#1653a1]/20')
                    : (item.special === 'amber' ? 'bg-amber-50/60 border border-amber-300/80 text-amber-900 font-bold hover:bg-amber-100' : 'text-slate-600 hover:bg-[#f0f7fb] hover:text-[#1653a1] font-medium'),
                  isSidebarCollapsed ? 'lg:justify-center lg:px-0' : 'px-3.5',
                  'group flex items-center py-2.5 text-xs rounded-2xl transition-all duration-200'
                ]"
                :title="isSidebarCollapsed ? item.name : ''"
              >
                <component 
                  :is="item.icon" 
                  :class="[
                    item.current ? 'text-white' : (item.special === 'amber' ? 'text-amber-600' : 'text-slate-400 group-hover:text-[#1653a1]'),
                    isSidebarCollapsed ? 'lg:mr-0' : 'mr-3',
                    'w-4 h-4 flex-shrink-0 transition-transform duration-200 group-hover:scale-110'
                  ]" 
                />
                <span :class="[isSidebarCollapsed ? 'lg:hidden' : 'block', 'whitespace-nowrap']">{{ item.name }}</span>
              </Link>
            </template>
          </nav>
        </div>

        <!-- Sidebar Collapse Toggle -->
        <div class="p-3 border-t border-slate-100 hidden lg:block text-right">
          <button 
            @click="isSidebarCollapsed = !isSidebarCollapsed"
            class="p-1.5 bg-slate-100 hover:bg-[#f0f7fb] rounded-lg text-slate-500 hover:text-[#1653a1] transition-colors cursor-pointer"
          >
            <ChevronLeft v-if="!isSidebarCollapsed" class="w-4 h-4" />
            <ChevronRight v-else class="w-4 h-4" />
          </button>
        </div>
      </aside>

      <!-- Main Content Wrapper -->
      <div 
        :class="[
          isSidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64',
          'flex flex-col min-h-screen transition-all duration-300 ease-in-out'
        ]"
      >
        <!-- Top Bar Header (Theme: Royal Blue to Teal Gradient matching Login) -->
        <header class="flex items-center justify-between h-16 px-6 md:px-8 bg-gradient-to-r from-[#0b1f3a] via-[#103f80] to-[#1653a1] text-white sticky top-0 z-30 shadow-md border-b border-[#04bdb2]/30">
          <div class="flex items-center gap-4">
            <button 
              @click="isSidebarOpen = true" 
              class="p-2 text-slate-300 hover:text-white lg:hidden"
            >
              <Menu class="w-6 h-6" />
            </button>
            
            <!-- Left Header Logo Badge -->
            <div class="flex items-center gap-3">
              <div class="px-2.5 py-1.5 bg-white/95 rounded-xl shadow-md flex items-center justify-center">
                <ApplicationLogo class="h-7 w-auto object-contain" />
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <h1 class="text-xs font-black tracking-wider text-[#a9fff7] uppercase">XSELLER</h1>
                  <span class="px-1.5 py-0.2 text-[9px] font-bold bg-white/10 text-slate-200 border border-white/20 rounded">v2.4 Member Area</span>
                </div>
                <p class="text-[9px] text-slate-300 font-medium tracking-tight hidden sm:block">Simple. Secure. Connected.</p>
              </div>
            </div>
          </div>

          <!-- Right Header Controls -->
          <div class="flex items-center gap-4 text-xs font-semibold">
            <!-- Notification Bell -->
            <button 
              @click="isNotificationsOpen = !isNotificationsOpen"
              class="relative p-2 rounded-full bg-white/10 hover:bg-white/20 text-[#a9fff7] transition-colors cursor-pointer"
            >
              <Bell class="w-4 h-4" />
              <span class="absolute top-1 right-1 w-2 h-2 bg-[#04bdb2] rounded-full animate-ping"></span>
            </button>

            <!-- User Avatar Circle -->
            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-[#04bdb2] to-[#1653a1] border border-white/40 text-white font-extrabold flex items-center justify-center text-xs shadow-sm">
              {{ user.name ? user.name.charAt(0).toUpperCase() : 'P' }}
            </div>

            <!-- Role Badge Text -->
            <div class="hidden sm:flex items-center gap-2">
              <span class="text-[11px] text-slate-300">MASUK SEBAGAI:</span>
              <span class="font-bold text-white text-xs">{{ user.name }}</span>
              <span class="px-2 py-0.5 text-[9px] font-bold bg-[#04bdb2]/20 text-[#a9fff7] border border-[#04bdb2]/40 rounded-md">Admin</span>
            </div>

            <!-- User Switch Button Dropdown Pill -->
            <div class="relative">
              <button 
                @click="isUserMenuOpen = !isUserMenuOpen"
                class="px-3 py-1.5 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs font-bold flex items-center gap-1.5 transition-colors cursor-pointer"
              >
                <Users class="w-3.5 h-3.5 text-[#a9fff7]" />
                <span>Akun Saya</span>
                <ChevronDown class="w-3.5 h-3.5 text-slate-300" />
              </button>

              <div v-if="isUserMenuOpen" @click="isUserMenuOpen = false" class="fixed inset-0 z-10"></div>
              <div v-if="isUserMenuOpen" class="absolute right-0 mt-2 w-48 bg-white text-slate-800 border border-slate-200 rounded-xl shadow-xl py-1 z-20 overflow-hidden">
                <Link :href="route('profile.edit')" class="block px-4 py-2 text-xs font-semibold hover:bg-[#f0f7fb]">
                  Pengaturan Profil
                </Link>
                <button @click="logout" class="w-full text-left block px-4 py-2 text-xs font-bold text-rose-600 hover:bg-slate-100 border-t border-slate-100">
                  Keluar
                </button>
              </div>
            </div>
          </div>
        </header>

        <!-- Main Dashboard Content Area -->
        <main class="flex-1 p-6 md:p-8">
          <slot />
        </main>

        <!-- Main Footer -->
        <footer class="p-4 text-center text-[11px] text-slate-500 border-t border-slate-200 bg-white">
          <p>© 2026 XSELLER.ID. Hak Cipta Dilindungi Undang-Undang. E-Commerce Trade Promotion Program & Affiliasi MLM Binary 2 Kaki.</p>
        </footer>
      </div>
    </div>

  </div>
</template>
