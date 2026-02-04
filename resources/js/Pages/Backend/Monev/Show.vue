<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Icon } from '@iconify/vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import Button from 'primevue/button';

const props = defineProps({
    share: Object,
    monev: Object,
    isAdmin: Boolean,
});

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
</script>

<template>
    <Head :title="share.title" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                {{ share.title }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-full sm:px-6 lg:px-8">
                <Breadcrumb class="mb-6" />

                <div class="max-w-full w-full bg-white rounded-lg shadow-sm dark:bg-gray-800 p-4 md:p-6">
                    <!-- Header -->
                    <div class="flex items-start justify-between mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                        <div>
                            <span class="text-xs font-mono text-gray-500 mb-1 block">{{ monev.kode_monev }}</span>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ monev.permohonan?.label }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ monev.permohonan?.nama_instansi }}</p>
                            <p class="text-sm text-gray-500 mt-2">
                                Tanggal Evaluasi: <strong>{{ formatDate(monev.tanggal_evaluasi) }}</strong>
                            </p>
                        </div>
                        <span class="px-3 py-1 text-sm font-medium rounded-full bg-green-100 text-green-700 flex items-center gap-1">
                            <Icon icon="solar:check-circle-bold" class="w-4 h-4" />
                            Selesai
                        </span>
                    </div>

                    <!-- Content -->
                    <div class="space-y-6">
                        <!-- Grouped Answers -->
                        <template v-for="section in ['Evaluasi Pelaksanaan', 'Capaian & Dampak', 'Administrasi', 'Rekomendasi']" :key="section">
                            <div>
                                <h4 class="text-sm font-bold text-gray-800 dark:text-white mb-3 flex items-center gap-2">
                                    <Icon icon="solar:widget-bold" class="w-4 h-4 text-blue-500" />
                                    {{ section }}
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
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
                        <div v-if="monev.kendala_administrasi || monev.saran_rekomendasi" class="pt-4 border-t border-gray-200 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-if="monev.kendala_administrasi">
                                <h4 class="text-sm font-bold text-gray-800 dark:text-white mb-2">Kendala Administrasi</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg">
                                    {{ monev.kendala_administrasi }}
                                </p>
                            </div>

                            <div v-if="monev.saran_rekomendasi">
                                <h4 class="text-sm font-bold text-gray-800 dark:text-white mb-2">Saran & Rekomendasi</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg">
                                    {{ monev.saran_rekomendasi }}
                                </p>
                            </div>
                        </div>

                        <!-- File Bukti -->
                        <div v-if="monev.file_bukti" class="pt-4 border-t border-gray-200">
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
                    </div>

                    <!-- Footer -->
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 flex justify-start">
                        <Link :href="route('monev.index')">
                            <Button label="Kembali" icon="pi pi-arrow-left" severity="secondary" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
