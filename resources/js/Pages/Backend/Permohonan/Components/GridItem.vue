<script setup>
import { computed } from 'vue';
import { Icon } from '@iconify/vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    item: Object,
    isAdmin: Boolean
});

const emit = defineEmits(['detail', 'tracking', 'upload', 'schedule']);

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
    const colors = {
        0: { bg: 'bg-amber-300',    text: 'text-amber-950',    border: 'border-amber-500' },
        1: { bg: 'bg-sky-300',      text: 'text-sky-950',      border: 'border-sky-500' },
        2: { bg: 'bg-blue-400',     text: 'text-blue-950',     border: 'border-blue-600' },
        3: { bg: 'bg-violet-400',   text: 'text-violet-950',   border: 'border-violet-600' },
        4: { bg: 'bg-pink-300',     text: 'text-pink-950',     border: 'border-pink-500' },
        5: { bg: 'bg-orange-300',   text: 'text-orange-950',   border: 'border-orange-500' },
        6: { bg: 'bg-teal-300',     text: 'text-teal-950',     border: 'border-teal-500' },
        7: { bg: 'bg-emerald-400',  text: 'text-emerald-950',  border: 'border-emerald-600' },
        9: { bg: 'bg-red-300',      text: 'text-red-950',      border: 'border-red-500' },
    };
    return colors[status] || { bg: 'bg-gray-300', text: 'text-gray-950', border: 'border-gray-500' };
};

const getUploadButtonLabel = (item) => {
    return (item.files && item.files.length > 0) ? 'Kelola Berkas' : 'Upload Berkas';
};

/**
 * Upload berkas (Surat Permohonan, KAK, MoU) hanya boleh saat status 1 (Dalam Pembahasan).
 * Setelah masuk Penjadwalan (status 2+), berkas dikunci sesuai Req 7.4.
 *
 * Catatan:
 * - Status 0 = Menunggu Validasi → admin yang upload, pemohon menunggu
 * - Status 1 = Dalam Pembahasan → pemohon upload berkas pertama kali
 * - Status >= 2 = Penjadwalan/Penandatanganan/Pelaksanaan → upload PKS & TTD
 *   pakai tombol terpisah di halaman Detail
 * - Status 9 = Ditolak → pemohon klik "Revisi & Ajukan Ulang", bukan upload
 */
