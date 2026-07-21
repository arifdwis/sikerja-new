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
// 0 Permohonan Baru | 1 Pembahasan | 2 Penjadwalan | 3 Jadwal Disetujui (Upload PKS)
// 4 Menunggu TTD | 5 Pasca-TTD | 6 Pelaksanaan | 7 Selesai | 8 Dicabut | 9 Ditolak
const status = computed(() => Number(props.data?.status ?? 0));

const showSchedule = computed(() => status.value >= 2 || (props.data?.penjadwalans?.length ?? 0) > 0);
const showPks = computed(() => status.value >= 3 || (props.data?.pksFiles?.length ?? 0) > 0);
const showTtd = computed(() => status.value >= 4 || (props.data?.ttdFiles?.length ?? 0) > 0);
// Dokumen pemohon (Surat, KAK, MOU) di-upload sebelum pembahasan, tapi terlihat di semua fase.
const showDocuments = computed(() => true);

const isInitialStage = computed(() => [0, 1, 9].includes(status.value));
const isScheduleStage = computed(() => status.value === 2);
const isSigningStage = computed(() => [3, 4, 5].includes(status.value));
const isExecutionStage = computed(() => [6, 7, 8].includes(status.value));

const stages = [
    { order: 0, ids: [0], label: 'Validasi', icon: 'solar:clipboard-check-bold' },
    { order: 1, ids: [1, 9], label: 'Pembahasan', icon: 'solar:documents-bold' },
    { order: 2, ids: [2], label: 'Jadwal', icon: 'solar:calendar-date-bold' },
    { order: 3, ids: [3, 4, 5], label: 'Tanda Tangan', icon: 'solar:pen-new-square-bold' },
    { order: 4, ids: [6], label: 'Pelaksanaan', icon: 'solar:rocket-bold' },
    { order: 5, ids: [7], label: 'Selesai', icon: 'solar:verified-check-bold' },
    { order: 6, ids: [8], label: 'Dicabut', icon: 'solar:close-circle-bold' },
];

const stageContext = computed(() => {
    const contexts = {
        0: {
            title: 'Tahap Validasi Permohonan',
            detail: 'Admin memeriksa data permohonan sebelum dokumen masuk pembahasan.',
            tone: 'border-slate-200 bg-white text-slate-900',
            icon: 'solar:clipboard-check-bold',
        },
        1: {
            title: 'Tahap Pembahasan Dokumen',
            detail: 'Dokumen awal sedang ditinjau. Revisi dilakukan dari dokumen yang ditolak.',
            tone: 'border-slate-200 bg-white text-slate-900',
            icon: 'solar:documents-bold',
        },
        2: {
            title: 'Tahap Penjadwalan',
            detail: 'Pembahasan selesai. Fokus tahap ini adalah pengajuan dan persetujuan jadwal.',
            tone: 'border-slate-200 bg-white text-slate-900',
            icon: 'solar:calendar-date-bold',
        },
        3: {
            title: 'Tahap PKS Final',
            detail: 'Jadwal sudah disetujui. PKS final harus disiapkan untuk penandatanganan.',
            tone: 'border-slate-200 bg-white text-slate-900',
            icon: 'solar:document-add-bold',
        },
        4: {
            title: 'Tahap Penandatanganan',
            detail: 'PKS final sudah masuk. Tunggu penandatanganan dan unggah dokumen tertandatangani.',
            tone: 'border-slate-200 bg-white text-slate-900',
            icon: 'solar:pen-new-square-bold',
        },
        5: {
            title: 'Tahap Validasi Pasca Tanda Tangan',
            detail: 'Admin memvalidasi dokumen tertandatangani sebelum kerja sama berjalan.',
            tone: 'border-slate-200 bg-white text-slate-900',
            icon: 'solar:shield-check-bold',
        },
        6: {
            title: 'Tahap Pelaksanaan Kerja Sama',
            detail: 'Kerja sama aktif. Dokumen proses dan berkas final tersedia sebagai ringkasan.',
            tone: 'border-slate-200 bg-white text-slate-900',
            icon: 'solar:rocket-bold',
        },
        7: {
            title: 'Kerja Sama Selesai',
            detail: 'Masa kerja sama selesai. Detail ini menjadi arsip proses dan dokumen.',
            tone: 'border-emerald-200 bg-white text-slate-900',
            icon: 'solar:verified-check-bold',
        },
        8: {
            title: 'Kerja Sama Dicabut',
            detail: 'Kerja sama dihentikan sebelum selesai karena pelanggaran atau keputusan pencabutan.',
            tone: 'border-rose-200 bg-rose-50 text-rose-900',
            icon: 'solar:close-circle-bold',
        },
        9: {
            title: 'Permohonan Perlu Revisi',
            detail: 'Perbaiki data permohonan lalu ajukan kembali sesuai alasan penolakan.',
            tone: 'border-red-200 bg-red-50 text-red-900',
            icon: 'solar:danger-triangle-bold',
        },
    };

    return contexts[status.value] || contexts[0];
});

const stageState = (stage) => {
    const currentOrder = {
        0: 0,
        1: 1,
        2: 2,
        3: 3,
        4: 3,
        5: 3,
        6: 4,
        7: 5,
        8: 6,
        9: 1,
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
