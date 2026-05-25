<script setup>
import { Icon } from '@iconify/vue';
import Dialog from 'primevue/dialog';
import Skeleton from 'primevue/skeleton';

defineProps({
    visible: Boolean,
    loading: Boolean,
    data: Object,
});

defineEmits(['update:visible', 'delete', 'edit']);

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
};
</script>

<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        modal
        header="Detail Kerjasama Manual"
        :style="{ width: '920px' }"
        :breakpoints="{ '960px': '95vw' }"
        maximizable
        contentClass="p-0"
    >
        <template #header>
            <div class="flex min-w-0 items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-700 dark:bg-gray-700 dark:text-gray-100">
                    <Icon icon="solar:archive-bold" class="h-5 w-5" />
                </span>
                <div class="min-w-0">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Detail PKS Manual</h3>
                    <p class="text-sm font-normal text-slate-500 dark:text-gray-300">Dokumen kerjasama yang dicatat di luar alur permohonan.</p>
                </div>
            </div>
        </template>

        <div v-if="loading" class="space-y-4 p-6">
            <Skeleton height="7rem" class="w-full rounded-xl" />
            <div class="grid gap-4 md:grid-cols-2">
                <Skeleton height="12rem" class="w-full rounded-xl" />
                <Skeleton height="12rem" class="w-full rounded-xl" />
            </div>
        </div>

        <div v-else-if="data" class="bg-white dark:bg-gray-800">
            <section class="border-b border-slate-200 px-6 py-5 dark:border-gray-700">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-gray-700 dark:text-gray-200">
                                {{ data.kategori?.label || 'Kerjasama Manual' }}
                            </span>
                            <span class="rounded border border-slate-200 px-2.5 py-1 font-mono text-xs text-slate-500 dark:border-gray-600 dark:text-gray-300">
                                {{ data.nomor_pks || 'Nomor PKS belum diisi' }}
                            </span>
                        </div>
                        <h2 class="mt-3 text-xl font-semibold leading-tight text-slate-900 dark:text-white">{{ data.label }}</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-gray-300">{{ data.nama_instansi }}</p>
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <button @click="$emit('edit', data)" class="inline-flex h-10 items-center gap-2 rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-gray-600 dark:text-gray-100 dark:hover:bg-gray-700">
                            <Icon icon="solar:pen-bold" class="h-4 w-4" />
                            Edit
                        </button>
                        <button @click="$emit('delete', data)" class="inline-flex h-10 items-center gap-2 rounded-lg border border-red-200 px-3 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-800 dark:text-red-200 dark:hover:bg-red-900/20">
                            <Icon icon="solar:trash-bin-trash-bold" class="h-4 w-4" />
                            Hapus
                        </button>
                    </div>
                </div>
            </section>

            <div class="grid gap-0 lg:grid-cols-[minmax(0,1fr)_290px]">
                <section class="px-6 py-5 lg:border-r lg:border-slate-200 dark:lg:border-gray-700">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-gray-300">Data PKS</h3>
                    <div class="mt-4 grid gap-x-6 gap-y-4 text-sm md:grid-cols-2">
                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-400">Tanggal Mulai</p>
                            <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ formatDate(data.tanggal_mulai) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-400">Tanggal Berakhir</p>
                            <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ formatDate(data.tanggal_berakhir) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-400">Jangka Waktu</p>
                            <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ data.jangka_waktu || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-400">Diinput Oleh</p>
                            <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ data.creator?.name || '-' }}</p>
                        </div>
                    </div>
                    <div class="mt-5 border-t border-gray-100 pt-4 dark:border-gray-700">
                        <p class="text-xs font-semibold uppercase text-gray-400">Ruang Lingkup</p>
                        <p class="mt-1 whitespace-pre-line text-sm leading-relaxed text-gray-700 dark:text-gray-200">{{ data.ruang_lingkup || '-' }}</p>
                    </div>
                </section>

                <aside class="space-y-5 bg-slate-50/70 px-6 py-5 dark:bg-gray-900/25">
                    <section>
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-gray-300">Dokumen</h3>
                        <a v-if="data.file_pks_url" :href="data.file_pks_url" target="_blank" class="mt-3 flex items-center gap-3 rounded-lg border border-slate-200 bg-white p-3 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-600 dark:bg-gray-700 dark:text-gray-100">
                                <Icon icon="solar:file-download-bold" class="h-4 w-4" />
                            </span>
                            <span class="min-w-0 truncate">{{ data.file_pks_name || 'Buka PKS' }}</span>
                        </a>
                        <p v-else class="mt-2 text-sm text-gray-400">Dokumen PKS belum tersedia.</p>
                    </section>
                    <section class="border-t border-slate-200 pt-5 dark:border-gray-700">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-gray-300">OPD Terlibat</h3>
                        <div v-if="data.opds?.length" class="mt-3 space-y-2">
                            <div v-for="opd in data.opds" :key="opd.id" class="flex items-start gap-2 rounded-lg bg-white p-2.5 text-sm text-slate-700 dark:bg-gray-800 dark:text-gray-200">
                                <Icon icon="solar:building-bold" class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" />
                                <span>{{ opd.nama }}</span>
                            </div>
                        </div>
                        <p v-else class="mt-2 text-sm text-gray-400">Belum ada OPD terhubung.</p>
                    </section>
                </aside>
            </div>
        </div>
    </Dialog>
</template>
