<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { Icon } from '@iconify/vue';
import { useToast } from "vue-toastification";
import Step1Instansi from './Steps/Step1Instansi.vue';
import Step2Pihak from './Steps/Step2Pihak.vue';
import Step3Detail from './Steps/Step3Detail.vue';

const props = defineProps({
    kategoris: Array,
    provinsis: Array,
    pemohon: Object,
    corporate: Object,
    pemohonanList: Array,
    mode: {
        type: String,
        default: 'create'
    },
    initialData: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['close', 'success']);
const page = usePage();
const toast = useToast();

const getInitialValue = (key, fallback = '') => {
    return props.initialData?.[key] !== undefined && props.initialData?.[key] !== null ? props.initialData[key] : fallback;
};

const form = useForm({
    id: props.initialData?.id || null,
    uuid: props.initialData?.uuid || null,
    id_kategori: getInitialValue('id_kategori'),
    label: getInitialValue('label'),
    nama_instansi: props.mode === 'edit' ? getInitialValue('nama_instansi') : (props.corporate?.name || ''),
    id_provinsi: props.mode === 'edit' ? getInitialValue('id_provinsi') : (props.pemohon?.kota_ref?.province_id || props.corporate?.kota_ref?.province_id || ''),
    id_kota: props.mode === 'edit' ? getInitialValue('id_kota') : (props.pemohon?.kota_id || props.corporate?.kota_id || ''),
    kode_pos: props.mode === 'edit' ? getInitialValue('kode_pos') : (props.corporate?.postal_code || ''),
    alamat: props.mode === 'edit' ? getInitialValue('alamat') : (props.corporate?.address || ''),
    email: props.mode === 'edit' ? getInitialValue('email') : (props.corporate?.email || ''),
    telepon: props.mode === 'edit' ? getInitialValue('telepon') : (props.corporate?.phone || ''),
    website: props.mode === 'edit' ? getInitialValue('website') : (props.corporate?.website || ''),
    id_pemohon_1: props.mode === 'edit' ? getInitialValue('id_pemohon_1') : '', 
    latar_belakang: getInitialValue('latar_belakang'),
    maksud_tujuan: getInitialValue('maksud_tujuan'),
    lokasi_kerjasama: getInitialValue('lokasi_kerjasama'),
    ruang_lingkup: getInitialValue('ruang_lingkup'),
    jangka_waktu: getInitialValue('jangka_waktu'),
    tanggal_mulai: getInitialValue('tanggal_mulai'),
    tanggal_berakhir: getInitialValue('tanggal_berakhir'),
    manfaat: getInitialValue('manfaat'),
    analisis_dampak: getInitialValue('analisis_dampak'),
    pembiayaan: getInitialValue('pembiayaan'),
});

const kotas = ref([]);
const loadingKotas = ref(false);

watch(() => form.id_provinsi, async (provinsiId, oldProvinsiId) => {
    if (provinsiId) {
        loadingKotas.value = true;
        try {
            const response = await fetch(route('api.kotas', provinsiId));
            kotas.value = await response.json();
        } catch (error) {
            console.error('Failed to load kotas:', error);
        }
        loadingKotas.value = false;
        
        if (oldProvinsiId !== undefined && oldProvinsiId !== provinsiId) {
             const isInitialLoad = props.mode === 'edit' && provinsiId == props.initialData?.id_provinsi && oldProvinsiId === '';
             if (!isInitialLoad) {
                 form.id_kota = null;
             }
        }
    } else {
        kotas.value = [];
        form.id_kota = null;
    }
}, { immediate: true });

const submit = () => {
    const routeName = props.mode === 'edit' ? 'permohonan.update' : 'permohonan.store';
    const routeId = props.mode === 'edit' ? form.uuid : undefined;
    const method = props.mode === 'edit' ? 'put' : 'post';

    form[method](route(routeName, routeId), {
        onSuccess: () => {
            toast.success(props.mode === 'edit' ? 'Permohonan berhasil diperbarui' : 'Permohonan berhasil dibuat');
            emit('success');
            if (props.mode !== 'edit') form.reset();
        },
        onError: (errors) => {
             const firstError = Object.values(errors)[0];
             toast.error(firstError || "Gagal memproses permohonan");
        }
    });
};

const currentStep = ref(1);
const totalSteps = 3;

const nextStep = () => {
    if (currentStep.value < totalSteps) currentStep.value++;
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const kategoriOptions = computed(() => props.kategoris.map(k => ({ value: k.id, name: k.label })));
const provinsiOptions = computed(() => props.provinsis.map(p => ({ value: p.id, name: p.name })));
const kotaOptions = computed(() => kotas.value.map(k => ({ value: k.id, name: k.name })));
const ppksdOptions = computed(() => props.pemohonanList.map(p => ({ value: p.id, name: `${p.name} — ${p.jabatan}` })));
</script>

<template>
    <div class="h-full flex flex-col relative bg-white dark:bg-gray-800">
        <div class="px-6 pt-6 pb-2 flex-shrink-0">
            <div class="flex items-center justify-center mb-4">
                <div v-for="step in totalSteps" :key="step" class="flex items-center">
                    <div 
                        class="w-8 h-8 rounded-full flex items-center justify-center font-semibold text-sm transition"
                        :class="step <= currentStep ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-400'"
                    >
                        {{ step }}
                    </div>
                    <div v-if="step < totalSteps" class="w-12 h-0.5 mx-2" :class="step < currentStep ? 'bg-blue-600' : 'bg-gray-100 dark:bg-gray-700'"></div>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="flex-1 overflow-y-auto px-6 py-2 custom-scrollbar">
            <Step1Instansi 
                v-if="currentStep === 1" 
                :form="form" 
                :kategoriOptions="kategoriOptions"
                :provinsiOptions="provinsiOptions"
                :kotaOptions="kotaOptions"
                :loadingKotas="loadingKotas"
            />
            
            <Step2Pihak 
                v-if="currentStep === 2" 
                :form="form" 
                :pemohon="pemohon"
                :ppksdOptions="ppksdOptions"
                :pemohonanList="pemohonanList"
            />
            
            <Step3Detail 
                v-if="currentStep === 3" 
                :form="form" 
            />
        </form>

         <div class="flex-shrink-0 px-6 py-4 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 z-10 flex justify-between items-center">
             <div class="text-sm text-gray-500">
                 Langkah {{ currentStep }} dari {{ totalSteps }}
             </div>
             <div class="flex gap-3">
                 <button v-if="currentStep > 1" type="button" @click="prevStep" class="px-6 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-white rounded-lg font-medium transition flex items-center">
                    <Icon icon="solar:arrow-left-linear" class="w-5 h-5 mr-2" /> Sebelumnya
                </button>
                
                <button v-if="currentStep < totalSteps" type="button" @click="nextStep" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition flex items-center">
                    Selanjutnya <Icon icon="solar:arrow-right-linear" class="w-5 h-5 ml-2" />
                </button>
                
                <button v-else type="submit" @click="submit" :disabled="form.processing" class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition flex items-center disabled:opacity-50">
                    <Icon v-if="form.processing" icon="solar:refresh-circle-line-duotone" class="w-5 h-5 mr-2 animate-spin" />
                    <Icon v-else icon="solar:send-bold" class="w-5 h-5 mr-2" />
                    {{ mode === 'edit' ? 'Simpan Perubahan' : 'Submit Data' }}
                </button>
             </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 20px;
}
</style>
