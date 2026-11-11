<script setup>
import { computed } from 'vue';
import { Icon } from '@iconify/vue';
import { getStatusTheme } from '@/utils/status';

const props = defineProps({
    item: Object,
    isAdmin: Boolean
});

const emit = defineEmits(['detail', 'edit', 'tracking', 'upload', 'schedule']);

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    }).toUpperCase();
};

const diffForHumans = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 60) return `${diffMins} menit yang lalu`;
    if (diffHours < 24) return `${diffHours} jam yang lalu`;
    return `${diffDays} hari yang lalu`;
};

const getStatusColor = (status) => {
    const theme = getStatusTheme(status);
    return {
        bg: theme.cardBg || 'bg-gray-300',
        text: theme.cardText || 'text-gray-950',
        border: theme.cardBorder || 'border-gray-500',
    };
};

const docTypeLabel = computed(() => props.item?.tipe_dokumen === 'PKS' ? 'PKS Final' : 'Naskah Final (KB/PKS)');

/**
 * Upload berkas awal (Surat Permohonan, KAK, MoU, Legalitas) diizinkan sejak tahap pengajuan
 * (status -1 Draft, 0 Menunggu Verifikasi, 11 Revisi, dan 1 Dalam Pembahasan) selama berkas belum diunggah.
 * Jika status 11 (Revisi) atau 9 (Ditolak), pemohon juga dapat mengunggah/melengkapi berkas.
 */
const canManageFiles = (item) => {
    if (props.isAdmin) return false;

    const status = Number(item.status);
    const allowedInitialStatuses = [-1, 0, 1, 9, 10, 11, 12, 20, 21];
    if (!allowedInitialStatuses.includes(status)) return false;

    // Jika berkas belum ada sama sekali, izinkan upload di semua tahap awal
    if (!item.files || item.files.length === 0) return true;

    // Jika sedang diminta revisi administrasi (status 11 atau 9), izinkan buka dialog upload/kelola berkas
    if ([9, 11].includes(status)) return true;

    return false;
};
</script>

