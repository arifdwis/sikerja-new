<script setup>
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Icon } from '@iconify/vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import Calendar from 'primevue/calendar';
import Tag from 'primevue/tag';
import FileUpload from 'primevue/fileupload';
import InputText from 'primevue/inputtext';

const props = defineProps({
    datas: Object,
    pendingPermohonans: Array,
    share: Object,
    filters: Object,
    isAdmin: Boolean,
});

// Filter/Search
const filterQuery = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status ?? '');
let searchTimeout;

const doSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.visit(route('monev.index'), {
            data: { search: filterQuery.value, status: statusFilter.value },
            preserveState: true,
            preserveScroll: true,
            only: ['datas']
        });
    }, 500);
};

// Modal Form
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
    { key: 'kesesuaian_tujuan', label: 'Kesesuaian dengan tujuan perjanjian?', section: 'Evaluasi Pelaksanaan' },
    { key: 'ketepatan_waktu', label: 'Ketepatan waktu pelaksanaan?', section: 'Evaluasi Pelaksanaan' },
    { key: 'kontribusi_mitra', label: 'Kontribusi mitra sesuai kesepakatan?', section: 'Evaluasi Pelaksanaan' },
    { key: 'tingkat_koordinasi', label: 'Tingkat koordinasi dengan mitra?', section: 'Evaluasi Pelaksanaan' },
    { key: 'capaian_indikator', label: 'Pencapaian indikator kinerja?', section: 'Capaian & Dampak' },
    { key: 'dampak_pelaksanaan', label: 'Dampak terhadap pelayanan publik?', section: 'Capaian & Dampak' },
    { key: 'inovasi_manfaat', label: 'Inovasi dan nilai tambah?', section: 'Capaian & Dampak' },
    { key: 'kelengkapan_dokumen', label: 'Kelengkapan dokumen administrasi?', section: 'Administrasi' },
    { key: 'pelaporan_berkala', label: 'Pelaporan berkala dilakukan?', section: 'Administrasi' },
    { key: 'relevansi_kebutuhan', label: 'Relevansi dengan kebutuhan daerah?', section: 'Rekomendasi' },
    { key: 'rekomendasi_lanjutan', label: 'Rekomendasi tindak lanjut?', section: 'Rekomendasi' },
];

const groupedQuestions = computed(() => {
    const groups = {};
    questions.forEach(q => {
        if (!groups[q.section]) groups[q.section] = [];
        groups[q.section].push(q);
    });
    return groups;
});

