<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
  Settings, 
  Building2, 
  UserCheck, 
  CreditCard, 
  Plus, 
  Trash2, 
  Check, 
  CheckCircle2, 
  AlertCircle,
  Image as ImageIcon,
  Upload
} from '@lucide/vue';

const props = defineProps({
  admin_user: Object,
  company_profile: Object,
  status: String,
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

// Form for Corporate & User Profile
const form = useForm({
  company_name: props.company_profile?.name || 'PT.Xseller Punya Kita',
  company_owner: props.company_profile?.owner || 'PT.Xseller Punya Kita',
  company_copyright: props.company_profile?.copyright || 'PT.Xseller Punya Kita Corp. Hak Cipta Dilindungi Undang-Undang.',
  name: props.admin_user?.name || 'President Director (Admin)',
  username: props.admin_user?.username || 'admin',
  email: props.admin_user?.email || 'admin@xseller.id',
  phone: props.admin_user?.phone || '081234567890',
  password: '',
  site_logo: null,
});

// Bank Accounts list state
const banksList = ref(props.company_profile?.banks || []);

// Bank modal/add form
const showAddBank = ref(false);
const newBank = useForm({
  bank_name: 'Bank BRI',
  account_number: '',
  account_name: '',
});

const handleLogoChange = (e) => {
  if (e.target.files.length > 0) {
    form.site_logo = e.target.files[0];
  }
};

const submitProfile = () => {
  form.post(route('profile.update'), {
    preserveScroll: true,
    forceFormData: true,
  });
};

const addBank = () => {
  if (!newBank.account_number || !newBank.account_name) return;
  banksList.value.push({
    bank_name: newBank.bank_name,
    account_number: newBank.account_number,
    account_name: newBank.account_name,
  });
  newBank.reset();
  showAddBank.value = false;
  saveBanks();
};

const removeBank = (index) => {
  banksList.value.splice(index, 1);
  saveBanks();
};

const bankForm = useForm({ banks: [] });
const saveBanks = () => {
  bankForm.banks = banksList.value;
  bankForm.post(route('profile.update-banks'), {
    preserveScroll: true,
  });
};
</script>

<template>
  <Head title="Pengaturan Profil Instansi & Administrator - XSELLER" />

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

      <!-- MAIN CONTAINER CARD (White Card matching Mockup Image 1) -->
      <div class="bg-white border border-slate-100 rounded-3xl p-6 md:p-8 shadow-sm space-y-6">
        
        <!-- Header -->
        <div class="flex items-start gap-3 border-b border-slate-100 pb-5">
          <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-2xl shrink-0 mt-0.5">
            <Settings class="w-6 h-6" />
          </div>
          <div>
            <h2 class="text-lg md:text-xl font-black text-slate-900 tracking-tight">
              Pengaturan Profil Instansi & Administrator Utama
            </h2>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
              Kelola Identitas korporat perusahaan, nomor rekening bank admin utama untuk transfer penarikan, serta kredensial akun login admin.
            </p>
          </div>
        </div>

        <form @submit.prevent="submitProfile" class="space-y-6">
          
          <!-- SECTION 1: LOGOS & AVATARS (Matching Mockup Gray Card) -->
          <div class="bg-slate-50/80 border border-slate-100 rounded-2xl p-5 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Company Logo -->
            <div class="flex items-center gap-4">
              <div class="w-16 h-16 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 text-xs font-bold shrink-0 shadow-xs">
                <img v-if="company_profile?.logo_url" :src="company_profile.logo_url" class="max-h-12 max-w-12 object-contain" />
                <span v-else>LOGO</span>
              </div>
              <div class="space-y-1.5">
                <h4 class="text-xs font-extrabold text-slate-900">Logo Perusahaan</h4>
                <p class="text-[10px] text-slate-400 font-medium">Tampil di header utama navigasi.</p>
                <label class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold rounded-xl shadow-xs cursor-pointer transition-colors">
                  <Upload class="w-3.5 h-3.5 mr-1.5" />
                  <span>Pilih File Logo</span>
                  <input type="file" @change="handleLogoChange" accept="image/*" class="hidden" />
                </label>
              </div>
            </div>

            <!-- Admin Profile Picture -->
            <div class="flex items-center gap-4">
              <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-700 border-2 border-emerald-300 flex items-center justify-center text-xl font-extrabold shrink-0 shadow-xs">
                A
              </div>
              <div class="space-y-1.5">
                <h4 class="text-xs font-extrabold text-slate-900">Foto Profil Admin</h4>
                <p class="text-[10px] text-slate-400 font-medium">Foto avatar administrator utama.</p>
                <button type="button" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold rounded-xl shadow-xs cursor-pointer transition-colors inline-flex items-center">
                  <span>Pilih Avatar</span>
                </button>
              </div>
            </div>
          </div>

          <!-- SECTION 2: COMPANY IDENTITIES (Inputs Grid) -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                NAMA PERUSAHAAN / PLATFORM
              </label>
              <input 
                v-model="form.company_name"
                type="text"
                required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500"
              />
            </div>

            <div>
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                NAMA PEMILIK / OWNER UTAMA
              </label>
              <input 
                v-model="form.company_owner"
                type="text"
                required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500"
              />
            </div>

            <div>
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                TEKS KETERANGAN HAK CIPTA / FOOTER
              </label>
              <input 
                v-model="form.company_copyright"
                type="text"
                required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500"
              />
            </div>
          </div>

          <!-- SECTION 3: COMPANY BANK ACCOUNTS (Matching Green Box Mockup) -->
          <div class="bg-emerald-50/40 border border-emerald-200/60 rounded-2xl p-5 space-y-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <CreditCard class="w-4 h-4 text-emerald-600" />
                <h3 class="text-xs font-black text-emerald-900 uppercase tracking-tight">
                  DAFTAR REKENING BANK PERUSAHAAN (ADMIN)
                </h3>
              </div>

              <button 
                type="button"
                @click="showAddBank = !showAddBank"
                class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold rounded-xl shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer"
              >
                <Plus class="w-3.5 h-3.5" />
                <span>Tambah Rekening</span>
              </button>
            </div>

            <!-- Add Bank Form Dropdown -->
            <div v-if="showAddBank" class="p-4 bg-white border border-emerald-200 rounded-xl space-y-3">
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <input v-model="newBank.bank_name" placeholder="Nama Bank (e.g. Bank Mandiri)" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs" />
                <input v-model="newBank.account_number" placeholder="Nomor Rekening" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs" />
                <input v-model="newBank.account_name" placeholder="Nama Pemilik" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs" />
              </div>
              <button @click="addBank" type="button" class="px-4 py-1.5 bg-emerald-600 text-white text-xs font-bold rounded-lg cursor-pointer">Simpan Rekening</button>
            </div>

            <!-- Bank Accounts List -->
            <div v-if="banksList.length === 0" class="text-center py-4 text-xs text-slate-400 italic">
              Tidak ada rekening bank yang tersimpan. Gunakan tombol + Tambah Rekening.
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div v-for="(b, idx) in banksList" :key="idx" class="p-3 bg-white border border-emerald-200/80 rounded-xl flex items-center justify-between">
                <div>
                  <h4 class="text-xs font-extrabold text-slate-900">{{ b.bank_name }}</h4>
                  <p class="text-[11px] text-slate-600 font-mono">{{ b.account_number }} a.n {{ b.account_name }}</p>
                </div>
                <button type="button" @click="removeBank(idx)" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer">
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>

          <!-- SECTION 4: ADMIN LOGIN CREDENTIALS (Matching Mockup Box) -->
          <div class="bg-slate-50/50 border border-slate-100 rounded-2xl p-5 space-y-4">
            <div class="flex items-center gap-2 border-b border-slate-200/60 pb-3">
              <UserCheck class="w-4 h-4 text-slate-600" />
              <h3 class="text-xs font-black text-slate-900 uppercase tracking-tight">
                DETAIL AKUN KREDENSIAL ADMINISTRATOR UTAMA (UNTUK LOGIN)
              </h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                  USERNAME ADMIN
                </label>
                <input 
                  v-model="form.username"
                  type="text"
                  required
                  class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500"
                />
              </div>

              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                  NAMA LENGKAP ADMIN
                </label>
                <input 
                  v-model="form.name"
                  type="text"
                  required
                  class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500"
                />
              </div>

              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                  ALAMAT EMAIL
                </label>
                <input 
                  v-model="form.email"
                  type="email"
                  required
                  class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500"
                />
              </div>

              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                  NO HP / WHATSAPP
                </label>
                <input 
                  v-model="form.phone"
                  type="text"
                  class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500"
                />
              </div>

              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                  PASSWORD BARU ADMIN
                </label>
                <input 
                  v-model="form.password"
                  type="password"
                  placeholder="Password Baru"
                  class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500"
                />
              </div>
            </div>
          </div>

          <!-- Bottom Submit Button -->
          <div class="flex justify-end pt-2">
            <button 
              type="submit"
              :disabled="form.processing"
              class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-black rounded-2xl shadow-md transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50"
            >
              <Check class="w-4 h-4 stroke-[3]" />
              <span>Simpan Profil & Identitas Perusahaan</span>
            </button>
          </div>

        </form>

      </div>

    </div>
  </AdminLayout>
</template>
