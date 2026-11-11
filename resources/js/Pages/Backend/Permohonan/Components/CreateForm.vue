<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { Icon } from '@iconify/vue';
import { useToast } from "vue-toastification";
import Step1Instansi from './Steps/Step1Instansi.vue';
import Step3Detail from './Steps/Step3Detail.vue';

const props = defineProps({
    kategoris: Array,
    provinsis: Array,
    opds: {
        type: Array,
        default: () => []
    },
    userOpd: {
        type: Object,
        default: null
    },
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

const getInitialOpdIds = () => {
    if (props.mode === 'edit') {
        const existing = props.initialData?.opds;
        if (Array.isArray(existing) && existing.length) {
            return existing.map(o => o.id);
        }
    }
    if (props.userOpd) return [props.userOpd.id];
    return [];
};

const form = useForm({
    _method: props.mode === 'edit' ? 'put' : 'post',
    id: props.initialData?.id || null,
    uuid: props.initialData?.uuid || null,
    id_kategori: getInitialValue('id_kategori'),
    label: getInitialValue('label'),
    nama_instansi: props.mode === 'edit' ? getInitialValue('nama_instansi') : (props.corporate?.name || props.corporate?.nama_instansi || props.pemohon?.nama_instansi || ''),
    id_provinsi: props.mode === 'edit' ? getInitialValue('id_provinsi') : (props.pemohon?.kota_ref?.province_id || props.corporate?.kota_ref?.province_id || ''),
    id_kota: props.mode === 'edit' ? getInitialValue('id_kota') : (props.pemohon?.kota_id || props.corporate?.kota_id || ''),
    kode_pos: props.mode === 'edit' ? getInitialValue('kode_pos') : (props.corporate?.postal_code || ''),
    alamat: props.mode === 'edit' ? getInitialValue('alamat') : (props.corporate?.address || props.corporate?.alamat_instansi || props.pemohon?.alamat_instansi || ''),
    email: props.mode === 'edit' ? getInitialValue('email') : (props.corporate?.email || props.corporate?.email_instansi || props.pemohon?.email_instansi || ''),
    telepon: props.mode === 'edit' ? getInitialValue('telepon') : (props.corporate?.phone || props.corporate?.telepon_instansi || props.pemohon?.telepon_instansi || ''),
    website: props.mode === 'edit' ? getInitialValue('website') : (props.corporate?.website || props.pemohon?.website || ''),
    nama_pimpinan: getInitialValue('nama_pimpinan'),
    jabatan_pimpinan: getInitialValue('jabatan_pimpinan'),
    nip_pimpinan: getInitialValue('nip_pimpinan'),
    pic1_nama: getInitialValue('pic1_nama'),
    pic1_jabatan: getInitialValue('pic1_jabatan'),
    pic1_phone: getInitialValue('pic1_phone'),
    pic1_email: getInitialValue('pic1_email'),
    pic2_nama: getInitialValue('pic2_nama'),
    pic2_jabatan: getInitialValue('pic2_jabatan'),
    pic2_phone: getInitialValue('pic2_phone'),
    pic2_email: getInitialValue('pic2_email'),
    is_draft: false,
    id_pemohon_1: props.mode === 'edit' ? getInitialValue('id_pemohon_1') : '', 
    latar_belakang: getInitialValue('latar_belakang'),
    maksud_tujuan: getInitialValue('maksud_tujuan'),
    lokasi_kerjasama: getInitialValue('lokasi_kerjasama'),
    ruang_lingkup: getInitialValue('ruang_lingkup'),
    opd_ids: getInitialOpdIds(),
    jangka_waktu: getInitialValue('jangka_waktu'),
    tanggal_mulai: getInitialValue('tanggal_mulai'),
    tanggal_berakhir: getInitialValue('tanggal_berakhir'),
    manfaat: getInitialValue('manfaat'),
    analisis_dampak: getInitialValue('analisis_dampak'),
    pembiayaan: getInitialValue('pembiayaan'),
    // Dokumen awal opsional langsung saat submit
    surat_permohonan: null,
    proposal_kak: null,
    draft_mou: null,
    dokumen_legalitas: null,
    dokumen_pendukung: null,
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

const submit = (asDraft = false) => {
    form.is_draft = asDraft;

    if (
        !asDraft &&
        form.tanggal_mulai &&
        form.tanggal_berakhir &&
        form.tanggal_berakhir < form.tanggal_mulai
    ) {
        toast.error('Tanggal berakhir tidak boleh lebih awal dari tanggal mulai.');
        return;
    }

    const routeName = props.mode === 'edit' ? 'permohonan.update' : 'permohonan.store';
    const routeId = props.mode === 'edit' ? form.uuid : undefined;

    form.post(route(routeName, routeId), {
        forceFormData: true,
        onSuccess: () => {
            const successMsg = asDraft 
                ? 'Draft permohonan berhasil disimpan' 
                : (props.mode === 'edit' ? 'Permohonan berhasil diperbarui' : 'Permohonan resmi berhasil dikirim');
            toast.success(successMsg);
            emit('success');
            if (props.mode !== 'edit') form.reset();
        },
        onError: (errors) => {
             const firstError = Object.values(errors)[0];
             toast.error(firstError || "Gagal memproses permohonan");
        }
    });
};

// Langsung dari Step 1 (Data Instansi, Pimpinan & 2 PIC) ke Step 3 (Detail Substansi & Dokumen)
// Sesuai kesepakatan: pemohon eksternal tidak perlu memilih pejabat penandatangan internal (Step 2 di-bypass).
const currentStep = ref(1);
const steps = [
    { number: 1, title: 'Identitas Instansi, Pimpinan & PIC' },
    { number: 2, title: 'Substansi Kerjasama & Lampiran Berkas' },
];
const totalSteps = steps.length;

const nextStep = () => {
    if (currentStep.value < totalSteps) currentStep.value++;
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const kategoriOptions = computed(() => (props.kategoris || []).map(k => ({ value: k.id, name: k.label })));
const provinsiOptions = computed(() => (props.provinsis || []).map(p => ({ value: p.id, name: p.name })));
const kotaOptions = computed(() => kotas.value.map(k => ({ value: k.id, name: k.name })));
</script>

<template>
    <div class="h-full flex flex-col relative bg-white dark:bg-gray-800">
        <div class="px-6 pt-6 pb-2 flex-shrink-0">
            <div class="flex items-center justify-center mb-4">
                <div v-for="(s, idx) in steps" :key="s.number" class="flex items-center">
                    <div class="flex items-center gap-2">
                        <div 
                            class="w-8 h-8 rounded-full flex items-center justify-center font-semibold text-sm transition"
                            :class="s.number < currentStep ? 'bg-emerald-600 text-white' : (s.number === currentStep ? 'bg-blue-600 text-white ring-4 ring-blue-100 dark:ring-blue-900/40' : 'bg-gray-100 dark:bg-gray-700 text-gray-400')"
                        >
                            <Icon v-if="s.number < currentStep" icon="lucide:check" class="w-4 h-4" />
                            <span v-else>{{ s.number }}</span>
                        </div>
                        <span class="text-xs font-semibold hidden sm:inline" :class="s.number === currentStep ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500'">
                            {{ s.title }}
                        </span>
                    </div>
                    <div v-if="idx < steps.length - 1" class="w-12 h-0.5 mx-3" :class="s.number < currentStep ? 'bg-emerald-600' : 'bg-gray-200 dark:bg-gray-700'"></div>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit(false)" class="flex-1 overflow-y-auto px-6 py-2 custom-scrollbar">
            <Step1Instansi 
                v-if="currentStep === 1" 
                :form="form" 
                :kategoriOptions="kategoriOptions"
                :provinsiOptions="provinsiOptions"
                :kotaOptions="kotaOptions"
                :loadingKotas="loadingKotas"
            />
            
            <Step3Detail 
                v-if="currentStep === 2" 
                :form="form" 
                :opds="opds"
                :userOpd="userOpd"
                :existingFiles="initialData?.files || []"
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
                
                <template v-else>
                    <button 
                        v-if="mode !== 'edit'"
                        type="button" 
                        @click="submit(true)" 
                        :disabled="form.processing" 
                        class="px-5 py-2.5 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-100 rounded-lg font-medium transition inline-flex items-center gap-2 disabled:opacity-50"
                    >
                        <Icon icon="solar:diskette-bold" class="w-5 h-5 text-slate-500" />
                        Simpan Draft
                    </button>
                    
                    <button 
                        type="button" 
                        @click="submit(false)" 
                        :disabled="form.processing" 
                        class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition inline-flex items-center gap-2 disabled:opacity-50 shadow-sm"
                    >
                        <Icon v-if="form.processing" icon="lucide:loader-circle" class="h-5 w-5 animate-spin" />
                        <Icon v-else icon="lucide:send" class="h-5 w-5 shrink-0" />
                        {{ mode === 'edit' ? 'Simpan Perubahan' : 'Kirim Permohonan Resmi' }}
                    </button>
                </template>
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