<template>
    <div 
        class="group relative rounded-lg border shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer overflow-hidden flex flex-col h-full"
        :class="[getStatusColor(item.status).bg, getStatusColor(item.status).border]"
        @click="$emit('detail', item)"
    >
        <div class="absolute -top-8 -right-8 w-32 h-32 rounded-full bg-white/20 blur-2xl pointer-events-none"></div>
        
        <div class="p-4 flex flex-col h-full relative z-10" :class="getStatusColor(item.status).text">
            <div class="flex justify-between items-start gap-2 mb-3">
                <span class="bg-white/80 px-2 py-1 text-xs font-bold rounded border border-white/50 uppercase tracking-wide flex-1">
                    {{ item.kategori?.label || 'Kerjasama' }}
                </span>
                <span class="font-bold uppercase text-sm whitespace-nowrap shrink-0 text-right">
                    {{ formatDate(item.created_at) }}
                </span>
            </div>

            <div class="mb-4">
                <div class="bg-white/80 backdrop-blur-sm border border-white/50 rounded-lg px-3 py-2 shadow-sm flex items-center justify-between gap-2">
                    <span class="text-xs font-mono font-bold select-all truncate block">
                        {{ item.nomor_permohonan || item.kode }}
                    </span>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded uppercase shrink-0"
                        :class="item.tipe_dokumen === 'PKS' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'">
                        {{ item.tipe_dokumen === 'PKS' ? 'PKS' : 'KB' }}
                    </span>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-sm border border-white/50 rounded-lg px-3 py-2 shadow-sm mb-4">
                <h4 class="font-semibold min-h-16 capitalize text-sm leading-snug line-clamp-3">
                    {{ item.label?.toLowerCase() }}
                </h4>
            </div>

            <!-- Alasan penolakan/pencabutan/revisi -->
            <div v-if="[8, 9, 11].includes(Number(item.status)) && item.alasan_tolak"
                class="mb-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-start gap-2 text-red-800">
                    <Icon icon="solar:danger-triangle-bold" class="w-4 h-4 flex-shrink-0 mt-0.5" />
                    <div class="text-xs">
                        <p class="font-bold uppercase tracking-wide mb-0.5">
                            {{ Number(item.status) === 8 ? 'Alasan Pencabutan' : (Number(item.status) === 11 ? 'Catatan Revisi' : 'Alasan Penolakan') }}
                        </p>
                        <p class="font-medium">{{ item.alasan_tolak }}</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 mb-3 text-sm">
                <Icon icon="solar:buildings-bold" class="w-5 h-5 flex-shrink-0" />
                <span class="truncate font-semibold capitalize">
                    {{ item.nama_instansi?.toLowerCase() || '-' }}
                </span>
            </div>
            <div class="flex items-center gap-2 mb-3 text-sm">
                <Icon icon="solar:user-bold" class="w-5 h-5 flex-shrink-0" />
                <span class="truncate font-semibold capitalize">
                    {{ item.operator?.name?.toLowerCase() || '-' }}
                </span>
            </div>

            <div class="mt-auto space-y-3 border-t border-gray-50/50 border-dashed pt-4">
                <div class="flex justify-between items-center text-xs font-bold">
                    <span class="opacity-80 uppercase">Status :</span>
                    <span class="bg-white/80 border border-white/50 px-2 py-1 rounded uppercase tracking-wide">
                        {{ item.status_label?.label || 'Permohonan' }}
                    </span>
                </div>
                
                <div class="text-xs font-semibold">
                    {{ diffForHumans(item.created_at) }}
                </div>

                <div class="w-full grid grid-cols-2 gap-2 mt-2">
                    <!-- Edit Button (untuk Pemohon, status -1=Draft, 0=Menunggu Validasi, 9=Ditolak, 11=Revisi) -->
                    <button 
                        v-if="!isAdmin && (item.status == -1 || item.status == 0 || item.status == 9 || item.status == 11)"
                        @click.stop="$emit('edit', item)" 
                        class="flex items-center justify-center gap-1.5 rounded-md px-2 py-2 text-xs font-bold uppercase transition-all bg-white shadow-sm col-span-2"
                        :class="(item.status == 9 || item.status == 11) ? 'text-red-600 hover:bg-red-50' : 'text-yellow-700 hover:bg-yellow-50'"
                    >
                        <Icon :icon="(item.status == 9 || item.status == 11) ? 'solar:refresh-bold' : 'solar:pen-bold'" class="w-3.5 h-3.5" />
                        <span>{{ (item.status == 9 || item.status == 11) ? 'Revisi & Ajukan Ulang' : (item.status == -1 ? 'Lanjutkan Draft' : 'Edit Pengajuan') }}</span>
                    </button>

                    <!-- Upload Berkas Awal (jika berkas belum diunggah atau sedang revisi) -->
                    <button 
                        v-if="canManageFiles(item)"
                        @click.stop="$emit('upload', item)" 
                        class="flex items-center justify-center gap-1.5 rounded-md px-2 py-2 text-xs font-bold uppercase transition-all col-span-2 bg-orange-600 hover:bg-orange-700 text-white shadow-sm"
                    >
                        <Icon icon="solar:upload-bold" class="w-3.5 h-3.5" />
                        <span>{{ (item.files && item.files.length > 0) ? 'Perbarui Berkas' : 'Upload Berkas Awal' }}</span>
                    </button>

                    <!-- Buat Jadwal (status 2) -->
                    <button 
                        v-if="!isAdmin && item.status == 2 && (!item.penjadwalans || item.penjadwalans.length === 0 || item.penjadwalans[0].status === 2)"
                        @click.stop="$emit('schedule', item)" 
                        class="flex items-center justify-center gap-1.5 rounded-md px-2 py-2 text-xs font-bold uppercase transition-all bg-indigo-600 hover:bg-indigo-700 text-white col-span-2 shadow-sm"
                    >
                        <Icon icon="solar:calendar-add-bold" class="w-3.5 h-3.5" />
                        <span>Buat Jadwal</span>
                    </button>

                    <!-- Upload Naskah Final (status 3) — admin: buka modal detail untuk upload -->
                    <button
                        v-if="isAdmin && item.status == 3"
                        @click.stop="$emit('detail', item)"
                        class="flex items-center justify-center gap-1.5 rounded-md px-2 py-2 text-xs font-bold uppercase transition-all bg-violet-700 hover:bg-violet-800 text-white col-span-2"
                    >
                        <Icon icon="solar:document-add-bold" class="w-3.5 h-3.5" />
                        <span>Upload {{ docTypeLabel }} (PDF)</span>
                    </button>

                    <!-- Status 3 — pemohon: info menunggu admin upload naskah final -->
                    <div
                        v-if="!isAdmin && item.status == 3"
                        class="flex items-center justify-center gap-1.5 rounded-md px-2 py-2 text-xs font-semibold col-span-2 bg-white/80 text-violet-800"
                    >
                        <Icon icon="solar:clock-circle-bold" class="w-3.5 h-3.5" />
                        <span>Menunggu {{ docTypeLabel }} dari admin</span>
                    </div>

                    <!-- Status 4 (Menunggu Penandatanganan) — Pemohon Upload Dokumen Tertandatangani -->
                    <button
                        v-if="!isAdmin && item.status == 4"
                        @click.stop="$emit('detail', item)"
                        class="flex items-center justify-center gap-1.5 rounded-md px-2 py-2 text-xs font-bold uppercase transition-all bg-pink-600 hover:bg-pink-700 text-white col-span-2 shadow-sm"
                    >
                        <Icon icon="solar:pen-new-square-bold" class="w-3.5 h-3.5" />
                        <span>Upload Dokumen TTD</span>
                    </button>

                    <!-- Status 5 (Pasca-Tandatangan) — Admin Validasi TTD / Pemohon Menunggu Validasi -->
                    <button
                        v-if="isAdmin && item.status == 5"
                        @click.stop="$emit('detail', item)"
                        class="flex items-center justify-center gap-1.5 rounded-md px-2 py-2 text-xs font-bold uppercase transition-all bg-orange-600 hover:bg-orange-700 text-white col-span-2 shadow-sm"
                    >
                        <Icon icon="solar:shield-check-bold" class="w-3.5 h-3.5" />
                        <span>Validasi Dokumen TTD</span>
                    </button>
                    <div
                        v-if="!isAdmin && item.status == 5"
                        class="flex items-center justify-center gap-1.5 rounded-md px-2 py-2 text-xs font-semibold col-span-2 bg-white/80 text-orange-800"
                    >
                        <Icon icon="solar:clock-circle-bold" class="w-3.5 h-3.5" />
                        <span>Menunggu Validasi TTD Admin</span>
                    </div>

                    <button 
                        @click.stop="$emit('detail', item)" 
                        class="flex items-center justify-center gap-1.5 rounded-md px-2 py-2 text-xs font-bold uppercase opacity-0 group-hover:opacity-100 transition-all bg-gray-900 text-white"
                    >
                        <span>Detail</span>
                        <Icon icon="solar:alt-arrow-right-bold" class="w-3.5 h-3.5" />
                    </button>
                    <button 
                        @click.stop="$emit('tracking', item)" 
                        class="flex items-center justify-center gap-1.5 rounded-md px-2 py-2 text-xs font-bold uppercase opacity-0 group-hover:opacity-100 transition-all bg-white/80 text-gray-900"
                    >
                        <span>Lacak</span>
                        <Icon icon="solar:alt-arrow-right-bold" class="w-3.5 h-3.5" />
                    </button>

                    <!-- Info: berkas sudah diupload & sedang ditinjau -->
                    <div 
                        v-if="!isAdmin && item.files && item.files.length > 0 && [0, 1, 10, 12].includes(Number(item.status))"
                        class="flex items-center justify-center gap-1.5 rounded-md px-2 py-2 text-xs font-semibold col-span-2 bg-white/80 text-blue-700"
                    >
                        <Icon icon="solar:check-circle-bold" class="w-3.5 h-3.5 text-emerald-600" />
                        <span>{{ item.files.length }} Berkas Terlampir</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
