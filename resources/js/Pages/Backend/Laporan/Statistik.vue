<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { Icon } from '@iconify/vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Dropdown from 'primevue/dropdown';

const props = defineProps({
    summary: Object,
    akumulatifData: Array,
    rekapMitraData: Array,
    persentaseOpdData: Array,
    persentaseBidangData: Array,
    availableYears: Array,
    filters: Object,
});

const handlePrint = () => window.print();

const activeTab = ref('akumulatif');

const tabs = [
    { id: 'akumulatif', name: 'Akumulatif Tahunan', icon: 'solar:chart-square-bold-duotone',
      activeCls: 'text-slate-900 border-slate-900 bg-white dark:text-white dark:border-white font-semibold' },
    { id: 'rekap_mitra', name: 'Rekap Mitra', icon: 'solar:users-group-two-rounded-bold-duotone',
      activeCls: 'text-slate-900 border-slate-900 bg-white dark:text-white dark:border-white font-semibold' },
    { id: 'persentase_opd', name: 'Capaian OPD', icon: 'solar:pie-chart-2-bold-duotone',
      activeCls: 'text-slate-900 border-slate-900 bg-white dark:text-white dark:border-white font-semibold' },
    { id: 'persentase_bidang', name: 'Sebaran Bidang', icon: 'solar:graph-new-bold-duotone',
      activeCls: 'text-slate-900 border-slate-900 bg-white dark:text-white dark:border-white font-semibold' },
];

// KPI cards
const kpis = computed(() => [
    { key: 'total', label: 'Total Kerjasama', value: props.summary?.total ?? 0, icon: 'solar:document-add-bold-duotone' },
    { key: 'aktif', label: 'Sedang Aktif', value: props.summary?.aktif ?? 0, icon: 'solar:rocket-2-bold-duotone' },
    { key: 'selesai', label: 'Sudah Selesai', value: props.summary?.selesai ?? 0, icon: 'solar:check-circle-bold-duotone' },
    { key: 'tanpa_jangka', label: 'Periode Belum Tercatat', value: props.summary?.tanpa_jangka ?? 0, icon: 'solar:hourglass-line-bold-duotone' },
    { key: 'manual', label: 'Data Sistem Lama', value: props.summary?.manual ?? 0, icon: 'solar:archive-down-bold-duotone' },
    { key: 'mitra_unik', label: 'Mitra Unik', value: props.summary?.mitra_unik ?? 0, icon: 'solar:users-group-rounded-bold-duotone' },
]);

// Year filter
const yearOptions = computed(() => {
    const items = (props.availableYears || []).map((y) => ({ label: String(y), value: y }));
    return [{ label: 'Semua Tahun', value: null }, ...items];
});
const selectedYear = ref(props.filters?.tahun ? Number(props.filters.tahun) : null);
watch(selectedYear, (val) => {
    router.get(route('laporan.statistik'), { tahun: val || undefined }, { preserveState: true, preserveScroll: true, replace: true });
});

const formatPercent = (n) => `${(Number(n) || 0).toFixed(2)}%`;

const maxAkumulatifTotal = computed(() => Math.max(
    1,
    ...(props.akumulatifData || []).map((item) => Number(item.total_kerjasama) || 0),
));

const trendWidth = (value) => `${Math.max(6, ((Number(value) || 0) / maxAkumulatifTotal.value) * 100)}%`;
</script>

<style>
@media print {
    nav, aside, footer, header { display: none !important; }
    main { margin-left: 0 !important; padding: 0 !important; width: 100% !important; }
    button, .no-print, .print-hide { display: none !important; }
    body { background-color: white !important; }
    .print-only { display: block !important; }
}
</style>

