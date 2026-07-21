<script setup>
import { ref, watch, computed, onMounted } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import { Icon } from '@iconify/vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import Calendar from 'primevue/calendar';
import FileUpload from 'primevue/fileupload';
import Tag from 'primevue/tag';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import { useToast } from 'vue-toastification';

const props = defineProps({
    datas: Object,
    pendingPermohonans: Array,
    share: Object,
    filters: Object,
    isAdmin: Boolean,
    isTkksdLokus: Boolean,
    canCreateMonev: Boolean,
    adminGrouped: { type: Boolean, default: false },
});
const toast = useToast();

// Filter/Search
const filterQuery = ref(props.filters?.search || '');
let searchTimeout;

const applyFilters = () => {
    router.visit(route('monev.index'), {
        data: { search: filterQuery.value },
        preserveState: true,
        preserveScroll: true,
        only: ['datas', 'pendingPermohonans']
    });
};

watch(filterQuery, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
});

const questions = [
    { key: 'kesesuaian_tujuan', label: 'Kesesuaian dengan tujuan', section: 'Pelaksanaan' },
    { key: 'ketepatan_waktu', label: 'Ketepatan waktu', section: 'Pelaksanaan' },
    { key: 'kontribusi_mitra', label: 'Kontribusi mitra', section: 'Pelaksanaan' },
    { key: 'tingkat_koordinasi', label: 'Tingkat koordinasi', section: 'Pelaksanaan' },
    { key: 'capaian_indikator', label: 'Capaian indikator', section: 'Capaian' },
    { key: 'dampak_pelaksanaan', label: 'Dampak pelaksanaan', section: 'Capaian' },
    { key: 'inovasi_manfaat', label: 'Inovasi & manfaat', section: 'Capaian' },
    { key: 'kelengkapan_dokumen', label: 'Kelengkapan dokumen', section: 'Administrasi' },
    { key: 'pelaporan_berkala', label: 'Pelaporan berkala', section: 'Administrasi' },
    { key: 'relevansi_kebutuhan', label: 'Relevansi kebutuhan', section: 'Rekomendasi' },
    { key: 'rekomendasi_lanjutan', label: 'Rekomendasi lanjutan', section: 'Rekomendasi' },
];

const pemohonQuestions = [
    { key: 'pmh_realisasi_kegiatan', label: 'Realisasi kegiatan sesuai rencana' },
    { key: 'pmh_kesesuaian_output', label: 'Kesesuaian output terhadap target' },
    { key: 'pmh_keberlanjutan', label: 'Usulan keberlanjutan' },
];

const tkksdQuestions = [
    { key: 'tkl_kepatuhan_pks', label: 'Kepatuhan terhadap PKS' },
    { key: 'tkl_koordinasi_mitra', label: 'Kualitas koordinasi OPD–mitra' },
    { key: 'tkl_kesesuaian_anggaran', label: 'Kesesuaian realisasi vs anggaran' },
    { key: 'tkl_rekomendasi_lokus', label: 'Rekomendasi TKKSD Lokus' },
];

const adminOptions = {
    kesesuaian_tujuan: ['Ya seluruhnya', 'Sebagian', 'Tidak'],
    ketepatan_waktu: ['Tepat waktu', 'Terlambat', 'Tidak terlaksana'],
    kontribusi_mitra: ['Ya sepenuhnya', 'Sebagian', 'Tidak'],
    tingkat_koordinasi: ['Sangat baik', 'Baik', 'Cukup', 'Kurang'],
    capaian_indikator: ['Tercapai seluruhnya', 'Sebagian', 'Tidak'],
    dampak_pelaksanaan: ['Sangat berdampak', 'Cukup', 'Kurang'],
    inovasi_manfaat: ['Ya signifikan', 'Ada', 'Tidak'],
    kelengkapan_dokumen: ['Lengkap', 'Sebagian', 'Tidak'],
    pelaporan_berkala: ['Rutin', 'Kadang', 'Tidak'],
    relevansi_kebutuhan: ['Sangat relevan', 'Cukup', 'Tidak'],
    rekomendasi_lanjutan: ['Dilanjutkan', 'Diperluas', 'Dihentikan'],
};

const adminGroupedQuestions = computed(() => questions.reduce((groups, q) => {
    if (!groups[q.section]) groups[q.section] = [];
    groups[q.section].push(q);
    return groups;
}, {}));

const roleFormType = computed(() => (props.isAdmin ? 'admin' : (props.isTkksdLokus ? 'tkksd_lokus' : 'pemohon')));
const roleFormTitle = computed(() => {
    if (roleFormType.value === 'admin') return 'Form Monev Admin';
    if (roleFormType.value === 'tkksd_lokus') return 'Form Monev TKKSD Lokus';
    return 'Form Monev Pemohon';
});

const createDialog = ref(false);
const selectedPermohonan = ref(null);
const createForm = useForm({
    id_permohonan: null,
    tanggal_evaluasi: new Date(),
    kesesuaian_tujuan: null,
    ketepatan_waktu: null,
    kontribusi_mitra: null,
    tingkat_koordinasi: null,
    capaian_indikator: null,
    dampak_pelaksanaan: null,
    inovasi_manfaat: null,
    kelengkapan_dokumen: null,
    pelaporan_berkala: null,
    kendala_administrasi: '',
    relevansi_kebutuhan: null,
    rekomendasi_lanjutan: null,
    saran_rekomendasi: '',
    rating: null,
    file_bukti: null,
    pmh_realisasi_kegiatan: null,
    pmh_kesesuaian_output: null,
    pmh_pemanfaatan_hasil: '',
    pmh_kendala_lapangan: '',
    pmh_keberlanjutan: null,
    pmh_file_laporan: null,
    tkl_kepatuhan_pks: null,
    tkl_koordinasi_mitra: null,
    tkl_kesesuaian_anggaran: null,
    tkl_temuan_pengawasan: '',
    tkl_rekomendasi_lokus: null,
    tkl_catatan: '',
});

