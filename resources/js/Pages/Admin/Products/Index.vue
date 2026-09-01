<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
  Package, 
  Plus, 
  Edit3, 
  Trash2, 
  CheckCircle2, 
  XCircle, 
  Search, 
  Filter, 
  Image as ImageIcon,
  Tag,
  Award
} from '@lucide/vue';

const props = defineProps({
  products: Array,
  current_type: String,
});

const page = usePage();
const activeFilter = ref(props.current_type || 'all');
const searchQuery = ref('');

const modalOpen = ref(false);
const isEditing = ref(false);
const editingProductId = ref(null);

const form = useForm({
  type: 'ro',
  name: '',
  price: 125000,
  quantity: 1,
  points: 1,
  description: '',
  image_url: '',
  image_file: null,
  is_active: true,
});

const openAddModal = () => {
  isEditing.value = false;
  editingProductId.value = null;
  form.reset();
  form.type = activeFilter.value === 'po' ? 'po' : 'ro';
  modalOpen.value = true;
};

const openEditModal = (item) => {
  isEditing.value = true;
  editingProductId.value = item.id;
  form.type = item.type;
  form.name = item.name;
  form.price = item.price;
  form.quantity = item.quantity;
  form.points = item.points;
  form.description = item.description || '';
  form.image_url = item.image || '';
  form.image_file = null;
  form.is_active = item.is_active;
  modalOpen.value = true;
};

const handleImageUpload = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.image_file = file;
  }
};

const submitForm = () => {
  if (isEditing.value) {
    form.post(route('products.update', editingProductId.value), {
      preserveScroll: true,
      onSuccess: () => {
        modalOpen.value = false;
        form.reset();
      },
    });
  } else {
    form.post(route('products.store'), {
      preserveScroll: true,
      onSuccess: () => {
        modalOpen.value = false;
        form.reset();
      },
    });
  }
};

const deleteProduct = (item) => {
  if (confirm(`Apakah Anda yakin ingin menghapus produk "${item.name}"?`)) {
    router.delete(route('products.destroy', item.id), {
      preserveScroll: true,
    });
  }
};

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);
};

const filteredProducts = computed(() => {
  return props.products.filter(p => {
    const matchesType = activeFilter.value === 'all' || p.type === activeFilter.value;
    const matchesSearch = !searchQuery.value || p.name.toLowerCase().includes(searchQuery.value.toLowerCase());
    return matchesType && matchesSearch;
  });
});
</script>

