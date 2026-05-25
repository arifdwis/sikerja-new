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
    
    if (positives.includes(answer)) return 'text-emerald-700';
    if (neutrals.includes(answer)) return 'text-amber-700';
    return 'text-rose-700';
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

        <div class="py-8">
            <div class="mx-auto max-w-full sm:px-6 lg:px-8">
                <Breadcrumb class="mb-6" />

                <div class="w-full rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-gray-800 md:p-6">
                    <!-- Header -->
                    <div class="mb-6 flex flex-col gap-4 rounded-xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800 md:flex-row md:items-start md:justify-between">
                        <div class="flex items-start gap-3">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-white text-slate-700 shadow-sm dark:bg-slate-700 dark:text-white">
                                <Icon icon="solar:clipboard-check-bold" class="h-6 w-6" />
                            </span>
                            <div>
                            <span class="mb-1 block font-mono text-xs text-gray-500">{{ monev.kode_monev }}</span>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                {{ monev.permohonan?.label }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ monev.permohonan?.nama_instansi }}</p>
                            <p class="mt-2 flex items-center gap-1.5 text-sm text-gray-500">
                                <Icon icon="solar:calendar-linear" class="h-4 w-4" />
                                Tanggal Evaluasi: <strong>{{ formatDate(monev.tanggal_evaluasi) }}</strong>
                            </p>
                            </div>
                        </div>
                        <span class="inline-flex w-fit items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1 text-sm font-medium text-emerald-700">
                            <Icon icon="solar:check-circle-bold" class="w-4 h-4" />
                            Selesai
                        </span>
                    </div>

                    <!-- Content -->
                    <div class="space-y-6">
                        <!-- Grouped Answers -->
                        <div class="grid gap-4 xl:grid-cols-2">
                            <template v-for="section in ['Evaluasi Pelaksanaan', 'Capaian & Dampak', 'Administrasi', 'Rekomendasi']" :key="section">
                            <div class="rounded-xl border border-slate-200 p-4">
                                <h4 class="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-800 dark:text-white">
                                    <Icon icon="solar:widget-bold" class="h-4 w-4 text-slate-500" />
                                    {{ section }}
                                </h4>
                                <div class="space-y-0">
                                    <template v-for="q in questions.filter(x => x.section === section)" :key="q.key">
                                        <div class="flex flex-col gap-1 border-b border-slate-100 py-2 last:border-0 sm:flex-row sm:items-start sm:justify-between">
                                            <p class="text-sm text-gray-500">{{ q.label }}</p>
                                            <p 
                                                class="text-sm font-medium sm:text-right"
                                                :class="getAnswerColor(monev[q.key])"
                                            >
                                                {{ monev[q.key] || '-' }}
                                            </p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            </template>
                        </div>

                        <!-- Text Answers -->
                        <div v-if="monev.kendala_administrasi || monev.saran_rekomendasi" class="grid grid-cols-1 gap-4 border-t border-gray-200 pt-4 md:grid-cols-2">
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
                        <div v-if="monev.file_bukti" class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <h4 class="mb-2 text-sm font-semibold text-gray-800 dark:text-white">Bukti Pendukung</h4>
                            <a 
                                :href="`/storage/${monev.file_bukti}`" 
                                target="_blank"
                                class="inline-flex items-center gap-2 text-sm font-medium text-slate-700 hover:text-slate-900 hover:underline"
                            >
                                <Icon icon="solar:file-download-bold" class="w-5 h-5" />
                                Lihat/Unduh Bukti
                            </a>
                        </div>

                        <!-- Rating (Req 13) -->
                        <div v-if="monev.rating" class="pt-4 border-t border-gray-200">
                            <h4 class="text-sm font-bold text-gray-800 dark:text-white mb-2">Penilaian</h4>
                            <div class="flex items-center gap-1">
                                <Icon
                                    v-for="n in 5"
                                    :key="n"
                                    :icon="monev.rating >= n ? 'solar:star-bold' : 'solar:star-line-duotone'"
                                    class="w-6 h-6"
                                        :class="monev.rating >= n ? 'text-slate-800' : 'text-gray-300'"
                                />
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">{{ monev.rating }} dari 5</span>
                            </div>
                        </div>

                        <!-- Status TKKSD Lokus (Req 11) -->
                        <div v-if="monev.tkksd_lokus" class="pt-4 border-t border-gray-200">
                            <h4 class="text-sm font-bold text-gray-800 dark:text-white mb-2">Persetujuan TKKSD Lokus</h4>
                            <div class="space-y-1 rounded-lg border border-slate-200 bg-slate-50 p-3">
                                <p class="text-sm">
                                    <span class="font-medium">Reviewer:</span>
                                    {{ monev.tkksd_lokus.name }}
                                </p>
                                <p v-if="monev.tkksd_approved_at" class="text-sm text-emerald-700 dark:text-green-400">
                                    <Icon icon="solar:verified-check-bold" class="inline w-4 h-4" />
                                    Disetujui pada {{ new Date(monev.tkksd_approved_at).toLocaleString('id-ID') }}
                                </p>
                                <p v-if="monev.tkksd_catatan" class="text-sm text-gray-600 dark:text-gray-300">
                                    <span class="font-medium">Catatan:</span> {{ monev.tkksd_catatan }}
                                </p>
                            </div>
                        </div>

                        <!-- Tipe Monev (Req 16) -->
                        <div v-if="monev.tipe === 'manual'" class="pt-4 border-t border-gray-200">
                            <span class="inline-flex items-center gap-1 rounded border border-slate-200 bg-slate-50 px-2 py-1 text-xs text-slate-600">
                                <Icon icon="solar:hand-shake-bold" class="w-3 h-3" />
                                Monev Manual (Admin)
                            </span>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="mt-6 flex justify-start border-t border-gray-200 pt-6 dark:border-gray-700">
                        <Link :href="route('monev.index')">
                            <Button label="Kembali" icon="pi pi-arrow-left" severity="secondary" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
