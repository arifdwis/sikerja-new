<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { useToast } from "vue-toastification";
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Editor from 'primevue/editor';
import Paginator from 'primevue/paginator';
import Dialog from 'primevue/dialog';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import ToggleSwitch from 'primevue/toggleswitch';
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
    label: '',
    content: '',
    status: 1,
});

// Computed for InputSwitch (converts 0/1 to boolean)
import { computed } from 'vue';
const statusActive = computed({
    get: () => form.status === 1,
    set: (val) => form.status = val ? 1 : 0
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
    showModal.value = true;
};

// Open Edit Modal
const openEditModal = (item) => {
    editMode.value = true;
    currentItem.value = item;
    form.label = item.label;
    form.content = item.content;
    form.status = item.status;
    showModal.value = true;
};

// Submit Form
const submitForm = () => {
    if (editMode.value && currentItem.value) {
        form.put(route(props.share.prefix + '.update', currentItem.value.uuid), {
            onSuccess: () => {
                showModal.value = false;
                toast.success('Laman berhasil diperbarui.');
            },
            onError: (errors) => {
                Object.values(errors).forEach(msg => toast.error(msg));
            }
        });
    } else {
        form.post(route(props.share.prefix + '.store'), {
            onSuccess: () => {
                showModal.value = false;
                toast.success('Laman berhasil ditambahkan.');
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
        message: `Apakah Anda yakin ingin menghapus laman "${item.label}"?`,
        header: 'Konfirmasi Hapus',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(route(props.share.prefix + '.destroy', item.uuid), {
                onSuccess: () => toast.success('Laman berhasil dihapus.'),
                onError: () => toast.error('Gagal menghapus laman.')
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
                    { label: 'Master Data', route: share.prefix + '.index' },
                    { label: share.title, route: null }
                ]" />

                <div class="max-w-full w-full bg-white rounded-lg shadow-sm dark:bg-gray-800 p-4 md:p-6 dark:text-gray-100">
                    <!-- Search and Filters -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4 md:gap-0">
                        <div class="flex flex-col sm:flex-row gap-2 md:gap-4">
                            <InputText size="small" v-model="search" placeholder="Cari laman..." class="border block rounded w-full sm:w-64 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
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
                                Tidak ada data laman.
                            </div>
                        </template>

                        <Column header="No" class="w-12 text-center">
                            <template #body="slotProps">
                                {{ (currentPage - 1) * perPage + slotProps.index + 1 }}
                            </template>
                        </Column>

                        <Column field="label" header="Judul" sortable>
                            <template #body="{ data }">
                                <span class="font-medium">{{ data.label }}</span>
                            </template>
                        </Column>

                        <Column field="slug" header="Slug" sortable>
                            <template #body="{ data }">
                                <span class="text-gray-500 font-mono text-xs">{{ data.slug }}</span>
                            </template>
                        </Column>

                        <Column field="status" header="Status" sortable class="w-24 text-center">
                            <template #body="{ data }">
                                <Tag :value="data.status === 1 ? 'Aktif' : 'Tidak Aktif'" :severity="data.status === 1 ? 'success' : 'danger'" />
                            </template>
                        </Column>

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
        <Dialog v-model:visible="showModal" :header="editMode ? 'Edit Laman' : 'Tambah Laman'" modal :style="{ width: '600px' }">
            <form @submit.prevent="submitForm" class="space-y-4">
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Judul Laman</label>
                    <InputText v-model="form.label" placeholder="Masukkan judul laman" class="w-full" autofocus />
                    <small v-if="form.errors.label" class="text-red-500">{{ form.errors.label }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Konten</label>
                    <Editor v-model="form.content" editorStyle="height: 300px" />
                    <small v-if="form.errors.content" class="text-red-500">{{ form.errors.content }}</small>
                </div>

                <div class="flex items-center gap-2">
                    <ToggleSwitch v-model="statusActive" inputId="status" />
                    <label for="status" class="text-sm font-medium text-gray-700 dark:text-gray-300">Aktif</label>
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
