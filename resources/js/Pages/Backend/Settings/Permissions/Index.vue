<script setup>
import { ref, watch } from 'vue';
import { Head, router, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'vue-toastification';
import { Icon } from '@iconify/vue';
import InputText from 'primevue/inputtext';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import ConfirmDialog from 'primevue/confirmdialog';
import Paginator from 'primevue/paginator';
import Dialog from 'primevue/dialog';

const toast = useToast();
const confirm = useConfirm();

const props = defineProps({
    datas: Object,
    filters: Object,
    share: Object,
});

const searchQuery = ref(props.filters?.search || '');
const currentPage = ref(props.datas.current_page);
const perPage = ref(props.datas.per_page);
const rowsPerPageOptions = [10, 25, 50, 100];

// Modal State
const displayModal = ref(false);
const modalTitle = ref('');
const isEdit = ref(false);
const editingId = ref(null);

const form = useForm({
    name: '',
    guard_name: 'web',
});

const openCreateModal = () => {
    isEdit.value = false;
    modalTitle.value = 'Tambah Permission';
    form.reset();
    form.guard_name = 'web';
    form.clearErrors();
    displayModal.value = true;
};

const openEditModal = (data) => {
    isEdit.value = true;
    modalTitle.value = 'Edit Permission';
    editingId.value = data.id;
    form.reset();
    form.clearErrors();
    form.name = data.name;
    form.guard_name = data.guard_name;
    displayModal.value = true;
};

const closeModal = () => {
    displayModal.value = false;
    form.reset();
    editingId.value = null;
};

const submitForm = () => {
    if (isEdit.value) {
        form.put(route(props.share.prefix + '.update', editingId.value), {
            onSuccess: () => {
                toast.success('Permission berhasil diperbarui');
                closeModal();
            },
            onError: () => toast.error('Gagal memperbarui permission'),
        });
    } else {
        form.post(route(props.share.prefix + '.store'), {
            onSuccess: () => {
                toast.success('Permission berhasil ditambahkan');
                closeModal();
            },
            onError: () => toast.error('Gagal menambahkan permission'),
        });
    }
};

const applyFilters = () => {
    router.visit(route(props.share.prefix + '.index'), {
        data: {
            page: currentPage.value,
            per_page: perPage.value,
            search: searchQuery.value || undefined,
        },
        preserveState: true,
        preserveScroll: true,
        only: ['datas']
    });
};

let searchTimeout;
watch(searchQuery, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        currentPage.value = 1;
        applyFilters();
    }, 500);
});

const onPageChange = (e) => {
    perPage.value = e.rows;
    currentPage.value = e.page + 1;
    applyFilters();
};

const deleteData = (id) => {
    confirm.require({
        message: 'Apakah Anda yakin ingin menghapus permission ini?',
        header: 'Konfirmasi Hapus',
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Ya Hapus',
        rejectLabel: 'Batal',
        acceptClass: 'p-button-danger',
        rejectClass: 'p-button-secondary',
        accept: () => {
            router.delete(route(props.share.prefix + '.destroy', id), {
                onSuccess: () => toast.success('Permission berhasil dihapus.'),
                onError: () => toast.error('Gagal menghapus permission.'),
            });
        },
    });
};

const size = ref({ label: 'Small', value: 'small' });

</script>

