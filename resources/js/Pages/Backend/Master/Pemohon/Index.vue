<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { useToast } from "vue-toastification";
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Paginator from 'primevue/paginator';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
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

const form = useForm({
    name: '',
    instansi: '',
    jabatan: '',
    email: '',
    phone: '',
    alamat: ''
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

const openCreateModal = () => {
    editMode.value = false;
    currentItem.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (item) => {
    editMode.value = true;
    currentItem.value = item;
    form.name = item.name;
    form.instansi = item.instansi;
    form.jabatan = item.jabatan;
    form.email = item.email;
    form.phone = item.phone;
    form.alamat = item.alamat;
    showModal.value = true;
};

const submitForm = () => {
    if (editMode.value && currentItem.value) {
        form.put(route(props.share.prefix + '.update', currentItem.value.uuid), {
            onSuccess: () => {
                showModal.value = false;
                toast.success('Pemohon berhasil diperbarui.');
            },
            onError: (errors) => {
                Object.values(errors).forEach(msg => toast.error(msg));
            }
        });
    } else {
        form.post(route(props.share.prefix + '.store'), {
            onSuccess: () => {
                showModal.value = false;
                toast.success('Pemohon berhasil ditambahkan.');
            },
            onError: (errors) => {
                Object.values(errors).forEach(msg => toast.error(msg));
            }
        });
    }
};

const confirmDelete = (item) => {
    confirm.require({
        message: `Apakah Anda yakin ingin menghapus pemohon "${item.name}"?`,
        header: 'Konfirmasi Hapus',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(route(props.share.prefix + '.destroy', item.uuid), {
                onSuccess: () => toast.success('Pemohon berhasil dihapus.'),
                onError: () => toast.error('Gagal menghapus pemohon.')
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
                    { label: 'Master Data', route: 'master.kategori.index' },
                    { label: share.title, route: null }
                ]" />

                <div class="max-w-full w-full bg-white rounded-lg shadow-sm dark:bg-gray-800 p-4 md:p-6 dark:text-gray-100">
                    <!-- Search and Filters -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4 md:gap-0">
                        <div class="flex flex-col sm:flex-row gap-2 md:gap-4">
                            <InputText size="small" v-model="search" placeholder="Cari pemohon..." class="border block rounded w-full sm:w-64 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
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
                            
                            <Button label="Tambah" icon="pi pi-plus" size="small" @click="openCreateModal" />
                        </div>
                    </div>

                    <!-- Data Table -->
                    <DataTable 
                        :value="datas?.data || []" 
                        dataKey="id"
                        showGridlines 
                        stripedRows 
                        class="text-sm"
                    >
                        <template #empty>
                            <div class="text-center p-4 text-gray-500">
                                Tidak ada data pemohon.
                            </div>
                        </template>

                        <Column header="No" class="w-12 text-center">
                            <template #body="slotProps">
                                {{ (currentPage - 1) * perPage + slotProps.index + 1 }}
                            </template>
                        </Column>

                        <Column field="name" header="Nama" sortable>
                            <template #body="{ data }">
                                <span class="font-medium">{{ data.name }}</span>
                            </template>
                        </Column>

                        <Column header="Instansi" sortable>
                            <template #body="{ data }">
                                {{ data.instansi || data.nama_instansi || data.unit_kerja || '-' }}
                            </template>
                        </Column>

                        <Column field="jabatan" header="Jabatan" sortable />

                        <Column field="email" header="Email" sortable>
                            <template #body="{ data }">
                                <span class="text-blue-600">{{ data.email }}</span>
                            </template>
                        </Column>

                        <Column field="phone" header="Telepon" sortable />

                        <Column header="Aksi" class="w-32 text-center">
                            <template #body="{ data }">
                                <div class="flex justify-center gap-2">
                                    <Button icon="pi pi-pencil" severity="info" size="small" @click="openEditModal(data)" v-tooltip="'Edit'" />
                                    <Button icon="pi pi-trash" severity="danger" size="small" @click="confirmDelete(data)" v-tooltip="'Hapus'" />
                                </div>
                            </template>
                        </Column>
                    </DataTable>

                    <!-- Paginator Bottom -->
                    <div class="mt-4">
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

        <!-- Modal Create/Edit -->
        <Dialog v-model:visible="showModal" :header="editMode ? 'Edit Pemohon' : 'Tambah Pemohon'" modal :style="{ width: '550px' }">
            <form @submit.prevent="submitForm" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium mb-2">Nama Lengkap *</label>
                        <InputText v-model="form.name" class="w-full" placeholder="Nama pemohon" />
                        <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Instansi *</label>
                        <InputText v-model="form.instansi" class="w-full" placeholder="Nama instansi" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Jabatan</label>
                        <InputText v-model="form.jabatan" class="w-full" placeholder="Jabatan" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Email *</label>
                        <InputText v-model="form.email" type="email" class="w-full" placeholder="email@example.com" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Telepon</label>
                        <InputText v-model="form.phone" class="w-full" placeholder="08123456789" />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium mb-2">Alamat</label>
                        <Textarea v-model="form.alamat" rows="2" class="w-full" placeholder="Alamat lengkap" />
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
