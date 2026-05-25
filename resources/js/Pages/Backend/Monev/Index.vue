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
import Tag from 'primevue/tag';
import FileUpload from 'primevue/fileupload';
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

// Create Form Modal
const formDialog = ref(false);
const selectedPermohonan = ref(null);

const form = useForm({
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
    file_bukti: null,
});

const options = {
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

const groupedQuestions = computed(() => questions.reduce((groups, question) => {
    if (!groups[question.section]) groups[question.section] = [];
    groups[question.section].push(question);
    return groups;
}, {}));

const openFormModal = (permohonan) => {
    selectedPermohonan.value = permohonan;
    form.reset();
    form.id_permohonan = permohonan.id;
    form.tanggal_evaluasi = new Date();
    formDialog.value = true;
};

const handleFileSelect = (event) => {
    form.file_bukti = event.files[0];
};

const submitForm = () => {
    form.post(route('monev.store'), {
        forceFormData: true,
        onSuccess: () => {
            formDialog.value = false;
            form.reset();
        }
    });
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
                                        <Button v-if="isAdmin || canCreateMonev" label="Buat Monev" icon="pi pi-pencil" severity="secondary" size="small" @click="openFormModal(data)" />
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
                                        <span class="text-xs font-medium" :class="getElegantAnswerColor(data.kesesuaian_tujuan)">
                                            {{ data.kesesuaian_tujuan || '-' }}
                                        </span>
                                    </template>
                                </Column>
                                <Column header="Capaian">
                                    <template #body="{ data }">
                                        <span class="text-xs font-medium" :class="getElegantAnswerColor(data.capaian_indikator)">
                                            {{ data.capaian_indikator || '-' }}
                                        </span>
                                    </template>
                                </Column>
                                <Column header="Rekomendasi">
                                    <template #body="{ data }">
                                        <Tag v-if="data.rekomendasi_lanjutan" :value="data.rekomendasi_lanjutan" :severity="data.rekomendasi_lanjutan === 'Dilanjutkan' ? 'success' : data.rekomendasi_lanjutan === 'Diperluas' ? 'info' : 'danger'" />
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

        <!-- Create Form Modal -->
        <Dialog v-model:visible="formDialog" modal :style="{ width: '920px' }" :breakpoints="{ '960px': '96vw' }" contentClass="p-0">
            <template #header>
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-700">
                        <Icon icon="solar:clipboard-check-bold" class="h-5 w-5" />
                    </span>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Form Monitoring & Evaluasi</h3>
                        <p class="mt-0.5 text-sm font-normal text-slate-500">Lengkapi penilaian per bagian agar evaluasi mudah ditinjau.</p>
                    </div>
                </div>
            </template>

            <form id="monev-modal-form" @submit.prevent="submitForm" class="bg-white px-6 py-5">
                <div class="grid gap-4 border-b border-slate-200 pb-5 lg:grid-cols-[minmax(0,1fr)_250px]">
                        <div v-if="selectedPermohonan" class="rounded-lg bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kerjasama yang dievaluasi</p>
                            <div class="mt-2 text-base font-semibold text-slate-900">{{ selectedPermohonan.label }}</div>
                            <div class="mt-1 text-sm text-slate-500">{{ selectedPermohonan.nama_instansi }}</div>
                        </div>

                        <div class="rounded-lg bg-slate-50 p-4">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Tanggal Evaluasi <span class="text-red-500">*</span></label>
                            <Calendar
                                v-model="form.tanggal_evaluasi"
                                dateFormat="dd/mm/yy"
                                showIcon
                                :manualInput="true"
                                placeholder="Pilih tanggal evaluasi"
                                class="mt-2 w-full"
                            />
                        </div>
                </div>

                <div class="divide-y divide-slate-200">
                        <section v-for="(sectionQuestions, sectionName) in groupedQuestions" :key="sectionName" class="py-5">
                            <div class="mb-4 flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                                    <Icon icon="solar:widget-bold" class="h-4 w-4" />
                                </span>
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-900">{{ sectionName }}</h4>
                                    <p class="text-xs text-slate-500">Pilih jawaban yang paling sesuai.</p>
                                </div>
                            </div>
                            <div class="grid gap-3 md:grid-cols-2">
                                <div v-for="q in sectionQuestions" :key="q.key" class="flex flex-col gap-2">
                                    <label class="text-sm font-medium leading-5 text-slate-700">{{ q.label }} <span class="text-red-500">*</span></label>
                                    <Dropdown v-model="form[q.key]" :options="options[q.key]" placeholder="Pilih jawaban" class="w-full text-sm" :class="{ 'p-invalid': form.errors[q.key] }" />
                                    <small v-if="form.errors[q.key]" class="text-xs text-red-500">{{ form.errors[q.key] }}</small>
                                </div>
                            </div>
                        </section>

                        <section class="grid gap-5 py-5 lg:grid-cols-[minmax(0,1fr)_320px]">
                        <div>
                            <h4 class="text-sm font-semibold text-slate-900">Catatan Evaluasi</h4>
                            <div class="mt-3 grid gap-3 md:grid-cols-2">
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-slate-700">Kendala Administrasi</label>
                                    <Textarea v-model="form.kendala_administrasi" rows="3" class="w-full text-sm" placeholder="Jelaskan kendala jika ada..." />
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-slate-700">Saran & Rekomendasi</label>
                                    <Textarea v-model="form.saran_rekomendasi" rows="3" class="w-full text-sm" placeholder="Tuliskan saran..." />
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="flex items-center gap-2 text-sm font-semibold text-slate-900">
                                <Icon icon="solar:upload-bold" class="h-4 w-4 text-slate-600" />
                                Bukti Pendukung
                                <span class="text-xs font-normal text-slate-500">(opsional)</span>
                            </label>
                            <p class="text-xs leading-5 text-slate-500">PDF, JPG, atau PNG. Maksimal 5 MB.</p>
                            <FileUpload mode="basic" accept=".pdf,.jpg,.jpeg,.png" :maxFileSize="5000000" @select="handleFileSelect" chooseLabel="Pilih File" class="w-full" />
                            <div v-if="form.file_bukti" class="flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700">
                                <Icon icon="solar:file-check-bold" class="h-4 w-4" />
                                <span class="truncate">{{ form.file_bukti.name }}</span>
                                <span class="ml-auto shrink-0 text-slate-500">{{ (form.file_bukti.size / 1024).toFixed(0) }} KB</span>
                            </div>
                        </div>
                        </section>
                </div>
            </form>
            <template #footer>
                <div class="flex w-full items-center justify-between gap-3">
                    <p class="hidden text-sm text-slate-500 sm:block">Field bertanda <span class="text-red-500">*</span> wajib diisi.</p>
                    <div class="ml-auto flex items-center gap-2">
                        <Button label="Batal" severity="secondary" text @click="formDialog = false" />
                        <Button type="submit" form="monev-modal-form" label="Simpan Evaluasi" icon="pi pi-check" :loading="form.processing" />
                    </div>
                </div>
            </template>
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
                    <!-- Two Column Layout -->
                    <div class="grid grid-cols-1 gap-x-8 gap-y-5 lg:grid-cols-2">
                        
                        <!-- Evaluasi Pelaksanaan -->
                        <div>
                            <h4 class="mb-2 flex items-center gap-2 border-b border-slate-200 pb-2 text-sm font-semibold text-slate-800">
                                <Icon icon="solar:checklist-minimalistic-bold" class="h-4 w-4 text-slate-500" />
                                Evaluasi Pelaksanaan
                            </h4>
                            <div>
                                <template v-for="q in questions.filter(x => x.section === 'Pelaksanaan')" :key="q.key">
                                    <div class="flex flex-col gap-1 border-b border-slate-100 py-2 last:border-0 sm:flex-row sm:items-start sm:justify-between">
                                        <span class="text-sm text-slate-600">{{ q.label }}</span>
                                        <span class="text-sm font-semibold sm:text-right" :class="getElegantAnswerColor(selectedMonev[q.key])">
                                            {{ selectedMonev[q.key] || '-' }}
                                        </span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Capaian & Dampak -->
                        <div>
                            <h4 class="mb-2 flex items-center gap-2 border-b border-slate-200 pb-2 text-sm font-semibold text-slate-800">
                                <Icon icon="solar:chart-bold" class="h-4 w-4 text-slate-500" />
                                Capaian & Dampak
                            </h4>
                            <div>
                                <template v-for="q in questions.filter(x => x.section === 'Capaian')" :key="q.key">
                                    <div class="flex flex-col gap-1 border-b border-slate-100 py-2 last:border-0 sm:flex-row sm:items-start sm:justify-between">
                                        <span class="text-sm text-slate-600">{{ q.label }}</span>
                                        <span class="text-sm font-semibold sm:text-right" :class="getElegantAnswerColor(selectedMonev[q.key])">
                                            {{ selectedMonev[q.key] || '-' }}
                                        </span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Administrasi -->
                        <div>
                            <h4 class="mb-2 flex items-center gap-2 border-b border-slate-200 pb-2 text-sm font-semibold text-slate-800">
                                <Icon icon="solar:document-text-bold" class="h-4 w-4 text-slate-500" />
                                Administrasi
                            </h4>
                            <div>
                                <template v-for="q in questions.filter(x => x.section === 'Administrasi')" :key="q.key">
                                    <div class="flex flex-col gap-1 border-b border-slate-100 py-2 last:border-0 sm:flex-row sm:items-start sm:justify-between">
                                        <span class="text-sm text-slate-600">{{ q.label }}</span>
                                        <span class="text-sm font-semibold sm:text-right" :class="getElegantAnswerColor(selectedMonev[q.key])">
                                            {{ selectedMonev[q.key] || '-' }}
                                        </span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Rekomendasi -->
                        <div>
                            <h4 class="mb-2 flex items-center gap-2 border-b border-slate-200 pb-2 text-sm font-semibold text-slate-800">
                                <Icon icon="solar:flag-bold" class="h-4 w-4 text-slate-500" />
                                Rekomendasi
                            </h4>
                            <div>
                                <template v-for="q in questions.filter(x => x.section === 'Rekomendasi')" :key="q.key">
                                    <div class="flex flex-col gap-1 border-b border-slate-100 py-2 last:border-0 sm:flex-row sm:items-start sm:justify-between">
                                        <span class="text-sm text-slate-600">{{ q.label }}</span>
                                        <span class="text-sm font-semibold sm:text-right" :class="getElegantAnswerColor(selectedMonev[q.key])">
                                            {{ selectedMonev[q.key] || '-' }}
                                        </span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Notes Section -->
                    <div v-if="selectedMonev.kendala_administrasi || selectedMonev.saran_rekomendasi" class="grid grid-cols-1 gap-4 border-t border-slate-200 pt-4 md:grid-cols-2">
                        <div v-if="selectedMonev.kendala_administrasi">
                            <h4 class="mb-2 text-sm font-semibold text-slate-800">Kendala Administrasi</h4>
                            <p class="rounded-lg bg-slate-50 p-3 text-sm leading-6 text-slate-600">{{ selectedMonev.kendala_administrasi }}</p>
                        </div>
                        <div v-if="selectedMonev.saran_rekomendasi">
                            <h4 class="mb-2 text-sm font-semibold text-slate-800">Saran & Rekomendasi</h4>
                            <p class="rounded-lg bg-slate-50 p-3 text-sm leading-6 text-slate-600">{{ selectedMonev.saran_rekomendasi }}</p>
                        </div>
                    </div>

                    <!-- File Bukti -->
                    <div v-if="selectedMonev.file_bukti" class="border-t border-slate-200 pt-4">
                        <a :href="`/storage/${selectedMonev.file_bukti}`" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50">
                            <Icon icon="solar:file-download-linear" class="w-4 h-4" />
                            Unduh Bukti Pendukung
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
                            <Tag v-if="m.rekomendasi_lanjutan" :value="m.rekomendasi_lanjutan" :severity="m.rekomendasi_lanjutan === 'Dilanjutkan' ? 'success' : m.rekomendasi_lanjutan === 'Diperluas' ? 'info' : 'danger'" />
                        </div>
                    </div>
                    <div class="mt-3 grid grid-cols-1 gap-x-8 px-1 text-sm md:grid-cols-2">
                        <div v-for="q in questions" :key="q.key" class="flex flex-col gap-1 border-b border-slate-100 py-2 sm:flex-row sm:justify-between sm:gap-3">
                            <span class="text-slate-600">{{ q.label }}</span>
                            <span class="font-semibold sm:text-right" :class="getElegantAnswerColor(m[q.key])">{{ m[q.key] || '-' }}</span>
                        </div>
                    </div>
                    <div v-if="m.kendala_administrasi || m.saran_rekomendasi" class="mt-3 grid grid-cols-1 gap-3 px-1 md:grid-cols-2">
                        <div v-if="m.kendala_administrasi">
                            <h5 class="text-xs font-semibold text-slate-700 mb-1">Kendala Administrasi</h5>
                            <p class="rounded-lg bg-slate-50 p-3 text-sm text-slate-600">{{ m.kendala_administrasi }}</p>
                        </div>
                        <div v-if="m.saran_rekomendasi">
                            <h5 class="text-xs font-semibold text-slate-700 mb-1">Saran & Rekomendasi</h5>
                            <p class="rounded-lg bg-slate-50 p-3 text-sm text-slate-600">{{ m.saran_rekomendasi }}</p>
                        </div>
                    </div>
                    <div v-if="m.file_bukti" class="px-1">
                        <div class="mt-3 border-t border-slate-100 pt-3">
                            <h5 class="text-xs font-semibold text-slate-700 mb-2 flex items-center gap-1.5">
                                <Icon icon="solar:paperclip-bold" class="w-3.5 h-3.5 text-slate-500" />
                                Data Dukung / Bukti Pendukung
                            </h5>
                            <a
                                :href="`/storage/${m.file_bukti}`"
                                target="_blank"
                                class="inline-flex items-center gap-2 rounded border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-700 transition-colors hover:bg-slate-100"
                            >
                                <Icon icon="solar:file-bold-duotone" class="w-4 h-4" />
                                <span class="truncate max-w-xs">{{ m.file_bukti.split('/').pop() }}</span>
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
