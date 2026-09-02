<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { 
  UserPlus, 
  Wand2, 
  HelpCircle, 
  KeyRound, 
  Check, 
  AlertCircle,
  User,
  Mail,
  Users
} from '@lucide/vue';

const props = defineProps({
  vouchers: Array,
  voucher_stocks: Object,
  users: Array,
  default_sponsor: String,
});

const form = useForm({
  username: '',
  name: '',
  email: '',
  sponsor_username: props.default_sponsor || 'admin',
  voucher_code: props.vouchers.length > 0 ? props.vouchers[0].code : '',
});

const fillDemoData = () => {
  const randomId = Math.floor(100 + Math.random() * 900);
  form.username = `hendra_${randomId}`;
  form.name = `Hendra Setiawan ${randomId}`;
  form.email = `hendra${randomId}@gmail.com`;
  form.sponsor_username = props.default_sponsor || 'admin';
  if (props.vouchers.length > 0) {
    form.voucher_code = props.vouchers[0].code;
  }
};

const submitForm = () => {
  form.post(route('admin.activation.store'), {
    preserveScroll: true,
  });
};
</script>

<template>
  <Head title="Aktivasi Member Baru - XSELLER" />

  <AdminLayout>
    <div class="space-y-6">
      
      <!-- Main Card Container -->
      <div class="bg-white border border-slate-200/80 rounded-3xl p-6 md:p-8 shadow-sm space-y-6">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
          <div class="space-y-1">
            <div class="flex items-center gap-2">
              <UserPlus class="w-5 h-5 text-indigo-600" />
              <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Registrasi & Aktivasi Member Baru</h2>
            </div>
            <p class="text-xs text-slate-500">Daftarkan mitra baru ke dalam jaringan Anda menggunakan VOUCHER Activation.</p>
          </div>

          <!-- Auto Fill Demo Data Button -->
          <button 
            type="button"
            @click="fillDemoData"
            class="px-3.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-700 text-xs font-bold rounded-full transition-all flex items-center gap-1.5 self-start sm:self-auto cursor-pointer shadow-sm"
          >
            <Wand2 class="w-3.5 h-3.5 text-emerald-600" />
            <span>Isi Data Demo Otomatis</span>
          </button>
        </div>

        <!-- Voucher Stock Summary Bar -->
        <div v-if="voucher_stocks" class="grid grid-cols-2 sm:grid-cols-5 gap-3 pt-1 pb-2">
          <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-2xl space-y-0.5">
            <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block">Seller (125k)</span>
            <span class="text-sm font-black text-slate-900">{{ voucher_stocks.seller || 0 }} <span class="text-[10px] font-medium text-slate-400">Voucher</span></span>
          </div>
          <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-2xl space-y-0.5">
            <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block">Star Seller (550k)</span>
            <span class="text-sm font-black text-slate-900">{{ voucher_stocks.star_seller || 0 }} <span class="text-[10px] font-medium text-slate-400">Voucher</span></span>
          </div>
          <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-2xl space-y-0.5">
            <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block">Affiliate (2.1m)</span>
            <span class="text-sm font-black text-slate-900">{{ voucher_stocks.affiliate || 0 }} <span class="text-[10px] font-medium text-slate-400">Voucher</span></span>
          </div>
          <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-2xl space-y-0.5">
            <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block">Business (4.3m)</span>
            <span class="text-sm font-black text-slate-900">{{ voucher_stocks.business || 0 }} <span class="text-[10px] font-medium text-slate-400">Voucher</span></span>
          </div>
          <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-2xl space-y-0.5 col-span-2 sm:col-span-1">
            <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block">Partner (10.5m)</span>
            <span class="text-sm font-black text-slate-900">{{ voucher_stocks.partner || 0 }} <span class="text-[10px] font-medium text-slate-400">Voucher</span></span>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitForm" class="space-y-6 pt-2">
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- 1. Username Downline -->
            <div class="space-y-1.5">
              <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-700">
                USERNAME DOWNLINE
              </label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 font-bold text-xs">@</span>
                <input 
                  v-model="form.username"
                  type="text"
                  placeholder="cth: budisantoso"
                  class="w-full bg-slate-50/70 border border-slate-200 rounded-xl pl-8 pr-4 py-2.5 text-xs font-semibold text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors"
                />
              </div>
              <p class="text-[10px] text-slate-400">Hanya huruf, angka, dan underscore. Otomatis menjadi lowercase.</p>
              <p v-if="form.errors.username" class="text-xs text-rose-500 font-medium">{{ form.errors.username }}</p>
            </div>

            <!-- 2. Nama Lengkap -->
            <div class="space-y-1.5">
              <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-700">
                NAMA LENGKAP
              </label>
              <input 
                v-model="form.name"
                type="text"
                placeholder="cth: Budi Santoso"
                class="w-full bg-slate-50/70 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors"
              />
              <p v-if="form.errors.name" class="text-xs text-rose-500 font-medium">{{ form.errors.name }}</p>
            </div>

            <!-- 3. Alamat Email -->
            <div class="space-y-1.5">
              <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-700">
                ALAMAT EMAIL
              </label>
              <input 
                v-model="form.email"
                type="email"
                placeholder="cth: budi@gmail.com"
                class="w-full bg-slate-50/70 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors"
              />
              <p v-if="form.errors.email" class="text-xs text-rose-500 font-medium">{{ form.errors.email }}</p>
            </div>

            <!-- 4. Username Sponsor -->
            <div class="space-y-1.5">
              <div class="flex items-center gap-1">
                <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-700">
                  USERNAME SPONSOR LANGSUNG
                </label>
                <HelpCircle class="w-3.5 h-3.5 text-slate-400 cursor-help" />
              </div>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 font-bold text-xs">@</span>
                <input 
                  v-model="form.sponsor_username"
                  type="text"
                  placeholder="admin"
                  class="w-full bg-slate-50/70 border border-slate-200 rounded-xl pl-8 pr-4 py-2.5 text-xs font-semibold text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors"
                />
              </div>
              <p class="text-[10px] text-slate-400">Sponsor berhak mendapatkan bonus Direct Referral 20% sesuai Paket Join.</p>
              <p v-if="form.errors.sponsor_username" class="text-xs text-rose-500 font-medium">{{ form.errors.sponsor_username }}</p>
            </div>

          </div>

          <!-- 5. Pilih VOUCHER Aktivasi (Full Width) -->
          <div class="bg-slate-50/70 border border-slate-200/80 rounded-2xl p-4 space-y-2">
            <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
              <span class="text-sm">🗝️</span>
              <span>PILIH VOUCHER ACTIVATION (PIN REGISTRASI)</span>
            </label>
            
            <div class="relative">
              <select
                v-model="form.voucher_code"
                class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-xs font-semibold text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors appearance-none cursor-pointer"
              >
                <option value="" disabled>-- Pilih VOUCHER Aktivasi dari Wallet Anda ({{ vouchers.length }} Tersedia) --</option>
                <option v-for="v in vouchers" :key="v.code" :value="v.code">
                  {{ v.label }}
                </option>
              </select>
              <span class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400 text-xs">
                ▼
              </span>
            </div>
            <p v-if="form.errors.voucher_code" class="text-xs text-rose-500 font-medium">{{ form.errors.voucher_code }}</p>
          </div>

          <!-- 6. Submit Button -->
          <div class="pt-2">
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full py-3.5 bg-[#0d131d] hover:bg-slate-800 text-white font-extrabold rounded-2xl text-xs flex items-center justify-center gap-2 shadow-lg shadow-slate-900/10 transition-all cursor-pointer disabled:opacity-50"
            >
              <UserPlus class="w-4 h-4 text-emerald-400" />
              <span>Daftarkan & Aktifkan Member Baru</span>
            </button>
          </div>

        </form>

      </div>

    </div>
  </AdminLayout>
</template>
