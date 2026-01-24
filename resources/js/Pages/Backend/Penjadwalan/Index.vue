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
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import { Icon } from '@iconify/vue';

const toast = useToast();
const props = defineProps(['datas', 'share', 'filters']);

// Pagination state
const currentPage = ref(props.datas?.current_page || 1);
const perPage = ref(props.datas?.per_page || 10);
const rowsPerPageOptions = [10, 25, 50, 100];

// Search
const search = ref(props.filters?.search || '');

// Modal State
const showReviewModal = ref(false);
const currentJadwal = ref(null);

const form = useForm({
    status: 1, // 1: Approve, 2: Reject
    admin_comment: ''
});

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const formatTime = (timeString) => {
    if (!timeString) return '-';
    // Handle "HH:mm:ss" or Date object
    if (timeString.includes('T')) {
        return new Date(timeString).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }
    return timeString.substring(0, 5);
};

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

const getStatusSeverity = (status) => {
    // 0: Menunggu, 1: Disetujui, 2: Ditolak
    const map = { 0: 'warning', 1: 'success', 2: 'danger' };
    return map[status] || 'secondary';
};

const getStatusLabel = (status) => {
    const map = { 0: 'Menunggu Persetujuan', 1: 'Disetujui', 2: 'Ditolak' };
    return map[status] || 'Unknown';
};

const openReviewModal = (item) => {
    currentJadwal.value = item;
    form.reset();
    form.status = 1; // Default approve
    showReviewModal.value = true;
};

const submitReview = (status) => {
    form.status = status;
    form.put(route('permohonan.file.review', { fileUuid: currentJadwal.value.uuid }) /* Logic needs update in Web routes to map to PenjadwalanController::review */, {
        onSuccess: () => {
            showReviewModal.value = false;
            toast.success(`Jadwal berhasil ${status === 1 ? 'disetujui' : 'ditolak'}`);
        },
        onError: () => {
            toast.error('Gagal memproses review jadwal');
        }
    }); 
    // Wait, route above is wrong. It should be permohonan.jadwal.review? 
    // In routes/web.php I haven't defined specific review route for jadwal yet?
    // Let me check Controller. I added `review` method but route in web.php?
};

// Correct submit handler
const submitApproval = () => {
    router.put(route('penjadwalan.update', currentJadwal.value.uuid), {
        status: 1,
        admin_comment: form.admin_comment
    }, {
         onSuccess: () => {
            showReviewModal.value = false;
            toast.success('Jadwal berhasil disetujui');
        }
    });
};