const canManageFiles = (item) => {
    if (props.isAdmin) return false;

    const status = Number(item.status);
    if (status !== 1) return false;

    // Jika file sudah diupload dan masih dalam review, jangan tampilkan tombol upload lagi.
    // (Pemohon harus tunggu admin review dulu, baru bisa upload revisi.)
    if (item.files && item.files.length > 0) return false;

    return true;
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
                <div class="bg-white/80 backdrop-blur-sm border border-white/50 rounded-lg px-3 py-2 shadow-sm">
                    <span class="text-xs font-mono font-bold select-all truncate block">
                        {{ item.nomor_permohonan || item.kode }}
                    </span>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-sm border border-white/50 rounded-lg px-3 py-2 shadow-sm mb-4">
                <h4 class="font-semibold min-h-16 capitalize text-sm leading-snug line-clamp-3">
                    {{ item.label?.toLowerCase() }}
                </h4>
            </div>

            <!-- Alasan Penolakan (hanya muncul jika status ditolak = 9) -->
            <div v-if="item.status == 9 && item.alasan_tolak"
                class="mb-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-start gap-2 text-red-800">
                    <Icon icon="solar:danger-triangle-bold" class="w-4 h-4 flex-shrink-0 mt-0.5" />
                    <div class="text-xs">
                        <p class="font-bold uppercase tracking-wide mb-0.5">Alasan Penolakan</p>
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
                    <!-- Edit Button (untuk Pemohon, status 0=Menunggu Validasi atau 9=Ditolak/Revisi) -->
                     <button 
                        v-if="!isAdmin && (item.status == 0 || item.status == 9)"
                        @click.stop="$emit('edit', item)" 
                        class="flex items-center justify-center gap-1.5 rounded-md px-2 py-2 text-xs font-bold uppercase transition-all bg-white shadow-sm col-span-2"
                        :class="item.status == 9 ? 'text-red-600 hover:bg-red-50' : 'text-yellow-600 hover:bg-yellow-50'"
                    >
                        <Icon :icon="item.status == 9 ? 'solar:refresh-bold' : 'solar:pen-bold'" class="w-3.5 h-3.5" />
                        <span>{{ item.status == 9 ? 'Revisi & Ajukan Ulang' : 'Edit Pengajuan' }}</span>
                    </button>

                    <button 
                        v-if="!isAdmin && item.status == 2 && (!item.penjadwalans || item.penjadwalans.length === 0 || item.penjadwalans[0].status === 2)"
                        @click.stop="$emit('schedule', item)" 
                        class="flex items-center justify-center gap-1.5 rounded-md px-2 py-2 text-xs font-bold uppercase opacity-0 group-hover:opacity-100 transition-all bg-indigo-600 text-white col-span-2"
                    >
                        <Icon icon="solar:calendar-add-bold" class="w-3.5 h-3.5" />
                        <span>Buat Jadwal</span>
                    </button>

                    <!-- Upload PKS Final (status 3) — admin: buka modal detail untuk upload -->
                    <button
                        v-if="isAdmin && item.status == 3"
                        @click.stop="$emit('detail', item)"
                        class="flex items-center justify-center gap-1.5 rounded-md px-2 py-2 text-xs font-bold uppercase transition-all bg-violet-700 hover:bg-violet-800 text-white col-span-2"
                    >
                        <Icon icon="solar:document-add-bold" class="w-3.5 h-3.5" />
                        <span>Upload PKS Final (PDF)</span>
                    </button>

                    <!-- Status 3 — pemohon: info menunggu admin upload PKS final -->
                    <div
                        v-if="!isAdmin && item.status == 3"
                        class="flex items-center justify-center gap-1.5 rounded-md px-2 py-2 text-xs font-semibold col-span-2 bg-white/80 text-violet-800"
                    >
                        <Icon icon="solar:clock-circle-bold" class="w-3.5 h-3.5" />
                        <span>Menunggu PKS final dari admin</span>
                    </div>

                    <!-- Menunggu Hari Penandatanganan (status 4) — info only -->
                    <div
                        v-if="!isAdmin && item.status == 4"
                        class="flex items-center justify-center gap-1.5 rounded-md px-2 py-2 text-xs font-semibold col-span-2 bg-white/80 text-pink-800"
                    >
                        <Icon icon="solar:clock-circle-bold" class="w-3.5 h-3.5" />
                        <span>Menunggu waktu penandatanganan</span>
                    </div>

                    <!-- Upload Dokumen Tertandatangani (status 5) — pemohon -->
                    <button
                        v-if="!isAdmin && item.status == 5"
                        @click.stop="$emit('detail', item)"
                        class="flex items-center justify-center gap-1.5 rounded-md px-2 py-2 text-xs font-bold uppercase transition-all bg-orange-600 hover:bg-orange-700 text-white col-span-2"
                    >
                        <Icon icon="solar:document-text-bold" class="w-3.5 h-3.5" />
                        <span>Upload Dokumen TTD</span>
                    </button>

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
                    <button 
                        v-if="canManageFiles(item)"
                        @click.stop="$emit('upload', item)" 
                        class="flex items-center justify-center gap-1.5 rounded-md px-2 py-2 text-xs font-bold uppercase opacity-0 group-hover:opacity-100 transition-all col-span-2 bg-orange-600 text-white"
                    >
                        <Icon icon="solar:upload-bold" class="w-3.5 h-3.5" />
                        <span>Upload Berkas</span>
                    </button>
                    <!-- Info: berkas masih dalam tahap pembahasan (status = 1) -->
                    <div 
                        v-else-if="!isAdmin && item.files && item.files.length > 0 && Number(item.status) === 1"
                        class="flex items-center justify-center gap-1.5 rounded-md px-2 py-2 text-xs font-semibold col-span-2 bg-white/80 text-blue-700"
                    >
                        <Icon icon="solar:clock-circle-bold" class="w-3.5 h-3.5" />
                        <span>Berkas sedang dalam pembahasan</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