const openFormModal = (permohonan) => {
    selectedPermohonan.value = permohonan;
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

const getStatusSeverity = (status) => {
    const map = { 0: 'secondary', 1: 'warning', 2: 'success' };
    return map[status] || 'info';
};

const getStatusLabel = (status) => {
    const map = { 0: 'Draft', 1: 'Menunggu Review', 2: 'Sudah Direview' };
    return map[status] || '-';
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const hasPendingPermohonans = computed(() => props.pendingPermohonans?.length > 0);
</script>

<template>
    <Head :title="share.title" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">{{ share.title }}</h2>
        </template>

        <section class="py-12">
            <div class="mx-auto max-w-full px-6 lg:px-8">
                <Breadcrumb class="mb-6" />

                <!-- Pending Permohonan Section -->
                <div v-if="!isAdmin && hasPendingPermohonans" class="mb-8">
                    <div class="flex items-center gap-3 mb-4">
                        <Icon icon="solar:clipboard-check-bold-duotone" class="w-6 h-6 text-orange-500" />
                        <h3 class="text-lg font-semibold">Kerjasama Perlu Diisi Monev</h3>
                        <Tag :value="pendingPermohonans.length" severity="warning" />
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <DataTable :value="pendingPermohonans" stripedRows class="p-datatable-sm" tableStyle="min-width: 50rem">
                            <template #empty>Tidak ada kerjasama yang perlu diisi Monev.</template>
                            <Column field="nomor_permohonan" header="Kode" style="width: 150px">
                                <template #body="{ data }">
                                    <span class="font-mono text-xs">{{ data.nomor_permohonan || data.kode }}</span>
                                </template>
                            </Column>
                            <Column field="label" header="Judul Kerjasama" sortable>
                                <template #body="{ data }">
                                    <div class="font-medium">{{ data.label || '-' }}</div>
                                </template>
                            </Column>
                            <Column field="nama_instansi" header="Instansi" sortable></Column>
                            <Column field="kategori.label" header="Kategori">
                                <template #body="{ data }">{{ data.kategori?.label || 'Umum' }}</template>
                            </Column>
                            <Column header="Aksi" style="width: 120px">
                                <template #body="{ data }">
                                    <Button label="Isi Monev" icon="pi pi-pencil" severity="warning" size="small" @click="openFormModal(data)" />
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </div>

                <!-- Riwayat Monev Section -->
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <Icon icon="solar:document-text-bold-duotone" class="w-6 h-6 text-blue-500" />
                        <h3 class="text-lg font-semibold">Riwayat Monev</h3>
                    </div>

                    <!-- Control Bar -->
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 mb-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="relative w-full sm:w-1/3">
                                <Icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                <input v-model="filterQuery" @input="doSearch" type="text" placeholder="Cari kode, instansi..." class="pl-10 pr-4 py-2.5 w-full border border-gray-300 focus:border-blue-500 rounded-lg text-sm dark:bg-gray-700" />
                            </div>
                            <select v-model="statusFilter" @change="doSearch" class="border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
                                <option value="">Semua Status</option>
                                <option value="0">Draft</option>
                                <option value="1">Menunggu Review</option>
                                <option value="2">Sudah Direview</option>
                            </select>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <DataTable :value="datas.data" stripedRows paginator :rows="10" class="p-datatable-sm" tableStyle="min-width: 50rem">
                            <template #empty>Belum ada riwayat Monev.</template>
                            <Column field="kode_monev" header="Kode Monev" style="width: 150px">
                                <template #body="{ data }">
                                    <span class="font-mono text-xs">{{ data.kode_monev }}</span>
                                </template>
                            </Column>
                            <Column field="permohonan.label" header="Kerjasama" sortable>
                                <template #body="{ data }">
                                    <div class="font-medium">{{ data.permohonan?.label || '-' }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ data.permohonan?.nama_instansi }}</div>
                                </template>
                            </Column>
                            <Column header="Tgl Evaluasi" style="width: 130px">
                                <template #body="{ data }">{{ formatDate(data.tanggal_evaluasi) }}</template>
                            </Column>
                            <Column header="Rekomendasi" style="width: 130px">
                                <template #body="{ data }">
                                    <Tag v-if="data.rekomendasi_lanjutan" :value="data.rekomendasi_lanjutan" :severity="data.rekomendasi_lanjutan === 'Dilanjutkan' ? 'success' : data.rekomendasi_lanjutan === 'Diperluas' ? 'info' : 'danger'" />
                                    <span v-else class="text-gray-400">-</span>
                                </template>
                            </Column>
                            <Column header="Status" style="width: 140px">
                                <template #body="{ data }">
                                    <Tag :value="getStatusLabel(data.status)" :severity="getStatusSeverity(data.status)" />
                                </template>
                            </Column>
                            <Column header="Aksi" style="width: 100px">
                                <template #body="{ data }">
                                    <Button icon="pi pi-eye" severity="secondary" rounded outlined size="small" @click="$inertia.visit(route('monev.show', data.uuid))" />
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </div>
            </div>
        </section>

        <!-- Monev Form Modal -->
        <Dialog v-model:visible="formDialog" modal header="Form Monitoring & Evaluasi" :style="{ width: '800px' }" :breakpoints="{ '960px': '90vw' }">
            <form @submit.prevent="submitForm">
                <div class="space-y-4">
                    <!-- Selected Permohonan Info -->
                    <div v-if="selectedPermohonan" class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <div class="font-bold text-blue-900">{{ selectedPermohonan.label }}</div>
                        <div class="text-sm text-blue-700">{{ selectedPermohonan.nama_instansi }}</div>
                    </div>

                    <!-- Tanggal Evaluasi -->
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium">Tanggal Evaluasi <span class="text-red-500">*</span></label>
                        <Calendar v-model="form.tanggal_evaluasi" dateFormat="dd/mm/yy" class="w-full" />
                    </div>

                    <!-- Questions by Section -->
                    <template v-for="(questionsInSection, sectionName) in groupedQuestions" :key="sectionName">
                        <div class="pt-3 border-t border-gray-200">
                            <h4 class="text-sm font-bold text-gray-700 mb-3">{{ sectionName }}</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div v-for="q in questionsInSection" :key="q.key" class="flex flex-col gap-1">
                                    <label class="text-sm">{{ q.label }} <span class="text-red-500">*</span></label>
                                    <Dropdown v-model="form[q.key]" :options="options[q.key]" placeholder="Pilih" class="w-full" :class="{ 'p-invalid': form.errors[q.key] }" />
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Kendala & Saran -->
                    <div class="pt-3 border-t border-gray-200 grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium">Kendala Administrasi</label>
                            <Textarea v-model="form.kendala_administrasi" rows="2" class="w-full" placeholder="Jelaskan jika ada..." />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium">Saran & Rekomendasi</label>
                            <Textarea v-model="form.saran_rekomendasi" rows="2" class="w-full" placeholder="Tuliskan saran..." />
                        </div>
                    </div>

                    <!-- File Upload -->
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium">Bukti Pendukung (opsional)</label>
                        <FileUpload mode="basic" accept=".pdf,.jpg,.jpeg,.png" :maxFileSize="5000000" @select="handleFileSelect" chooseLabel="Pilih File" class="w-full" />
                        <small class="text-gray-500">Format: PDF, JPG, PNG. Maks 5MB</small>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-200">
                    <Button label="Batal" severity="secondary" text @click="formDialog = false" />
                    <Button type="submit" label="Submit Evaluasi" icon="pi pi-check" :loading="form.processing" />
                </div>
            </form>
        </Dialog>
    </AuthenticatedLayout>
</template>
