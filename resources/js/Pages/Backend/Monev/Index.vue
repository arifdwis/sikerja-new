<script setup>
import { ref, watch, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
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

const props = defineProps({
    datas: Object,
    pendingPermohonans: Array,
    share: Object,
    filters: Object,
    isAdmin: Boolean,
});

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

const openDetailModal = (monev) => {
    selectedMonev.value = monev;
    detailDialog.value = true;
};

const getAnswerColor = (answer) => {
    const positives = ['Ya seluruhnya', 'Ya sepenuhnya', 'Tepat waktu', 'Sangat baik', 'Tercapai seluruhnya', 'Sangat berdampak', 'Ya signifikan', 'Lengkap', 'Rutin', 'Sangat relevan', 'Dilanjutkan'];
    const neutrals = ['Sebagian', 'Baik', 'Cukup', 'Ada', 'Kadang', 'Diperluas'];
    
    if (positives.includes(answer)) return 'text-green-600 bg-green-50';
    if (neutrals.includes(answer)) return 'text-yellow-600 bg-yellow-50';
    return 'text-red-600 bg-red-50';
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

        <section class="py-12">
            <div class="mx-auto max-w-full px-6 lg:px-8">
                <!-- Breadcrumb -->
                <Breadcrumb class="mb-6" />

                <!-- Control Bar -->
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="relative w-full sm:w-1/2">
                            <Icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <input v-model="filterQuery" type="text" placeholder="Cari monev..." class="pl-10 pr-4 py-2.5 w-full border border-gray-300 focus:border-green-500 rounded-lg text-sm dark:bg-gray-700" />
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 dark:border-gray-700 p-6">
                    <TabView class="w-full">
                        
                        <!-- Perlu Evaluasi Tab -->
                        <TabPanel v-if="isAdmin">
                            <template #header>
                                <div class="flex items-center gap-2">
                                    <Icon icon="solar:clipboard-check-bold" class="w-4 h-4 text-orange-500" />
                                    <span>Perlu Evaluasi</span>
                                    <Tag :value="pendingCount" severity="warning" />
                                </div>
                            </template>
                            
                            <div v-if="pendingCount > 0" class="mb-6 p-4 bg-orange-50 border-l-4 border-orange-400 rounded-r-lg flex items-start gap-4">
                                <div class="bg-orange-100 p-2 rounded-full text-orange-600">
                                    <Icon icon="solar:clipboard-check-bold-duotone" class="w-6 h-6" />
                                </div>
                                <div>
                                    <h4 class="font-bold text-orange-800 text-lg">Kerjasama Perlu Evaluasi</h4>
                                    <p class="text-sm text-orange-700 mt-1">Terdapat <span class="font-bold">{{ pendingCount }}</span> kerjasama yang telah berakhir dan perlu diisi form Monev.</p>
                                </div>
                            </div>
                            
                            <DataTable :value="pendingPermohonans" paginator :rows="10" stripedRows tableStyle="min-width: 50rem" class="p-datatable-sm mt-4">
                                <template #empty>Tidak ada kerjasama yang perlu evaluasi.</template>
                                <Column field="label" header="Judul Kerjasama" sortable>
                                    <template #body="{ data }">
                                        <div class="font-bold text-gray-800 dark:text-white text-base">{{ data.label }}</div>
                                        <div class="text-xs text-gray-500 mt-1">{{ data.kategori?.label || 'Umum' }}</div>
                                    </template>
                                </Column>
                                <Column field="nama_instansi" header="Instansi" sortable></Column>
                                <Column header="Tanggal Berakhir">
                                    <template #body="{ data }">
                                        <div class="text-sm font-bold text-red-600">{{ formatDate(data.tanggal_berakhir) }}</div>
                                    </template>
                                </Column>
                                <Column header="Status">
                                    <template #body>
                                        <Tag value="Belum Evaluasi" severity="warning" />
                                    </template>
                                </Column>
                                <Column header="Aksi" style="width: 120px">
                                    <template #body="{ data }">
                                        <Button label="Isi Monev" icon="pi pi-pencil" severity="warning" size="small" @click="openFormModal(data)" />
                                    </template>
                                </Column>
                            </DataTable>
                        </TabPanel>
                        
                        <!-- Riwayat Monev Tab -->
                        <TabPanel>
                            <template #header>
                                <div class="flex items-center gap-2">
                                    <Icon icon="solar:document-text-bold" class="w-4 h-4 text-blue-500" />
                                    <span>Riwayat Monev</span>
                                    <Tag :value="completedCount" severity="info" />
                                </div>
                            </template>

                            <DataTable :value="datas?.data || []" paginator :rows="10" stripedRows tableStyle="min-width: 50rem" class="mt-4">
                                <template #empty>Belum ada riwayat Monev.</template>
                                <Column field="kode_monev" header="Kode Monev">
                                    <template #body="{ data }">
                                        <span class="font-mono text-xs">{{ data.kode_monev }}</span>
                                    </template>
                                </Column>
                                <Column field="permohonan.label" header="Kerjasama" sortable>
                                    <template #body="{ data }">
                                        <div class="font-bold text-gray-800 dark:text-white text-base">{{ data.permohonan?.label || '-' }}</div>
                                        <div class="text-xs text-gray-500 mt-1">{{ data.permohonan?.nama_instansi }}</div>
                                    </template>
                                </Column>
                                <Column header="Tgl Evaluasi">
                                    <template #body="{ data }">{{ formatDate(data.tanggal_evaluasi) }}</template>
                                </Column>
                                <Column header="Rekomendasi">
                                    <template #body="{ data }">
                                        <Tag v-if="data.rekomendasi_lanjutan" :value="data.rekomendasi_lanjutan" :severity="data.rekomendasi_lanjutan === 'Dilanjutkan' ? 'success' : data.rekomendasi_lanjutan === 'Diperluas' ? 'info' : 'danger'" />
                                        <span v-else class="text-gray-400">-</span>
                                    </template>
                                </Column>
                                <Column header="Aksi" style="width: 100px">
                                    <template #body="{ data }">
                                        <Button icon="pi pi-eye" severity="secondary" rounded outlined @click="openDetailModal(data)" />
                                    </template>
                                </Column>
                            </DataTable>
                        </TabPanel>
                    </TabView>
                </div>
            </div>
        </section>

        <!-- Create Form Modal -->
        <Dialog v-model:visible="formDialog" modal header="Form Monitoring & Evaluasi" :style="{ width: '700px' }" :breakpoints="{ '960px': '90vw' }">
            <form @submit.prevent="submitForm">
                <div class="space-y-4">
                    <!-- Selected Permohonan Info -->
                    <div v-if="selectedPermohonan" class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                        <div class="font-bold text-blue-900 text-sm">{{ selectedPermohonan.label }}</div>
                        <div class="text-xs text-blue-700">{{ selectedPermohonan.nama_instansi }}</div>
                    </div>

                    <!-- Tanggal Evaluasi -->
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium">Tanggal Evaluasi <span class="text-red-500">*</span></label>
                        <Calendar v-model="form.tanggal_evaluasi" dateFormat="dd/mm/yy" class="w-full" />
                    </div>

                    <!-- Questions -->
                    <div class="grid grid-cols-2 gap-3">
                        <div v-for="q in questions" :key="q.key" class="flex flex-col gap-1">
                            <label class="text-xs font-medium text-gray-600">{{ q.label }} <span class="text-red-500">*</span></label>
                            <Dropdown v-model="form[q.key]" :options="options[q.key]" placeholder="Pilih" class="w-full text-sm" size="small" :class="{ 'p-invalid': form.errors[q.key] }" />
                        </div>
                    </div>

                    <!-- Kendala & Saran -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-medium text-gray-600">Kendala Administrasi</label>
                            <Textarea v-model="form.kendala_administrasi" rows="2" class="w-full text-sm" placeholder="Jelaskan..." />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-medium text-gray-600">Saran & Rekomendasi</label>
                            <Textarea v-model="form.saran_rekomendasi" rows="2" class="w-full text-sm" placeholder="Tuliskan..." />
                        </div>
                    </div>

                    <!-- File Upload -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-600">Bukti Pendukung (opsional)</label>
                        <FileUpload mode="basic" accept=".pdf,.jpg,.jpeg,.png" :maxFileSize="5000000" @select="handleFileSelect" chooseLabel="Pilih File" class="w-full" />
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-4 pt-4 border-t">
                    <Button label="Batal" severity="secondary" text size="small" @click="formDialog = false" />
                    <Button type="submit" label="Simpan" icon="pi pi-check" size="small" :loading="form.processing" />
                </div>
            </form>
        </Dialog>

        <!-- Detail Modal -->
        <Dialog v-model:visible="detailDialog" modal header="Detail Monev" :style="{ width: '800px' }" :breakpoints="{ '960px': '90vw' }">
            <div v-if="selectedMonev" class="space-y-4">
                <!-- Header Info -->
                <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-100">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-xs font-mono text-gray-500">{{ selectedMonev.kode_monev }}</span>
                            <h3 class="text-lg font-bold text-gray-900">{{ selectedMonev.permohonan?.label }}</h3>
                            <p class="text-sm text-gray-600">{{ selectedMonev.permohonan?.nama_instansi }}</p>
                            <p class="text-sm text-gray-500 mt-1">Evaluasi: <strong>{{ formatDate(selectedMonev.tanggal_evaluasi) }}</strong></p>
                        </div>
                        <Tag value="Selesai" severity="success" />
                    </div>
                </div>

                <!-- Answers Grid -->
                <div class="space-y-4">
                    <template v-for="section in ['Pelaksanaan', 'Capaian', 'Administrasi', 'Rekomendasi']" :key="section">
                        <div>
                            <h4 class="text-sm font-bold text-gray-800 mb-2 flex items-center gap-2">
                                <Icon icon="solar:widget-bold" class="w-4 h-4 text-blue-500" />
                                {{ section === 'Pelaksanaan' ? 'Evaluasi Pelaksanaan' : section === 'Capaian' ? 'Capaian & Dampak' : section }}
                            </h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                <template v-for="q in questions.filter(x => x.section === section)" :key="q.key">
                                    <div class="p-2 bg-gray-50 rounded-lg">
                                        <p class="text-xs text-gray-500">{{ q.label }}</p>
                                        <p class="font-medium text-sm px-2 py-0.5 rounded inline-block mt-1" :class="getAnswerColor(selectedMonev[q.key])">
                                            {{ selectedMonev[q.key] || '-' }}
                                        </p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Notes -->
                <div v-if="selectedMonev.kendala_administrasi || selectedMonev.saran_rekomendasi" class="grid grid-cols-2 gap-4 pt-4 border-t">
                    <div v-if="selectedMonev.kendala_administrasi">
                        <h4 class="text-sm font-bold text-gray-800 mb-1">Kendala</h4>
                        <p class="text-sm text-gray-600 bg-gray-50 p-2 rounded">{{ selectedMonev.kendala_administrasi }}</p>
                    </div>
                    <div v-if="selectedMonev.saran_rekomendasi">
                        <h4 class="text-sm font-bold text-gray-800 mb-1">Saran</h4>
                        <p class="text-sm text-gray-600 bg-gray-50 p-2 rounded">{{ selectedMonev.saran_rekomendasi }}</p>
                    </div>
                </div>

                <!-- File -->
                <div v-if="selectedMonev.file_bukti" class="pt-4 border-t">
                    <a :href="`/storage/${selectedMonev.file_bukti}`" target="_blank" class="inline-flex items-center gap-2 text-sm text-blue-600 hover:underline">
                        <Icon icon="solar:file-download-bold" class="w-5 h-5" />
                        Lihat/Unduh Bukti
                    </a>
                </div>
            </div>
            <template #footer>
                <Button label="Tutup" severity="secondary" @click="detailDialog = false" />
            </template>
        </Dialog>
    </AuthenticatedLayout>
</template>
