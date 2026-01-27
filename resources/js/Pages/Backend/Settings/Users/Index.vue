<script setup>
import { ref, watch } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'vue-toastification';
import { Icon } from '@iconify/vue';
import InputText from 'primevue/inputtext';
import SelectButton from 'primevue/selectbutton';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import ConfirmDialog from 'primevue/confirmdialog';
import Paginator from 'primevue/paginator';
import UserModal from './Components/UserModal.vue';

const toast = useToast();
const confirm = useConfirm();

const props = defineProps({
    datas: Object,
    filters: Object,
    share: Object,
    roles: Array, 
});

const ssoFilter = ref(null); 
const ssoOptions = ref([
    { label: 'Semua', value: null },
    { label: 'SSO', value: true },
    { label: 'Non-SSO', value: false },
]);

const verifiedFilter = ref(null); 
const verifiedOptions = ref([
    { label: 'Semua', value: null },
    { label: 'Terverifikasi', value: true },
    { label: 'Tidak Terverifikasi', value: false },
]);

const expandedRows = ref([]);

const searchQuery = ref(props.filters?.search || '');
const currentPage = ref(props.datas.current_page);
const perPage = ref(props.datas.per_page);
const rowsPerPageOptions = [10, 25, 50, 100];

const displayModal = ref(false);
const isEdit = ref(false);
const editingUser = ref(null);

const openCreateModal = () => {
    isEdit.value = false;
    editingUser.value = null;
    displayModal.value = true;
};

const openEditModal = (data) => {
    isEdit.value = true;
    editingUser.value = data;
    displayModal.value = true;
};

const resetModal = () => {
    displayModal.value = false;
    editingUser.value = null;
    router.reload({ only: ['datas'] });
};

const deleteData = (id) => {
    confirm.require({
        message: 'Apakah Anda yakin ingin menghapus user ini?',
        header: 'Konfirmasi Hapus',
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Ya Hapus',
        rejectLabel: 'Batal',
        acceptClass: 'p-button-danger',
        rejectClass: 'p-button-secondary',
        accept: () => {
            router.delete(route(props.share.prefix + '.destroy', id), {
                onSuccess: () => toast.success('User berhasil dihapus.'),
                onError: () => toast.error('Gagal menghapus user.'),
            });
        },
    });
};

const applyFilters = () => {
    router.visit(route(props.share.prefix + '.index'), {
        data: {
            page: currentPage.value,
            per_page: perPage.value,
            search: searchQuery.value || undefined,
            sso: ssoFilter.value,
            verified: verifiedFilter.value,
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

watch([ssoFilter, verifiedFilter], () => {
    currentPage.value = 1;
    applyFilters();
});

const onPageChange = (e) => {
    perPage.value = e.rows;
    currentPage.value = e.page + 1;
    applyFilters();
};
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
                     <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4 md:gap-0">
                        <div class="flex flex-col sm:flex-row gap-2 md:gap-4">
                            <InputText size="small" v-model="searchQuery" :placeholder="`Cari ${share.title}...`" class="border block rounded w-full sm:w-64 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                        </div>
                        <div class="flex flex-col sm:flex-row gap-2 md:gap-4 items-start sm:items-center">
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

                    <div class="mb-4">
                        <div class="flex justify-between gap-3 w-full">
                            <div>
                                <SelectButton v-model="ssoFilter" :options="ssoOptions" optionLabel="label" optionValue="value" size="small" class="flex-1" />
                            </div>
                        </div>
                    </div>

                    <DataTable 
                        v-model:expandedRows="expandedRows"
                        :value="datas.data" 
                        dataKey="id" 
                        showGridlines
                        stripedRows 
                        tableStyle="min-width: 50rem;"
                    >
                         <Column expander style="width: 3rem" />

                         <Column header="NO" class="w-10 !text-center">
                            <template #body="slotProps">
                                {{ (currentPage - 1) * perPage + slotProps.index + 1 }}
                            </template>
                        </Column>

                        <Column header="NAMA USER" field="name" sortable>
                            <template #body="{ data }">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold uppercase text-gray-500">
                                        {{ data.name.substring(0,2) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-sm">{{ data.name }}</span>
                                        <small class="text-gray-500">{{ data.email }}</small>
                                    </div>
                                    <span v-if="data.email && data.email.endsWith('@gmail.com')" class="bg-red-100 text-red-800 text-[10px] px-1.5 py-0.5 rounded ml-1">Google</span>
                                </div>
                            </template>
                        </Column>
                        
                        <Column header="ROLES" class="w-48">
                             <template #body="{ data }">
                                <div class="flex flex-wrap gap-1">
                                    <span v-for="role in data.roles" :key="role.id" class="bg-gray-100 text-gray-800 text-xs font-medium px-2 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300">
                                        {{ role.name }}
                                    </span>
                                </div>
                            </template>
                        </Column>

                        <Column header="SSO" class="w-20 !text-center font-bold uppercase">
                            <template #body="{ data }">
                                <Icon v-if="data.uid" class="w-6 h-6 text-sky-500 mx-auto" icon="solar:bolt-bold-duotone" title="SSO Account (Samarinda)" />
                                <Icon v-else-if="data.email && data.email.endsWith('@gmail.com')" class="w-5 h-5 mx-auto" icon="logos:google-icon" title="Google Account" />
                                <span v-else class="text-gray-300">-</span>
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
                                        v-if="!['administrator'].includes(data.slug) && (share.auth?.user?.id !== data.id)"
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

                        <template #expansion="{ data }">
                            <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                                <h4 class="font-semibold mb-2 text-sm">Detail Informasi</h4>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <p class="text-gray-500">Email Verified At:</p>
                                        <p>{{ data.email_verified_at ? $formatDate(data.email_verified_at) : 'Not Verified' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Created At:</p>
                                        <p>{{ $formatDate(data.created_at) }}</p>
                                    </div>
                                    <div v-if="data.uid">
                                        <p class="text-gray-500">SSO UID:</p>
                                        <p class="font-mono text-xs">{{ data.uid }}</p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </DataTable>

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

        <UserModal 
            v-model:visible="displayModal" 
            :user="editingUser" 
            :is-edit="isEdit" 
            :roles="roles" 
            @success="resetModal"
        />
    </AuthenticatedLayout>
    <ConfirmDialog></ConfirmDialog>
</template>