<template>
  <Head title="Kelola Produk (RO & PO) - XSELLER" />

  <AdminLayout>
    <div class="space-y-6 max-w-7xl mx-auto">
      
      <!-- Header Banner -->
      <div class="bg-gradient-to-r from-[#0b1f3a] via-[#103f80] to-[#1653a1] rounded-3xl p-6 md:p-8 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-2">
          <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 backdrop-blur border border-white/20 rounded-full text-xs text-[#a9fff7] font-extrabold uppercase tracking-wider">
            <Package class="w-3.5 h-3.5" />
            <span>Manajemen Katalog Produk</span>
          </div>
          <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white">Kelola Produk RO & PO</h1>
          <p class="text-xs md:text-sm text-slate-200 font-medium max-w-xl">
            Tambah, edit, dan atur ketersediaan produk untuk paket Repeat Order (RO) dan Purchase Order (PO).
          </p>
        </div>

        <button 
          @click="openAddModal"
          class="px-5 py-3 bg-[#04bdb2] hover:bg-[#009c94] text-white text-xs font-black rounded-2xl shadow-lg transition-all flex items-center gap-2 shrink-0 cursor-pointer"
        >
          <Plus class="w-4 h-4" />
          <span>Tambah Produk Baru</span>
        </button>
      </div>

      <!-- Flash Notification -->
      <div v-if="$page.props.flash?.success" class="p-4 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-2xl flex items-center gap-2 text-xs font-bold shadow-xs">
        <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
        <span>{{ $page.props.flash?.success }}</span>
      </div>

      <!-- Filter Controls & Search -->
      <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        
        <!-- Filter Tabs -->
        <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-2xl w-full sm:w-auto">
          <button 
            @click="activeFilter = 'all'"
            :class="[
              activeFilter === 'all' ? 'bg-white text-slate-900 font-black shadow-xs' : 'text-slate-500 font-bold hover:text-slate-800',
              'px-4 py-2 text-xs rounded-xl transition-all cursor-pointer flex-1 sm:flex-none'
            ]"
          >
            Semua ({{ products.length }})
          </button>
          <button 
            @click="activeFilter = 'ro'"
            :class="[
              activeFilter === 'ro' ? 'bg-white text-slate-900 font-black shadow-xs' : 'text-slate-500 font-bold hover:text-slate-800',
              'px-4 py-2 text-xs rounded-xl transition-all cursor-pointer flex-1 sm:flex-none'
            ]"
          >
            Produk RO
          </button>
          <button 
            @click="activeFilter = 'po'"
            :class="[
              activeFilter === 'po' ? 'bg-white text-slate-900 font-black shadow-xs' : 'text-slate-500 font-bold hover:text-slate-800',
              'px-4 py-2 text-xs rounded-xl transition-all cursor-pointer flex-1 sm:flex-none'
            ]"
          >
            Produk PO
          </button>
        </div>

        <!-- Search Bar -->
        <div class="relative w-full sm:w-72">
          <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
          <input 
            type="text" 
            v-model="searchQuery" 
            placeholder="Cari nama produk..." 
            class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-[#04bdb2]"
          />
        </div>
      </div>

      <!-- Products Grid Display -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
        <div 
          v-for="item in filteredProducts" 
          :key="item.id"
          class="bg-white border border-slate-200/80 rounded-3xl p-5 shadow-sm flex flex-col justify-between space-y-4 hover:shadow-md transition-all relative group"
        >
          <div class="space-y-3">
            <!-- Image / Placeholder -->
            <div class="w-full aspect-square rounded-2xl bg-slate-50 border border-slate-100 overflow-hidden flex items-center justify-center p-3 relative">
              <img 
                v-if="item.image" 
                :src="item.image" 
                :alt="item.name" 
                class="w-full h-full object-contain"
              />
              <div v-else class="w-full h-full bg-slate-100 rounded-xl flex flex-col items-center justify-center text-slate-400 gap-1">
                <ImageIcon class="w-8 h-8 opacity-50" />
                <span class="text-[10px] font-extrabold uppercase">No Image</span>
              </div>

              <!-- Type Badge -->
              <span 
                :class="[
                  item.type === 'ro' ? 'bg-[#5c3a21] text-white' : 'bg-[#1653a1] text-white',
                  'absolute top-3 left-3 px-2.5 py-0.5 text-[9px] font-black uppercase rounded-full tracking-wider shadow-xs'
                ]"
              >
                PRODUK {{ item.type.toUpperCase() }}
              </span>

              <!-- Active Status Badge -->
              <span 
                :class="[
                  item.is_active ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-rose-100 text-rose-800 border-rose-300',
                  'absolute top-3 right-3 px-2 py-0.5 text-[9px] font-black uppercase rounded-full border shadow-xs'
                ]"
              >
                {{ item.is_active ? 'Aktif' : 'Non-Aktif' }}
              </span>
            </div>

            <!-- Title & Price -->
            <div class="space-y-1">
              <h3 class="text-base font-black text-slate-900 tracking-tight leading-tight uppercase">{{ item.name }}</h3>
              <p class="text-sm font-black text-[#1653a1]">{{ formatRupiah(item.price) }}</p>
              <div class="flex items-center justify-between text-xs text-slate-500 font-bold pt-1">
                <span>Isi / Jumlah: {{ item.quantity }}</span>
                <span class="text-amber-600 flex items-center gap-1">
                  <Award class="w-3.5 h-3.5" />
                  {{ item.points }} Poin
                </span>
              </div>
              <p v-if="item.description" class="text-[11px] text-slate-500 line-clamp-2 pt-1 font-medium">
                {{ item.description }}
              </p>
            </div>
          </div>

          <!-- Actions Footer -->
          <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
            <button 
              @click="openEditModal(item)" 
              class="p-2 text-slate-600 hover:text-[#1653a1] hover:bg-slate-100 rounded-xl transition-colors cursor-pointer"
              title="Edit Produk"
            >
              <Edit3 class="w-4 h-4" />
            </button>
            <button 
              @click="deleteProduct(item)" 
              class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors cursor-pointer"
              title="Hapus Produk"
            >
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </div>

        <div v-if="filteredProducts.length === 0" class="col-span-full py-12 text-center bg-white border border-slate-200/80 rounded-3xl">
          <p class="text-xs text-slate-400 font-semibold italic">Belum ada produk yang cocok dengan pencarian.</p>
        </div>
      </div>

    </div>

    <!-- Modal Form (Tambah / Edit Produk) -->
    <div v-if="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white rounded-3xl max-w-lg w-full p-6 md:p-8 space-y-6 shadow-2xl border border-slate-100 animate-fade-in max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <h3 class="text-base font-black text-slate-900">
            {{ isEditing ? 'Edit Data Produk' : 'Tambah Produk Baru' }}
          </h3>
          <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-700">
            <XCircle class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitForm" class="space-y-4">
          <!-- Type Selection -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                KATEGORI PAKET
              </label>
              <select 
                v-model="form.type" 
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900"
                required
              >
                <option value="ro">PRODUK RO (Rp 125.000)</option>
                <option value="po">PRODUK PO (Rp 550.000+)</option>
              </select>
            </div>

            <!-- Status Aktif -->
            <div>
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                STATUS KETERSEDIAAN
              </label>
              <select 
                v-model="form.is_active" 
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900"
              >
                <option :value="true">Aktif (Tampil)</option>
                <option :value="false">Non-Aktif (Sembunyikan)</option>
              </select>
            </div>
          </div>

          <!-- Product Name -->
          <div>
            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
              NAMA PRODUK
            </label>
            <input 
              type="text" 
              v-model="form.name" 
              placeholder="cth: HAZAPRO / HERBAQUEENA" 
              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900"
              required
            />
          </div>

          <!-- Price, Quantity, Points Grid -->
          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                HARGA (RP)
              </label>
              <input 
                type="number" 
                v-model.number="form.price" 
                class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900"
                required
              />
            </div>
            <div>
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                JUMLAH / ISI
              </label>
              <input 
                type="number" 
                v-model.number="form.quantity" 
                min="1" 
                class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900"
                required
              />
            </div>
            <div>
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                PEROLEHAN POIN
              </label>
              <input 
                type="number" 
                v-model.number="form.points" 
                min="0" 
                class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900"
                required
              />
            </div>
          </div>

          <!-- Image Upload / URL -->
          <div>
            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
              UPLOAD GAMBAR PRODUK
            </label>
            <input 
              type="file" 
              @change="handleImageUpload" 
              accept="image/*"
              class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#1653a1]/10 file:text-[#1653a1] cursor-pointer"
            />
          </div>

          <!-- Description -->
          <div>
            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
              DESKRIPSI PRODUK (OPSIONAL)
            </label>
            <textarea 
              v-model="form.description" 
              rows="2" 
              placeholder="Deskripsi singkat produk..." 
              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900"
            ></textarea>
          </div>

          <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
            <button 
              type="button" 
              @click="modalOpen = false" 
              class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl cursor-pointer"
            >
              Batal
            </button>
            <button 
              type="submit" 
              :disabled="form.processing"
              class="px-5 py-2.5 bg-[#1653a1] hover:bg-[#103f80] text-white font-black text-xs rounded-xl shadow-md cursor-pointer"
            >
              {{ isEditing ? 'Simpan Perubahan' : 'Tambah Produk' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