const submitRejection = () => {
    if (!form.admin_comment) {
        toast.error('Harap isi alasan penolakan');
        return;
    }
    router.put(route('penjadwalan.update', currentJadwal.value.uuid), {
        status: 2,
        admin_comment: form.admin_comment
    }, {
         onSuccess: () => {
            showReviewModal.value = false;
            toast.success('Jadwal berhasil ditolak');
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
                    { label: 'Dashboard', route: 'dashboard' },
                    { label: share.title, route: null }
                ]" />

                <div class="max-w-full w-full bg-white rounded-lg shadow-sm dark:bg-gray-800 p-4 md:p-6 dark:text-gray-100">
                    <!-- Search and Filters -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4 md:gap-0">
                        <div class="flex flex-col sm:flex-row gap-2 md:gap-4">
                            <InputText size="small" v-model="search" placeholder="Cari jadwal / permohonan..." class="border block rounded w-full sm:w-64 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
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
                                Tidak ada data penjadwalan.
                            </div>
                        </template>

                        <Column header="No" class="w-12 text-center">
                            <template #body="slotProps">
                                {{ (currentPage - 1) * perPage + slotProps.index + 1 }}
                            </template>
                        </Column>

                        <Column header="Permohonan" sortable>
                            <template #body="{ data }">
                                <div class="font-medium text-blue-600">{{ data.permohonan?.label || '-' }}</div>
                                <div class="text-xs text-gray-500 mt-1">{{ data.permohonan?.nomor_permohonan || data.permohonan?.kode }}</div>
                                <div class="text-xs text-gray-500">{{ data.permohonan?.nama_instansi }}</div>
                            </template>
                        </Column>

                        <Column header="Jadwal Usulan" sortable>
                            <template #body="{ data }">
                                <div class="flex items-center gap-2">
                                    <Icon icon="solar:calendar-date-bold" class="text-gray-400" />
                                    <span class="font-medium">{{ formatDate(data.tanggal) }}</span>
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <Icon icon="solar:clock-circle-bold" class="text-gray-400" />
                                    <span>{{ formatTime(data.waktu) }}</span>
                                </div>
                                <div class="mt-1 text-xs px-2 py-0.5 rounded inline-block" :class="data.tipe === 'calendar' ? 'bg-indigo-50 text-indigo-700' : 'bg-orange-50 text-orange-700'">
                                    {{ data.tipe === 'calendar' ? 'Online (Calendar)' : 'Offline (Langsung)' }}
                                </div>
                            </template>
                        </Column>

                        <Column header="Lokasi/Agenda">
                             <template #body="{ data }">
                                <div class="text-xs mb-1 font-semibold text-gray-500">Lokasi:</div>
                                <div class="mb-2">{{ data.lokasi }}</div>
                                <div class="text-xs mb-1 font-semibold text-gray-500">Agenda:</div>
                                <div class="italic text-gray-600 line-clamp-2">{{ data.agenda }}</div>
                             </template>
                        </Column>

                        <Column field="status" header="Status" sortable class="w-32 text-center">
                            <template #body="{ data }">
                                <Tag :value="getStatusLabel(data.status)" :severity="getStatusSeverity(data.status)" />
                                <div v-if="data.approved_by" class="text-[10px] text-gray-400 mt-1">
                                    Oleh: {{ data.approver?.name || 'Admin' }}
                                </div>
                            </template>
                        </Column>

                        <Column header="Aksi" class="w-24 text-center">
                            <template #body="{ data }">
                                <div class="flex justify-center gap-2">
                                    <Button 
                                        v-if="data.status === 0"
                                        label="Review" 
                                        icon="pi pi-check-square" 
                                        severity="info" 
                                        size="small" 
                                        @click="openReviewModal(data)" 
                                    />
                                    <Button 
                                        v-else
                                        icon="pi pi-eye" 
                                        severity="secondary" 
                                        size="small" 
                                        @click="openReviewModal(data)" 
                                    />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </div>
        </div>

        <!-- Review Modal -->
        <Dialog v-model:visible="showReviewModal" :header="currentJadwal?.status === 0 ? 'Review Pengajuan Jadwal' : 'Detail Jadwal'" modal :style="{ width: '600px' }">
            <div class="space-y-6" v-if="currentJadwal">
                <!-- Info Header -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-4 rounded-lg">
                    <p class="text-xs font-bold text-blue-500 uppercase">Perihal Permohonan</p>
                    <p class="font-semibold text-lg">{{ currentJadwal.permohonan?.label }}</p>
                    <p class="text-sm text-gray-600">{{ currentJadwal.permohonan?.nama_instansi }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                     <div class="bg-gray-50 p-3 rounded">
                        <label class="block text-xs font-bold text-gray-500 mb-1">TANGGAL</label>
                        <p class="font-medium">{{ formatDate(currentJadwal.tanggal) }}</p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded">
                        <label class="block text-xs font-bold text-gray-500 mb-1">WAKTU</label>
                        <p class="font-medium">{{ formatTime(currentJadwal.waktu) }}</p>
                    </div>
                    <div class="col-span-2 bg-gray-50 p-3 rounded">
                        <label class="block text-xs font-bold text-gray-500 mb-1">LOKASI</label>
                        <p class="font-medium">{{ currentJadwal.lokasi }}</p>
                    </div>
                    <div class="col-span-2 bg-gray-50 p-3 rounded">
                        <label class="block text-xs font-bold text-gray-500 mb-1">AGENDA</label>
                        <p class="font-medium">{{ currentJadwal.agenda }}</p>
                    </div>
                    <div class="col-span-2 bg-gray-50 p-3 rounded" v-if="currentJadwal.keterangan">
                        <label class="block text-xs font-bold text-gray-500 mb-1">CATATAN PEMOHON</label>
                        <p class="text-sm">{{ currentJadwal.keterangan }}</p>
                    </div>
                </div>

                <!-- Approval Form -->
                <div v-if="currentJadwal.status === 0" class="border-t pt-4">
                     <label class="block text-sm font-medium mb-2">Catatan Admin / Alasan Penolakan</label>
                     <Textarea v-model="form.admin_comment" rows="3" class="w-full mb-4" placeholder="Tulis catatan di sini..." />
                     
                     <div class="flex gap-3 justify-end">
                        <Button label="Tolak Jadwal" icon="pi pi-times" severity="danger" @click="submitRejection" />
                        <Button label="Setujui Jadwal" icon="pi pi-check" severity="success" @click="submitApproval" />
                     </div>
                </div>

                <!-- Status View -->
                <div v-else class="border-t pt-4">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="font-bold text-sm">Status:</span>
                        <Tag :value="getStatusLabel(currentJadwal.status)" :severity="getStatusSeverity(currentJadwal.status)" />
                    </div>
                     <div v-if="currentJadwal.admin_comment" class="bg-yellow-50 p-3 rounded text-sm text-yellow-800">
                        <strong>Catatan Admin:</strong> {{ currentJadwal.admin_comment }}
                     </div>
                     <div class="mt-4 flex justify-end">
                         <Button label="Tutup" severity="secondary" @click="showReviewModal = false" />
                     </div>
                </div>
            </div>
        </Dialog>
    </AuthenticatedLayout>
</template>
