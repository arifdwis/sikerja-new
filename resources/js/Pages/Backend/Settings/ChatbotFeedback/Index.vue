<script setup>
import { ref, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue'
import InputText from 'primevue/inputtext'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dropdown from 'primevue/dropdown'
import Paginator from 'primevue/paginator'

const props = defineProps({
    datas: Object,
    filters: Object,
    share: Object,
    confidenceOptions: {
        type: Array,
        default: () => []
    },
    statusOptions: {
        type: Array,
        default: () => []
    }
})

const searchQuery = ref(props.filters?.search || '')
const selectedConfidence = ref(props.filters?.confidence || null)
const selectedStatus = ref(props.filters?.status || null)

const currentPage = ref(props.datas.current_page)
const perPage = ref(props.datas.per_page)
const rowsPerPageOptions = [10, 20, 50, 100]

const applyFilters = () => {
    router.visit(route(props.share.prefix + '.index'), {
        data: {
            page: currentPage.value,
            per_page: perPage.value,
            search: searchQuery.value || undefined,
            confidence: selectedConfidence.value || undefined,
            status: selectedStatus.value || undefined,
        },
        preserveState: true,
        preserveScroll: true,
        only: ['datas', 'filters']
    })
}

let searchTimeout
watch(searchQuery, () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        currentPage.value = 1
        applyFilters()
    }, 400)
})

watch([selectedConfidence, selectedStatus], () => {
    currentPage.value = 1
    applyFilters()
})

const onPageChange = (e) => {
    perPage.value = e.rows
    currentPage.value = e.page + 1
    applyFilters()
}

const truncate = (text, limit = 120) => {
    if (!text) return '-'
    return text.length > limit ? `${text.slice(0, limit)}...` : text
}
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
                    { label: 'AI Feedback Log', route: share.prefix + '.index' }
                ]" />

                <div class="max-w-full w-full bg-white rounded-lg shadow-sm dark:bg-gray-800 p-4 md:p-6 dark:text-gray-100">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4">
                        <div class="flex flex-col sm:flex-row gap-2 md:gap-4 w-full md:w-auto">
                            <Dropdown
                                v-model="selectedConfidence"
                                :options="confidenceOptions"
                                optionLabel="label"
                                optionValue="value"
                                showClear
                                placeholder="Confidence"
                                size="small"
                                class="w-full md:w-40"
                            />
                            <Dropdown
                                v-model="selectedStatus"
                                :options="statusOptions"
                                optionLabel="label"
                                optionValue="value"
                                showClear
                                placeholder="Status"
                                size="small"
                                class="w-full md:w-52"
                            />
                            <InputText
                                size="small"
                                v-model="searchQuery"
                                placeholder="Cari pertanyaan/jawaban..."
                                class="border block rounded w-full sm:w-72 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            />
                        </div>

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

                    <DataTable :value="datas.data" stripedRows tableStyle="min-width: 60rem;">
                        <Column header="Waktu" class="w-48">
                            <template #body="{ data }">
                                <div class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ $formatDateTime(data.created_at) }}
                                </div>
                            </template>
                        </Column>

                        <Column header="Pertanyaan">
                            <template #body="{ data }">
                                <div class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                    {{ truncate(data.question, 180) }}
                                </div>
                            </template>
                        </Column>

                        <Column header="Confidence" class="w-32">
                            <template #body="{ data }">
                                <span
                                    class="px-2 py-0.5 rounded text-[11px] font-bold uppercase tracking-wide"
                                    :class="{
                                        'bg-emerald-100 text-emerald-700': data.confidence === 'tinggi',
                                        'bg-yellow-100 text-yellow-700': data.confidence === 'sedang',
                                        'bg-red-100 text-red-700': data.confidence === 'rendah'
                                    }"
                                >
                                    {{ data.confidence }}
                                </span>
                            </template>
                        </Column>

                        <Column header="Status" class="w-40">
                            <template #body="{ data }">
                                <span class="text-xs font-semibold text-gray-600 dark:text-gray-300">{{ data.status }}</span>
                            </template>
                        </Column>

                        <Column header="Failure Reason" class="w-56">
                            <template #body="{ data }">
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ data.failure_reason || '-' }}</span>
                            </template>
                        </Column>
                    </DataTable>

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
</template>
