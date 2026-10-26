<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useToast } from "vue-toastification";
import { Icon } from '@iconify/vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Paginator from 'primevue/paginator';
import Dialog from 'primevue/dialog';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import InputSwitch from 'primevue/inputswitch';
import Dropdown from 'primevue/dropdown';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from "primevue/useconfirm";

const toast = useToast();
const confirm = useConfirm();
const props = defineProps({
    mitras: Object,
    kategoris: Array,
    share: Object,
    filters: Object,
});

const activeTab = ref('mitra'); // 'mitra' | 'kategori'
const search = ref(props.filters?.search || '');
const filterKategoriId = ref(props.filters?.kategori_id || null);

// Modal Mitra
const showMitraModal = ref(false);
const editMitraMode = ref(false);
const currentMitra = ref(null);
const logoPreview = ref(null);

const mitraForm = useForm({
    nama: '',
    kategori_mitra_id: null,
    url_website: '',
    deskripsi: '',
    order: 0,
    is_active: true,
    logo: null,
});

const handleLogoUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
        mitraForm.logo = file;
        logoPreview.value = URL.createObjectURL(file);
    }
};

const openCreateMitra = () => {
    editMitraMode.value = false;
    currentMitra.value = null;
    logoPreview.value = null;
    mitraForm.reset();
    mitraForm.kategori_mitra_id = props.kategoris?.[0]?.id || null;
    mitraForm.is_active = true;
    mitraForm.order = 0;
    showMitraModal.value = true;
};

const openEditMitra = (mitra) => {
    editMitraMode.value = true;
    currentMitra.value = mitra;
    logoPreview.value = mitra.logo ? '/' + mitra.logo.replace(/^\/+/, '') : null;
    mitraForm.nama = mitra.nama;
    mitraForm.kategori_mitra_id = mitra.kategori_mitra_id;
    mitraForm.url_website = mitra.url_website || '';
    mitraForm.deskripsi = mitra.deskripsi || '';
    mitraForm.order = mitra.order || 0;
    mitraForm.is_active = Boolean(mitra.is_active);
    mitraForm.logo = null;
    showMitraModal.value = true;
};

const submitMitra = () => {
    if (editMitraMode.value) {
        mitraForm.transform((data) => ({
            ...data,
            _method: 'PUT'
        })).post(route('master.mitra.update', currentMitra.value.id), {
            onSuccess: () => {
                showMitraModal.value = false;
                toast.success('Mitra berhasil diperbarui');
            },
            onError: () => toast.error('Gagal memperbarui mitra. Periksa form input.'),
        });
    } else {
        mitraForm.post(route('master.mitra.store'), {
            onSuccess: () => {
                showMitraModal.value = false;
                toast.success('Mitra berhasil ditambahkan');
            },
            onError: () => toast.error('Gagal menambahkan mitra. Periksa form input.'),
        });
    }
};

const deleteMitra = (mitra) => {
    confirm.require({
        message: `Hapus mitra "${mitra.nama}"?`,
        header: 'Konfirmasi Hapus',
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Ya, Hapus',
        rejectLabel: 'Batal',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(route('master.mitra.destroy', mitra.id), {
                onSuccess: () => toast.success('Mitra berhasil dihapus'),
                onError: () => toast.error('Gagal menghapus mitra'),
            });
        }
    });
};

// Modal Kategori
const showKatModal = ref(false);
const editKatMode = ref(false);
const currentKat = ref(null);

const katForm = useForm({
    nama: '',
    deskripsi: '',
    order: 0,
    is_active: true,
});

const openCreateKat = () => {
    editKatMode.value = false;
    currentKat.value = null;
    katForm.reset();
    katForm.order = (props.kategoris?.length || 0) + 1;
    katForm.is_active = true;
    showKatModal.value = true;
};

const openEditKat = (kat) => {
    editKatMode.value = true;
    currentKat.value = kat;
    katForm.nama = kat.nama;
    katForm.deskripsi = kat.deskripsi || '';
    katForm.order = kat.order || 0;
    katForm.is_active = Boolean(kat.is_active);
    showKatModal.value = true;
};

const submitKat = () => {
    if (editKatMode.value) {
        katForm.put(route('master.mitra.kategori.update', currentKat.value.id), {
            onSuccess: () => {
                showKatModal.value = false;
                toast.success('Kategori berhasil diperbarui');
            },
            onError: () => toast.error('Gagal memperbarui kategori'),
        });
    } else {
        katForm.post(route('master.mitra.kategori.store'), {
            onSuccess: () => {
                showKatModal.value = false;
                toast.success('Kategori berhasil ditambahkan');
            },
            onError: () => toast.error('Gagal menambahkan kategori'),
        });
    }
};

