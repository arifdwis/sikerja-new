<script setup>
import { ref, computed, watch } from 'vue';
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
import GridItem from '../Pembahasan/Components/PembahasanGridItem.vue';
import ListItem from '../Permohonan/Components/ListItem.vue';
import DetailHeader from '../Permohonan/Components/Detail/DetailHeader.vue';
import DetailParties from '../Permohonan/Components/Detail/DetailParties.vue';
import DetailSubstance from '../Permohonan/Components/Detail/DetailSubstance.vue';
import DetailDocuments from '../Permohonan/Components/Detail/DetailDocuments.vue';
import DetailContact from '../Permohonan/Components/Detail/DetailContact.vue';
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

// Grouped data (like Pembahasan)
const groupedData = computed(() => {
    const data = props.datas?.data || [];
    return { 'Daftar Validasi': data };
});

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

                <!-- Content (reusing Pembahasan layout) -->
                <div v-else v-for="(items, groupTitle) in groupedData" :key="groupTitle" class="mb-10">
                    <div class="flex justify-between gap-2 mb-4">
                        <div class="border-l-4 border-blue-600 pl-2">
                            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 uppercase">{{ groupTitle }}</h3>
                        </div>
                        <span class="text-base font-semibold text-gray-800 dark:text-gray-200">{{ items.length }} Permohonan</span>
                    </div>

                    <div v-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <GridItem 
                            v-for="item in items" 
                            :key="item.id" 
                            :item="item"
                            @detail="openDetailModal($event)"
                        />
                    </div>

                    <div v-else class="space-y-3">
                        <ListItem 
                            v-for="item in items" 
                            :key="item.id" 
                            :item="item"
                            @detail="openDetailModal($event)"
                        />
                    </div>
                </div>
            </div>
        </section>

        <!-- Detail Modal (same as Permohonan detail, with sticky action footer) -->
        <Dialog v-model:visible="detailDialog" modal header="Detail Lengkap Permohonan" :style="{ width: '1100px' }" :breakpoints="{ '1199px': '95vw' }" maximizable :contentStyle="{ overflow: 'hidden', padding: 0, display: 'flex', flexDirection: 'column', height: '80vh' }">
             <div v-if="loadingDetail" class="space-y-6 p-6">
                <div class="grid grid-cols-2 gap-6">
                    <Skeleton height="15rem" class="w-full rounded-xl" />
                    <Skeleton height="15rem" class="w-full rounded-xl" />
                </div>
                <Skeleton height="25rem" class="w-full rounded-xl" />
             </div>
             <div v-else-if="detailData" class="flex flex-col h-full">
                <!-- Scrollable Content -->
                <div class="flex-1 overflow-y-auto p-6 space-y-8 bg-gray-50/50 dark:bg-gray-900/50">
                    <DetailHeader :data="detailData" />
                    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                        <div class="xl:col-span-2 space-y-8">
                            <DetailParties :data="detailData" />
                            <DetailSubstance :data="detailData" />
                        </div>
                        <div class="space-y-6">
                            <DetailDocuments :data="detailData" />
                            <DetailContact :data="detailData" />
                        </div>
                    </div>
                </div>
                <!-- Action Footer (sticky, always visible) -->
                <div class="shrink-0 bg-white dark:bg-gray-800 p-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
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
