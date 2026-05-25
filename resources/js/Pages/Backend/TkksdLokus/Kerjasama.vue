<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Icon } from '@iconify/vue';
import axios from 'axios';
import ControlBar from '../Permohonan/Components/ControlBar.vue';
import PermohonanList from '../Permohonan/Components/PermohonanList.vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import DetailModal from '../Permohonan/Components/DetailModal.vue';

const props = defineProps({
    permohonan: Object,
    tipe: {
        type: String,
        default: 'aktif',
    },
    counts: {
        type: Object,
        default: () => ({ aktif: 0, selesai: 0 }),
    },
    share: Object,
    filters: Object,
});

const page = usePage();

const filterQuery = ref(props.filters?.search || '');
const filteredData = computed(() => {
    if (!filterQuery.value) return props.permohonan?.data || [];
    const query = filterQuery.value.toLowerCase();
    return (props.permohonan?.data || []).filter(item =>
        item.label?.toLowerCase().includes(query) ||
        item.kode?.toLowerCase().includes(query) ||
        item.nomor_permohonan?.toLowerCase().includes(query) ||
        item.nama_instansi?.toLowerCase().includes(query)
    );
});

const viewMode = ref(localStorage.getItem('tkksdLokusViewMode') || 'grid');
watch(viewMode, (v) => localStorage.setItem('tkksdLokusViewMode', v));

const groupBy = ref(localStorage.getItem('tkksdLokusGroupBy') || 'latest');
watch(groupBy, (v) => localStorage.setItem('tkksdLokusGroupBy', v));

// Detail modal state
const detailDialog = ref(false);
const selectedItem = ref(null);
const detailData = ref(null);
const loadingDetail = ref(false);

const openDetailModal = async (item) => {
    selectedItem.value = item;
    detailDialog.value = true;
    loadingDetail.value = true;
    try {
        const res = await axios.get(route('permohonan.show', item.uuid), {
            params: { _: Date.now() },
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        detailData.value = res.data;
    } catch (e) {
        console.error('Gagal memuat detail kerjasama', e);
    } finally {
        loadingDetail.value = false;
    }
};

const refreshDetail = () => {
    if (selectedItem.value) openDetailModal(selectedItem.value);
};

// Auto-open detail bila URL berisi ?detail=uuid (dipakai Dashboard TKKSD Lokus)
onMounted(() => {
    const detailUuid = props.filters?.detail;
    if (detailUuid) {
        openDetailModal({ uuid: detailUuid });
    }
});

// TKKSD Lokus selalu read-only
const isAdmin = computed(() => false);

const groupedData = computed(() => {
    const data = filteredData.value;
    const fallbackTitle = props.tipe === 'aktif' ? 'Kerjasama Aktif' : 'Kerjasama Selesai';

    if (groupBy.value === 'latest') {
        return { [fallbackTitle]: data };
    } else if (groupBy.value === 'kategori') {
        return data.reduce((acc, item) => {
            const key = item.kategori?.label || 'Tanpa Kategori';
            (acc[key] = acc[key] || []).push(item);
            return acc;
        }, {});
    } else if (groupBy.value === 'status') {
        return data.reduce((acc, item) => {
            const key = item.status_label?.label || 'Unknown';
            (acc[key] = acc[key] || []).push(item);
            return acc;
        }, {});
    }
    return { [fallbackTitle]: data };
});

const tabs = [
    {
        key: 'aktif',
        label: 'Kerjasama Aktif',
        sub: 'Sedang berjalan',
        icon: 'solar:rocket-bold-duotone',
        accentActive: 'bg-teal-50 text-teal-700 border-teal-500',
        accentBadge: 'bg-teal-600 text-white',
    },
    {
        key: 'selesai',
        label: 'Kerjasama Selesai',
        sub: 'Sudah berakhir',
        icon: 'solar:check-circle-bold-duotone',
        accentActive: 'bg-emerald-50 text-emerald-700 border-emerald-500',
        accentBadge: 'bg-emerald-600 text-white',
    },
];

const switchTab = (tabKey) => {
    if (tabKey === props.tipe) return;
    router.get(
        route('tkksd-lokus.kerjasama', tabKey),
        { search: filterQuery.value || undefined },
        { preserveState: true, preserveScroll: true, replace: true }
    );
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
                <Breadcrumb class="mb-6" />

                <!-- Tab Bar: Aktif / Selesai -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-2 mb-6 shadow-sm">
                    <div class="flex flex-col sm:flex-row gap-2">
                        <button
                            v-for="tab in tabs"
                            :key="tab.key"
                            type="button"
                            @click="switchTab(tab.key)"
                            :class="[
                                'flex-1 flex items-center justify-between gap-3 px-4 py-3 rounded-lg border-2 transition-all text-left',
                                tipe === tab.key
                                    ? tab.accentActive + ' shadow-sm'
                                    : 'bg-gray-50 text-gray-600 border-transparent hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300'
                            ]"
                        >
                            <div class="flex items-center gap-3">
                                <Icon :icon="tab.icon" class="w-6 h-6 shrink-0" />
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold leading-tight">{{ tab.label }}</span>
                                    <span class="text-xs opacity-70 leading-tight">{{ tab.sub }}</span>
                                </div>
                            </div>
                            <span
                                :class="[
                                    'inline-flex items-center justify-center min-w-[2rem] px-2 py-0.5 rounded-full text-xs font-bold',
                                    tipe === tab.key ? tab.accentBadge : 'bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-200'
                                ]"
                            >
                                {{ counts[tab.key] ?? 0 }}
                            </span>
                        </button>
                    </div>
                </div>

                <ControlBar
                    v-model:filterQuery="filterQuery"
                    v-model:viewMode="viewMode"
                    v-model:groupBy="groupBy"
                    :showCreate="false"
                />

                <PermohonanList
                    :groupedData="groupedData"
                    :viewMode="viewMode"
                    :isAdmin="isAdmin"
                    :hasData="filteredData.length > 0"
                    :showCreate="false"
                    :emptyTitle="tipe === 'aktif' ? 'Belum ada Kerjasama Aktif' : 'Belum ada Kerjasama Selesai'"
                    :emptyDescription="tipe === 'aktif'
                        ? 'OPD Anda belum memiliki kerjasama yang sedang berjalan saat ini.'
                        : 'OPD Anda belum memiliki kerjasama yang sudah berakhir.'"
                    @detail="openDetailModal"
                />
            </div>
        </section>

        <!-- Detail Modal — read-only untuk TKKSD Lokus -->
        <DetailModal
            :visible="detailDialog"
            @update:visible="detailDialog = $event"
            :loading="loadingDetail"
            :data="detailData"
            :isAdmin="false"
            @refresh="refreshDetail"
        />
    </AuthenticatedLayout>
</template>
