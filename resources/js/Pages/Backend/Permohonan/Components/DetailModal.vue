<script setup>
import { computed } from 'vue';
import { Icon } from '@iconify/vue';
import Dialog from 'primevue/dialog';
import Skeleton from 'primevue/skeleton';
import DetailHeader from './Detail/DetailHeader.vue';
import DetailParties from './Detail/DetailParties.vue';
import DetailSubstance from './Detail/DetailSubstance.vue';
import DetailSchedule from './Detail/DetailSchedule.vue';
import DetailDocuments from './Detail/DetailDocuments.vue';
import DetailContact from './Detail/DetailContact.vue';
import PksSection from './PksSection.vue';
import TtdSection from './TtdSection.vue';

const props = defineProps({
    visible: Boolean,
    loading: Boolean,
    data: Object,
    isAdmin: Boolean
});

const emit = defineEmits(['update:visible', 'refresh', 'upload-complete']);

const handleUpdateVisible = (val) => {
    emit('update:visible', val);
};

const closeAfterUpload = () => {
    emit('update:visible', false);
    emit('upload-complete');
};

// Status mapping (sesuai konstanta Permohonan)
const status = computed(() => Number(props.data?.status ?? 0));
const isPksDoc = computed(() => props.data?.tipe_dokumen === 'PKS');
const docName = computed(() => isPksDoc.value ? 'PKS' : 'Kesepakatan Bersama (KB)');

const showSchedule = computed(() => [2, 3, 4, 5, 6, 7, 8, 22, 23, 25, 26].includes(status.value) || (props.data?.penjadwalans?.length ?? 0) > 0);
const showPks = computed(() => [3, 4, 5, 6, 7, 8, 13, 22, 23, 25, 26].includes(status.value) || (props.data?.pks_files?.length ?? 0) > 0 || (props.data?.pksFiles?.length ?? 0) > 0);
const showTtd = computed(() => [4, 5, 6, 7, 8, 23, 25, 26].includes(status.value) || (props.data?.ttd_files?.length ?? 0) > 0 || (props.data?.ttdFiles?.length ?? 0) > 0);
const showDocuments = computed(() => true);

const isInitialStage = computed(() => [-1, 0, 1, 9, 10, 11, 12, 20, 21].includes(status.value));
const isScheduleStage = computed(() => status.value === 2);
const isSigningStage = computed(() => [3, 4, 5, 13, 22, 23].includes(status.value));
const isExecutionStage = computed(() => [6, 7, 8, 25, 26].includes(status.value));

const stages = [
    { order: 0, ids: [-1, 0, 10, 11, 12, 20], label: 'Validasi', icon: 'solar:clipboard-check-bold' },
    { order: 1, ids: [1, 9, 21], label: 'Pembahasan', icon: 'solar:documents-bold' },
    { order: 2, ids: [2], label: 'Jadwal', icon: 'solar:calendar-date-bold' },
    { order: 3, ids: [3, 4, 5, 13, 22, 23], label: 'Tanda Tangan', icon: 'solar:pen-new-square-bold' },
    { order: 4, ids: [6, 25, 26], label: 'Pelaksanaan', icon: 'solar:rocket-bold' },
    { order: 5, ids: [7], label: 'Selesai', icon: 'solar:verified-check-bold' },
    { order: 6, ids: [8], label: 'Dicabut', icon: 'solar:close-circle-bold' },
];

