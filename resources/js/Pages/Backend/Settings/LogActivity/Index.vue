<script setup>
import { ref, watch } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'vue-toastification';
import InputText from 'primevue/inputtext';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import ConfirmDialog from 'primevue/confirmdialog';
import Paginator from 'primevue/paginator';
import Dropdown from 'primevue/dropdown';
import ChangesDiff from '@/Pages/Backend/Settings/LogActivity/Components/ChangesDiff.vue';

const toast = useToast();
const confirm = useConfirm();

const props = defineProps({
    datas: Object,
    filters: Object,
    share: Object,
    logNames: {
        type: Array,
        default: () => []
    }
});

const searchQuery = ref(props.filters?.search || '');
const selectedLogName = ref(props.filters?.log_name || null);

const currentPage = ref(props.datas.current_page);
const perPage = ref(props.datas.per_page);
const rowsPerPageOptions = [10, 20, 50, 100];

const applyFilters = () => {
    router.visit(route(props.share.prefix + '.index'), {
        data: {
            page: currentPage.value,
            per_page: perPage.value,
            search: searchQuery.value || undefined,
            log_name: selectedLogName.value || undefined,
        },
        preserveState: true,
        preserveScroll: true,
        only: ['datas']
    });
};

// Debounce Search
let searchTimeout;
watch(searchQuery, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        currentPage.value = 1;
        applyFilters();
    }, 500);
});

// Watch Log Name Filter
watch(selectedLogName, () => {
    currentPage.value = 1;
    applyFilters();
});

const onPageChange = (e) => {
    perPage.value = e.rows;
    currentPage.value = e.page + 1;
    applyFilters();
};

const expandedRows = ref([]);

// Readable Subject Name Helper
const getSubjectName = (data) => {
    if (!data.subject_type) return '-';
    
    // Shorten Model Name (App\Models\Permohonan -> Permohonan)
    const model = data.subject_type.split('\\').pop();
    
    // If exact subject loaded, try to get specific label if available (using properties or causer context if complex)
    // For now, return Model #ID
    return `${model} #${data.subject_id}`;
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
                    { label: 'Settings', route: 'settings.users.index' },
                    { label: share.title, route: share.prefix + '.index' }
                ]" />
                
                <div class="max-w-full w-full bg-white rounded-lg shadow-sm dark:bg-gray-800 p-4 md:p-6 dark:text-gray-100">
                    <!-- Filters -->
                    <!-- Search and Filters -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4 md:gap-0">
                        <div class="flex flex-col sm:flex-row gap-2 md:gap-4 w-full md:w-auto">
                            <Dropdown 
                                v-model="selectedLogName" 
                                :options="logNames" 
                                showClear
                                placeholder="Tipe Log" 
                                size="small"
                                class="w-full md:w-48"
                            />
                            <InputText 
                                size="small" 
                                v-model="searchQuery" 
                                placeholder="Cari log..." 
                                class="border block rounded w-full sm:w-64 dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                            />
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
                        </div>
                    </div>

                    <!-- Data Table -->
                    <DataTable 
                        v-model:expandedRows="expandedRows" 
                        :value="datas.data" 
                        dataKey="id"
                        stripedRows 
                        tableStyle="min-width: 50rem;"
                    >
                        <Column expander style="width: 3rem" />
                        
                        <Column header="Time" class="w-48 whitespace-nowrap align-top">
                            <template #body="{ data }">
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $formatDate(data.created_at) }}</span>
                                    <span class="text-xs text-gray-500">{{ $formatDateTime(data.created_at).split(' ')[3] }} ({{ $diffForHumans(data.created_at) }})</span>
                                </div>
                            </template>
                        </Column>

                        <Column header="Activity">
                            <template #body="{ data }">
                                <div class="flex items-center gap-2 mb-1">
                                    <span 
                                        class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider text-white"
                                        :class="{
                                            'bg-blue-500': data.event === 'created',
                                            'bg-yellow-500': data.event === 'updated',
                                            'bg-red-500': data.event === 'deleted',
                                            'bg-gray-500': !['created', 'updated', 'deleted'].includes(data.event)
                                        }"
                                    >
                                        {{ data.event }}
                                    </span>
                                    <span class="bg-gray-100 text-gray-600 text-[10px] font-medium px-2 py-0.5 rounded dark:bg-gray-700 dark:text-gray-400 border border-gray-200 dark:border-gray-600">
                                        {{ data.log_name }}
                                    </span>
                                </div>
                                <div class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                    {{ data.description }}
                                </div>
                                <div v-if="data.subject_type" class="text-xs text-gray-500 mt-0.5 font-mono">
                                    Target: {{ getSubjectName(data) }}
                                </div>
                            </template>
                        </Column>

                        <Column header="Causer" class="w-48 align-top">
                            <template #body="{ data }">
                                <div v-if="data.causer" class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold">
                                        {{ data.causer.name.substring(0,2).toUpperCase() }}
                                    </div>
                                    <div class="flex flex-col overflow-hidden">
                                        <span class="text-sm font-medium truncate">{{ data.causer.name }}</span>
                                        <span class="text-[10px] text-gray-500 truncate">{{ data.causer.email }}</span>
                                    </div>
                                </div>
                                <div v-else class="text-xs text-gray-400 italic">System</div>
                            </template>
                        </Column>
                        
                        <!-- Expanded Content: Changes Diff -->
                        <template #expansion="{ data }">
                            <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg ml-10 border-l-4 border-indigo-500">
                                <h4 class="font-semibold mb-3 text-sm flex items-center gap-2">
                                    <i class="pi pi-file-edit text-indigo-500"></i>
                                    Recorded Changes
                                </h4>
                                <ChangesDiff :properties="data.properties" />
                            </div>
                        </template>

                    </DataTable>

                    <!-- Paginator -->
                    <div class="mt-4 border-t pt-4 dark:border-gray-700">
                        <Paginator 
                            :totalRecords="datas.total" 
                            :rows="perPage" 
                            :first="(currentPage - 1) * perPage"
                            :rowsPerPageOptions="rowsPerPageOptions" 
                            @page="onPageChange" 
                            template="RowsPerPageDropdown FirstPageLink PrevPageLink CurrentPageReport NextPageLink LastPageLink"
                            currentPageReportTemplate="{first} - {last} of {totalRecords}" 
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
    <ConfirmDialog></ConfirmDialog>
</template>
