<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { useToast } from "vue-toastification";
import { Icon } from '@iconify/vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Editor from 'primevue/editor';
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

const form = useForm({
    question: '',
    answer: '',
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
    form.question = item.label;
    form.answer = item.jawaban;
    showModal.value = true;
};

// Submit Form
const submitForm = () => {
    if (editMode.value && currentItem.value) {
        form.put(route(props.share.prefix + '.update', currentItem.value.uuid), {
            onSuccess: () => {
                showModal.value = false;
                toast.success('FAQ berhasil diperbarui.');
            },
            onError: (errors) => {
                Object.values(errors).forEach(msg => toast.error(msg));
            }
        });
    } else {
        form.post(route(props.share.prefix + '.store'), {
            onSuccess: () => {
                showModal.value = false;
                toast.success('FAQ berhasil ditambahkan.');
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
        message: `Apakah Anda yakin ingin menghapus FAQ "${item.label}"?`,
        header: 'Konfirmasi Hapus',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(route(props.share.prefix + '.destroy', item.uuid), {
                onSuccess: () => toast.success('FAQ berhasil dihapus.'),
                onError: () => toast.error('Gagal menghapus FAQ.')
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
                            <InputText size="small" v-model="search" placeholder="Cari pertanyaan..." class="border block rounded w-full sm:w-64 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
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
                                Tidak ada data FAQ.
                            </div>
                        </template>

                        <Column header="No" class="w-12 text-center">
                            <template #body="slotProps">
                                {{ (currentPage - 1) * perPage + slotProps.index + 1 }}
                            </template>
                        </Column>

                        <Column field="label" header="Pertanyaan" sortable>
                            <template #body="{ data }">
                                <div class="font-medium prose prose-sm dark:prose-invert max-w-none" v-html="data.label"></div>
                            </template>
                        </Column>

                         <Column field="jawaban" header="Jawaban" sortable>
                            <template #body="{ data }">
                                <div class="text-gray-500 line-clamp-2 prose prose-sm dark:prose-invert max-w-none" v-html="data.jawaban"></div>
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
        <Dialog v-model:visible="showModal" :header="editMode ? 'Edit FAQ' : 'Tambah FAQ'" modal :style="{ width: '500px' }">
            <form @submit.prevent="submitForm" class="space-y-4">
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Pertanyaan</label>
                    <InputText v-model="form.question" placeholder="Masukkan pertanyaan" class="w-full" autofocus />
                    <small v-if="form.errors.question" class="text-red-500">{{ form.errors.question }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Jawaban</label>
                    <Editor v-model="form.answer" editorStyle="height: 200px" />
                    <small v-if="form.errors.answer" class="text-red-500">{{ form.errors.answer }}</small>
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
