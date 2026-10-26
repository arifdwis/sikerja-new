<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useToast } from "vue-toastification";
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import FileUpload from 'primevue/fileupload';
import Paginator from 'primevue/paginator';
import Dialog from 'primevue/dialog';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import InputSwitch from 'primevue/inputswitch';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from "primevue/useconfirm";

const toast = useToast();
const confirm = useConfirm();
const props = defineProps(['datas', 'mitras', 'kategoris', 'share', 'filters']);

const activeTab = ref('mitra'); // 'mitra' | 'kategori'

// Pagination state
const currentPage = ref(props.datas?.current_page || 1);
const perPage = ref(props.datas?.per_page || 10);
const rowsPerPageOptions = [10, 25, 50, 100];

// Search & Filter
const search = ref(props.filters?.search || '');
const filterKategoriId = ref(props.filters?.kategori_id || null);

// Modal Mitra State
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

// Modal Kategori State
const showKatModal = ref(false);
const editKatMode = ref(false);
const currentKat = ref(null);

const katForm = useForm({
    nama: '',
    deskripsi: '',
    order: 0,
    is_active: true,
});

// Apply filters
const applyFilters = () => {
    router.visit(route(props.share.prefix + '.index'), {
        data: {
            page: currentPage.value,
            per_page: perPage.value,
            search: search.value || undefined,
            kategori_id: filterKategoriId.value || undefined,
        },
        preserveState: true,
        preserveScroll: true,
        only: ['datas', 'mitras']
    });
};

// Debounced search
let searchTimeout;
watch([search, filterKategoriId], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        currentPage.value = 1;
        applyFilters();
    }, 500);
});

// Page change handler
const onPageChange = (e) => {
    perPage.value = e.rows;
    currentPage.value = e.page + 1;
    applyFilters();
};

// Open Create Mitra Modal
const openCreateMitraModal = () => {
    editMitraMode.value = false;
    currentMitra.value = null;
    mitraForm.reset();
    mitraForm.clearErrors();
    mitraForm.kategori_mitra_id = props.kategoris?.[0]?.id || null;
    mitraForm.is_active = true;
    mitraForm.order = 0;
    logoPreview.value = null;
    showMitraModal.value = true;
};

// Open Edit Mitra Modal
const openEditMitraModal = (item) => {
    editMitraMode.value = true;
    currentMitra.value = item;
    mitraForm.clearErrors();
    mitraForm.nama = item.nama;
    mitraForm.kategori_mitra_id = item.kategori_mitra_id;
    mitraForm.url_website = item.url_website || '';
    mitraForm.deskripsi = item.deskripsi || '';
    mitraForm.order = item.order || 0;
    mitraForm.is_active = !!item.is_active;
    mitraForm.logo = null;
    logoPreview.value = item.logo ? '/' + item.logo.replace(/^\/+/, '') : null;
    showMitraModal.value = true;
};

