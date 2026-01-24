<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Icon } from '@iconify/vue';
import { useToast } from 'vue-toastification';
import Dialog from 'primevue/dialog';
import Tag from 'primevue/tag';
import Skeleton from 'primevue/skeleton';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import axios from 'axios';

const props = defineProps({
    datas: Object,
    share: Object,
    filters: Object,
});

const toast = useToast();

// Pagination & Search
const filterQuery = ref(props.filters?.search || '');
let searchTimeout;
watch(filterQuery, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.visit(route(props.share.prefix + '.index'), {
            data: { search: val },
            preserveState: true,
            preserveScroll: true,
            only: ['datas']
        });
    }, 500);
});

// View mode
const viewMode = ref(localStorage.getItem('validasiViewMode') || 'grid');
watch(viewMode, (newVal) => localStorage.setItem('validasiViewMode', newVal));

// Dialog States
const detailDialog = ref(false);
const validasiDialog = ref(false);
const revisiDialog = ref(false);
const selectedItem = ref(null);
const detailData = ref(null);
const loadingDetail = ref(false);
const processing = ref(false);
const keterangan = ref('');

// Date Helper
const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    }).toUpperCase();
};

const diffForHumans = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffDays = Math.floor(diffMs / 86400000);
    return `${diffDays} hari yang lalu`;
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

const openValidasi = (item) => {
    selectedItem.value = item;
    validasiDialog.value = true;
};

const openRevisi = (item) => {
    selectedItem.value = item;
    keterangan.value = '';
    revisiDialog.value = true;
};

const submitValidasi = () => {
    processing.value = true;
    router.put(route(`${props.share.prefix}.update`, selectedItem.value.uuid), {
        status: 1 
    }, {
        onSuccess: () => {
            validasiDialog.value = false;
            detailDialog.value = false;
            processing.value = false;
            toast.success('Permohonan berhasil divalidasi');
        },
        onError: () => {
             processing.value = false;
             toast.error('Gagal memvalidasi permohonan');
        }
    });
};