const stageContext = computed(() => {
    const contexts = {
        '-1': {
            title: 'Draft Permohonan',
            detail: 'Permohonan masih dalam bentuk draft dan belum diajukan secara resmi. Pastikan berkas awal telah dilengkapi.',
            tone: 'border-slate-200 bg-slate-50 text-slate-800',
            icon: 'solar:document-text-bold',
        },
        0: {
            title: 'Tahap Verifikasi Administrasi Staf',
            detail: 'Staf Bagian Kerja Sama memeriksa kelengkapan dokumen permohonan sebelum diteruskan ke Kepala Bagian Kerja Sama.',
            tone: 'border-amber-200 bg-amber-50/50 text-slate-900',
            icon: 'solar:clipboard-check-bold',
        },
        10: {
            title: 'Tahap Validasi & Disposisi Kabag Kerja Sama',
            detail: 'Dokumen administrasi telah diverifikasi staf dan sedang menunggu validasi serta disposisi Kepala Bagian Kerja Sama.',
            tone: 'border-indigo-200 bg-indigo-50 text-indigo-900',
            icon: 'solar:user-check-bold',
        },
        11: {
            title: 'Permohonan Perlu Perbaikan / Revisi',
            detail: 'Bagian Kerja Sama meminta perbaikan data atau kelengkapan berkas. Silakan perbarui data/berkas lalu ajukan ulang.',
            tone: 'border-amber-200 bg-amber-50 text-amber-900',
            icon: 'solar:refresh-bold',
        },
        12: {
            title: 'Terverifikasi Kabag — Siap Pembahasan',
            detail: 'Permohonan telah disetujui Kepala Bagian Kerja Sama dan siap memasuki pembahasan bersama TKKSD.',
            tone: 'border-cyan-200 bg-cyan-50 text-cyan-900',
            icon: 'solar:verified-check-bold',
        },
        1: {
            title: `Tahap Pembahasan Draf ${docName.value}`,
            detail: 'Dokumen sedang ditinjau oleh Tim Koordinasi Kerja Sama Daerah (TKKSD). Revisi dilakukan pada dokumen yang diminta perbaikan.',
            tone: 'border-sky-200 bg-sky-50/40 text-slate-900',
            icon: 'solar:documents-bold',
        },
        13: {
            title: `Finalisasi Draf ${docName.value}`,
            detail: `Draf ${docName.value} telah difinalisasi dan siap dilanjutkan ke penjadwalan penandatanganan.`,
            tone: 'border-purple-200 bg-purple-50 text-purple-900',
            icon: 'solar:document-add-bold',
        },
        2: {
            title: 'Tahap Penjadwalan Penandatanganan',
            detail: 'Pembahasan selesai. Pemohon mengajukan usulan jadwal dan metode penandatanganan untuk disetujui Bagian Kerja Sama.',
            tone: 'border-blue-200 bg-blue-50/40 text-slate-900',
            icon: 'solar:calendar-date-bold',
        },
        3: {
            title: `Penyiapan Naskah Final ${docName.value}`,
            detail: `Jadwal penandatanganan telah disetujui. Admin menyiapkan dan mengunggah PDF naskah final ${docName.value}.`,
            tone: 'border-violet-200 bg-violet-50/40 text-slate-900',
            icon: 'solar:document-add-bold',
        },
        4: {
            title: `Tahap Penandatanganan ${docName.value}`,
            detail: `Naskah final ${docName.value} telah tersedia. Setelah penandatanganan, pemohon wajib mengunggah PDF dokumen tertandatangani beserta checklist paraf, materai, dan stempel.`,
            tone: 'border-pink-200 bg-pink-50/40 text-slate-900',
            icon: 'solar:pen-new-square-bold',
        },
        5: {
            title: 'Tahap Validasi Dokumen Pasca-Tandatangan',
            detail: 'Admin memvalidasi dokumen tertandatangani yang diunggah pemohon sebelum kerja sama resmi memasuki tahap pelaksanaan.',
            tone: 'border-orange-200 bg-orange-50/40 text-slate-900',
            icon: 'solar:shield-check-bold',
        },
        20: {
            title: 'Pengajuan Draf PKS Turunan',
            detail: 'Pengajuan Perjanjian Kerja Sama (PKS) operasional sebagai turunan dari Kesepakatan Bersama (KB) induk.',
            tone: 'border-amber-200 bg-amber-50 text-amber-900',
            icon: 'solar:document-add-bold',
        },
        21: {
            title: 'Pembahasan Teknis PKS',
            detail: 'Pembahasan draf teknis PKS antara Perangkat Daerah (OPD) pengampu, TKKSD, dan mitra.',
            tone: 'border-cyan-200 bg-cyan-50 text-cyan-900',
            icon: 'solar:documents-bold',
        },
        22: {
            title: 'Draf PKS Final',
            detail: 'Naskah PKS final telah disepakati dan siap untuk ditandatangani oleh Kepala OPD dan mitra.',
            tone: 'border-indigo-200 bg-indigo-50 text-indigo-900',
            icon: 'solar:file-check-bold',
        },
        23: {
            title: 'Penandatanganan PKS OPD',
            detail: 'Proses penandatanganan PKS oleh Kepala Perangkat Daerah pengampu bersama pimpinan mitra.',
            tone: 'border-purple-200 bg-purple-50 text-purple-900',
            icon: 'solar:pen-new-square-bold',
        },
        6: {
            title: 'Tahap Pelaksanaan Kerja Sama',
            detail: 'Kerja sama resmi berjalan aktif. Pelaksanaan dipantau berkala melalui modul Monitoring & Evaluasi (Monev).',
            tone: 'border-teal-200 bg-teal-50/40 text-slate-900',
            icon: 'solar:rocket-bold',
        },
        25: {
            title: 'Masa Evaluasi Akhir (H-90 Hari)',
            detail: 'Kerja sama akan berakhir dalam <= 90 hari. Pemohon, TKKSD Lokus, dan Admin wajib mengisi form Monitoring & Evaluasi.',
            tone: 'border-yellow-200 bg-yellow-50 text-yellow-900',
            icon: 'solar:clipboard-list-bold',
        },
        26: {
            title: 'Usulan Perpanjangan Kerja Sama',
            detail: 'Kerja sama dalam proses pengajuan perpanjangan berdasarkan hasil evaluasi pelaksanaan.',
            tone: 'border-blue-200 bg-blue-50 text-blue-900',
            icon: 'solar:refresh-circle-bold',
        },
        7: {
            title: 'Kerja Sama Selesai',
            detail: 'Masa berlaku kerja sama telah selesai. Seluruh dokumen dan riwayat menjadi arsip resmi daerah.',
            tone: 'border-emerald-200 bg-emerald-50/40 text-slate-900',
            icon: 'solar:verified-check-bold',
        },
        8: {
            title: 'Kerja Sama Dicabut',
            detail: 'Kerja sama dihentikan sebelum berakhir karena pelanggaran ketentuan atau kesepakatan pencabutan.',
            tone: 'border-rose-200 bg-rose-50 text-rose-900',
            icon: 'solar:close-circle-bold',
        },
        9: {
            title: 'Permohonan Ditolak / Perlu Revisi',
            detail: 'Permohonan ditolak atau dikembalikan. Silakan periksa alasan penolakan untuk tindak lanjut.',
            tone: 'border-red-200 bg-red-50 text-red-900',
            icon: 'solar:danger-triangle-bold',
        },
    };

    return contexts[status.value] || contexts[0];
});

