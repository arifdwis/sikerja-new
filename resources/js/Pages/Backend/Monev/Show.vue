<script setup>
import { ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Icon } from '@iconify/vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';

const props = defineProps({
    share: Object,
    monev: Object,
    isAdmin: Boolean,
});

const reviewDialog = ref(false);
const reviewForm = useForm({
    catatan_admin: '',
});

const getStatusBadge = (status) => {
    const badges = {
        0: { label: 'Draft', class: 'bg-gray-100 text-gray-700', icon: 'solar:document-bold' },
        1: { label: 'Menunggu Review', class: 'bg-orange-100 text-orange-700', icon: 'solar:clock-circle-bold' },
        2: { label: 'Sudah Direview', class: 'bg-green-100 text-green-700', icon: 'solar:check-circle-bold' },
    };
    return badges[status] || badges[0];
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const getAnswerColor = (answer) => {
    const positives = ['Ya seluruhnya', 'Ya sepenuhnya', 'Tepat waktu', 'Sangat baik', 'Tercapai seluruhnya', 'Sangat berdampak', 'Ya signifikan', 'Lengkap', 'Rutin', 'Sangat relevan', 'Dilanjutkan'];
    const neutrals = ['Sebagian', 'Baik', 'Cukup', 'Ada', 'Kadang', 'Diperluas'];
    
    if (positives.includes(answer)) return 'text-green-600 bg-green-50';
    if (neutrals.includes(answer)) return 'text-yellow-600 bg-yellow-50';
    return 'text-red-600 bg-red-50';
};

const questions = [
    { key: 'kesesuaian_tujuan', label: 'Kesesuaian dengan Tujuan Perjanjian', section: 'Evaluasi Pelaksanaan' },
    { key: 'ketepatan_waktu', label: 'Ketepatan Waktu Pelaksanaan', section: 'Evaluasi Pelaksanaan' },
    { key: 'kontribusi_mitra', label: 'Kontribusi Pihak Mitra', section: 'Evaluasi Pelaksanaan' },
    { key: 'tingkat_koordinasi', label: 'Tingkat Koordinasi', section: 'Evaluasi Pelaksanaan' },
    { key: 'capaian_indikator', label: 'Capaian Indikator Kinerja', section: 'Capaian & Dampak' },
    { key: 'dampak_pelaksanaan', label: 'Dampak terhadap Pelayanan Publik', section: 'Capaian & Dampak' },
    { key: 'inovasi_manfaat', label: 'Inovasi & Manfaat Strategis', section: 'Capaian & Dampak' },
    { key: 'kelengkapan_dokumen', label: 'Kelengkapan Dokumen Administrasi', section: 'Administrasi' },
    { key: 'pelaporan_berkala', label: 'Pelaporan Berkala', section: 'Administrasi' },
    { key: 'relevansi_kebutuhan', label: 'Relevansi dengan Kebutuhan Daerah', section: 'Rekomendasi' },
    { key: 'rekomendasi_lanjutan', label: 'Rekomendasi Tindak Lanjut', section: 'Rekomendasi' },
];

const submitReview = () => {
    reviewForm.post(route('monev.review', props.monev.uuid), {
        onSuccess: () => {
            reviewDialog.value = false;
        }
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

                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <!-- Header -->
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="text-xs font-mono text-gray-500 mb-1 block">{{ monev.kode_monev }}</span>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                    {{ monev.permohonan?.label }}
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ monev.permohonan?.nama_instansi }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span 
                                    class="px-3 py-1 text-sm font-medium rounded-full flex items-center gap-1"
                                    :class="getStatusBadge(monev.status).class"
                                >
                                    <Icon :icon="getStatusBadge(monev.status).icon" class="w-4 h-4" />
                                    {{ getStatusBadge(monev.status).label }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-3 text-sm text-gray-500">
                            <span>Tanggal Evaluasi: <strong>{{ formatDate(monev.tanggal_evaluasi) }}</strong></span>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-6 space-y-6">
                        <!-- Grouped Answers -->
                        <template v-for="section in ['Evaluasi Pelaksanaan', 'Capaian & Dampak', 'Administrasi', 'Rekomendasi']" :key="section">
                            <div>
                                <h4 class="text-sm font-bold text-gray-800 dark:text-white mb-3 flex items-center gap-2">
                                    <Icon icon="solar:widget-bold" class="w-4 h-4 text-blue-500" />
                                    {{ section }}
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <template v-for="q in questions.filter(x => x.section === section)" :key="q.key">
                                        <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                            <p class="text-xs text-gray-500 mb-1">{{ q.label }}</p>
                                            <p 
                                                class="font-medium text-sm px-2 py-1 rounded inline-block"
                                                :class="getAnswerColor(monev[q.key])"
                                            >
                                                {{ monev[q.key] || '-' }}
                                            </p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Text Answers -->
                        <div v-if="monev.kendala_administrasi" class="pt-4 border-t border-gray-100">
                            <h4 class="text-sm font-bold text-gray-800 dark:text-white mb-2">Kendala Administrasi</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg">
                                {{ monev.kendala_administrasi }}
                            </p>
                        </div>

                        <div v-if="monev.saran_rekomendasi" class="pt-4 border-t border-gray-100">
                            <h4 class="text-sm font-bold text-gray-800 dark:text-white mb-2">Saran & Rekomendasi</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg">
                                {{ monev.saran_rekomendasi }}
                            </p>
                        </div>

                        <!-- File Bukti -->
                        <div v-if="monev.file_bukti" class="pt-4 border-t border-gray-100">
                            <h4 class="text-sm font-bold text-gray-800 dark:text-white mb-2">Bukti Pendukung</h4>
                            <a 
                                :href="`/storage/${monev.file_bukti}`" 
                                target="_blank"
                                class="inline-flex items-center gap-2 text-sm text-blue-600 hover:underline"
                            >
                                <Icon icon="solar:file-download-bold" class="w-5 h-5" />
                                Lihat/Unduh Bukti
                            </a>
                        </div>

                        <!-- Admin Review Section -->
                        <div v-if="monev.status === 2 && monev.catatan_admin" class="pt-4 border-t border-gray-100">
                            <h4 class="text-sm font-bold text-green-700 mb-2 flex items-center gap-2">
                                <Icon icon="solar:shield-check-bold" class="w-5 h-5" />
                                Catatan dari Administrator
                            </h4>
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <p class="text-sm text-green-800">{{ monev.catatan_admin }}</p>
                                <p class="text-xs text-green-600 mt-2">
                                    Direview oleh {{ monev.reviewer?.name }} pada {{ formatDate(monev.reviewed_at) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="p-6 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700 flex justify-between">
                        <Link :href="route('monev.index')">
                            <Button label="Kembali" icon="pi pi-arrow-left" severity="secondary" text />
                        </Link>
                        <Button 
                            v-if="isAdmin && monev.status === 1"
                            label="Review & Setujui" 
                            icon="pi pi-check" 
                            @click="reviewDialog = true"
                        />
                    </div>
                </div>
            </div>
        </section>

        <!-- Review Dialog -->
        <Dialog v-model:visible="reviewDialog" modal header="Review Monev" :style="{ width: '500px' }">
            <div class="space-y-4">
                <p class="text-sm text-gray-600">
                    Anda akan mereview dan menyetujui form Monev ini. Tambahkan catatan jika diperlukan.
                </p>
                <div>
                    <label class="block text-sm font-medium mb-2">Catatan (Opsional)</label>
                    <Textarea v-model="reviewForm.catatan_admin" rows="4" class="w-full" placeholder="Tuliskan catatan untuk pemohon..." />
                </div>
            </div>
            <template #footer>
                <Button label="Batal" severity="secondary" text @click="reviewDialog = false" />
                <Button label="Setujui" icon="pi pi-check" @click="submitReview" :loading="reviewForm.processing" />
            </template>
        </Dialog>
    </AuthenticatedLayout>
</template>
