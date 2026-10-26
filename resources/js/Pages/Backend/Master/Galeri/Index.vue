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
const props = defineProps(['datas', 'share', 'filters']);

// Pagination state
const currentPage = ref(props.datas?.current_page || 1);
const perPage = ref(props.datas?.per_page || 10);
const rowsPerPageOptions = [10, 25, 50, 100];

// Search
const search = ref(props.filters?.search || '');

// Modal State
const showModal = ref(false);
const editMode = ref(false);
const currentItem = ref(null);
const imagePreview = ref(null);

const form = useForm({
    judul: '',
    deskripsi: '',
    tanggal_kegiatan: '',
    kategori_kegiatan: 'Rapat Pembahasan',
    lokasi: 'Kota Samarinda',
    order: 0,
    is_active: true,
    foto: null,
});

// Apply filters with pagination
const applyFilters = () => {
    router.visit(route(props.share.prefix + '.index'), {
        data: {
            page: currentPage.value,
            per_page: perPage.value,
            search: search.value || undefined,
        },
        preserveState: true,
        preserveScroll: true,
        only: ['datas']
    });
};

// Debounced search
let searchTimeout;
watch(search, () => {
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

// Open Create Modal
const openCreateModal = () => {
    editMode.value = false;
    currentItem.value = null;
    form.reset();
    form.clearErrors();
    form.tanggal_kegiatan = new Date().toISOString().split('T')[0];
    form.kategori_kegiatan = 'Rapat Pembahasan';
    form.lokasi = 'Kota Samarinda';
    form.is_active = true;
    form.order = 0;
    imagePreview.value = null;
    showModal.value = true;
};

// Open Edit Modal
const openEditModal = (item) => {
    editMode.value = true;
    currentItem.value = item;
    form.clearErrors();
    form.judul = item.judul;
    form.deskripsi = item.deskripsi || '';
    form.tanggal_kegiatan = item.tanggal_kegiatan ? item.tanggal_kegiatan.split('T')[0] : '';
    form.kategori_kegiatan = item.kategori_kegiatan || 'Rapat Pembahasan';
    form.lokasi = item.lokasi || '';
    form.order = item.order || 0;
    form.is_active = !!item.is_active;
    form.foto = null;
    imagePreview.value = item.foto ? '/' + item.foto.replace(/^\/+/, '') : null;
    showModal.value = true;
};

// Handle FileUpload PrimeVue
const onFotoSelect = (e) => {
    const file = e.files[0];
    if (file) {
        form.foto = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

// Submit Form
const submitForm = () => {
    if (editMode.value) {
        form.transform((data) => ({
            ...data,
            _method: 'PUT'
        })).post(route(props.share.prefix + '.update', currentItem.value.id), {
            onSuccess: () => {
                showModal.value = false;
                toast.success('Foto kegiatan TKKSD berhasil diperbarui.');
            },
            onError: (errors) => {
                Object.values(errors).forEach(msg => toast.error(msg));
            }
        });
    } else {
        form.post(route(props.share.prefix + '.store'), {
            onSuccess: () => {
                showModal.value = false;
                toast.success('Foto kegiatan TKKSD berhasil ditambahkan.');
            },
            onError: (errors) => {
                Object.values(errors).forEach(msg => toast.error(msg));
            }
        });
    }
};

// Delete
const confirmDelete = (item) => {
    confirm.require({
        message: `Apakah Anda yakin ingin menghapus foto kegiatan "${item.judul}"?`,
        header: 'Konfirmasi Hapus',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        acceptLabel: 'Ya, Hapus',
        rejectLabel: 'Batal',
        accept: () => {
            router.delete(route(props.share.prefix + '.destroy', item.id), {
                onSuccess: () => toast.success('Foto kegiatan TKKSD berhasil dihapus.'),
                onError: () => toast.error('Gagal menghapus foto kegiatan.')
            });
        }
    });
};

const formatTanggal = (d) => {
    if (!d) return '-';
    try {
        const date = new Date(d);
        return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    } catch {
        return d;
    }
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
                    <!-- Search and Filters -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4 md:gap-0">
                        <div class="flex flex-col sm:flex-row gap-2 md:gap-4">
                            <InputText 
                                size="small" 
                                v-model="search" 
                                placeholder="Cari kegiatan..." 
                                class="border block rounded w-full sm:w-64 dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                            />
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
                            
                            <Button label="Tambah Kegiatan" icon="pi pi-plus" size="small" @click="openCreateModal" />
                        </div>
                    </div>

                    <!-- Data Table PrimeVue with Responsive Overflow & Precise Widths -->
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
                                    Tidak ada data dokumentasi kegiatan TKKSD.
                                </div>
                            </template>

                            <Column header="No" class="w-12 text-center" style="width: 50px;">
                                <template #body="slotProps">
                                    {{ (currentPage - 1) * perPage + slotProps.index + 1 }}
                                </template>
                            </Column>

                            <Column header="Foto" class="w-24 text-center" style="width: 90px;">
                                <template #body="{ data }">
                                    <img 
                                        v-if="data.foto" 
                                        :src="'/' + data.foto.replace(/^\/+/, '')" 
                                        :alt="data.judul" 
                                        class="w-16 h-10 object-cover rounded shadow-sm mx-auto" 
                                    />
                                    <span v-else class="text-gray-400 text-xs">No Foto</span>
                                </template>
                            </Column>

                            <Column field="judul" header="Judul Kegiatan" sortable>
                                <template #body="{ data }">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ data.judul }}</span>
                                    <span v-if="data.deskripsi" class="text-gray-500 text-xs block truncate max-w-sm">{{ data.deskripsi }}</span>
                                </template>
                            </Column>

                            <Column field="kategori_kegiatan" header="Kategori" sortable class="w-36 text-center" style="width: 140px;">
                                <template #body="{ data }">
                                    <span class="px-2 py-0.5 rounded bg-teal-50 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300 text-xs font-semibold whitespace-nowrap">
                                        {{ data.kategori_kegiatan || 'TKKSD' }}
                                    </span>
                                </template>
                            </Column>

                            <Column field="tanggal_kegiatan" header="Tanggal" sortable class="w-28 text-center" style="width: 110px;">
                                <template #body="{ data }">
                                    <span class="text-xs text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ formatTanggal(data.tanggal_kegiatan) }}</span>
                                </template>
                            </Column>

                            <Column field="lokasi" header="Lokasi" sortable class="w-40" style="width: 160px;">
                                <template #body="{ data }">
                                    <span class="text-xs text-gray-600 dark:text-gray-300 truncate block max-w-[150px]" :title="data.lokasi">{{ data.lokasi || '-' }}</span>
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
                                        <Button icon="pi pi-pencil" severity="info" size="small" @click="openEditModal(data)" v-tooltip="'Edit'" />
                                        <Button icon="pi pi-trash" severity="danger" size="small" @click="confirmDelete(data)" v-tooltip="'Hapus'" />
                                    </div>
                                </template>
                            </Column>
                        </DataTable>
                    </div>

                    <!-- Paginator Bottom hanya tampil jika data > perPage -->
                    <div v-if="datas?.total > perPage" class="mt-4">
                        <Paginator 
                            :totalRecords="datas?.total || 0" 
                            :rows="perPage" 
                            :first="(currentPage - 1) * perPage"
                            :rowsPerPageOptions="rowsPerPageOptions" 
                            @page="onPageChange" 
                            template="FirstPageLink PrevPageLink CurrentPageReport NextPageLink LastPageLink"
                            currentPageReportTemplate="{first} - {last} dari {totalRecords}" 
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Create/Edit (Clean PrimeVue with FileUpload) -->
        <Dialog v-model:visible="showModal" :header="editMode ? 'Edit Foto Kegiatan TKKSD' : 'Tambah Foto Kegiatan TKKSD'" modal :style="{ width: '520px' }">
            <form @submit.prevent="submitForm" class="space-y-4">
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Judul Kegiatan *</label>
                    <InputText v-model="form.judul" placeholder="Masukkan judul kegiatan" class="w-full" autofocus required />
                    <small v-if="form.errors.judul" class="text-red-500">{{ form.errors.judul }}</small>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Kategori Kegiatan</label>
                        <InputText v-model="form.kategori_kegiatan" placeholder="Rapat Pembahasan / Penandatanganan" class="w-full" />
                        <small v-if="form.errors.kategori_kegiatan" class="text-red-500">{{ form.errors.kategori_kegiatan }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Kegiatan</label>
                        <input type="date" v-model="form.tanggal_kegiatan" class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm py-2" />
                        <small v-if="form.errors.tanggal_kegiatan" class="text-red-500">{{ form.errors.tanggal_kegiatan }}</small>
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Lokasi Kegiatan</label>
                    <InputText v-model="form.lokasi" placeholder="Contoh: Balai Kota Samarinda" class="w-full" />
                    <small v-if="form.errors.lokasi" class="text-red-500">{{ form.errors.lokasi }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi (Opsional)</label>
                    <Textarea v-model="form.deskripsi" rows="3" placeholder="Masukkan ringkasan agenda kegiatan" class="w-full" />
                    <small v-if="form.errors.deskripsi" class="text-red-500">{{ form.errors.deskripsi }}</small>
                </div>

                <!-- Foto FileUpload PrimeVue -->
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Foto Kegiatan {{ editMode ? '(Opsional jika tidak diganti)' : '*' }}</label>
                    <div v-if="imagePreview" class="mb-2">
                        <img :src="imagePreview" class="w-full h-36 object-cover rounded border" />
                    </div>
                    <FileUpload 
                        mode="basic" 
                        name="foto" 
                        accept="image/*" 
                        :maxFileSize="10000000" 
                        @select="onFotoSelect" 
                        :auto="true" 
                        chooseLabel="Pilih Foto Kegiatan" 
                        class="w-full" 
                    />
                    <small v-if="form.errors.foto" class="text-red-500">{{ form.errors.foto }}</small>
                </div>

                <div class="grid grid-cols-2 gap-4 items-center">
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Urutan Tampil</label>
                        <input type="number" v-model="form.order" class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm py-2" />
                    </div>
                    <div class="flex items-center gap-2 pt-6">
                        <InputSwitch v-model="form.is_active" inputId="is_active_galeri" />
                        <label for="is_active_galeri" class="text-sm font-medium text-gray-700 dark:text-gray-300">Aktif</label>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <Button type="button" label="Batal" severity="secondary" @click="showModal = false" />
                    <Button type="submit" :label="editMode ? 'Simpan Perubahan' : 'Simpan'" :loading="form.processing" />
                </div>
            </form>
        </Dialog>

        <ConfirmDialog />
    </AuthenticatedLayout>
</template>
