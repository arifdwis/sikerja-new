<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Icon } from '@iconify/vue';
import { useToast } from 'vue-toastification';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import ControlBar from '../Permohonan/Components/ControlBar.vue';
import GridItem from '../Pembahasan/Components/PembahasanGridItem.vue';
import ListItem from '../Permohonan/Components/ListItem.vue';
import DetailModal from '../Permohonan/Components/DetailModal.vue';
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

const groupBy = ref(localStorage.getItem('validasiGroupBy') || 'latest');
watch(groupBy, (newVal) => localStorage.setItem('validasiGroupBy', newVal));

const groupedData = computed(() => {
    const data = props.datas?.data || [];
    if (groupBy.value === 'kategori') {
        return data.reduce((groups, item) => {
            const key = item.kategori?.label || 'Tanpa Kategori';
            (groups[key] = groups[key] || []).push(item);
            return groups;
        }, {});
    }

    if (groupBy.value === 'status') {
        return data.reduce((groups, item) => {
            const key = item.status_label?.label || 'Menunggu Validasi';
            (groups[key] = groups[key] || []).push(item);
            return groups;
        }, {});
    }

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

                <ControlBar
                    v-model:filterQuery="filterQuery"
                    v-model:viewMode="viewMode"
                    v-model:groupBy="groupBy"
                    searchPlaceholder="Cari permohonan..."
                    :showCreate="false"
                />

                <!-- Empty State -->
                <div v-if="!datas.data.length" class="bg-white dark:bg-gray-800 rounded-2xl p-16 text-center border-2 border-dashed border-gray-200 dark:border-gray-700">
                     <div class="w-24 h-24 bg-gray-50 dark:bg-gray-700/50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <Icon icon="solar:inbox-line-bold-duotone" class="w-12 h-12 text-gray-400" />
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

        <DetailModal v-model:visible="detailDialog" :loading="loadingDetail" :data="detailData">
            <template #footer>
                <div class="flex shrink-0 justify-end gap-3 border-t border-gray-200 bg-white p-4 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] dark:border-gray-700 dark:bg-gray-800">
                    <Button label="Tutup" severity="secondary" text @click="detailDialog = false" />
                    <Button label="Revisi" icon="pi pi-times" severity="danger" @click="openRevisi(detailData)" />
                    <Button label="Validasi Permohonan" icon="pi pi-check" severity="success" @click="openValidasi(detailData)" />
                </div>
            </template>
        </DetailModal>

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
