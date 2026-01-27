<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Icon } from '@iconify/vue';
import { useToast } from 'vue-toastification';
import Dialog from 'primevue/dialog';
import Tag from 'primevue/tag';
import Skeleton from 'primevue/skeleton';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Paginator from 'primevue/paginator';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import ApprovalModal from './Components/ApprovalModal.vue';
import ActionDialogs from './Components/ActionDialogs.vue';
import axios from 'axios';

const props = defineProps({
    datas: Object,
    kategoris: Array,
    share: Object,
    filters: Object,
});

const toast = useToast();

// Pagination & Search
const filterQuery = ref(props.filters?.search || '');
const selectedKategori = ref(props.filters?.kategori || null);
let searchTimeout;

const applyFilters = () => {
    router.visit(route(props.share.prefix + '.index'), {
        data: { 
            search: filterQuery.value,
            kategori: selectedKategori.value
        },
        preserveState: true,
        preserveScroll: true,
        only: ['datas']
    });
};

watch([filterQuery, selectedKategori], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
});

// View mode
const viewMode = ref(localStorage.getItem('persetujuanViewMode') || 'grid');
watch(viewMode, (newVal) => localStorage.setItem('persetujuanViewMode', newVal));

// States
const detailDialog = ref(false);
const confirmDialog = ref(false);
const rejectDialog = ref(false);
const selectedItem = ref(null);
const detailData = ref(null);
const loadingDetail = ref(false);
const processing = ref(false);
const rejectReason = ref('');

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
         day: 'numeric', month: 'short', year: 'numeric'
    }).toUpperCase();
};

const formatTime = (timeString) => {
   if (!timeString) return '-';
   if (timeString.includes('T') || timeString.includes(' ')) {
      const date = new Date(timeString);
      return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace('.', ':');
   }
   return timeString.substring(0, 5);
};

// Actions
const openDetailModal = async (item) => {
    selectedItem.value = item;
    detailDialog.value = true;
    loadingDetail.value = true;
    try {
        const response = await axios.get(route('permohonan.show', item.uuid));
        detailData.value = response.data;
    } catch (error) {
        toast.error("Gagal memuat detail data");
    } finally {
        loadingDetail.value = false;
    }
};

const openConfirmDialog = (item) => {
    selectedItem.value = item;
    confirmDialog.value = true;
};

const openRejectDialog = (item) => {
    selectedItem.value = item;
    rejectReason.value = '';
    rejectDialog.value = true;
};

const submitRejection = () => {
    if (!rejectReason.value) return toast.error('Mohon isi alasan penolakan');
    processing.value = true;
    router.put(route(`${props.share.prefix}.update`, selectedItem.value.uuid), {
        status: 9, admin_comment: rejectReason.value
    }, {
        onSuccess: () => {
            rejectDialog.value = false;
            detailDialog.value = false;
            processing.value = false;
            toast.success('Permohonan berhasil ditolak');
        },
        onError: () => processing.value = false
    });
};

const submitPersetujuan = () => {
    processing.value = true;
    router.put(route(`${props.share.prefix}.update`, selectedItem.value.uuid), {
        status: 1
    }, {
        onSuccess: () => {
            confirmDialog.value = false;
            detailDialog.value = false;
            processing.value = false;
            toast.success('Permohonan berhasil disetujui');
        },
        onError: () => processing.value = false
    });
};
</script>