const submitRevisi = () => {
    processing.value = true;
    router.put(route(`${props.share.prefix}.update`, selectedItem.value.uuid), {
        status: 99,
        keterangan: keterangan.value
    }, {
        onSuccess: () => {
            revisiDialog.value = false;
            detailDialog.value = false;
            processing.value = false;
            toast.warning('Permohonan dikembalikan untuk revisi');
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
                        <div class="relative w-full sm:w-80">
                            <Icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <input v-model="filterQuery" type="text" placeholder="Cari permohonan..." class="pl-10 pr-4 py-2.5 w-full border border-gray-300 focus:border-cyan-500 rounded-lg text-sm dark:bg-gray-700" />
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="bg-gray-100 dark:bg-gray-700 p-1 rounded-lg border border-gray-200 dark:border-gray-600">
                                <button @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-white shadow-sm text-cyan-600' : 'text-gray-500'" class="p-2 rounded-md transition-all">
                                    <Icon icon="lucide:layout-grid" class="w-4 h-4" />
                                </button>
                                <button @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-white shadow-sm text-cyan-600' : 'text-gray-500'" class="p-2 rounded-md transition-all">
                                    <Icon icon="lucide:list" class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!datas.data.length" class="bg-white dark:bg-gray-800 rounded-2xl p-16 text-center border-2 border-dashed border-gray-200 dark:border-gray-700">
                     <div class="w-24 h-24 bg-cyan-50 dark:bg-cyan-900/20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <Icon icon="solar:inbox-line-bold-duotone" class="w-12 h-12 text-cyan-400" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Tidak ada Permohonan</h3>
                    <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">Belum ada permohonan baru yang masuk untuk divalidasi.</p>
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
                                    <span class="bg-cyan-100 text-cyan-700 px-2 py-1 text-xs font-bold rounded border border-cyan-200 uppercase tracking-wide flex items-center gap-1">
                                        <Icon icon="solar:hourglass-bold" /> MENUNGGU VALIDASI
                                    </span>
                                </div>
                                <div class="mb-2 text-xs text-gray-400 font-mono">
                                    {{ item.nomor_permohonan || item.kode }} • {{ formatDate(item.created_at) }}
                                </div>
                                <h4 @click="openDetailModal(item)" class="font-bold text-gray-900 dark:text-white mb-2 line-clamp-2 hover:text-cyan-600 transition-colors cursor-pointer">
                                    {{ item.label }}
                                </h4>
                                <div class="text-sm text-gray-500 mb-4 flex items-center gap-2">
                                     <Icon icon="solar:buildings-bold" class="w-4 h-4" /> {{ item.nama_instansi }}
                                </div>
                                <div class="mt-auto pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center gap-2">
                                    <button @click="openDetailModal(item)" class="flex-1 py-2 text-xs font-bold text-gray-600 hover:text-cyan-600 bg-gray-50 hover:bg-cyan-50 rounded flex items-center justify-center gap-1 transition-colors">
                                        <Icon icon="solar:eye-linear" /> Detail
                                    </button>
                                    <button @click="openRevisi(item)" class="p-2 text-red-600 bg-red-50 hover:bg-red-100 rounded transition-colors" v-tooltip="'Revisi'">
                                        <Icon icon="solar:close-circle-bold" class="w-4 h-4" />
                                    </button>
                                    <button @click="openValidasi(item)" class="p-2 text-green-600 bg-green-50 hover:bg-green-100 rounded transition-colors" v-tooltip="'Validasi'">
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
                                    <span class="bg-cyan-100 text-cyan-700 px-2 py-0.5 text-[10px] font-bold rounded border border-cyan-200 uppercase">MENUNGGU VALIDASI</span>
                                </div>
                                <h4 @click="openDetailModal(item)" class="font-semibold text-gray-950 dark:text-white hover:text-cyan-600 cursor-pointer transition-colors capitalize text-lg">
                                    {{ item.label }}
                                </h4>
                                <div class="text-sm text-gray-500 mt-1 flex gap-2 items-center">
                                     <span>{{ item.nama_instansi }}</span> 
                                     <span class="text-gray-300">•</span>
                                     <span>{{ formatDate(item.created_at) }}</span>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <Button icon="pi pi-eye" severity="secondary" @click="openDetailModal(item)" />
                                <Button icon="pi pi-times" severity="danger" @click="openRevisi(item)" />
                                <Button icon="pi pi-check" severity="success" @click="openValidasi(item)" />
                            </div>
                         </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Detail Modal (Teal Theme) -->
        <Dialog v-model:visible="detailDialog" modal header="Detail Permohonan" :style="{ width: '1100px' }" :breakpoints="{ '1199px': '95vw' }" maximizable class="p-0 overflow-hidden">
             <div v-if="loadingDetail" class="p-6 space-y-4"><Skeleton height="20rem" /></div>
             <div v-else-if="detailData" class="flex flex-col h-[85vh]">
                <div class="flex-1 overflow-y-auto p-6 space-y-8 bg-gray-50/50 dark:bg-gray-900/50">
                    <!-- Header -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-cyan-200 dark:border-cyan-900 shadow-sm relative overflow-hidden">
                         <div class="absolute top-0 right-0 w-64 h-64 bg-cyan-50/50 dark:bg-cyan-900/20 rounded-bl-full -mr-16 -mt-16 pointer-events-none"></div>
                         <div class="relative z-10">
                            <div class="flex items-center gap-2 mb-2">
                                <Tag :value="detailData.kategori?.label" severity="info" class="text-xs px-2 py-1" />
                                <span class="text-xs font-mono text-gray-500 border border-gray-200 rounded px-1.5 py-0.5">{{ detailData.nomor_permohonan || detailData.kode }}</span>
                            </div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ detailData.label }}</h1>
                            <p class="text-gray-500 flex items-center gap-2">
                                <Icon icon="solar:calendar-date-bold" class="w-4 h-4" /> Diajukan pada {{ formatDate(detailData.created_at) }}
                            </p>
                         </div>
                    </div>

                    <!-- Layout -->
                    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                        <div class="xl:col-span-2 space-y-6">
                            <!-- Pihak 1 -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-cyan-200 dark:border-cyan-700 shadow-sm relative overflow-hidden">
                                <div class="absolute top-0 left-0 w-1 h-full bg-cyan-500"></div>
                                <h3 class="text-sm font-bold text-cyan-600 uppercase tracking-widest mb-4 flex items-center gap-2"><Icon icon="solar:user-circle-bold" /> Pihak Kesatu (Pemohon)</h3>
                                <div class="space-y-2">
                                    <p class="font-bold text-lg text-gray-900">{{ detailData.nama_instansi }}</p>
                                    <p class="text-gray-600" v-if="detailData.pemohon1">{{ detailData.pemohon1.name }} ({{ detailData.pemohon1.jabatan }})</p>
                                    <p class="text-sm text-gray-500">{{ detailData.alamat }}</p>
                                </div>
                            </div>
                             <!-- Substansi -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 shadow-sm">
                                <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2"><Icon icon="solar:document-text-bold" class="text-cyan-600" /> Substansi Kerjasama</h3>
                                <div class="space-y-4 text-sm text-gray-700">
                                    <div><span class="font-bold block mb-1">Latar Belakang:</span> {{ detailData.latar_belakang }}</div>
                                    <div><span class="font-bold block mb-1">Maksud & Tujuan:</span> {{ detailData.maksud_tujuan }}</div>
                                    <div><span class="font-bold block mb-1">Ruang Lingkup:</span> {{ detailData.ruang_lingkup }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-6">
                            <!-- Files -->
                            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                                <h5 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Berkas</h5>
                                <div v-if="detailData.files?.length" class="space-y-2">
                                    <div v-for="file in detailData.files" :key="file.id" class="border p-3 rounded-lg flex items-center gap-3 hover:bg-cyan-50 hover:border-cyan-200 transition">
                                        <div class="w-8 h-8 rounded bg-cyan-100 text-cyan-600 flex items-center justify-center"><Icon icon="solar:file-text-bold" /></div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold truncate">{{ file.label }}</p>
                                            <a :href="file.file_url" target="_blank" class="text-xs text-blue-600 hover:underline">Download</a>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-center text-sm text-gray-400">Tidak ada berkas</div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Action Footer -->
                <div class="bg-white dark:bg-gray-800 p-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3 rounded-b-xl z-20 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)]">
                    <Button label="Tutup" severity="secondary" text @click="detailDialog = false" />
                    <Button label="Revisi" icon="pi pi-times" severity="danger" @click="openRevisi(detailData)" />
                    <Button label="Validasi Permohonan" icon="pi pi-check" severity="success" @click="openValidasi(detailData)" />
                </div>
            </div>
        </Dialog>

         <!-- Dialog Validasi -->
        <Dialog v-model:visible="validasiDialog" modal header="Konfirmasi Validasi" :style="{ width: '400px' }">
            <div class="text-center p-4">
                <i class="pi pi-check-circle text-green-500 text-5xl mb-4"></i>
                <p class="mb-4">Validasi permohonan ini dan lanjut ke tahap Pembahasan?</p>
                <div class="font-semibold text-gray-700 mb-6">{{ selectedItem?.label }}</div>
                <div class="flex justify-center gap-2">
                    <Button label="Batal" text @click="validasiDialog = false" />
                    <Button label="Ya, Validasi" severity="success" @click="submitValidasi" :loading="processing" />
                </div>
            </div>
        </Dialog>

        <!-- Dialog Revisi -->
        <Dialog v-model:visible="revisiDialog" modal header="Kembalikan untuk Revisi" :style="{ width: '500px' }">
            <div class="p-2">
                <p class="mb-4 text-gray-600">Berikan catatan revisi untuk pemohon.</p>
                <div class="mb-4">
                    <Textarea v-model="keterangan" rows="4" class="w-full border-gray-300 rounded-md" placeholder="Catatan revisi..." />
                </div>
                <div class="flex justify-end gap-2">
                    <Button label="Batal" text @click="revisiDialog = false" />
                    <Button label="Kirim Revisi" severity="danger" @click="submitRevisi" :loading="processing" />
                </div>
            </div>
        </Dialog>
    </AuthenticatedLayout>
</template>