const openCreateModal = (permohonan) => {
    selectedPermohonan.value = permohonan;
    createForm.reset();
    createForm.clearErrors();
    createForm.id_permohonan = permohonan.id;
    createForm.tanggal_evaluasi = new Date();
    createDialog.value = true;
};

const submitCreateForm = () => {
    createForm.post(route('monev.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            createDialog.value = false;
            createForm.reset();
        }
    });
};

const onAdminFileSelect = (event) => {
    createForm.file_bukti = event.files?.[0] || null;
};

const onPemohonFileSelect = (event) => {
    createForm.pmh_file_laporan = event.files?.[0] || null;
};

// Detail Modal
const detailDialog = ref(false);
const selectedMonev = ref(null);

const openDetailModal = async (monev) => {
    // Bila monev sudah lengkap (data dari list), pakai langsung; kalau hanya { uuid }, fetch detail
    if (monev?.kode_monev) {
        selectedMonev.value = monev;
        detailDialog.value = true;
        return;
    }

    if (!monev?.uuid) return;
    detailDialog.value = true;
    try {
        const res = await axios.get(route('monev.show', monev.uuid), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        });
        selectedMonev.value = res.data;
    } catch (e) {
        console.error('Gagal memuat detail monev', e);
        detailDialog.value = false;
    }
};

// Auto-open detail modal jika URL berisi ?detail={uuid}
onMounted(() => {
    if (props.filters?.detail) {
        openDetailModal({ uuid: props.filters.detail });
    }
});

// === ADMIN GROUPED VIEW ===
const groupedDialog = ref(false);
const selectedGrouped = ref(null);

const openGroupedDetailModal = (row) => {
    selectedGrouped.value = row;
    groupedDialog.value = true;
};

const roleLabel = (role) => ({
    pemohon: 'Pemohon',
    tkksd_lokus: 'TKKSD Lokus',
    admin: 'Admin',
}[role] || role);

const roleSeverity = () => 'secondary';

// UUID monev terbaru (untuk endpoint notify-pemohon)
const latestMonevUuid = computed(() => {
    if (!selectedGrouped.value?.monevs?.length) return null;
    // Ambil monev terbaru — prioritaskan submitter admin, jika tidak ada ambil terakhir
    const list = [...selectedGrouped.value.monevs];
    const adminMonev = list.find(m => m.submitter_role === 'admin');
    return (adminMonev || list[list.length - 1])?.uuid;
});

// 3 role yang diharapkan untuk setiap monev kerjasama
const expectedRoles = ['pemohon', 'tkksd_lokus', 'admin'];

const submittedRoles = computed(() => {
    return new Set((selectedGrouped.value?.submitter_roles || []).map(r => r));
});

const hasSubmitted = (role) => submittedRoles.value.has(role);

// Daftar nama OPD yang terlibat dalam kerjasama
const opdNames = computed(() => {
    const opds = selectedGrouped.value?.permohonan?.opds || [];
    if (!opds.length) return '';
    return opds.map(o => o.singkatan || o.nama).join(', ');
});

const sendGroupedNotification = () => {
    if (!latestMonevUuid.value) return;
    router.post(route('monev.notify-pemohon', latestMonevUuid.value), {}, {
        preserveScroll: true,
        onSuccess: () => toast.success('Notifikasi hasil Monev dikirim ke pemohon.'),
        onError: () => toast.error('Notifikasi hasil Monev gagal dikirim.'),
    });
};

const sendMonevNotification = () => {
    if (!selectedMonev.value?.uuid) return;

    router.post(route('monev.notify-pemohon', selectedMonev.value.uuid), {}, {
        preserveScroll: true,
        onSuccess: () => toast.success('Notifikasi hasil Monev dikirim ke pemohon.'),
        onError: () => toast.error('Notifikasi hasil Monev gagal dikirim.'),
    });
};

const getElegantAnswerColor = (answer) => {
    const positives = ['Ya seluruhnya', 'Ya sepenuhnya', 'Tepat waktu', 'Sangat baik', 'Tercapai seluruhnya', 'Sangat berdampak', 'Ya signifikan', 'Lengkap', 'Rutin', 'Sangat relevan', 'Dilanjutkan'];
    const neutrals = ['Sebagian', 'Baik', 'Cukup', 'Ada', 'Kadang', 'Diperluas'];
    
    if (positives.includes(answer)) return 'text-emerald-700';
    if (neutrals.includes(answer)) return 'text-amber-700';
    return 'text-rose-700';
};

const getQuestionsForRole = (role) => {
    if (role === 'pemohon') return pemohonQuestions;
    if (role === 'tkksd_lokus') return tkksdQuestions;
    return questions;
};

const getQuestionGridClass = (role) => {
    return role === 'admin' ? 'md:grid-cols-2' : 'md:grid-cols-1';
};

const getSummaryPelaksanaan = (data) => {
    if (!data) return '-';
    if (data.submitter_role === 'pemohon') return data.pmh_realisasi_kegiatan || '-';
    if (data.submitter_role === 'tkksd_lokus') return data.tkl_kepatuhan_pks || '-';
    return data.kesesuaian_tujuan || '-';
};

const getSummaryCapaian = (data) => {
    if (!data) return '-';
    if (data.submitter_role === 'pemohon') return data.pmh_kesesuaian_output || '-';
    if (data.submitter_role === 'tkksd_lokus') return data.tkl_kesesuaian_anggaran || '-';
    return data.capaian_indikator || '-';
};

const getSupportFile = (data) => data?.pmh_file_laporan || data?.file_bukti || null;

