<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Calendar from 'primevue/calendar';
import Dropdown from 'primevue/dropdown';
import RadioButton from 'primevue/radiobutton';

const props = defineProps({
    permohonan: Object,
});

const emit = defineEmits(['close', 'submitted']);

const form = useForm({
    id_permohonan: props.permohonan?.id,
    tipe: 'calendar', // calendar, langsung
    tanggal: null,
    waktu: null,
    lokasi: '',
    agenda: 'Pembahasan Kerjasama ' + (props.permohonan?.label || ''),
    keterangan: '',
});

const typeOptions = [
    { label: 'Pilih Jadwal (Calendar)', value: 'calendar' },
    { label: 'Request Meeting Langsung', value: 'langsung' }
];

const submit = () => {
    form.post(route('penjadwalan.store'), {
        onSuccess: () => {
            emit('submitted');
            emit('close');
        }
    });
};

import { watch } from 'vue';
watch(() => form.tipe, (newVal) => {
    if (newVal === 'calendar') {
        form.lokasi = 'Online via Zoom / Google Meet';
    } else {
        form.lokasi = '';
    }
});
</script>

<template>
    <div class="space-y-6">
        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg flex items-start gap-3">
            <Icon icon="solar:info-circle-bold" class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" />
            <div class="text-sm">
                <p class="font-semibold text-blue-800 dark:text-blue-200">Pengajuan Jadwal Pembahasan</p>
                <p class="text-blue-600 dark:text-blue-300 mt-1">
                    Silahkan ajukan jadwal pembahasan kerjasama. Anda dapat memilih jadwal yang tersedia atau mengajukan waktu khusus.
                </p>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Tipe Jadwal -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Metode Penjadwalan</label>
                <div class="flex flex-col sm:flex-row gap-4">
                    <div v-for="option in typeOptions" :key="option.value" 
                        class="flex items-center ps-4 border border-gray-200 rounded dark:border-gray-700 flex-1 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                        :class="{'ring-2 ring-blue-500 border-blue-500 bg-blue-50/50': form.tipe === option.value}"
                        @click="form.tipe = option.value"
                    >
                        <RadioButton v-model="form.tipe" :inputId="option.value" :value="option.value" class="mr-2" />
                        <label :for="option.value" class="w-full py-4 ml-2 text-sm font-medium text-gray-900 dark:text-gray-300 cursor-pointer">
                            {{ option.label }}
                        </label>
                    </div>
                </div>
            </div>

            <!-- Calendar Mode -->
            <div v-if="form.tipe === 'calendar'" class="animate-fade-in space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Tanggal</label>
                        <Calendar v-model="form.tanggal" dateFormat="dd/mm/yy" showIcon class="w-full" :minDate="new Date()" placeholder="Pilih Tanggal" />
                        <small class="text-red-500" v-if="form.errors.tanggal">{{ form.errors.tanggal }}</small>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Waktu</label>
                        <Calendar v-model="form.waktu" timeOnly showIcon class="w-full" placeholder="Pilih Waktu" />
                        <small class="text-red-500" v-if="form.errors.waktu">{{ form.errors.waktu }}</small>
                    </div>
                </div>
                
                <div class="bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded text-sm text-yellow-800 dark:text-yellow-200 flex gap-2">
                    <Icon icon="solar:calendar-mark-bold" class="w-5 h-5" />
                    <span>Meeting akan dilaksanakan secara Online via Zoom / Google Meet. Link akan diberikan setelah disetujui.</span>
                </div>
                <!-- Set default location for Calendar mode -->
               <input type="hidden" :value="form.lokasi = 'Online via Zoom / Google Meet'" />
            </div>

            <!-- Direct Mode -->
            <div v-else class="animate-fade-in space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Tanggal Usulan</label>
                        <Calendar v-model="form.tanggal" dateFormat="dd/mm/yy" showIcon class="w-full" :minDate="new Date()" />
                        <small class="text-red-500" v-if="form.errors.tanggal">{{ form.errors.tanggal }}</small>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Waktu Usulan</label>
                        <Calendar v-model="form.waktu" timeOnly showIcon class="w-full" />
                        <small class="text-red-500" v-if="form.errors.waktu">{{ form.errors.waktu }}</small>
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium">Lokasi Meeting</label>
                    <InputText v-model="form.lokasi" class="w-full" placeholder="Contoh: Ruang Rapat Kantor Pemohon" />
                    <small class="text-red-500" v-if="form.errors.lokasi">{{ form.errors.lokasi }}</small>
                </div>
            </div>

            <!-- Common Fields -->
            <div class="space-y-1">
                <label class="text-sm font-medium">Agenda Pembahasan</label>
                <InputText v-model="form.agenda" class="w-full" placeholder="Agenda utama meeting" />
                <small class="text-red-500" v-if="form.errors.agenda">{{ form.errors.agenda }}</small>
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium">Catatan Tambahan (Opsional)</label>
                <Textarea v-model="form.keterangan" rows="3" class="w-full" placeholder="Catatan untuk admin..." />
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-4 border-t dark:border-gray-700">
                <button type="button" @click="$emit('close')" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2" :disabled="form.processing">
                    <Icon v-if="form.processing" icon="svg-spinners:90-ring-with-bg" />
                    <span>Ajukan Jadwal</span>
                </button>
            </div>
        </form>
    </div>
</template>
