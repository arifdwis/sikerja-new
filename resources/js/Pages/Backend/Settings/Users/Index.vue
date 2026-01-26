<script setup>
import { ref, watch } from 'vue';
import { Head, router, Link, useForm } from '@inertiajs/vue3';
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
import Dialog from 'primevue/dialog';
import AutoComplete from 'primevue/autocomplete';
import Dropdown from 'primevue/dropdown';
import axios from 'axios';

const toast = useToast();
const confirm = useConfirm();

const props = defineProps({
    datas: Object,
    filters: Object,
    share: Object,
    roles: Array, // Passed from Controller
});

// SSO filter placeholder
const ssoFilter = ref(null); 
const ssoOptions = ref([
    { label: 'Semua', value: null },
    { label: 'SSO', value: true },
    { label: 'Non-SSO', value: false },
]);

// Verified filter placeholder
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

// Role Options for Dropdown
const roleOptions = props.roles.map(role => ({
    label: role.name,
    value: role.id
}));

// Modal State
const displayModal = ref(false);
const modalTitle = ref('');
const isEdit = ref(false);
const editingId = ref(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role_id: null,
    uid: null,
});

// SSO State
const ssoUsers = ref([]);
const selectedSsoUser = ref(null);
const isSsoLocked = ref(false);

const searchSso = async (event) => {
    try {
        const res = await axios.get(route('settings.users.search-sso'), {
            params: { query: event.query }
        });
        
        let results = res.data;
        if (Array.isArray(results) && results.length > 0) {
            if (typeof results[0] === 'string' && results[0].trim().startsWith('{')) {
                try { results = results.map(item => JSON.parse(item)); } catch (e) {}
            }
        }
        ssoUsers.value = results.slice(0, 50);
    } catch (e) {
        ssoUsers.value = [];
    }
};

const onSsoSelect = (event) => {
    const item = event.value;
    let name = item.text || item.name;
    let email = item.email || '';
    let uid = item.uid || item.id;

    if (typeof item.id === 'string' && item.id.includes('|||')) {
        const parts = item.id.split('|||');
        if (parts.length >= 3) {
            uid = parts[0];
            email = parts[1];
            name = parts[2];
        }
    }

    form.name = name;
    form.email = email;
    form.uid = uid;
    form.password = 'Substituted by SSO';
    form.password_confirmation = 'Substituted by SSO';
    isSsoLocked.value = true;
    toast.info("Data user SSO berhasil dimuat.");
};

const clearSso = () => {
    selectedSsoUser.value = null;
    isSsoLocked.value = false;
    form.uid = null;
    form.name = '';
    form.email = '';
    form.password = '';
    form.password_confirmation = '';
};

const openCreateModal = () => {
    isEdit.value = false;
    modalTitle.value = 'Tambah User';
    form.reset();
    form.clearErrors();
    clearSso();
    displayModal.value = true;
};

const openEditModal = (data) => {
    isEdit.value = true;
    modalTitle.value = 'Edit User';
    editingId.value = data.id;
    form.reset();
    form.clearErrors();
    
    // Populate Form
    form.name = data.name;
    form.email = data.email;
    form.uid = data.uid; // If SSO
    
    // Determine Role
    const userRole = data.roles && data.roles.length > 0 ? data.roles[0].id : null;
    form.role_id = userRole;

    // SSO Check
    if (data.uid) {
        isSsoLocked.value = true;
        selectedSsoUser.value = { text: data.name, uid: data.uid }; // Dummy for display
    } else {
        isSsoLocked.value = false;
        selectedSsoUser.value = null;
    }
    
    displayModal.value = true;
};

const closeModal = () => {
    displayModal.value = false;
    form.reset();
    clearSso();
    editingId.value = null;
};

const submitForm = () => {
    if (isEdit.value) {
        // Validation for Edit
        if (!form.role_id) {
            toast.warning('Pilih Role terlebih dahulu.');
            return;
        }

        form.put(route(props.share.prefix + '.update', editingId.value), {
            onSuccess: () => {
                toast.success('User berhasil diperbarui');
                closeModal();
            },
            onError: (errors) => {
                Object.values(errors).forEach(msg => toast.error(msg));
            },
        });
    } else {
        // Validation for Create
         if (!form.role_id) {
            toast.warning('Pilih Role terlebih dahulu.');
            return;
        }

        form.post(route(props.share.prefix + '.store'), {
            onSuccess: () => {
                toast.success('User berhasil ditambahkan');
                closeModal();
            },
            onError: (errors) => {
                Object.values(errors).forEach(msg => toast.error(msg));
            },
        });
    }
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

                    <!-- Filter Buttons -->
                    <div class="mb-4">
                        <div class="flex justify-between gap-3 w-full">
                            <div>
                                <SelectButton v-model="ssoFilter" :options="ssoOptions" optionLabel="label" optionValue="value" size="small" class="flex-1" />
                            </div>
                        </div>
                    </div>

                    <!-- Data Table -->
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

                        <!-- Expanded Content -->
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
         <Dialog v-model:visible="displayModal" :header="modalTitle" modal class="w-full md:w-1/2">
            <div class="flex flex-col gap-4 mt-2">
                <!-- SSO Search (Only for Create) -->
                 <div v-if="!isEdit" class="p-3 bg-gray-50 border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600">
                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Cari User SSO (Samarinda SSO)</label>
                    <div class="flex gap-2">
                        <AutoComplete 
                            v-model="selectedSsoUser" 
                            :suggestions="ssoUsers" 
                            @complete="searchSso" 
                            @item-select="onSsoSelect" 
                            field="text" 
                            placeholder="Ketik Nama / NIP / Email..." 
                            class="w-full"
                            inputClass="w-full"
                            :disabled="isSsoLocked"
                        >
                            <template #item="slotProps">
                                <div class="flex flex-col">
                                    <span class="font-semibold">{{ slotProps.item.text || slotProps.item.name }}</span>
                                    <span v-if="slotProps.item.email" class="text-xs text-gray-500">{{ slotProps.item.email }}</span>
                                </div>
                            </template>
                        </AutoComplete>
                        <Button v-if="isSsoLocked" icon="pi pi-times" severity="secondary" @click="clearSso" v-tooltip="'Reset Form'" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                     <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap</label>
                        <InputText v-model="form.name" :disabled="isSsoLocked" class="w-full" placeholder="Nama Lengkap" />
                        <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                        <InputText v-model="form.email" :disabled="isSsoLocked" class="w-full" placeholder="Email Address" type="email" />
                         <small v-if="form.errors.email" class="text-red-500">{{ form.errors.email }}</small>
                    </div>

                    <!-- Role Dropdown -->
                     <div class="flex flex-col gap-2 md:col-span-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                        <Dropdown 
                            v-model="form.role_id" 
                            :options="roleOptions" 
                            optionLabel="label" 
                            optionValue="value" 
                            placeholder="Pilih Role" 
                            class="w-full"
                            filter
                        />
                         <small v-if="form.errors.role_id" class="text-red-500">{{ form.errors.role_id }}</small>
                    </div>
                    
                    <template v-if="!isSsoLocked">
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                            <InputText v-model="form.password" class="w-full" placeholder="Password" type="password" />
                            <small class="text-xs text-gray-500" v-if="isEdit">Kosongkan jika tidak ingin mengganti.</small>
                             <small v-if="form.errors.password" class="text-red-500">{{ form.errors.password }}</small>
                        </div>
                        
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Konfirmasi Password</label>
                            <InputText v-model="form.password_confirmation" class="w-full" placeholder="Konfirmasi Password" type="password" />
                        </div>
                    </template>
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
