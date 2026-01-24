<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import Dialog from 'primevue/dialog';
import Skeleton from 'primevue/skeleton';
import Tag from 'primevue/tag';
import { Icon } from '@iconify/vue';
import { useToast } from 'vue-toastification';

const props = defineProps({
    visible: Boolean,
    loading: Boolean,
    data: Object,
    isAdmin: Boolean
});

const emit = defineEmits(['update:visible', 'open-file-diskusi', 'refresh']);

const toast = useToast();

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    }).toUpperCase();
};

const handleJadwalApproval = (jadwal, status) => {
    if (confirm(`Apakah anda yakin ingin ${status === 1 ? 'menyetujui' : 'menolak'} jadwal ini?`)) {
        router.put(route('penjadwalan.update', jadwal.uuid), {
            status: status
        }, {
            onSuccess: () => {
                toast.success(`Jadwal berhasil ${status === 1 ? 'disetujui' : 'ditolak'}`);
                emit('refresh');
            }
        });
    }
};

const handleUpdateVisible = (val) => {
    emit('update:visible', val);
};
</script>

<template>
    <Dialog 
        :visible="visible" 
        @update:visible="handleUpdateVisible"
        modal 
        header="Detail Lengkap Permohonan" 
        :style="{ width: '1100px' }" 
        :breakpoints="{ '1199px': '95vw' }" 
        maximizable 
        class="p-0 overflow-hidden"
    >
        <div v-if="loading" class="space-y-6 p-6">
                <div class="grid grid-cols-2 gap-6">
                    <Skeleton height="15rem" class="w-full rounded-xl" />
                    <Skeleton height="15rem" class="w-full rounded-xl" />
                </div>
                <Skeleton height="25rem" class="w-full rounded-xl" />
        </div>
        
        <div v-else-if="data" class="flex flex-col h-[85vh]">
            <div class="flex-1 overflow-y-auto p-6 space-y-8 bg-gray-50/50 dark:bg-gray-900/50">
                
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-blue-100 dark:border-blue-900 shadow-sm relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-64 h-64 bg-blue-50/50 dark:bg-blue-900/20 rounded-bl-full -mr-16 -mt-16 pointer-events-none"></div>
                        
                        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start gap-6">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <Tag :value="data.kategori?.label" severity="info" class="text-xs px-2 py-1" />
                                <span class="text-xs font-mono text-gray-400 border border-gray-200 dark:border-gray-700 rounded px-1.5 py-0.5">{{ data.nomor_permohonan || data.kode }}</span>
                            </div>
                            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white leading-tight mb-2">{{ data.label }}</h1>
                            <p class="text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                <Icon icon="solar:calendar-date-bold" class="w-4 h-4" />
                                Diajukan pada {{ formatDate(data.created_at) }}
                            </p>
                        </div>

                        <div class="flex flex-col items-end gap-3">
                            <Tag :value="data.status_label?.label" :severity="data.status_label?.color || 'info'" class="text-sm px-3 py-1.5" />
                            
                            <div v-if="data.penjadwalans && data.penjadwalans.length > 0" class="mt-2 text-right">
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border text-xs font-medium"
                                    :class="data.penjadwalans[0].status === 1 ? 'bg-green-50 text-green-700 border-green-200' : 
                                            (data.penjadwalans[0].status === 2 ? 'bg-red-50 text-red-700 border-red-200' : 'bg-orange-50 text-orange-700 border-orange-200')"
                                >
                                    <Icon icon="solar:calendar-bold" class="w-4 h-4" />
                                    <span>
                                        Jadwal: {{ data.penjadwalans[0].status === 1 ? 'Disetujui' : (data.penjadwalans[0].status === 2 ? 'Ditolak' : 'Menunggu') }}
                                    </span>
                                </div>
                                <div v-if="data.penjadwalans[0].status === 1" class="text-xs text-gray-500 mt-1">
                                    {{ formatDate(data.penjadwalans[0].tanggal) }} • {{ data.penjadwalans[0].waktu?.substring(0,5) }}
                                </div>
                            </div>
                        </div>
                        </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                    
                    <div class="xl:col-span-2 space-y-8">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                                    <div class="absolute top-0 left-0 w-1 h-full bg-blue-500"></div>
                                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                    <Icon icon="solar:user-circle-bold" class="text-blue-500" /> PIHAK KESATU
                                    </h3>
                                    
                                    <div class="space-y-4">
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Instansi</p>
                                        <p class="font-bold text-lg text-gray-900 dark:text-white leading-snug">{{ data.nama_instansi || '-' }}</p>
                                    </div>
                                    <div v-if="data.alamat">
                                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Alamat</p>
                                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ data.alamat }}</p>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Penanggung Jawab</p>
                                            <p class="text-sm font-medium">{{ data.operator?.name || '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Kontak</p>
                                            <p class="text-sm">{{ data.telepon || data.email || '-' }}</p>
                                        </div>
                                    </div>
                                    </div>
                            </div>

                            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                                    <div class="absolute top-0 left-0 w-1 h-full bg-indigo-500"></div>
                                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                    <Icon icon="solar:buildings-2-bold" class="text-indigo-500" /> PIHAK KEDUA
                                    </h3>

                                    <div class="space-y-4">
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Unit Kerja Mitra</p>
                                        <p class="font-bold text-lg text-gray-900 dark:text-white leading-snug">
                                            {{ data.pemohon2?.unit_kerja || data.pemohon2?.name || 'Pemerintah Kota Samarinda' }}
                                        </p>
                                    </div>
                                        <div v-if="data.pemohon2?.jabatan">
                                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Jabatan</p>
                                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ data.pemohon2?.jabatan }}</p>
                                    </div>
                                    <div v-else>
                                        <p class="text-sm text-gray-500 italic">Mitra spesifik belum ditentukan / General.</p>
                                    </div>
                                    </div>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2 bg-gray-50/50">
                                <Icon icon="solar:document-text-bold" class="text-gray-400" />
                                <h3 class="font-bold text-gray-800 dark:text-gray-200">Substansi Kerjasama</h3>
                            </div>
                            <div class="p-6 space-y-8">
                                <div class="grid grid-cols-1 gap-6">
                                    <div>
                                        <h4 class="text-sm font-bold text-blue-600 mb-2">Latar Belakang</h4>
                                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">{{ data.latar_belakang }}</p>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-blue-600 mb-2">Maksud & Tujuan</h4>
                                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">{{ data.maksud_tujuan }}</p>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-blue-600 mb-2">Ruang Lingkup</h4>
                                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">{{ data.ruang_lingkup }}</p>
                                    </div>
                                </div>

                                <div class="border-t border-dashed border-gray-200 my-6"></div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                    <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                        <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">Jangka Waktu</h4>
                                        <p class="text-sm font-medium">{{ data.jangka_waktu || '-' }}</p>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                                        <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">Lokasi Kerjasama</h4>
                                        <p class="text-sm font-medium">{{ data.lokasi_kerjasama || '-' }}</p>
                                    </div>
                                    <div v-if="data.manfaat" class="col-span-1 md:col-span-2">
                                        <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">Manfaat</h4>
                                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ data.manfaat }}</p>
                                    </div>
                                    <div v-if="data.analisis_dampak" class="col-span-1 md:col-span-2">
                                        <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">Analisis Dampak</h4>
                                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ data.analisis_dampak }}</p>
                                    </div>
                                        <div v-if="data.pembiayaan" class="col-span-1 md:col-span-2">
                                        <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">Pembiayaan</h4>
                                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ data.pembiayaan }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        
                            <div v-if="data.penjadwalans && data.penjadwalans.length > 0" 
                            class="bg-white dark:bg-gray-800 rounded-xl border p-5 shadow-sm overflow-hidden"
                            :class="data.penjadwalans[0].status === 1 ? 'border-green-200' : (data.penjadwalans[0].status === 2 ? 'border-red-200' : 'border-indigo-200')"
                            >
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2">
                                        <div class="p-1.5 rounded-lg text-white" :class="data.penjadwalans[0].status === 1 ? 'bg-green-500' : (data.penjadwalans[0].status === 2 ? 'bg-red-500' : 'bg-indigo-500')">
                                            <Icon icon="solar:calendar-date-bold" class="w-4 h-4" />
                                        </div>
                                        <h3 class="font-bold text-gray-800 dark:text-gray-200">
                                            {{ data.penjadwalans[0].status === 1 ? 'Jadwal Disetujui' : (data.penjadwalans[0].status === 2 ? 'Jadwal Ditolak' : 'Status Jadwal') }}
                                        </h3>
                                </div>
                                
                                <div v-if="isAdmin && data.penjadwalans[0].status === 0" class="flex gap-2">
                                    <button @click="handleJadwalApproval(data.penjadwalans[0], 2)" class="px-2 py-1 bg-red-100 hover:bg-red-200 text-red-700 rounded text-xs font-bold transition">
                                        Tolak
                                    </button>
                                    <button @click="handleJadwalApproval(data.penjadwalans[0], 1)" class="px-2 py-1 bg-green-600 hover:bg-green-700 text-white rounded text-xs font-bold transition shadow-sm">
                                        Setujui
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-3 text-sm">
                                <div class="flex items-start gap-3">
                                    <Icon icon="solar:clock-circle-bold" class="w-4 h-4 text-gray-400 mt-0.5" />
                                    <div>
                                        <p class="text-gray-500 text-xs">Waktu</p>
                                        <p class="font-semibold">{{ formatDate(data.penjadwalans[0].tanggal) }}</p>
                                        <p>{{ data.penjadwalans[0].waktu }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <Icon icon="solar:map-point-bold" class="w-4 h-4 text-gray-400 mt-0.5" />
                                    <div>
                                        <p class="text-gray-500 text-xs">Lokasi</p>
                                        <p class="font-medium">{{ data.penjadwalans[0].lokasi }}</p>
                                    </div>
                                </div>
                                <div v-if="data.penjadwalans[0].agenda">
                                    <div class="bg-gray-50 p-2 rounded text-xs text-gray-600 italic">
                                        "{{ data.penjadwalans[0].agenda }}"
                                    </div>
                                </div>
                                <div v-if="data.penjadwalans[0].admin_comment" class="bg-yellow-50 p-2 rounded text-xs text-yellow-800 border border-yellow-100">
                                    <strong>Catatan Admin:</strong> {{ data.penjadwalans[0].admin_comment }}
                                </div>
                            </div>
                            </div>

                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
                            <h5 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                                <Icon icon="solar:folder-with-files-bold" /> Dokumen Kerjasama
                            </h5>
                            <div v-if="data.files?.length" class="space-y-3">
                                <div v-for="file in data.files" :key="file.id"
                                    class="border rounded-lg p-3 transition hover:shadow-md bg-white"
                                    :class="{
                                        'border-green-200 bg-green-50/20': file.status === 1,
                                        'border-red-200 bg-red-50/20': file.status === 2,
                                        'border-gray-200 bg-gray-50/20': file.status === 0
                                    }"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-blue-50 text-blue-600">
                                            <Icon icon="solar:file-text-bold-duotone" class="w-6 h-6" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-bold text-gray-800 dark:text-gray-200 truncate">{{ file.label }}</p>
                                            <a :href="file.file_url" target="_blank" class="text-xs text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1 mt-0.5">
                                                Lihat Dokumen <Icon icon="solar:arrow-right-up-linear" class="w-3 h-3" />
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-700">
                                            <Tag :value="file.status_label?.label" :severity="file.status_label?.color" class="text-[10px]" />
                                            <button @click="$emit('open-file-diskusi', file)" class="text-xs font-medium text-gray-500 hover:text-blue-600 flex items-center gap-1">
                                            <Icon icon="solar:chat-round-dots-bold" /> Diskusi
                                            </button>
                                    </div>

                                    <div v-if="file.komentar" class="mt-2 bg-red-50 text-red-700 p-2 rounded text-xs flex gap-2">
                                        <Icon icon="solar:info-circle-bold" class="shrink-0 mt-0.5" />
                                        <span>{{ file.komentar }}</span>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-8">
                                <Icon icon="solar:folder-error-linear" class="w-12 h-12 text-gray-300 mx-auto mb-2" />
                                <p class="text-xs text-gray-400">Belum ada dokumen yang diupload.</p>
                            </div>
                            </div>

                    </div>
                </div>
            </div>
        </div>
    </Dialog>
</template>