<template>
    <Head title="Statistik & Laporan" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Statistik Laporan</h2>
        </template>

        <section class="py-8">
            <div class="mx-auto max-w-full px-6 lg:px-8">
                <Breadcrumb class="mb-6 print-hide" />

                <!-- HERO HEADER -->
                <div class="mb-5 flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm print-hide dark:border-gray-700 dark:bg-gray-800 lg:flex-row lg:items-center">
                    <div class="shrink-0 rounded-lg bg-slate-100 p-3 text-slate-700 dark:bg-gray-700 dark:text-white">
                        <Icon icon="solar:chart-2-bold-duotone" class="w-8 h-8" />
                    </div>
                    <div class="flex-1">
                        <h2 class="text-xl font-semibold leading-tight text-slate-900 dark:text-white">Statistik Kerjasama Daerah</h2>
                        <p class="mt-1 max-w-3xl text-sm text-slate-500 dark:text-gray-300">
                            Rekap akumulatif semua kerjasama (data sistem baru &amp; data sistem lama / manual), termasuk data dengan periode yang belum tercatat.
                        </p>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <Dropdown
                            v-model="selectedYear"
                            :options="yearOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Pilih Tahun"
                            class="min-w-[10rem] rounded-lg text-slate-800"
                            :pt="{ root: { class: 'h-10' } }"
                        />
                        <button @click="handlePrint" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-slate-900 px-4 text-sm font-medium text-white transition hover:bg-slate-700">
                            <Icon icon="solar:printer-bold" class="w-4 h-4" />
                            Cetak
                        </button>
                    </div>
                </div>

                <!-- KPI CARDS -->
                <div class="mb-5 grid grid-cols-2 gap-3 print-hide sm:grid-cols-3 lg:grid-cols-6">
                    <div
                        v-for="kpi in kpis"
                        :key="kpi.key"
                        class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800"
                    >
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-slate-100 p-2 text-slate-700 dark:bg-gray-700 dark:text-gray-100">
                                <Icon :icon="kpi.icon" class="h-5 w-5" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide truncate">{{ kpi.label }}</div>
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ kpi.value }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MAIN CARD WITH TABS -->
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <!-- Tabs -->
                    <div class="border-b border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/80 px-2 sm:px-4 pt-3 print-hide">
                        <ul class="flex flex-wrap -mb-px text-sm font-medium text-gray-500 dark:text-gray-400 gap-1">
                            <li v-for="tab in tabs" :key="tab.id">
                                <button
                                    @click="activeTab = tab.id"
                                    :class="[
                                        'inline-flex items-center justify-center px-4 py-2.5 border-b-2 rounded-t-lg group transition-all',
                                        activeTab === tab.id
                                            ? tab.activeCls
                                            : 'border-transparent hover:text-gray-700 hover:border-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50'
                                    ]"
                                >
                                    <Icon :icon="tab.icon" class="w-5 h-5 mr-2" />
                                    {{ tab.name }}
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="p-6 md:p-8">
                        <!-- AKUMULATIF -->
                        <div v-show="activeTab === 'akumulatif'">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-3">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Trend Kerjasama Tahunan</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total kerjasama, status aktif vs selesai, termasuk data dengan periode yang belum tercatat.</p>
                                </div>
                            </div>

                            <div v-if="akumulatifData.length" class="space-y-2">
                                <div
                                    v-for="item in akumulatifData"
                                    :key="item.tahun"
                                    class="grid gap-3 rounded-lg border border-slate-200 px-4 py-3 md:grid-cols-[84px_minmax(0,1fr)_auto] md:items-center"
                                >
                                    <div>
                                        <p class="font-mono text-sm font-semibold text-slate-900 dark:text-white">{{ item.tahun }}</p>
                                        <p class="text-xs text-slate-500">Tahun</p>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="mb-2 flex items-center justify-between gap-3">
                                            <span class="text-sm text-slate-500">Total kerjasama</span>
                                            <span class="text-base font-semibold text-slate-900 dark:text-white">{{ item.total_kerjasama }}</span>
                                        </div>
                                        <div class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-gray-700">
                                            <div class="h-full rounded-full bg-slate-800 transition-all dark:bg-slate-200" :style="{ width: trendWidth(item.total_kerjasama) }"></div>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-2 md:justify-end">
                                        <span class="inline-flex items-center gap-1 rounded bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                                            Aktif {{ item.aktif }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 rounded bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                                            Selesai {{ item.selesai }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 rounded bg-slate-900 px-2.5 py-1 text-xs font-medium text-white">
                                            Periode belum tercatat {{ item.tanpa_jangka }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="rounded-lg border border-dashed border-slate-200 py-10 text-center text-sm text-gray-500">
                                Belum ada data kerjasama yang sesuai filter ini.
                            </div>
                        </div>

                        <!-- REKAP MITRA -->
                        <div v-show="activeTab === 'rekap_mitra'">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-3">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Top 20 Mitra Kerjasama</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Institusi mitra dengan jumlah kerjasama terbanyak (gabungan sistem + manual).</p>
                                </div>
                            </div>

                            <DataTable :value="rekapMitraData" stripedRows class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden" tableStyle="min-width: 50rem">
                                <template #empty>
                                    <div class="py-10 text-center text-sm text-gray-500">Belum ada data mitra.</div>
                                </template>
                                <Column field="id" header="No" style="width: 60px"></Column>
                                <Column field="mitra" header="Nama Mitra" sortable>
                                    <template #body="{ data }">
                                        <span class="font-medium text-gray-800 dark:text-gray-100">{{ data.mitra }}</span>
                                    </template>
                                </Column>
                                <Column header="Sumber Data" style="width: 220px">
                                    <template #body="{ data }">
                                        <div class="flex items-center gap-2">
                                            <Tag v-if="data.sistem > 0" severity="info" :value="`Sistem: ${data.sistem}`" class="text-xs" />
                                            <Tag v-if="data.manual > 0" severity="secondary" :value="`Manual: ${data.manual}`" class="text-xs" />
                                        </div>
                                    </template>
                                </Column>
                                <Column field="total_kerjasama" header="Total" sortable style="width: 120px">
                                    <template #body="{ data }">
                                        <span class="font-bold text-gray-900 dark:text-gray-100 text-base">{{ data.total_kerjasama }}</span>
                                    </template>
                                </Column>
                            </DataTable>
                        </div>

                        <!-- PERSENTASE OPD -->
                        <div v-show="activeTab === 'persentase_opd'">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-3">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Capaian Kerjasama Per Perangkat Daerah</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Distribusi kerjasama per OPD (1 kerjasama bisa melibatkan beberapa OPD).</p>
                                </div>
                            </div>

                            <DataTable :value="persentaseOpdData" stripedRows class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden" tableStyle="min-width: 50rem">
                                <template #empty>
                                    <div class="py-10 text-center text-sm text-gray-500">Belum ada data OPD.</div>
                                </template>
                                <Column field="opd" header="Perangkat Daerah" sortable>
                                    <template #body="{ data }">
                                        <span class="font-medium text-gray-800 dark:text-gray-100">{{ data.opd }}</span>
                                    </template>
                                </Column>
                                <Column field="realisasi" header="Jumlah" sortable style="width: 120px"></Column>
                                <Column field="persentase" header="Capaian" sortable>
                                    <template #body="{ data }">
                                        <div class="flex items-center gap-3">
                                            <div class="h-2.5 max-w-[280px] flex-1 overflow-hidden rounded-full border border-gray-200 bg-gray-100 dark:border-gray-600 dark:bg-gray-700/50">
                                                <div class="h-full rounded-full bg-slate-700 transition-all" :style="{ width: data.persentase + '%' }"></div>
                                            </div>
                                            <span class="w-16 text-right text-sm font-bold text-slate-700 dark:text-slate-200">{{ formatPercent(data.persentase) }}</span>
                                        </div>
                                    </template>
                                </Column>
                            </DataTable>
                        </div>

                        <!-- PERSENTASE BIDANG -->
                        <div v-show="activeTab === 'persentase_bidang'">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-3">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Sebaran Kerjasama per Bidang</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Distribusi kerjasama berdasarkan kategori/bidang.</p>
                                </div>
                            </div>

                            <DataTable :value="persentaseBidangData" stripedRows class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden" tableStyle="min-width: 50rem">
                                <template #empty>
                                    <div class="py-10 text-center text-sm text-gray-500">Belum ada data bidang.</div>
                                </template>
                                <Column field="bidang" header="Bidang" sortable>
                                    <template #body="{ data }">
                                        <span class="font-medium text-gray-800 dark:text-gray-100">{{ data.bidang }}</span>
                                    </template>
                                </Column>
                                <Column header="Sumber" style="width: 220px">
                                    <template #body="{ data }">
                                        <div class="flex items-center gap-2">
                                            <Tag v-if="data.sistem > 0" severity="info" :value="`Sistem: ${data.sistem}`" class="text-xs" />
                                            <Tag v-if="data.manual > 0" severity="secondary" :value="`Manual: ${data.manual}`" class="text-xs" />
                                        </div>
                                    </template>
                                </Column>
                                <Column field="jumlah" header="Jumlah" sortable style="width: 120px">
                                    <template #body="{ data }">
                                        <span class="font-bold text-gray-900 dark:text-white text-base">{{ data.jumlah }}</span>
                                    </template>
                                </Column>
                                <Column field="persentase" header="Persentase" sortable>
                                    <template #body="{ data }">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-1 max-w-[240px] bg-gray-100 dark:bg-gray-700/50 rounded-full h-2.5 border border-gray-200 dark:border-gray-600 overflow-hidden">
                                                <div class="h-full rounded-full bg-slate-700 transition-all" :style="{ width: data.persentase + '%' }"></div>
                                            </div>
                                            <span class="w-16 text-right text-sm font-bold text-slate-700 dark:text-slate-200">{{ formatPercent(data.persentase) }}</span>
                                        </div>
                                    </template>
                                </Column>
                            </DataTable>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