const stageState = (stage) => {
    const currentOrder = {
        '-1': 0,
        0: 0,
        10: 0,
        11: 0,
        12: 0,
        20: 0,
        1: 1,
        21: 1,
        9: 1,
        2: 2,
        13: 3,
        22: 3,
        23: 3,
        3: 3,
        4: 3,
        5: 3,
        6: 4,
        25: 4,
        26: 4,
        7: 5,
        8: 6,
    }[status.value] ?? 0;

    if (stage.ids.includes(status.value)) return 'active';
    return stage.order < currentOrder ? 'done' : 'pending';
};
</script>

<template>
    <Dialog 
        :visible="visible" 
        @update:visible="handleUpdateVisible"
        modal 
        header="Detail Lengkap Permohonan" 
        :style="{ width: '1180px' }" 
        :breakpoints="{ '1199px': '95vw' }" 
        maximizable 
        class="p-0"
        :contentStyle="{ overflow: 'hidden', padding: 0, display: 'flex', flexDirection: 'column', height: '80vh' }"
    >
        <div v-if="loading" class="flex-1 space-y-6 overflow-y-auto p-6">
                <div class="grid grid-cols-2 gap-6">
                    <Skeleton height="15rem" class="w-full rounded-xl" />
                    <Skeleton height="15rem" class="w-full rounded-xl" />
                </div>
                <Skeleton height="25rem" class="w-full rounded-xl" />
        </div>
        
        <div v-else-if="data" class="flex-1 space-y-6 overflow-y-auto bg-gray-50/50 p-6 dark:bg-gray-900/50">
            <DetailHeader :data="data" />

            <section class="rounded-xl border p-5 shadow-sm" :class="stageContext.tone">
                <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_560px] xl:items-center">
                    <div class="flex min-w-0 gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-700">
                            <Icon :icon="stageContext.icon" class="h-5 w-5" />
                        </span>
                        <div class="min-w-0">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Tahap Saat Ini</p>
                            <h2 class="mt-1 text-base font-bold text-slate-950">{{ stageContext.title }}</h2>
                            <p class="mt-1 max-w-2xl text-sm leading-relaxed text-slate-600">{{ stageContext.detail }}</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-3">
                        <div class="flex min-w-[500px] items-start">
                            <div
                                v-for="(stage, index) in stages"
                                :key="stage.label"
                                class="relative flex flex-1 flex-col items-center px-1 text-center"
                            >
                                <span
                                    v-if="index > 0"
                                    class="absolute left-[-50%] top-4 h-px w-full"
                                    :class="stageState(stage) === 'pending' ? 'bg-slate-200' : 'bg-emerald-300'"
                                ></span>
                                <span
                                    class="relative z-10 flex h-8 w-8 items-center justify-center rounded-full border"
                                    :class="{
                                        'border-slate-900 bg-slate-900 text-white shadow-sm': stageState(stage) === 'active',
                                        'border-emerald-200 bg-white text-emerald-700': stageState(stage) === 'done',
                                        'border-slate-200 bg-white text-slate-400': stageState(stage) === 'pending',
                                    }"
                                >
                                    <Icon :icon="stage.icon" class="h-4 w-4" />
                                </span>
                                <p
                                    class="relative z-10 mt-2 text-[11px] font-medium leading-tight"
                                    :class="{
                                        'text-slate-950': stageState(stage) === 'active',
                                        'text-emerald-700': stageState(stage) === 'done',
                                        'text-slate-500': stageState(stage) === 'pending',
                                    }"
                                >
                                    {{ stage.label }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div v-if="isInitialStage" class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_360px] gap-6 items-start">
                <div class="space-y-6 min-w-0">
                    <DetailParties :data="data" />
                    <DetailSubstance :data="data" />
                </div>

                <aside class="space-y-6 min-w-0">
                    <DetailDocuments v-if="showDocuments" :data="data" />
                    <DetailContact :data="data" />
                </aside>
            </div>

            <div v-else-if="isScheduleStage" class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_360px] gap-6 items-start">
                <div class="space-y-6 min-w-0">
                    <DetailSchedule v-if="showSchedule" :data="data" :isAdmin="isAdmin" @refresh="$emit('refresh')" />
                    <DetailParties :data="data" />
                    <DetailSubstance :data="data" />
                </div>

                <aside class="space-y-6 min-w-0">
                    <DetailDocuments :data="data" />
                    <DetailContact :data="data" />
                </aside>
            </div>

            <div v-else-if="isSigningStage" class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_360px] gap-6 items-start">
                <div class="space-y-6 min-w-0">
                    <PksSection v-if="showPks" :permohonan="data" @refresh="$emit('refresh')" @uploaded="closeAfterUpload" />
                    <TtdSection v-if="showTtd" :permohonan="data" @refresh="$emit('refresh')" @uploaded="closeAfterUpload" />
                    <DetailParties :data="data" />
                    <DetailSubstance :data="data" />
                </div>

                <aside class="space-y-6 min-w-0">
                    <DetailSchedule v-if="showSchedule" :data="data" :isAdmin="isAdmin" @refresh="$emit('refresh')" />
                    <DetailDocuments :data="data" />
                    <DetailContact :data="data" />
                </aside>
            </div>

            <div v-else-if="isExecutionStage" class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_360px] gap-6 items-start">
                <div class="space-y-6 min-w-0">
                    <DetailParties :data="data" />
                    <DetailSubstance :data="data" />
                    <PksSection v-if="showPks" :permohonan="data" @refresh="$emit('refresh')" @uploaded="closeAfterUpload" />
                    <TtdSection v-if="showTtd" :permohonan="data" @refresh="$emit('refresh')" @uploaded="closeAfterUpload" />
                </div>

                <aside class="space-y-6 min-w-0">
                    <DetailDocuments :data="data" />
                    <DetailSchedule v-if="showSchedule" :data="data" :isAdmin="isAdmin" @refresh="$emit('refresh')" />
                    <DetailContact :data="data" />
                </aside>
            </div>
        </div>

        <slot v-if="data && !loading" name="footer" :data="data" />
    </Dialog>
</template>
