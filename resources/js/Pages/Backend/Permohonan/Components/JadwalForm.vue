<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Calendar from 'primevue/calendar';
import RadioButton from 'primevue/radiobutton';

const props = defineProps({
    permohonan: Object,
});

const emit = defineEmits(['close', 'submitted']);

const form = useForm({
    id_permohonan: props.permohonan?.id,
    // Req 6: Metode penjadwalan baru
    tipe: 'desk_to_desk',
    tanggal: null,
    waktu: null,
    lokasi: '',
    agenda: 'Penandatanganan Kerjasama ' + (props.permohonan?.label || ''),
    keterangan: '',
});

const typeOptions = [
    {
        label: 'Desk to Desk',
        value: 'desk_to_desk',
        desc: 'Penandatanganan dilakukan secara langsung dari meja ke meja antar pihak.',
        icon: 'solar:document-bold',
    },
    {
        label: 'Seremonial',
        value: 'seremonial',
        desc: 'Penandatanganan formal dalam acara seremonial dengan dihadiri pihak terkait.',
        icon: 'solar:cup-star-bold',
    },
    {
        label: 'Hybrid',
        value: 'hybrid',
        desc: 'Kombinasi luring dan daring (sebagian hadir fisik, sebagian via online meeting).',
        icon: 'solar:satellite-bold',
    },
];

const formatTimeToString = (val) => {
    if (val instanceof Date) {
        return val.getHours().toString().padStart(2, '0') + ':' + val.getMinutes().toString().padStart(2, '0');
    }
    return val;
};

const formatDateToString = (val) => {
    if (val instanceof Date) {
        const year = val.getFullYear();
        const month = String(val.getMonth() + 1).padStart(2, '0');
        const day = String(val.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    return val;
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        tanggal: formatDateToString(data.tanggal),
        waktu: formatTimeToString(data.waktu),
    })).post(route('penjadwalan.store'), {
        onSuccess: () => {
            emit('submitted');
            emit('close');
        },
    });
};
</script>

<template>
    <div class="space-y-6">
        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg flex items-start gap-3">
            <Icon icon="solar:info-circle-bold" class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" />
            <div class="text-sm">
                <p class="font-semibold text-blue-800 dark:text-blue-200">Pengajuan Jadwal Penandatanganan</p>
                <p class="text-blue-600 dark:text-blue-300 mt-1">
                    Silahkan ajukan jadwal penandatanganan kerjasama. Anda dapat memilih jadwal yang tersedia atau mengajukan waktu khusus.
                </p>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Tipe Jadwal: Desk to Desk / Seremonial / Hybrid -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Metode Penandatanganan</label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div v-for="option in typeOptions" :key="option.value"
                        class="flex flex-col items-start p-4 border border-gray-200 rounded-lg dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                        :class="{ 'ring-2 ring-blue-500 border-blue-500 bg-blue-50/50 dark:bg-blue-900/20': form.tipe === option.value }"
                        @click="form.tipe = option.value">
                        <div class="flex items-center gap-2 mb-1">
                            <RadioButton v-model="form.tipe" :inputId="option.value" :value="option.value" />
                            <Icon :icon="option.icon" class="w-5 h-5 text-blue-600" />
                            <label :for="option.value" class="text-sm font-semibold text-gray-900 dark:text-gray-200 cursor-pointer">
                                {{ option.label }}
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 ml-7">{{ option.desc }}</p>
                    </div>
                </div>
                <small class="text-red-500" v-if="form.errors.tipe">{{ form.errors.tipe }}</small>
            </div>

            <!-- Tanggal & Waktu -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-sm font-medium">Tanggal Penandatanganan</label>
                    <Calendar v-model="form.tanggal" dateFormat="dd/mm/yy" showIcon class="w-full" :minDate="new Date()" placeholder="Pilih Tanggal" />
                    <small class="text-red-500" v-if="form.errors.tanggal">{{ form.errors.tanggal }}</small>
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium">Waktu</label>
                    <Calendar v-model="form.waktu" timeOnly showIcon class="w-full" placeholder="Pilih Waktu" />
                    <small class="text-red-500" v-if="form.errors.waktu">{{ form.errors.waktu }}</small>
                </div>
            </div>

            <!-- Lokasi -->
            <div class="space-y-1">
                <label class="text-sm font-medium">Lokasi Penandatanganan</label>
                <InputText v-model="form.lokasi" class="w-full"
                    :placeholder="form.tipe === 'hybrid' ? 'Contoh: Ruang Rapat Pemkot + Online via Zoom' : 'Contoh: Ruang Rapat Pemkot Samarinda'" />
                <small class="text-red-500" v-if="form.errors.lokasi">{{ form.errors.lokasi }}</small>
            </div>

            <!-- Agenda -->
            <div class="space-y-1">
                <label class="text-sm font-medium">Agenda Penandatanganan</label>
                <InputText v-model="form.agenda" class="w-full" placeholder="Agenda utama acara penandatanganan" />
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
                    <span>Ajukan Jadwal Penandatanganan</span>
                </button>
            </div>
        </form>
    </div>
</template>