<template>
    <Head :title="share.title" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                {{ share.title }}
            </h2>
        </template>

        <section class="py-12">
            <div class="mx-auto max-w-full px-6 lg:px-8">
                <Breadcrumb class="mb-6" :crumbs="[{ label: 'Dashboard', route: 'dashboard' }, { label: share.title, route: null }]" />

                <!-- Control Bar -->
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                            <div class="relative w-full sm:w-80">
                                <Icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                <input v-model="filterQuery" type="text" placeholder="Cari permohonan..." class="pl-10 pr-4 py-2.5 w-full border border-gray-300 focus:border-indigo-500 rounded-lg text-sm dark:bg-gray-700" />
                            </div>
                            <Dropdown v-model="selectedKategori" :options="kategoris" optionLabel="label" optionValue="id" placeholder="Semua Kategori" class="w-full sm:w-48" showClear />
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="bg-gray-100 dark:bg-gray-700 p-1 rounded-lg border border-gray-200 dark:border-gray-600">
                                <button @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-500'" class="p-2 rounded-md transition-all">
                                    <Icon icon="lucide:layout-grid" class="w-4 h-4" />
                                </button>
                                <button @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-500'" class="p-2 rounded-md transition-all">
                                    <Icon icon="lucide:list" class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!datas.data.length" class="bg-white dark:bg-gray-800 rounded-2xl p-16 text-center border-2 border-dashed border-gray-200 dark:border-gray-700">
                     <div class="w-24 h-24 bg-indigo-50 dark:bg-indigo-900/20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <Icon icon="solar:calendar-date-bold-duotone" class="w-12 h-12 text-indigo-400" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Tidak ada Persetujuan</h3>
                    <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">Tidak ada jadwal yang menunggu persetujuan saat ini.</p>
                </div>

                <!-- Content -->
                <div v-else class="mb-10">
                    <!-- Grid View -->
                    <div v-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="item in datas.data" :key="item.id" 
                            class="group relative rounded-lg border shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 flex flex-col h-full"
                        >
                            <div class="p-5 flex flex-col h-full relative z-10">
                                <div class="flex justify-between items-center mb-3">
                                    <span class="bg-blue-50 text-blue-700 px-2 py-1 text-xs font-bold rounded border border-blue-200 uppercase tracking-wide">
                                        {{ item.kategori?.label || 'Kerjasama' }}
                                    </span>
                                    <span class="bg-purple-100 text-purple-700 px-2 py-1 text-xs font-bold rounded border border-purple-200 uppercase tracking-wide flex items-center gap-1">
                                        <Icon icon="solar:calendar-mark-bold" /> PERSETUJUAN
                                    </span>
                                </div>
                                <div class="mb-2 text-xs text-gray-400 font-mono">
                                    {{ item.nomor_permohonan || item.kode }}
                                </div>
                                <h4 @click="openDetailModal(item)" class="font-bold text-gray-900 dark:text-white mb-2 line-clamp-2 hover:text-indigo-600 transition-colors cursor-pointer">
                                    {{ item.label }}
                                </h4>
                                
                                <div class="text-sm text-gray-500 mb-4 flex items-center gap-2">
                                     <Icon icon="solar:buildings-bold" class="w-4 h-4" /> {{ item.nama_instansi }}
                                </div>

                                <!-- Schedule Highlight -->
                                <div v-if="item.penjadwalans && item.penjadwalans.length" class="mt-auto mb-4 bg-indigo-50 border border-indigo-100 rounded-lg p-3">
                                    <div class="flex items-center gap-2 mb-1 text-indigo-800 font-bold text-xs uppercase">
                                        <Icon icon="solar:calendar-date-bold" /> Jadwal Diajukan
                                    </div>
                                    <div class="text-sm font-semibold text-gray-800">{{ formatDate(item.penjadwalans[0].tanggal) }} • {{ formatTime(item.penjadwalans[0].waktu) }}</div>
                                    <div class="text-xs text-gray-500 truncate">{{ item.penjadwalans[0].lokasi }}</div>
                                </div>
                                
                                <div class="mt-auto pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center gap-2">
                                     <button @click="openDetailModal(item)" class="flex-1 py-2 text-xs font-bold text-gray-600 hover:text-cyan-600 bg-gray-50 hover:bg-cyan-50 rounded flex items-center justify-center gap-1 transition-colors">
                                        <Icon icon="solar:eye-linear" /> Review
                                    </button>
                                     <button @click="openRejectDialog(item)" class="p-2 text-red-600 bg-red-50 hover:bg-red-100 rounded transition-colors" v-tooltip="'Tolak'">
                                        <Icon icon="solar:close-circle-bold" class="w-4 h-4" />
                                    </button>
                                    <button @click="openConfirmDialog(item)" class="p-2 text-green-600 bg-green-50 hover:bg-green-100 rounded transition-colors" v-tooltip="'Setujui'">
                                        <Icon icon="solar:check-circle-bold" class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- List View -->
                    <div v-else class="space-y-3">
                         <div v-for="item in datas.data" :key="item.id" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-all flex flex-col md:flex-row md:items-center gap-4 group">
                             <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="font-bold text-sm font-mono text-gray-500">{{ item.nomor_permohonan || item.kode }}</span>
                                    <span class="bg-purple-100 text-purple-700 px-2 py-0.5 text-[10px] font-bold rounded border border-purple-200 uppercase">PERSETUJUAN</span>
                                </div>
                                <h4 @click="openDetailModal(item)" class="font-semibold text-gray-950 dark:text-white hover:text-indigo-600 cursor-pointer transition-colors capitalize text-lg">
                                    {{ item.label }}
                                </h4>
                                 <div class="text-sm text-gray-500 mt-1 flex gap-2 items-center">
                                      <Icon icon="solar:calendar-date-bold" class="w-4 h-4 text-indigo-500" />
                                     <span class="font-medium text-indigo-600" v-if="item.penjadwalans[0]">
                                         {{ formatDate(item.penjadwalans[0].tanggal) }} {{ formatTime(item.penjadwalans[0].waktu) }}
                                     </span>
                                     <span class="text-gray-300 mx-2">•</span>
                                     <span>{{ item.nama_instansi }}</span> 
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <Button icon="pi pi-eye" severity="secondary" @click="openDetailModal(item)" />
                                <Button icon="pi pi-times" severity="danger" @click="openRejectDialog(item)" />
                                <Button icon="pi pi-check" severity="success" @click="openConfirmDialog(item)" />
                            </div>
                         </div>
                    </div>

                     <!-- Paginator -->
                     <div class="mt-4">
                        <Paginator 
                            :totalRecords="datas?.total || 0" 
                            :rows="10" 
                            @page="params => router.get(datas.next_page_url)" 
                            template="FirstPageLink PrevPageLink CurrentPageReport NextPageLink LastPageLink" 
                        />
                    </div>
                </div>
            </div>
        </section>

        <!-- Components -->
        <ApprovalModal 
            v-model:visible="detailDialog"
            :loading="loadingDetail"
            :data="detailData"
            @reject="openRejectDialog"
            @approve="openConfirmDialog"
        />

        <ActionDialogs 
            v-model:confirmVisible="confirmDialog"
            v-model:rejectVisible="rejectDialog"
            v-model:rejectReason="rejectReason"
            :item="selectedItem"
            :processing="processing"
            @confirm="submitPersetujuan"
            @reject="submitRejection"
        />
    </AuthenticatedLayout>
</template>