const getRecommendationValue = (data) => data?.rekomendasi_lanjutan || data?.pmh_keberlanjutan || data?.tkl_rekomendasi_lokus || null;
const getRecommendationSeverity = (value) => {
    if (!value) return 'secondary';
    if (['Dilanjutkan', 'Perlu dilanjutkan', 'Lanjutkan'].includes(value)) return 'success';
    if (['Diperluas', 'Cukup', 'Perbaiki'].includes(value)) return 'info';
    return 'danger';
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const pendingCount = computed(() => props.pendingPermohonans?.length || 0);
const completedCount = computed(() => props.datas?.data?.length || 0);
</script>

<template>
    <Head :title="share.title" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">{{ share.title }}</h2>
        </template>

        <section class="py-8">
            <div class="mx-auto max-w-full px-6 lg:px-8">
                <!-- Breadcrumb -->
                <Breadcrumb class="mb-6" />

                <!-- Control Bar -->
                <div class="mb-5 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                    <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                        <div class="flex min-w-0 items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-100">
                                    <Icon icon="solar:clipboard-check-bold" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0 pt-0.5">
                                <h3 class="text-base font-semibold text-slate-900 dark:text-white">Monitoring dan Evaluasi</h3>
                                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-300">
                                    Kelola evaluasi kerjasama yang sudah mencapai periode monitoring.
                                </p>
                            </div>
                        </div>
                        <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center xl:w-auto">
                            <div class="relative w-full sm:w-80">
                                <Icon icon="lucide:search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                <input v-model="filterQuery" type="text" placeholder="Cari monev..." class="h-11 w-full rounded-lg border border-slate-300 py-0 pl-10 pr-4 text-sm text-slate-700 focus:border-slate-500 focus:ring-slate-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100" />
                            </div>
                            <a v-if="isAdmin && completedCount > 0" :href="route('monev.export')" class="inline-flex h-11 shrink-0 items-center justify-center gap-2 whitespace-nowrap rounded-lg bg-slate-900 px-4 text-sm font-medium text-white transition-colors hover:bg-slate-700">
                                <Icon icon="solar:download-bold" class="h-4 w-4" />
                                <span>Export CSV</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800 sm:p-6">
                    <TabView class="w-full">
                        
                        <!-- Perlu Evaluasi Tab (admin, pemohon dengan monev.create, dan TKKSD Lokus untuk monitoring) -->
                        <TabPanel v-if="isAdmin || canCreateMonev || isTkksdLokus">
                            <template #header>
                                <div class="flex items-center gap-2">
                                    <Icon icon="solar:clipboard-check-bold" class="w-4 h-4 text-slate-500" />
                                    <span>{{ isTkksdLokus && !canCreateMonev ? 'Belum Dimonev' : 'Perlu Evaluasi' }}</span>
                                    <Tag :value="pendingCount" severity="warning" />
                                </div>
                            </template>

                            <div v-if="pendingCount > 0" class="mb-5 flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <div class="rounded-lg bg-white p-2 text-slate-700 shadow-sm">
                                    <Icon icon="solar:clipboard-check-bold-duotone" class="w-6 h-6" />
                                </div>
                                <div>
                                    <h4 class="text-base font-semibold text-slate-900">
                                        {{ isTkksdLokus && !canCreateMonev ? 'Kerjasama OPD Anda Belum Dimonev' : 'Kerjasama Perlu Evaluasi' }}
                                    </h4>
                                    <p class="mt-1 text-sm text-slate-600">
                                        Terdapat <span class="font-bold">{{ pendingCount }}</span>
                                        {{ isTkksdLokus && !canCreateMonev
                                            ? 'kerjasama OPD Anda yang telah berakhir namun pemohon belum mengisi form Monev. Mohon dorong pemohon untuk segera melakukan evaluasi.'
                                            : 'kerjasama yang telah berakhir dan perlu diisi form Monev.' }}
                                    </p>
                                </div>
                            </div>

                            <div v-else class="mb-5 flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <div class="rounded-lg bg-white p-2 text-slate-500 shadow-sm">
                                    <Icon icon="solar:info-circle-bold-duotone" class="w-6 h-6" />
                                </div>
                                <div>
                                    <h4 class="font-semibold text-slate-700">Belum Ada Kerjasama yang Perlu Evaluasi</h4>
                                    <p class="text-sm text-slate-600 mt-1">Monev dapat dibuat setelah kerjasama mencapai tahap pelaksanaan/selesai dan tanggal berakhir terlampaui.</p>
                                </div>
                            </div>

                            <DataTable :value="pendingPermohonans" paginator :rows="10" stripedRows tableStyle="min-width: 50rem" class="p-datatable-sm mt-4">
                                <template #empty>Tidak ada kerjasama yang perlu evaluasi.</template>
                                <Column field="label" header="Judul Kerjasama" sortable>
                                    <template #body="{ data }">
                                        <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ data.label }}</div>
                                        <div class="text-xs text-gray-500 mt-1">{{ data.kategori?.label || 'Umum' }}</div>
                                    </template>
                                </Column>
                                <Column field="nama_instansi" header="Instansi" sortable></Column>
                                <Column header="Tanggal Berakhir">
                                    <template #body="{ data }">
                                        <div class="text-sm font-medium text-slate-700">{{ formatDate(data.tanggal_berakhir) }}</div>
                                    </template>
                                </Column>
                                <Column header="Status">
                                    <template #body>
                                        <Tag value="Belum Evaluasi" severity="warning" />
                                    </template>
                                </Column>
                                <Column header="Aksi" style="width: 140px">
                                    <template #body="{ data }">
                                        <Button
                                            v-if="isAdmin || canCreateMonev"
                                            label="Buat Monev"
                                            icon="pi pi-pencil"
                                            severity="secondary"
                                            size="small"
                                            @click="openCreateModal(data)"
                                        />
                                        <span v-else class="text-xs text-gray-400 italic">Menunggu pemohon</span>
                                    </template>
                                </Column>
                            </DataTable>
                        </TabPanel>
                        
                        <!-- Riwayat Monev Tab -->
                        <TabPanel>
                            <template #header>
                                <div class="flex items-center gap-2">
                                    <Icon icon="solar:document-text-bold" class="w-4 h-4 text-slate-500" />
                                    <span>Riwayat Monev</span>
                                    <Tag :value="completedCount" severity="info" />
                                </div>
                            </template>

                            <!-- ADMIN: 1 row per kerjasama (grouped) -->
                            <DataTable v-if="adminGrouped" :value="datas?.data || []" paginator :rows="10" stripedRows tableStyle="min-width: 50rem" class="mt-4">
                                <template #empty>Belum ada riwayat Monev.</template>
                                <Column field="permohonan.label" header="Kerjasama" sortable>
                                    <template #body="{ data }">
                                        <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ data.permohonan?.label || '-' }}</div>
                                        <div class="text-xs text-gray-500 mt-1">{{ data.permohonan?.nama_instansi }}</div>
                                    </template>
                                </Column>
                                <Column header="Sumber Monev">
                                    <template #body="{ data }">
                                        <div class="flex flex-wrap gap-1">
                                            <Tag
                                                v-for="role in (data.submitter_roles || [])"
                                                :key="role"
                                                :value="roleLabel(role)"
                                                :severity="roleSeverity(role)"
                                                class="text-xs"
                                            />
                                            <span v-if="!data.submitter_roles?.length" class="text-xs text-gray-400">-</span>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">{{ data.monev_count }} monev</div>
                                    </template>
                                </Column>
                                <Column header="Tgl Evaluasi Terakhir">
                                    <template #body="{ data }">{{ formatDate(data.latest_tanggal_evaluasi) }}</template>
                                </Column>
                                <Column header="Rekomendasi Terakhir">
                                    <template #body="{ data }">
                                        <Tag v-if="data.latest_rekomendasi" :value="data.latest_rekomendasi" :severity="data.latest_rekomendasi === 'Dilanjutkan' ? 'success' : data.latest_rekomendasi === 'Diperluas' ? 'info' : 'danger'" />
                                        <span v-else class="text-gray-400">-</span>
                                    </template>
                                </Column>
                                <Column header="Aksi" style="width: 100px">
                                    <template #body="{ data }">
                                        <Button icon="pi pi-eye" severity="secondary" rounded outlined @click="openGroupedDetailModal(data)" />
                                    </template>
                                </Column>
                            </DataTable>

                            <!-- NON-ADMIN: 1 row per monev (existing) -->
                            <DataTable v-else :value="datas?.data || []" paginator :rows="10" stripedRows tableStyle="min-width: 50rem" class="mt-4">
                                <template #empty>Belum ada riwayat Monev.</template>
                                <Column field="kode_monev" header="Kode Monev">
                                    <template #body="{ data }">
                                        <span class="font-mono text-xs">{{ data.kode_monev }}</span>
                                    </template>
                                </Column>
                                <Column field="permohonan.label" header="Kerjasama" sortable>
                                    <template #body="{ data }">
                                        <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ data.permohonan?.label || '-' }}</div>
                                        <div class="text-xs text-gray-500 mt-1">{{ data.permohonan?.nama_instansi }}</div>
                                    </template>
                                </Column>
                                <Column header="Tgl Evaluasi">
                                    <template #body="{ data }">{{ formatDate(data.tanggal_evaluasi) }}</template>
                                </Column>
                                <Column header="Pelaksanaan">
                                    <template #body="{ data }">
                                        <span class="text-xs font-medium" :class="getElegantAnswerColor(getSummaryPelaksanaan(data))">
                                            {{ getSummaryPelaksanaan(data) }}
                                        </span>
                                    </template>
                                </Column>
                                <Column header="Capaian">
                                    <template #body="{ data }">
                                        <span class="text-xs font-medium" :class="getElegantAnswerColor(getSummaryCapaian(data))">
                                            {{ getSummaryCapaian(data) }}
                                        </span>
                                    </template>
                                </Column>
                                <Column header="Rekomendasi">
                                    <template #body="{ data }">
                                        <Tag v-if="getRecommendationValue(data)" :value="getRecommendationValue(data)" :severity="getRecommendationSeverity(getRecommendationValue(data))" />
                                        <span v-else class="text-gray-400">-</span>
                                    </template>
                                </Column>
                                <Column header="Aksi" style="width: 100px">
                                    <template #body="{ data }">
                                        <Button icon="pi pi-eye" severity="secondary" rounded outlined v-tooltip="'Lihat detail jawaban evaluasi'" @click="openDetailModal(data)" />
                                    </template>
                                </Column>
                            </DataTable>
                        </TabPanel>
                    </TabView>
                </div>
            </div>
        </section>

        <Dialog v-model:visible="createDialog" modal :style="{ width: '980px' }" :breakpoints="{ '960px': '96vw' }" :header="roleFormTitle">
            <form @submit.prevent="submitCreateForm" class="space-y-4">
                <div class="grid gap-4 rounded-lg border border-slate-200 bg-slate-50 p-4 md:grid-cols-[minmax(0,1fr)_260px]">
                    <div>
                        <p class="text-xs font-semibold uppercase text-slate-500">Kerjasama</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ selectedPermohonan?.label || '-' }}</p>
                        <p class="text-xs text-slate-500">{{ selectedPermohonan?.nama_instansi || '-' }}</p>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Tanggal Evaluasi <span class="text-red-500">*</span></label>
                        <Calendar v-model="createForm.tanggal_evaluasi" dateFormat="dd/mm/yy" showIcon class="w-full" />
                        <small v-if="createForm.errors.tanggal_evaluasi" class="text-red-500">{{ createForm.errors.tanggal_evaluasi }}</small>
                    </div>
                </div>

                <div v-if="roleFormType === 'admin'" class="space-y-4">
                    <div v-for="(sectionQuestions, sectionName) in adminGroupedQuestions" :key="sectionName" class="rounded-lg border border-slate-200 p-4">
                        <h4 class="mb-3 text-sm font-semibold text-slate-800">{{ sectionName }}</h4>
                        <div class="grid gap-3 md:grid-cols-2">
                            <div v-for="q in sectionQuestions" :key="q.key">
                                <label class="mb-1 block text-sm text-slate-700">{{ q.label }} <span class="text-red-500">*</span></label>
                                <Dropdown v-model="createForm[q.key]" :options="adminOptions[q.key]" placeholder="Pilih jawaban" class="w-full" />
                                <small v-if="createForm.errors[q.key]" class="text-red-500">{{ createForm.errors[q.key] }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm text-slate-700">Kendala Administrasi</label>
                            <Textarea v-model="createForm.kendala_administrasi" rows="3" class="w-full" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm text-slate-700">Saran & Rekomendasi</label>
                            <Textarea v-model="createForm.saran_rekomendasi" rows="3" class="w-full" />
                        </div>
                    </div>
                    <div class="rounded-lg border border-slate-200 p-4">
                        <label class="mb-2 block text-sm text-slate-700">Bukti Pendukung (opsional)</label>
                        <FileUpload mode="basic" accept=".pdf,.jpg,.jpeg,.png" :maxFileSize="5000000" chooseLabel="Pilih File" @select="onAdminFileSelect" />
                    </div>
                </div>

                <div v-else-if="roleFormType === 'pemohon'" class="space-y-4">
                    <div class="grid gap-3 rounded-lg border border-slate-200 p-4 md:grid-cols-[minmax(0,1fr)_300px] md:items-center">
                        <label class="text-sm text-slate-700">Apakah kegiatan kerjasama terlaksana sesuai rencana? <span class="text-red-500">*</span></label>
                        <div>
                            <Dropdown v-model="createForm.pmh_realisasi_kegiatan" :options="['Terlaksana penuh','Sebagian','Tidak']" placeholder="Pilih jawaban" class="w-full" />
                            <small v-if="createForm.errors.pmh_realisasi_kegiatan" class="text-red-500">{{ createForm.errors.pmh_realisasi_kegiatan }}</small>
                        </div>
                    </div>
                    <div class="grid gap-3 rounded-lg border border-slate-200 p-4 md:grid-cols-[minmax(0,1fr)_300px] md:items-center">
                        <label class="text-sm text-slate-700">Apakah output sesuai target perjanjian? <span class="text-red-500">*</span></label>
                        <div>
                            <Dropdown v-model="createForm.pmh_kesesuaian_output" :options="['Ya','Sebagian','Tidak']" placeholder="Pilih jawaban" class="w-full" />
                            <small v-if="createForm.errors.pmh_kesesuaian_output" class="text-red-500">{{ createForm.errors.pmh_kesesuaian_output }}</small>
                        </div>
                    </div>
                    <div class="rounded-lg border border-slate-200 p-4">
                        <label class="mb-1 block text-sm text-slate-700">Bagaimana hasil kerjasama dimanfaatkan?</label>
                        <Textarea v-model="createForm.pmh_pemanfaatan_hasil" rows="3" class="w-full" />
                    </div>
                    <div class="rounded-lg border border-slate-200 p-4">
                        <label class="mb-1 block text-sm text-slate-700">Kendala selama pelaksanaan</label>
                        <Textarea v-model="createForm.pmh_kendala_lapangan" rows="3" class="w-full" />
                    </div>
                    <div class="grid gap-3 rounded-lg border border-slate-200 p-4 md:grid-cols-[minmax(0,1fr)_300px] md:items-center">
                        <label class="text-sm text-slate-700">Usulan keberlanjutan <span class="text-red-500">*</span></label>
                        <div>
                            <Dropdown v-model="createForm.pmh_keberlanjutan" :options="['Perlu dilanjutkan','Cukup','Hentikan']" placeholder="Pilih jawaban" class="w-full" />
                            <small v-if="createForm.errors.pmh_keberlanjutan" class="text-red-500">{{ createForm.errors.pmh_keberlanjutan }}</small>
                        </div>
                    </div>
                    <div class="rounded-lg border border-slate-200 p-4">
                        <label class="mb-2 block text-sm text-slate-700">Upload Laporan Pelaksanaan (opsional)</label>
                        <FileUpload mode="basic" accept=".pdf,.jpg,.jpeg,.png" :maxFileSize="5000000" chooseLabel="Pilih File" @select="onPemohonFileSelect" />
                        <small v-if="createForm.errors.pmh_file_laporan" class="text-red-500">{{ createForm.errors.pmh_file_laporan }}</small>
                    </div>
                </div>

                <div v-else class="space-y-4">
                    <div class="grid gap-3 rounded-lg border border-slate-200 p-4 md:grid-cols-[minmax(0,1fr)_300px] md:items-center">
                        <label class="text-sm text-slate-700">Kepatuhan pelaksanaan terhadap PKS <span class="text-red-500">*</span></label>
                        <div>
                            <Dropdown v-model="createForm.tkl_kepatuhan_pks" :options="['Patuh','Sebagian','Tidak']" placeholder="Pilih jawaban" class="w-full" />
                            <small v-if="createForm.errors.tkl_kepatuhan_pks" class="text-red-500">{{ createForm.errors.tkl_kepatuhan_pks }}</small>
                        </div>
                    </div>
                    <div class="grid gap-3 rounded-lg border border-slate-200 p-4 md:grid-cols-[minmax(0,1fr)_300px] md:items-center">
                        <label class="text-sm text-slate-700">Kualitas koordinasi OPD–mitra <span class="text-red-500">*</span></label>
                        <div>
                            <Dropdown v-model="createForm.tkl_koordinasi_mitra" :options="['Sangat baik','Baik','Cukup','Kurang']" placeholder="Pilih jawaban" class="w-full" />
                            <small v-if="createForm.errors.tkl_koordinasi_mitra" class="text-red-500">{{ createForm.errors.tkl_koordinasi_mitra }}</small>
                        </div>
                    </div>
                    <div class="grid gap-3 rounded-lg border border-slate-200 p-4 md:grid-cols-[minmax(0,1fr)_300px] md:items-center">
                        <label class="text-sm text-slate-700">Kesesuaian realisasi terhadap anggaran <span class="text-red-500">*</span></label>
                        <div>
                            <Dropdown v-model="createForm.tkl_kesesuaian_anggaran" :options="['Sesuai','Sebagian','Tidak']" placeholder="Pilih jawaban" class="w-full" />
                            <small v-if="createForm.errors.tkl_kesesuaian_anggaran" class="text-red-500">{{ createForm.errors.tkl_kesesuaian_anggaran }}</small>
                        </div>
                    </div>
                    <div class="rounded-lg border border-slate-200 p-4">
                        <label class="mb-1 block text-sm text-slate-700">Temuan pengawasan</label>
                        <Textarea v-model="createForm.tkl_temuan_pengawasan" rows="3" class="w-full" />
                    </div>
                    <div class="grid gap-3 rounded-lg border border-slate-200 p-4 md:grid-cols-[minmax(0,1fr)_300px] md:items-center">
                        <label class="text-sm text-slate-700">Rekomendasi lokus <span class="text-red-500">*</span></label>
                        <div>
                            <Dropdown v-model="createForm.tkl_rekomendasi_lokus" :options="['Lanjutkan','Perbaiki','Hentikan']" placeholder="Pilih jawaban" class="w-full" />
                            <small v-if="createForm.errors.tkl_rekomendasi_lokus" class="text-red-500">{{ createForm.errors.tkl_rekomendasi_lokus }}</small>
                        </div>
                    </div>
                    <div class="rounded-lg border border-slate-200 p-4">
                        <label class="mb-1 block text-sm text-slate-700">Catatan tambahan</label>
                        <Textarea v-model="createForm.tkl_catatan" rows="3" class="w-full" />
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <Button label="Batal" severity="secondary" text @click="createDialog = false" />
                    <Button type="submit" label="Simpan Evaluasi" icon="pi pi-check" :loading="createForm.processing" />
                </div>
            </form>
        </Dialog>

        <!-- Detail Modal -->
        <Dialog v-model:visible="detailDialog" modal :style="{ width: '900px' }" :breakpoints="{ '960px': '95vw' }" :showHeader="false" contentClass="p-0">
            <div v-if="selectedMonev">
                <!-- Header -->
                <div class="border-b border-slate-200 bg-white px-6 py-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex min-w-0 items-start gap-3">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-700">
                                <Icon icon="solar:clipboard-check-bold" class="h-6 w-6" />
                            </span>
                            <div class="min-w-0">
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <span class="rounded bg-slate-100 px-2.5 py-1 font-mono text-xs text-slate-600">{{ selectedMonev.kode_monev }}</span>
                                <span class="rounded border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">Selesai</span>
                            </div>
                            <h3 class="text-xl font-semibold text-slate-900">{{ selectedMonev.permohonan?.label }}</h3>
                            <p class="text-slate-500 text-sm">{{ selectedMonev.permohonan?.nama_instansi }}</p>
                            </div>
                        </div>
                        <button @click="detailDialog = false" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:bg-slate-50 hover:text-slate-800">
                            <Icon icon="solar:close-circle-linear" class="h-6 w-6" />
                        </button>
                    </div>
                    <div class="mt-4 inline-flex items-center gap-2 rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-600">
                        <span class="flex items-center gap-1.5">
                            <Icon icon="solar:calendar-linear" class="w-4 h-4" />
                            Dievaluasi {{ formatDate(selectedMonev.tanggal_evaluasi) }}
                        </span>
                    </div>
                </div>

                <!-- Content -->
                <div class="space-y-5 px-6 py-5">
                    <div class="rounded-lg border border-slate-200 p-4">
                        <h4 class="mb-3 flex items-center gap-2 border-b border-slate-200 pb-2 text-sm font-semibold text-slate-800">
                            <Icon icon="solar:checklist-minimalistic-bold" class="h-4 w-4 text-slate-500" />
                            Ringkasan Jawaban
                        </h4>
                        <div class="grid gap-x-6 gap-y-1" :class="getQuestionGridClass(selectedMonev.submitter_role)">
                            <div v-for="q in getQuestionsForRole(selectedMonev.submitter_role)" :key="q.key" class="flex flex-col gap-1 border-b border-slate-100 py-2 last:border-0 sm:flex-row sm:items-start sm:justify-between">
                                <span class="text-sm text-slate-600">{{ q.label }}</span>
                                <span class="text-sm font-semibold sm:text-right" :class="getElegantAnswerColor(selectedMonev[q.key])">
                                    {{ selectedMonev[q.key] || '-' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="selectedMonev.kendala_administrasi || selectedMonev.saran_rekomendasi || selectedMonev.pmh_pemanfaatan_hasil || selectedMonev.pmh_kendala_lapangan || selectedMonev.tkl_temuan_pengawasan || selectedMonev.tkl_catatan"
                        class="grid grid-cols-1 gap-4 border-t border-slate-200 pt-4 md:grid-cols-2"
                    >
                        <div v-if="selectedMonev.kendala_administrasi">
                            <h4 class="mb-2 text-sm font-semibold text-slate-800">Kendala Administrasi</h4>
                            <p class="rounded-lg bg-slate-50 p-3 text-sm leading-6 text-slate-600">{{ selectedMonev.kendala_administrasi }}</p>
                        </div>
                        <div v-if="selectedMonev.saran_rekomendasi">
                            <h4 class="mb-2 text-sm font-semibold text-slate-800">Saran & Rekomendasi</h4>
                            <p class="rounded-lg bg-slate-50 p-3 text-sm leading-6 text-slate-600">{{ selectedMonev.saran_rekomendasi }}</p>
                        </div>
                        <div v-if="selectedMonev.pmh_pemanfaatan_hasil">
                            <h4 class="mb-2 text-sm font-semibold text-slate-800">Pemanfaatan Hasil</h4>
                            <p class="rounded-lg bg-slate-50 p-3 text-sm leading-6 text-slate-600">{{ selectedMonev.pmh_pemanfaatan_hasil }}</p>
                        </div>
                        <div v-if="selectedMonev.pmh_kendala_lapangan">
                            <h4 class="mb-2 text-sm font-semibold text-slate-800">Kendala Lapangan</h4>
                            <p class="rounded-lg bg-slate-50 p-3 text-sm leading-6 text-slate-600">{{ selectedMonev.pmh_kendala_lapangan }}</p>
                        </div>
                        <div v-if="selectedMonev.tkl_temuan_pengawasan">
                            <h4 class="mb-2 text-sm font-semibold text-slate-800">Temuan Pengawasan</h4>
                            <p class="rounded-lg bg-slate-50 p-3 text-sm leading-6 text-slate-600">{{ selectedMonev.tkl_temuan_pengawasan }}</p>
                        </div>
                        <div v-if="selectedMonev.tkl_catatan">
                            <h4 class="mb-2 text-sm font-semibold text-slate-800">Catatan TKKSD Lokus</h4>
                            <p class="rounded-lg bg-slate-50 p-3 text-sm leading-6 text-slate-600">{{ selectedMonev.tkl_catatan }}</p>
                        </div>
                    </div>

                    <div v-if="getSupportFile(selectedMonev)" class="border-t border-slate-200 pt-4">
                        <a :href="`/storage/${getSupportFile(selectedMonev)}`" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50">
                            <Icon icon="solar:file-download-linear" class="w-4 h-4" />
                            Unduh Berkas Pendukung
                        </a>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-wrap items-center gap-2">
                        <a 
                            v-if="selectedMonev?.permohonan?.pemohon?.phone"
                            :href="`https://wa.me/${selectedMonev.permohonan.pemohon.phone.replace(/^0/, '62').replace(/[^0-9]/g, '')}`"
                            target="_blank"
                            class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-100"
                        >
                            <Icon icon="solar:phone-bold" class="w-4 h-4" />
                            Hubungi Pemohon
                        </a>
                        <button
                            v-if="isAdmin"
                            @click="sendMonevNotification"
                            class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-slate-700"
                        >
                            <Icon icon="solar:bell-bold" class="w-4 h-4" />
                            Kirim Notifikasi
                        </button>
                    </div>
                    <button @click="detailDialog = false" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-100">
                        Tutup
                    </button>
                </div>
            </div>
        </Dialog>

        <!-- Grouped Detail Dialog (Admin) — menampilkan semua monev dari pemohon, TKKSD Lokus, & admin -->
        <Dialog v-model:visible="groupedDialog" modal :style="{ width: '980px' }" :breakpoints="{ '960px': '95vw' }" header="Detail Monev Kerjasama">
            <div v-if="selectedGrouped" class="space-y-5">
                <!-- Kerjasama header -->
                <div class="flex flex-col gap-4 border-b border-slate-200 pb-5 md:flex-row md:items-start md:justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kerjasama</p>
                        <h3 class="mt-1 text-xl font-semibold text-slate-900">{{ selectedGrouped.permohonan?.label }}</h3>

                        <!-- Pihak yang berkerjasama: OPD Pemkot ↔ Mitra -->
                        <div class="mt-3 flex flex-wrap items-center gap-2 text-sm">
                            <div class="inline-flex min-w-0 items-center gap-1.5 rounded-lg bg-slate-50 px-3 py-2 text-slate-700">
                                <Icon icon="solar:buildings-bold" class="w-3.5 h-3.5" />
                                <span class="font-medium">Pemkot Samarinda</span>
                                <span v-if="opdNames" class="text-xs">({{ opdNames }})</span>
                                <span v-else class="text-xs italic text-slate-400">OPD belum di-set</span>
                            </div>
                            <Icon icon="solar:arrows-right-left-bold" class="w-4 h-4 text-slate-400" />
                            <div class="inline-flex items-center gap-1.5 rounded-lg bg-slate-50 px-3 py-2 text-slate-700">
                                <Icon icon="solar:case-round-bold" class="w-3.5 h-3.5" />
                                <span class="font-medium">{{ selectedGrouped.permohonan?.nama_instansi || '-' }}</span>
                            </div>
                        </div>

                        <!-- Status setiap role: Sudah / Belum -->
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="text-sm text-slate-500">Total Monev <strong class="text-slate-800">{{ selectedGrouped.monev_count }}</strong>/3</span>
                            <div
                                v-for="role in expectedRoles"
                                :key="role"
                                class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium"
                                :class="hasSubmitted(role)
                                    ? 'bg-white text-slate-700 border-slate-200'
                                    : 'bg-slate-100 text-slate-500 border-slate-200'"
                            >
                                <Icon
                                    :icon="hasSubmitted(role) ? 'solar:check-circle-bold' : 'solar:close-circle-bold'"
                                    class="w-4 h-4"
                                />
                                <span>{{ roleLabel(role) }}</span>
                                <span class="opacity-70">{{ hasSubmitted(role) ? 'Sudah' : 'Belum' }}</span>
                            </div>
                        </div>
                    </div>
                    <button
                        v-if="isAdmin"
                        @click="sendGroupedNotification"
                        :disabled="!latestMonevUuid"
                        class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-slate-700 disabled:opacity-50"
                    >
                        <Icon icon="solar:bell-bold" class="w-4 h-4" />
                        Kirim Notifikasi
                    </button>
                </div>

                <!-- Setiap monev ditampilkan sebagai card terpisah -->
                <div v-for="m in (selectedGrouped.monevs || [])" :key="m.id" class="border-b border-slate-200 pb-5 last:border-b-0 last:pb-0">
                    <div class="flex flex-col gap-3 rounded-lg bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white text-slate-700 shadow-sm">
                                <Icon icon="solar:document-text-bold" class="h-5 w-5" />
                            </span>
                            <div class="text-sm">
                                <div class="flex flex-wrap items-center gap-2">
                                    <Tag :value="roleLabel(m.submitter_role)" :severity="roleSeverity(m.submitter_role)" />
                                    <span class="font-mono text-xs text-slate-500">{{ m.kode_monev }}</span>
                                </div>
                                <div class="mt-0.5 text-xs text-slate-600">Diisi oleh {{ m.submitter_name }}</div>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 sm:justify-end">
                            <span class="inline-flex items-center gap-1 rounded bg-white px-2.5 py-1.5">
                                <Icon icon="solar:calendar-linear" class="h-3.5 w-3.5" />
                                {{ formatDate(m.tanggal_evaluasi) }}
                            </span>
                            <Tag v-if="getRecommendationValue(m)" :value="getRecommendationValue(m)" :severity="getRecommendationSeverity(getRecommendationValue(m))" />
                        </div>
                    </div>
                    <div class="mt-3 grid grid-cols-1 gap-x-8 px-1 text-sm" :class="getQuestionGridClass(m.submitter_role)">
                        <div v-for="q in getQuestionsForRole(m.submitter_role)" :key="q.key" class="flex flex-col gap-1 border-b border-slate-100 py-2 sm:flex-row sm:justify-between sm:gap-3">
                            <span class="text-slate-600">{{ q.label }}</span>
                            <span class="font-semibold sm:text-right" :class="getElegantAnswerColor(m[q.key])">{{ m[q.key] || '-' }}</span>
                        </div>
                    </div>
                    <div v-if="m.kendala_administrasi || m.saran_rekomendasi || m.pmh_pemanfaatan_hasil || m.pmh_kendala_lapangan || m.tkl_temuan_pengawasan || m.tkl_catatan" class="mt-3 grid grid-cols-1 gap-3 px-1 md:grid-cols-2">
                        <div v-if="m.kendala_administrasi">
                            <h5 class="text-xs font-semibold text-slate-700 mb-1">Kendala Administrasi</h5>
                            <p class="rounded-lg bg-slate-50 p-3 text-sm text-slate-600">{{ m.kendala_administrasi }}</p>
                        </div>
                        <div v-if="m.saran_rekomendasi">
                            <h5 class="text-xs font-semibold text-slate-700 mb-1">Saran & Rekomendasi</h5>
                            <p class="rounded-lg bg-slate-50 p-3 text-sm text-slate-600">{{ m.saran_rekomendasi }}</p>
                        </div>
                        <div v-if="m.pmh_pemanfaatan_hasil">
                            <h5 class="text-xs font-semibold text-slate-700 mb-1">Pemanfaatan Hasil</h5>
                            <p class="rounded-lg bg-slate-50 p-3 text-sm text-slate-600">{{ m.pmh_pemanfaatan_hasil }}</p>
                        </div>
                        <div v-if="m.pmh_kendala_lapangan">
                            <h5 class="text-xs font-semibold text-slate-700 mb-1">Kendala Lapangan</h5>
                            <p class="rounded-lg bg-slate-50 p-3 text-sm text-slate-600">{{ m.pmh_kendala_lapangan }}</p>
                        </div>
                        <div v-if="m.tkl_temuan_pengawasan">
                            <h5 class="text-xs font-semibold text-slate-700 mb-1">Temuan Pengawasan</h5>
                            <p class="rounded-lg bg-slate-50 p-3 text-sm text-slate-600">{{ m.tkl_temuan_pengawasan }}</p>
                        </div>
                        <div v-if="m.tkl_catatan">
                            <h5 class="text-xs font-semibold text-slate-700 mb-1">Catatan TKKSD Lokus</h5>
                            <p class="rounded-lg bg-slate-50 p-3 text-sm text-slate-600">{{ m.tkl_catatan }}</p>
                        </div>
                    </div>
                    <div v-if="getSupportFile(m)" class="px-1">
                        <div class="mt-3 border-t border-slate-100 pt-3">
                            <h5 class="text-xs font-semibold text-slate-700 mb-2 flex items-center gap-1.5">
                                <Icon icon="solar:paperclip-bold" class="w-3.5 h-3.5 text-slate-500" />
                                Data Dukung / Bukti Pendukung
                            </h5>
                            <a
                                :href="`/storage/${getSupportFile(m)}`"
                                target="_blank"
                                class="inline-flex items-center gap-2 rounded border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-700 transition-colors hover:bg-slate-100"
                            >
                                <Icon icon="solar:file-bold-duotone" class="w-4 h-4" />
                                <span class="truncate max-w-xs">{{ getSupportFile(m).split('/').pop() }}</span>
                                <Icon icon="solar:download-bold" class="w-3.5 h-3.5 ml-1" />
                            </a>
                        </div>
                    </div>
                    <div v-else class="px-1">
                        <div class="mt-3 border-t border-slate-100 pt-3">
                            <p class="text-xs text-slate-400 italic flex items-center gap-1.5">
                                <Icon icon="solar:paperclip-bold" class="w-3.5 h-3.5" />
                                Tidak ada data dukung yang dilampirkan.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </Dialog>
    </AuthenticatedLayout>
</template>
