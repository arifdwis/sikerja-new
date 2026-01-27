<script setup>
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import Textarea from 'primevue/textarea';

const props = defineProps({
    confirmVisible: Boolean,
    rejectVisible: Boolean,
    item: Object,
    processing: Boolean,
    rejectReason: String
});

defineEmits(['update:confirmVisible', 'update:rejectVisible', 'update:rejectReason', 'confirm', 'reject']);

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
         day: 'numeric', month: 'short', year: 'numeric'
    }).toUpperCase();
};

const formatTime = (timeString) => {
   if (!timeString) return '-';
   if (timeString.includes('T') || timeString.includes(' ')) {
      const date = new Date(timeString);
      return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace('.', ':');
   }
   return timeString.substring(0, 5);
};
</script>

<template>
    <div>
        <!-- Confirm Dialog -->
        <Dialog 
            :visible="confirmVisible" 
            @update:visible="$emit('update:confirmVisible', $event)"
            modal 
            header="Konfirmasi Persetujuan Jadwal" 
            :style="{ width: '500px' }"
        >
            <div class="text-center p-4">
                <i class="pi pi-verified text-green-600 text-5xl mb-4"></i>
                <p class="mb-4">Setujui jadwal pembahasan ini?</p>
                <div v-if="item?.penjadwalans?.[0]" class="bg-gray-50 p-3 rounded mb-6 text-left">
                        <p class="font-bold">{{ formatDate(item.penjadwalans[0].tanggal) }} • {{ formatTime(item.penjadwalans[0].waktu) }}</p>
                        <p class="text-sm text-gray-600">{{ item.penjadwalans[0].lokasi }}</p>
                </div>
                <div class="flex justify-center gap-2">
                    <Button label="Batal" text @click="$emit('update:confirmVisible', false)" />
                    <Button label="Setujui Jadwal" severity="success" @click="$emit('confirm')" :loading="processing" />
                </div>
            </div>
        </Dialog>

        <!-- Reject Dialog -->
        <Dialog 
            :visible="rejectVisible" 
            @update:visible="$emit('update:rejectVisible', $event)" 
            modal 
            header="Konfirmasi Penolakan Jadwal" 
            :style="{ width: '500px' }"
        >
                <div class="p-2">
                    <p class="mb-4">Anda akan menolak jadwal ini. Berikan alasan?</p>
                    <div class="mb-4">
                        <Textarea 
                            :value="rejectReason" 
                            @input="$emit('update:rejectReason', $event.target.value)"
                            class="w-full" 
                            rows="4" 
                            placeholder="Alasan penolakan..." 
                        />
                    </div>
                    <div class="flex justify-end gap-2">
                        <Button label="Batal" text @click="$emit('update:rejectVisible', false)" />
                        <Button label="Tolak Jadwal" severity="danger" @click="$emit('reject')" :loading="processing" />
                    </div>
                </div>
        </Dialog>
    </div>
</template>
