<script setup>
import { computed } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import Textarea from 'primevue/textarea';
import { Icon } from '@iconify/vue';

const props = defineProps({
    confirmVisible: Boolean,
    rejectVisible: Boolean,
    item: Object,
    processing: Boolean,
    rejectReason: String
});

defineEmits(['update:confirmVisible', 'update:rejectVisible', 'update:rejectReason', 'confirm', 'reject']);

const jadwal = computed(() => props.item?.penjadwalans?.[0] || null);

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const formatTime = (timeString) => {
    if (!timeString) return '-';
    if (timeString.includes('T') || timeString.includes(' ')) {
        const date = new Date(timeString);
        return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace('.', ':') + ' WITA';
    }
    return timeString.substring(0, 5) + ' WITA';
};

const tipeLabel = (tipe) => {
    const map = {
        desk_to_desk: { label: 'Desk to Desk', icon: 'solar:laptop-bold-duotone', color: 'bg-sky-50 text-sky-700 border-sky-200' },
        seremonial: { label: 'Seremonial', icon: 'solar:medal-star-bold-duotone', color: 'bg-amber-50 text-amber-700 border-amber-200' },
        hybrid: { label: 'Hybrid', icon: 'solar:videocamera-record-bold-duotone', color: 'bg-violet-50 text-violet-700 border-violet-200' },
        offline: { label: 'Offline', icon: 'solar:users-group-rounded-bold-duotone', color: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
        online: { label: 'Online', icon: 'solar:videocamera-bold-duotone', color: 'bg-blue-50 text-blue-700 border-blue-200' },
    };
    return map[tipe] || { label: tipe || '-', icon: 'solar:calendar-bold-duotone', color: 'bg-gray-50 text-gray-700 border-gray-200' };
};
</script>

<template>
    <div>
        <!-- Confirm Dialog -->
        <Dialog 
            :visible="confirmVisible" 
            @update:visible="$emit('update:confirmVisible', $event)"
            modal 
            header="Konfirmasi Persetujuan Jadwal Penandatanganan" 
            :style="{ width: '560px' }"
        >
            <div class="p-4 space-y-4">
                <div class="text-center mb-2">
                    <Icon icon="solar:verified-check-bold-duotone" class="text-green-600 w-14 h-14 mx-auto" />
                    <p class="mt-2 font-medium text-gray-700">Setujui jadwal penandatanganan kerjasama ini?</p>
                </div>

                <div v-if="jadwal" class="border border-gray-200 rounded-lg overflow-hidden">
                    <!-- Header dengan jenis jadwal -->
                    <div :class="tipeLabel(jadwal.tipe).color" class="px-4 py-2.5 border-b flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <Icon :icon="tipeLabel(jadwal.tipe).icon" class="w-5 h-5" />
                            <span class="font-bold uppercase text-xs tracking-wide">{{ tipeLabel(jadwal.tipe).label }}</span>
                        </div>
                        <span class="text-xs opacity-75">Jenis Penjadwalan</span>
                    </div>

                    <!-- Detail jadwal -->
                    <div class="p-4 space-y-3 bg-white">
                        <div class="flex items-start gap-3">
                            <Icon icon="solar:calendar-date-bold" class="w-4 h-4 text-gray-400 mt-1 shrink-0" />
                            <div class="flex-1">
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Tanggal & Waktu</p>
                                <p class="font-semibold text-gray-800">{{ formatDate(jadwal.tanggal) }}</p>
                                <p class="text-sm text-gray-600">{{ formatTime(jadwal.waktu) }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <Icon icon="solar:map-point-bold" class="w-4 h-4 text-gray-400 mt-1 shrink-0" />
                            <div class="flex-1">
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Lokasi</p>
                                <p class="text-sm font-medium text-gray-800">{{ jadwal.lokasi || '-' }}</p>
                            </div>
                        </div>

                        <div v-if="jadwal.link_meeting" class="flex items-start gap-3">
                            <Icon icon="solar:link-bold" class="w-4 h-4 text-gray-400 mt-1 shrink-0" />
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Link Meeting</p>
                                <a :href="jadwal.link_meeting" target="_blank" class="text-sm font-medium text-blue-600 hover:underline break-all">
                                    {{ jadwal.link_meeting }}
                                </a>
                            </div>
                        </div>

                        <div v-if="jadwal.agenda" class="flex items-start gap-3">
                            <Icon icon="solar:notebook-bookmark-bold" class="w-4 h-4 text-gray-400 mt-1 shrink-0" />
                            <div class="flex-1">
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Agenda</p>
                                <p class="text-sm text-gray-700 italic">"{{ jadwal.agenda }}"</p>
                            </div>
                        </div>

                        <div v-if="jadwal.catatan_pemohon" class="flex items-start gap-3">
                            <Icon icon="solar:chat-round-line-bold" class="w-4 h-4 text-gray-400 mt-1 shrink-0" />
                            <div class="flex-1">
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Catatan Pemohon</p>
                                <p class="text-sm text-gray-700">{{ jadwal.catatan_pemohon }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-sm text-amber-800 text-center">
                    Tidak ada jadwal yang diajukan.
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                    <Button label="Batal" text @click="$emit('update:confirmVisible', false)" />
                    <Button
                        label="Setujui Jadwal"
                        icon="pi pi-check-circle"
                        severity="success"
                        @click="$emit('confirm')"
                        :loading="processing"
                        :disabled="!jadwal"
                    />
                </div>
            </div>
        </Dialog>

        <!-- Reject Dialog -->
        <Dialog 
            :visible="rejectVisible" 
            @update:visible="$emit('update:rejectVisible', $event)" 
            modal 
            header="Konfirmasi Penolakan Jadwal Penandatanganan" 
            :style="{ width: '560px' }"
        >
            <div class="p-4 space-y-4">
                <div v-if="jadwal" class="border border-rose-200 rounded-lg overflow-hidden">
                    <div :class="tipeLabel(jadwal.tipe).color" class="px-4 py-2.5 border-b flex items-center gap-2">
                        <Icon :icon="tipeLabel(jadwal.tipe).icon" class="w-5 h-5" />
                        <span class="font-bold uppercase text-xs tracking-wide">{{ tipeLabel(jadwal.tipe).label }}</span>
                    </div>
                    <div class="p-3 bg-rose-50/40 text-sm space-y-1">
                        <div class="flex items-center gap-2 text-gray-700">
                            <Icon icon="solar:calendar-date-bold" class="w-4 h-4 text-rose-500" />
                            <span>{{ formatDate(jadwal.tanggal) }} • {{ formatTime(jadwal.waktu) }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-700">
                            <Icon icon="solar:map-point-bold" class="w-4 h-4 text-rose-500" />
                            <span>{{ jadwal.lokasi || '-' }}</span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1 block">Alasan Penolakan <span class="text-red-500">*</span></label>
                    <Textarea 
                        :value="rejectReason" 
                        @input="$emit('update:rejectReason', $event.target.value)"
                        class="w-full" 
                        rows="4" 
                        placeholder="Berikan alasan jadwal ini ditolak agar pemohon dapat memperbaiki dan mengajukan ulang..." 
                    />
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                    <Button label="Batal" text @click="$emit('update:rejectVisible', false)" />
                    <Button label="Tolak Jadwal" icon="pi pi-times-circle" severity="danger" @click="$emit('reject')" :loading="processing" />
                </div>
            </div>
        </Dialog>
    </div>
</template>
