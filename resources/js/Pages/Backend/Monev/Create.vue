<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Icon } from '@iconify/vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import Calendar from 'primevue/calendar';
import FileUpload from 'primevue/fileupload';

const props = defineProps({
    share: Object,
    permohonans: Array,
    selectedPermohonan: Object,
    operator: Object,
});

const form = useForm({
    id_permohonan: props.selectedPermohonan?.id || null,
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

// Options for dropdowns
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
    { key: 'kesesuaian_tujuan', label: 'Apakah pelaksanaan kegiatan kerjasama telah sesuai dengan tujuan yang tercantum dalam naskah perjanjian?', section: 'Evaluasi Pelaksanaan' },
    { key: 'ketepatan_waktu', label: 'Sejauh mana kegiatan kerjasama telah dilaksanakan sesuai dengan rencana kerja/timeline yang disepakati?', section: 'Evaluasi Pelaksanaan' },
    { key: 'kontribusi_mitra', label: 'Apakah pihak mitra berkontribusi sesuai dengan kesepakatan dalam perjanjian (pendanaan, tenaga, fasilitas, dsb)?', section: 'Evaluasi Pelaksanaan' },
    { key: 'tingkat_koordinasi', label: 'Bagaimana tingkat koordinasi antara perangkat daerah dan mitra dalam pelaksanaan kerjasama?', section: 'Evaluasi Pelaksanaan' },
    { key: 'capaian_indikator', label: 'Apakah indikator kinerja (output/outcome) yang ditetapkan dalam perjanjian telah tercapai?', section: 'Capaian & Dampak' },
    { key: 'dampak_pelaksanaan', label: 'Bagaimana dampak nyata pelaksanaan kerjasama terhadap pelayanan publik/pembangunan daerah?', section: 'Capaian & Dampak' },
    { key: 'inovasi_manfaat', label: 'Apakah terdapat inovasi, nilai tambah, atau manfaat strategis dari pelaksanaan kerjasama ini bagi Pemerintah Kota Samarinda?', section: 'Capaian & Dampak' },
    { key: 'kelengkapan_dokumen', label: 'Apakah pelaksanaan kerjasama didukung dengan dokumen administrasi yang lengkap (MoU, PKS, laporan kegiatan, laporan keuangan)?', section: 'Administrasi' },
    { key: 'pelaporan_berkala', label: 'Apakah dilakukan pelaporan berkala kepada Bagian Kerjasama atau instansi terkait?', section: 'Administrasi' },
    { key: 'relevansi_kebutuhan', label: 'Apakah kerjasama ini masih relevan dengan kebutuhan daerah saat ini?', section: 'Rekomendasi' },
    { key: 'rekomendasi_lanjutan', label: 'Apakah kerjasama ini layak untuk dilanjutkan, diperluas, atau dihentikan?', section: 'Rekomendasi' },
];

const groupedQuestions = computed(() => {
    const groups = {};
    questions.forEach(q => {
        if (!groups[q.section]) groups[q.section] = [];
        groups[q.section].push(q);
    });
    return groups;
});

const handleFileSelect = (event) => {
    form.file_bukti = event.files[0];
};

const submit = () => {
    form.post(route('monev.store'), {
        forceFormData: true,
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
            <div class="mx-auto max-w-4xl px-6 lg:px-8">
                <Breadcrumb class="mb-6" />

                <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <!-- Header -->
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <Icon icon="solar:clipboard-check-bold-duotone" class="w-6 h-6 text-blue-600" />
                            Form Monitoring & Evaluasi Kerjasama
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Isi form evaluasi setelah kerjasama selesai dilaksanakan</p>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- Pilih Kerjasama -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Kerjasama <span class="text-red-500">*</span></label>
                            <Dropdown 
                                v-model="form.id_permohonan"
                                :options="permohonans"
                                optionLabel="label"
                                optionValue="id"
                                placeholder="Pilih kerjasama yang akan dievaluasi"
                                class="w-full"
                                :class="{ 'p-invalid': form.errors.id_permohonan }"
                            />
                            <small v-if="form.errors.id_permohonan" class="text-red-500">{{ form.errors.id_permohonan }}</small>
                        </div>

                        <!-- Tanggal Evaluasi -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Evaluasi <span class="text-red-500">*</span></label>
                            <Calendar v-model="form.tanggal_evaluasi" dateFormat="dd/mm/yy" class="w-full" />
                        </div>

                        <!-- Questions grouped by section -->
                        <template v-for="(questionsInSection, sectionName) in groupedQuestions" :key="sectionName">
                            <div class="pt-4 border-t border-gray-100">
                                <h4 class="text-sm font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                                    <Icon icon="solar:widget-bold" class="w-4 h-4 text-blue-500" />
                                    {{ sectionName }}
                                </h4>
                                <div class="space-y-4">
                                    <div v-for="q in questionsInSection" :key="q.key" class="space-y-2">
                                        <label class="block text-sm text-gray-700 dark:text-gray-300">{{ q.label }} <span class="text-red-500">*</span></label>
                                        <Dropdown 
                                            v-model="form[q.key]"
                                            :options="options[q.key]"
                                            placeholder="Pilih jawaban"
                                            class="w-full"
                                            :class="{ 'p-invalid': form.errors[q.key] }"
                                        />
                                        <small v-if="form.errors[q.key]" class="text-red-500">{{ form.errors[q.key] }}</small>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Kendala Administrasi -->
                        <div class="pt-4 border-t border-gray-100 space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Apakah ada kendala dalam proses administrasi atau pelaporan kerjasama?</label>
                            <Textarea v-model="form.kendala_administrasi" rows="3" class="w-full" placeholder="Jelaskan kendala jika ada..." />
                        </div>

                        <!-- Saran Rekomendasi -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Saran atau rekomendasi untuk peningkatan efektivitas pelaksanaan kerjasama ke depan</label>
                            <Textarea v-model="form.saran_rekomendasi" rows="3" class="w-full" placeholder="Tuliskan saran dan rekomendasi..." />
                        </div>

                        <!-- File Upload -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Upload Bukti Pendukung (opsional)</label>
                            <FileUpload 
                                mode="basic" 
                                accept=".pdf,.jpg,.jpeg,.png"
                                :maxFileSize="5000000"
                                @select="handleFileSelect"
                                chooseLabel="Pilih File"
                                class="w-full"
                            />
                            <small class="text-gray-500">Format: PDF, JPG, PNG. Maks 5MB</small>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="p-6 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
                        <Link :href="route('monev.index')">
                            <Button label="Batal" severity="secondary" text />
                        </Link>
                        <Button 
                            type="submit" 
                            label="Submit Evaluasi" 
                            icon="pi pi-check" 
                            :loading="form.processing"
                        />
                    </div>
                </form>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