// Handle FileUpload PrimeVue
const onLogoSelect = (e) => {
    const file = e.files[0];
    if (file) {
        mitraForm.logo = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            logoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const submitMitraForm = () => {
    if (editMitraMode.value) {
        mitraForm.transform((data) => ({
            ...data,
            _method: 'PUT'
        })).post(route(props.share.prefix + '.update', currentMitra.value.id), {
            onSuccess: () => {
                showMitraModal.value = false;
                toast.success('Mitra kerja sama berhasil diperbarui.');
            },
            onError: (errors) => {
                Object.values(errors).forEach(msg => toast.error(msg));
            }
        });
    } else {
        mitraForm.post(route(props.share.prefix + '.store'), {
            onSuccess: () => {
                showMitraModal.value = false;
                toast.success('Mitra kerja sama berhasil ditambahkan.');
            },
            onError: (errors) => {
                Object.values(errors).forEach(msg => toast.error(msg));
            }
        });
    }
};

const confirmDeleteMitra = (item) => {
    confirm.require({
        message: `Apakah Anda yakin ingin menghapus mitra "${item.nama}"?`,
        header: 'Konfirmasi Hapus',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        acceptLabel: 'Ya, Hapus',
        rejectLabel: 'Batal',
        accept: () => {
            router.delete(route(props.share.prefix + '.destroy', item.id), {
                onSuccess: () => toast.success('Mitra kerja sama berhasil dihapus.'),
                onError: () => toast.error('Gagal menghapus mitra.')
            });
        }
    });
};

// Kategori Actions
const openCreateKatModal = () => {
    editKatMode.value = false;
    currentKat.value = null;
    katForm.reset();
    katForm.clearErrors();
    katForm.order = (props.kategoris?.length || 0) + 1;
    katForm.is_active = true;
    showKatModal.value = true;
};

const openEditKatModal = (item) => {
    editKatMode.value = true;
    currentKat.value = item;
    katForm.clearErrors();
    katForm.nama = item.nama;
    katForm.deskripsi = item.deskripsi || '';
    katForm.order = item.order || 0;
    katForm.is_active = !!item.is_active;
    showKatModal.value = true;
};

const submitKatForm = () => {
    if (editKatMode.value) {
        katForm.put(route(props.share.prefix + '.kategori.update', currentKat.value.id), {
            onSuccess: () => {
                showKatModal.value = false;
                toast.success('Kategori mitra berhasil diperbarui.');
            },
            onError: (errors) => {
                Object.values(errors).forEach(msg => toast.error(msg));
            }
        });
    } else {
        katForm.post(route(props.share.prefix + '.kategori.store'), {
            onSuccess: () => {
                showKatModal.value = false;
                toast.success('Kategori mitra berhasil ditambahkan.');
            },
            onError: (errors) => {
                Object.values(errors).forEach(msg => toast.error(msg));
            }
        });
    }
};

const confirmDeleteKat = (item) => {
    confirm.require({
        message: `Apakah Anda yakin ingin menghapus kategori "${item.nama}"?`,
        header: 'Konfirmasi Hapus Kategori',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        acceptLabel: 'Ya, Hapus',
        rejectLabel: 'Batal',
        accept: () => {
            router.delete(route(props.share.prefix + '.kategori.destroy', item.id), {
                onSuccess: () => toast.success('Kategori berhasil dihapus.'),
                onError: (err) => toast.error(err?.error || 'Gagal menghapus kategori.')
            });
        }
    });
};
</script>

<template>
    <Head :title="share.title" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">{{ share.title }}</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-full sm:px-6 lg:px-8 space-y-4">
                <Breadcrumb :crumbs="[
                    { label: 'Master Data', route: 'master.slider.index' },
                    { label: share.title, route: null }
                ]" />

                <div class="max-w-full w-full bg-white rounded-lg shadow-sm dark:bg-gray-800 p-4 md:p-6 dark:text-gray-100">
                    <!-- Tabs Selector Inside Card -->
                    <div class="flex gap-4 border-b border-gray-200 dark:border-gray-700 mb-5">
                        <button 
                            @click="activeTab = 'mitra'"
                            :class="[
                                'pb-3 text-sm font-bold border-b-2 transition-colors flex items-center gap-2',
                                activeTab === 'mitra'
                                    ? 'border-teal-600 text-teal-600 dark:border-teal-400 dark:text-teal-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'
                            ]"
                        >
                            <span>Daftar Mitra Kerja Sama</span>
                            <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                {{ datas?.total || mitras?.total || 0 }}
                            </span>
                        </button>
                        <button 
                            @click="activeTab = 'kategori'"
                            :class="[
                                'pb-3 text-sm font-bold border-b-2 transition-colors flex items-center gap-2',
                                activeTab === 'kategori'
                                    ? 'border-teal-600 text-teal-600 dark:border-teal-400 dark:text-teal-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'
                            ]"
                        >
                            <span>Kelola 4 Kategori Kemitraan</span>
                            <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                {{ kategoris?.length || 0 }}
                            </span>
                        </button>
                    </div>

                    <!-- TAB 1: MITRA TABLE -->
                    <div v-if="activeTab === 'mitra'">
                        <!-- Search and Filters -->
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4 md:gap-0">
                            <div class="flex flex-col sm:flex-row gap-2 md:gap-4">
                                <InputText 
                                    size="small" 
                                    v-model="search" 
                                    placeholder="Cari nama mitra..." 
                                    class="border block rounded w-full sm:w-64 dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                                />
                                <select 
                                    v-model="filterKategoriId"
                                    class="text-xs border rounded p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                >
                                    <option :value="null">Semua Kategori</option>
                                    <option v-for="kat in kategoris" :key="kat.id" :value="kat.id">
                                        {{ kat.nama }} ({{ kat.mitras_count || 0 }})
                                    </option>
                                </select>
                            </div>
                            <div class="flex flex-col sm:flex-row gap-2 md:gap-4 items-start sm:items-center">
                                <Paginator 
                                    size="small" 
                                    :totalRecords="datas?.total || 0" 
                                    :rows="perPage" 
                                    :first="(currentPage - 1) * perPage"
                                    :rowsPerPageOptions="rowsPerPageOptions" 
                                    @page="onPageChange" 
                                    template="RowsPerPageDropdown PrevPageLink CurrentPageReport NextPageLink"
                                    currentPageReportTemplate="{first} - {last} dari {totalRecords}" 
                                />
                                
                                <Button label="Tambah Mitra" icon="pi pi-plus" size="small" @click="openCreateMitraModal" />
                            </div>
                        </div>

                        <!-- Data Table PrimeVue with Precise Column Widths -->
                        <div class="overflow-x-auto">
                            <DataTable 
                                :value="datas?.data || []" 
                                dataKey="id"
                                showGridlines 
                                stripedRows 
                                class="text-sm w-full"
                            >
                                <template #empty>
                                    <div class="text-center p-4 text-gray-500">
                                        Tidak ada data mitra kerja sama.
                                    </div>
                                </template>

                                <Column header="No" class="w-12 text-center" style="width: 50px;">
                                    <template #body="slotProps">
                                        {{ (currentPage - 1) * perPage + slotProps.index + 1 }}
                                    </template>
                                </Column>

                                <Column header="Logo" class="w-24 text-center" style="width: 90px;">
                                    <template #body="{ data }">
                                        <img 
                                            v-if="data.logo" 
                                            :src="'/' + data.logo.replace(/^\/+/, '')" 
                                            :alt="data.nama" 
                                            class="w-14 h-9 object-contain mx-auto rounded border bg-white p-1" 
                                        />
                                        <span v-else class="text-gray-400 text-xs">No Logo</span>
                                    </template>
                                </Column>

                                <Column field="nama" header="Nama Mitra" sortable>
                                    <template #body="{ data }">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ data.nama }}</div>
                                        <div v-if="data.deskripsi" class="text-gray-500 text-xs line-clamp-1 max-w-md">{{ data.deskripsi }}</div>
                                    </template>
                                </Column>

                                <Column field="kategori.nama" header="Kategori" sortable class="w-36 text-center" style="width: 140px;">
                                    <template #body="{ data }">
                                        <span class="px-2 py-1 rounded bg-teal-50 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300 text-xs font-semibold whitespace-nowrap">
                                            {{ data.kategori?.nama || '-' }}
                                        </span>
                                    </template>
                                </Column>

                                <Column field="url_website" header="Website" class="w-44" style="width: 180px;">
                                    <template #body="{ data }">
                                        <a 
                                            v-if="data.url_website" 
                                            :href="data.url_website" 
                                            target="_blank" 
                                            class="text-xs text-blue-600 dark:text-blue-400 hover:underline truncate block max-w-[170px]"
                                            :title="data.url_website"
                                        >
                                            {{ data.url_website }}
                                        </a>
                                        <span v-else class="text-gray-400 text-xs">-</span>
                                    </template>
                                </Column>

                                <Column field="order" header="Urutan" sortable class="w-16 text-center" style="width: 70px;">
                                    <template #body="{ data }">
                                        <span class="font-semibold">{{ data.order }}</span>
                                    </template>
                                </Column>

                                <Column field="is_active" header="Status" sortable class="w-20 text-center" style="width: 85px;">
                                    <template #body="{ data }">
                                        <Tag :value="data.is_active ? 'Aktif' : 'Tidak Aktif'" :severity="data.is_active ? 'success' : 'danger'" />
                                    </template>
                                </Column>

                                <Column header="Aksi" class="w-24 text-center" style="width: 95px;">
                                    <template #body="{ data }">
                                        <div class="flex justify-center gap-1.5">
                                            <Button icon="pi pi-pencil" severity="info" size="small" @click="openEditMitraModal(data)" v-tooltip="'Edit'" />
                                            <Button icon="pi pi-trash" severity="danger" size="small" @click="confirmDeleteMitra(data)" v-tooltip="'Hapus'" />
                                        </div>
                                    </template>
                                </Column>
                            </DataTable>
                        </div>
                    </div>

                    <!-- TAB 2: KATEGORI MITRA TABLE -->
                    <div v-else>
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h3 class="font-bold text-gray-900 dark:text-white">Kelola 4 Kategori Kemitraan</h3>
                                <p class="text-xs text-gray-500">Ubah nama kategori publik (Pemerintah Daerah, Pihak Ketiga, Organisasi, Sister City) dan atur urutannya.</p>
                            </div>
                            <Button label="Tambah Kategori" icon="pi pi-plus" size="small" @click="openCreateKatModal" />
                        </div>

                        <DataTable 
                            :value="kategoris || []" 
                            dataKey="id"
                            showGridlines 
                            stripedRows 
                            class="text-sm w-full"
                        >
                            <Column header="No" class="w-12 text-center" style="width: 50px;">
                                <template #body="slotProps">
                                    {{ slotProps.index + 1 }}
                                </template>
                            </Column>

                            <Column field="nama" header="Nama Kategori" sortable class="w-64">
                                <template #body="{ data }">
                                    <span class="font-bold text-gray-900 dark:text-white">{{ data.nama }}</span>
                                </template>
                            </Column>

                            <Column field="deskripsi" header="Deskripsi">
                                <template #body="{ data }">
                                    <span class="text-gray-500 text-xs">{{ data.deskripsi || '-' }}</span>
                                </template>
                            </Column>

                            <Column field="mitras_count" header="Jumlah Mitra" class="w-32 text-center" style="width: 120px;">
                                <template #body="{ data }">
                                    <Tag :value="(data.mitras_count || 0) + ' Mitra'" severity="info" />
                                </template>
                            </Column>

                            <Column field="order" header="Urutan" sortable class="w-20 text-center" style="width: 80px;">
                                <template #body="{ data }">
                                    <span class="font-semibold">{{ data.order }}</span>
                                </template>
                            </Column>

                            <Column header="Aksi" class="w-24 text-center" style="width: 95px;">
                                <template #body="{ data }">
                                    <div class="flex justify-center gap-1.5">
                                        <Button icon="pi pi-pencil" severity="info" size="small" @click="openEditKatModal(data)" v-tooltip="'Edit'" />
                                        <Button icon="pi pi-trash" severity="danger" size="small" @click="confirmDeleteKat(data)" v-tooltip="'Hapus'" />
                                    </div>
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Create/Edit Mitra (Clean PrimeVue with FileUpload) -->
        <Dialog v-model:visible="showMitraModal" :header="editMitraMode ? 'Edit Mitra Kerja Sama' : 'Tambah Mitra Kerja Sama'" modal :style="{ width: '500px' }">
            <form @submit.prevent="submitMitraForm" class="space-y-4">
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nama Mitra *</label>
                    <InputText v-model="mitraForm.nama" placeholder="Masukkan nama mitra" class="w-full" autofocus required />
                    <small v-if="mitraForm.errors.nama" class="text-red-500">{{ mitraForm.errors.nama }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Kategori Mitra *</label>
                    <select v-model="mitraForm.kategori_mitra_id" class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm py-2" required>
                        <option v-for="kat in kategoris" :key="kat.id" :value="kat.id">
                            {{ kat.nama }}
                        </option>
                    </select>
                    <small v-if="mitraForm.errors.kategori_mitra_id" class="text-red-500">{{ mitraForm.errors.kategori_mitra_id }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">URL Website Resmi</label>
                    <InputText v-model="mitraForm.url_website" placeholder="Contoh: https://samarindakota.go.id" class="w-full" />
                    <small v-if="mitraForm.errors.url_website" class="text-red-500">{{ mitraForm.errors.url_website }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi (Opsional)</label>
                    <Textarea v-model="mitraForm.deskripsi" rows="2" placeholder="Keterangan singkat mitra" class="w-full" />
                    <small v-if="mitraForm.errors.deskripsi" class="text-red-500">{{ mitraForm.errors.deskripsi }}</small>
                </div>

                <!-- Logo Mitra FileUpload PrimeVue -->
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Logo Mitra</label>
                    <div v-if="logoPreview" class="mb-2">
                        <img :src="logoPreview" class="w-full h-28 object-contain rounded border p-2 bg-white" />
                    </div>
                    <FileUpload 
                        mode="basic" 
                        name="logo" 
                        accept="image/*" 
                        :maxFileSize="5000000" 
                        @select="onLogoSelect" 
                        :auto="true" 
                        chooseLabel="Pilih Logo Mitra" 
                        class="w-full" 
                    />
                    <small v-if="mitraForm.errors.logo" class="text-red-500">{{ mitraForm.errors.logo }}</small>
                </div>

                <div class="grid grid-cols-2 gap-4 items-center">
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Urutan Tampil</label>
                        <input type="number" v-model="mitraForm.order" class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm py-2" />
                    </div>
                    <div class="flex items-center gap-2 pt-6">
                        <InputSwitch v-model="mitraForm.is_active" inputId="is_active_mitra" />
                        <label for="is_active_mitra" class="text-sm font-medium text-gray-700 dark:text-gray-300">Aktif</label>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <Button type="button" label="Batal" severity="secondary" @click="showMitraModal = false" />
                    <Button type="submit" :label="editMitraMode ? 'Simpan Perubahan' : 'Simpan'" :loading="mitraForm.processing" />
                </div>
            </form>
        </Dialog>

        <!-- Modal Create/Edit Kategori -->
        <Dialog v-model:visible="showKatModal" :header="editKatMode ? 'Edit Kategori Mitra' : 'Tambah Kategori Mitra'" modal :style="{ width: '450px' }">
            <form @submit.prevent="submitKatForm" class="space-y-4">
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nama Kategori *</label>
                    <InputText v-model="katForm.nama" placeholder="Contoh: Sister City" class="w-full" autofocus required />
                    <small v-if="katForm.errors.nama" class="text-red-500">{{ katForm.errors.nama }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi (Opsional)</label>
                    <Textarea v-model="katForm.deskripsi" rows="2" placeholder="Uraian kategori kemitraan" class="w-full" />
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Urutan Tampil</label>
                    <input type="number" v-model="katForm.order" class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm py-2" />
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <Button type="button" label="Batal" severity="secondary" @click="showKatModal = false" />
                    <Button type="submit" :label="editKatMode ? 'Simpan Perubahan' : 'Simpan'" :loading="katForm.processing" />
                </div>
            </form>
        </Dialog>

        <ConfirmDialog />
    </AuthenticatedLayout>
</template>