<template>
    <Head :title="`Manajemen ${share.title}`" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Manajemen {{ share.title }}</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-full sm:px-6 lg:px-8 space-y-4">
                <Breadcrumb :crumbs="[
                    { label: 'Pengaturan', route: 'settings.users.index' },
                    { label: share.title, route: share.prefix + '.index' }
                ]" />
                
                <div class="max-w-full w-full bg-white rounded-lg shadow-sm dark:bg-gray-800 p-4 md:p-6 dark:text-gray-100">
                    <!-- Search and Filters -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4 md:gap-0">
                        <div class="flex flex-col sm:flex-row gap-2 md:gap-4">
                            <InputText size="small" v-model="searchQuery" :placeholder="`Cari ${share.title}...`" class="border block rounded w-full sm:w-64 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                        </div>
                        <div class="flex flex-col sm:flex-row gap-2 md:gap-4 items-start sm:items-center">
                             <!-- Paginator Top -->
                             <Paginator 
                                size="small" 
                                :totalRecords="datas.total" 
                                :rows="perPage" 
                                :first="(currentPage - 1) * perPage"
                                :rowsPerPageOptions="rowsPerPageOptions" 
                                @page="onPageChange" 
                                template="RowsPerPageDropdown PrevPageLink CurrentPageReport NextPageLink"
                                currentPageReportTemplate="{first} - {last} dari {totalRecords}" 
                            />
                            
                            <Button 
                                label="Tambah" 
                                icon="pi pi-plus" 
                                size="small"
                                class="p-button-primary"
                                @click="openCreateModal"
                            />
                        </div>
                    </div>

                    <!-- Data Table -->
                    <DataTable 
                        :value="datas.data" 
                        dataKey="id" 
                        showGridlines
                        stripedRows 
                        tableStyle="min-width: 50rem;"
                    >
                        <Column header="NO" class="w-10 !text-center">
                            <template #body="slotProps">
                                {{ (currentPage - 1) * perPage + slotProps.index + 1 }}
                            </template>
                        </Column>

                        <Column header="NAMA PERMISSION" field="name" sortable>
                            <template #body="{ data }">
                                <span class="font-semibold text-sm">{{ data.name }}</span>
                                <small class="block text-gray-500">{{ data.slug }}</small>
                            </template>
                        </Column>
                        
                        <Column header="GUARD" class="w-32 !text-center">
                             <template #body="{ data }">
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">
                                    {{ data.guard_name || 'web' }}
                                </span>
                            </template>
                        </Column>

                        <Column header="DIPERBARUI" class="w-32 text-end">
                            <template #body="{ data }">
                                <div class="text-xs text-right">
                                    <div class="font-medium text-gray-900 dark:text-white">
                                        {{ $formatDate(data.updated_at || data.created_at) }}
                                    </div>
                                    <div class="text-gray-500">
                                        {{ $diffForHumans(data.updated_at || data.created_at) }}
                                    </div>
                                </div>
                            </template>
                        </Column>

                        <Column header="AKSI" class="w-10 !text-end">
                            <template #body="{ data }">
                                <div class="flex items-center justify-end gap-2">
                                    <Button 
                                        severity="secondary" 
                                        outlined
                                        size="small"
                                        icon="pi pi-pencil" 
                                        @click="openEditModal(data)" 
                                        v-tooltip.top="'Edit'" 
                                        class="!w-8 !h-8 !p-0 bg-white"
                                    />
                                    <Button 
                                        severity="danger" 
                                        size="small"
                                        icon="pi pi-trash" 
                                        @click="deleteData(data.id)" 
                                        v-tooltip.top="'Hapus'" 
                                        class="!w-8 !h-8 !p-0"
                                    />
                                </div>
                            </template>
                        </Column>
                    </DataTable>

                    <!-- Paginator Bottom -->
                    <div class="mt-4">
                        <Paginator 
                            :totalRecords="datas.total" 
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
        <Dialog v-model:visible="displayModal" :header="modalTitle" modal class="w-full md:w-1/3">
            <div class="flex flex-col gap-4 mt-2">
                 <div class="flex flex-col gap-2">
                    <label for="name" class="text-sm font-medium text-gray-700 dark:text-gray-300">Nama Permission</label>
                    <InputText id="name" v-model="form.name" class="w-full" placeholder="Contoh: users.create" autofocus />
                    <small class="text-xs text-gray-500">Format: <code>resource.action</code></small>
                    <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
                </div>
                
                <div class="flex flex-col gap-2">
                    <label for="guard_name" class="text-sm font-medium text-gray-700 dark:text-gray-300">Guard Name</label>
                    <InputText id="guard_name" v-model="form.guard_name" class="w-full" placeholder="web" />
                    <small class="text-xs text-gray-500">Default: <code>web</code></small>
                    <small v-if="form.errors.guard_name" class="text-red-500">{{ form.errors.guard_name }}</small>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-2 pt-4">
                    <Button label="Batal" icon="pi pi-times" text @click="closeModal" severity="secondary" />
                    <Button label="Simpan" icon="pi pi-check" @click="submitForm" :loading="form.processing" />
                </div>
            </template>
        </Dialog>
    </AuthenticatedLayout>
    <ConfirmDialog></ConfirmDialog>
</template>