const deleteKat = (kat) => {
    confirm.require({
        message: `Hapus kategori "${kat.nama}"?`,
        header: 'Konfirmasi Hapus Kategori',
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Ya, Hapus',
        rejectLabel: 'Batal',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(route('master.mitra.kategori.destroy', kat.id), {
                onSuccess: () => toast.success('Kategori berhasil dihapus'),
                onError: (err) => toast.error(err?.error || 'Gagal menghapus kategori'),
            });
        }
    });
};

// Filter
let searchTimeout;
watch([search, filterKategoriId], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.visit(route('master.mitra.index'), {
            data: {
                search: search.value || undefined,
                kategori_id: filterKategoriId.value || undefined,
            },
            preserveState: true,
            preserveScroll: true,
        });
    }, 400);
});

const onPageChange = (event) => {
    router.visit(route('master.mitra.index'), {
        data: {
            page: event.page + 1,
            search: search.value || undefined,
            kategori_id: filterKategoriId.value || undefined,
        },
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Master Mitra Kerja Sama" />

        <div class="p-4 sm:p-6 lg:p-8 space-y-6">
            <!-- Header & Breadcrumb -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white font-['Outfit'] flex items-center gap-2">
                        <Icon icon="solar:handshake-bold-duotone" class="w-7 h-7 text-teal-600" />
                        Pihak yang Bekerjasama (Our Partners)
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Kelola data mitra kerja sama Kota Samarinda beserta 4 kategori kemitraan publik.
                    </p>
                </div>

                <!-- Navigation Tabs -->
                <div class="flex items-center gap-2 bg-slate-100 dark:bg-slate-800 p-1 rounded-xl">
                    <button 
                        @click="activeTab = 'mitra'"
                        :class="[
                            'px-4 py-2 rounded-lg text-xs font-bold transition-all flex items-center gap-2',
                            activeTab === 'mitra' 
                                ? 'bg-white dark:bg-slate-700 text-teal-600 dark:text-teal-400 shadow-sm' 
                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'
                        ]"
                    >
                        <Icon icon="solar:users-group-two-rounded-bold-duotone" class="w-4 h-4" />
                        Daftar Mitra ({{ mitras?.total || 0 }})
                    </button>
                    <button 
                        @click="activeTab = 'kategori'"
                        :class="[
                            'px-4 py-2 rounded-lg text-xs font-bold transition-all flex items-center gap-2',
                            activeTab === 'kategori' 
                                ? 'bg-white dark:bg-slate-700 text-teal-600 dark:text-teal-400 shadow-sm' 
                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'
                        ]"
                    >
                        <Icon icon="solar:folder-with-files-bold-duotone" class="w-4 h-4" />
                        Kategori Mitra ({{ kategoris?.length || 0 }})
                    </button>
                </div>
            </div>

            <!-- TAB 1: DAFTAR MITRA -->
            <div v-if="activeTab === 'mitra'" class="space-y-4">
                <!-- Toolbar -->
                <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-3 flex-1">
                        <div class="relative w-full md:w-64">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <Icon icon="solar:magnifer-linear" class="w-4 h-4" />
                            </span>
                            <input 
                                v-model="search"
                                type="text"
                                placeholder="Cari nama mitra..."
                                class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none"
                            />
                        </div>

                        <select 
                            v-model="filterKategoriId"
                            class="py-2 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-teal-500"
                        >
                            <option :value="null">Semua Kategori</option>
                            <option v-for="kat in kategoris" :key="kat.id" :value="kat.id">
                                {{ kat.nama }} ({{ kat.mitras_count || 0 }})
                            </option>
                        </select>
                    </div>

                    <button 
                        @click="openCreateMitra"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold rounded-xl shadow-md transition-colors"
                    >
                        <Icon icon="solar:add-circle-bold" class="w-5 h-5" />
                        Tambah Mitra
                    </button>
                </div>

                <!-- Table Card -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                            <thead class="bg-slate-50 dark:bg-slate-900/50 text-xs uppercase font-extrabold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                                <tr>
                                    <th class="py-3 px-4 w-12 text-center">No</th>
                                    <th class="py-3 px-4 w-24 text-center">Logo</th>
                                    <th class="py-3 px-4">Nama Mitra</th>
                                    <th class="py-3 px-4">Kategori</th>
                                    <th class="py-3 px-4">Website</th>
                                    <th class="py-3 px-4 w-20 text-center">Urutan</th>
                                    <th class="py-3 px-4 w-24 text-center">Status</th>
                                    <th class="py-3 px-4 w-28 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                                <tr v-for="(mitra, idx) in mitras?.data" :key="mitra.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30">
                                    <td class="py-3 px-4 text-center font-bold text-slate-400">
                                        {{ (mitras.current_page - 1) * mitras.per_page + idx + 1 }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="w-14 h-10 mx-auto rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 p-1 flex items-center justify-center">
                                            <img 
                                                v-if="mitra.logo" 
                                                :src="'/' + mitra.logo.replace(/^\/+/, '')" 
                                                :alt="mitra.nama" 
                                                class="max-h-8 max-w-full object-contain"
                                            />
                                            <span v-else class="text-xs font-black text-slate-400">No Logo</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-900 dark:text-white">{{ mitra.nama }}</div>
                                        <div v-if="mitra.deskripsi" class="text-xs text-slate-400 line-clamp-1">{{ mitra.deskripsi }}</div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 border border-teal-200/50">
                                            {{ mitra.kategori?.nama || '-' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <a 
                                            v-if="mitra.url_website" 
                                            :href="mitra.url_website" 
                                            target="_blank" 
                                            class="inline-flex items-center gap-1 text-xs text-teal-600 hover:underline max-w-[180px] truncate"
                                        >
                                            <Icon icon="solar:link-bold" class="w-3.5 h-3.5 shrink-0" />
                                            {{ mitra.url_website }}
                                        </a>
                                        <span v-else class="text-slate-400 text-xs">-</span>
                                    </td>
                                    <td class="py-3 px-4 text-center font-semibold">
                                        {{ mitra.order }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span v-if="mitra.is_active" class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400">
                                            Aktif
                                        </span>
                                        <span v-else class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500 dark:bg-slate-800">
                                            Nonaktif
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button 
                                                @click="openEditMitra(mitra)"
                                                class="p-1.5 text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/40 rounded-lg transition-colors"
                                                title="Edit"
                                            >
                                                <Icon icon="solar:pen-bold" class="w-4 h-4" />
                                            </button>
                                            <button 
                                                @click="deleteMitra(mitra)"
                                                class="p-1.5 text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40 rounded-lg transition-colors"
                                                title="Hapus"
                                            >
                                                <Icon icon="solar:trash-bin-trash-bold" class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!mitras?.data?.length">
                                    <td colspan="8" class="text-center py-10 text-slate-400">
                                        Belum ada data mitra kerja sama.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="mitras?.total > mitras?.per_page" class="p-4 border-t border-slate-200 dark:border-slate-700">
                        <Paginator 
                            :rows="mitras.per_page" 
                            :totalRecords="mitras.total" 
                            :first="(mitras.current_page - 1) * mitras.per_page"
                            @page="onPageChange"
                        />
                    </div>
                </div>
            </div>

            <!-- TAB 2: KELOLA KATEGORI MITRA -->
            <div v-else class="space-y-4">
                <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Kategori Mitra Kerja Sama</h2>
                        <p class="text-xs text-slate-500">Standar 4 Kategori: Pemerintah Daerah, Pihak Ketiga, Organisasi, Sister City. Anda dapat mengubah nama dan urutan.</p>
                    </div>
                    <button 
                        @click="openCreateKat"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold rounded-xl shadow-md transition-colors"
                    >
                        <Icon icon="solar:add-circle-bold" class="w-5 h-5" />
                        Tambah Kategori
                    </button>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-900/50 text-xs uppercase font-extrabold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="py-3 px-4 w-12 text-center">No</th>
                                <th class="py-3 px-4">Nama Kategori</th>
                                <th class="py-3 px-4">Deskripsi</th>
                                <th class="py-3 px-4 w-28 text-center">Jumlah Mitra</th>
                                <th class="py-3 px-4 w-20 text-center">Urutan</th>
                                <th class="py-3 px-4 w-28 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            <tr v-for="(kat, idx) in kategoris" :key="kat.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30">
                                <td class="py-3 px-4 text-center font-bold text-slate-400">{{ idx + 1 }}</td>
                                <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">{{ kat.nama }}</td>
                                <td class="py-3 px-4 text-xs text-slate-500">{{ kat.deskripsi || '-' }}</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-teal-50 text-teal-700 dark:bg-teal-950/50 dark:text-teal-400">
                                        {{ kat.mitras_count || 0 }} Mitra
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center font-bold">{{ kat.order }}</td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button 
                                            @click="openEditKat(kat)"
                                            class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg"
                                            title="Edit Kategori"
                                        >
                                            <Icon icon="solar:pen-bold" class="w-4 h-4" />
                                        </button>
                                        <button 
                                            @click="deleteKat(kat)"
                                            class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg"
                                            title="Hapus Kategori"
                                        >
                                            <Icon icon="solar:trash-bin-trash-bold" class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- MODAL FORM MITRA -->
        <Dialog 
            v-model:visible="showMitraModal" 
            :modal="true" 
            :header="editMitraMode ? 'Edit Mitra Kerja Sama' : 'Tambah Mitra Kerja Sama'" 
            :style="{ width: '550px' }"
            class="p-fluid"
        >
            <form @submit.prevent="submitMitra" class="space-y-4 pt-2">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                        Nama Mitra *
                    </label>
                    <InputText v-model="mitraForm.nama" placeholder="Contoh: Universitas Mulawarman" class="w-full" required />
                    <span v-if="mitraForm.errors.nama" class="text-xs text-red-500">{{ mitraForm.errors.nama }}</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                        Kategori Mitra *
                    </label>
                    <select v-model="mitraForm.kategori_mitra_id" class="w-full rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm" required>
                        <option v-for="kat in kategoris" :key="kat.id" :value="kat.id">
                            {{ kat.nama }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                        Logo Mitra
                    </label>
                    <div class="flex items-center gap-4">
                        <div v-if="logoPreview" class="w-16 h-16 rounded-xl bg-slate-100 dark:bg-slate-800 p-2 border border-slate-200 dark:border-slate-700 flex items-center justify-center overflow-hidden">
                            <img :src="logoPreview" class="max-h-full max-w-full object-contain" />
                        </div>
                        <input 
                            type="file" 
                            accept="image/*" 
                            @change="handleLogoUpload"
                            class="text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                        URL Website Resmi
                    </label>
                    <InputText v-model="mitraForm.url_website" placeholder="https://..." class="w-full" />
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                        Deskripsi Singkat
                    </label>
                    <Textarea v-model="mitraForm.deskripsi" rows="2" placeholder="Keterangan singkat mitra" class="w-full" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                            Urutan Tampil
                        </label>
                        <input type="number" v-model="mitraForm.order" class="w-full rounded-lg border-slate-300 text-sm" />
                    </div>
                    <div class="flex items-center justify-between pt-5">
                        <span class="text-xs font-bold uppercase text-slate-700 dark:text-slate-300">Status Aktif</span>
                        <InputSwitch v-model="mitraForm.is_active" />
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-200 dark:border-slate-700">
                    <Button label="Batal" icon="pi pi-times" class="p-button-text" @click="showMitraModal = false" />
                    <Button type="submit" label="Simpan" icon="pi pi-check" :loading="mitraForm.processing" class="bg-teal-600 hover:bg-teal-700 text-white" />
                </div>
            </form>
        </Dialog>

        <!-- MODAL FORM KATEGORI -->
        <Dialog 
            v-model:visible="showKatModal" 
            :modal="true" 
            :header="editKatMode ? 'Edit Kategori Mitra' : 'Tambah Kategori Mitra'" 
            :style="{ width: '450px' }"
            class="p-fluid"
        >
            <form @submit.prevent="submitKat" class="space-y-4 pt-2">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                        Nama Kategori *
                    </label>
                    <InputText v-model="katForm.nama" placeholder="Contoh: Sister City" class="w-full" required />
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                        Deskripsi Kategori
                    </label>
                    <Textarea v-model="katForm.deskripsi" rows="2" placeholder="Penjelasan kategori" class="w-full" />
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                        Urutan Tampil
                    </label>
                    <input type="number" v-model="katForm.order" class="w-full rounded-lg border-slate-300 text-sm" />
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-200 dark:border-slate-700">
                    <Button label="Batal" icon="pi pi-times" class="p-button-text" @click="showKatModal = false" />
                    <Button type="submit" label="Simpan" icon="pi pi-check" :loading="katForm.processing" class="bg-teal-600 hover:bg-teal-700 text-white" />
                </div>
            </form>
        </Dialog>

        <ConfirmDialog />
    </AuthenticatedLayout>
</template>
