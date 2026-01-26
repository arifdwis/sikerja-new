<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { Icon } from '@iconify/vue';
import SearchSelect from '@/Components/SearchSelect.vue';

const props = defineProps({
    kategoris: Array,
    provinsis: Array,
    pemohon: Object,
    corporate: Object,
    pemohonanList: Array,
    mode: {
        type: String,
        default: 'create' // 'create' or 'edit'
    },
    initialData: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['close', 'success']);

const page = usePage();

// Helper to safely access nested properties
const getInitialValue = (key, fallback = '') => {
    return props.initialData?.[key] !== undefined && props.initialData?.[key] !== null ? props.initialData[key] : fallback;
};

// Initialize form
const form = useForm({
    id: props.initialData?.id || null, // For update route
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

// Load kotas when provinsi changes
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
        
        // Only reset kota if strictly changing selection manually, not during initial load
        if (oldProvinsiId !== undefined && oldProvinsiId !== provinsiId) {
             // In edit mode, if we just loaded the form and the provinsi matches initial, don't reset.
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


import { useToast } from "vue-toastification";
const toast = useToast();

const submit = () => {
    if (props.mode === 'edit') {
        form.put(route('permohonan.update', form.uuid), {
            onSuccess: () => {
                toast.success('Permohonan berhasil diperbarui');
                emit('success');
            },
            onError: (errors) => {
                 const firstError = Object.values(errors)[0];
                 toast.error(firstError || "Gagal memperbarui permohonan");
            }
        });
    } else {
        form.post(route('permohonan.store'), {
            onSuccess: () => {
                toast.success('Permohonan berhasil dibuat');
                emit('success');
                form.reset();
            },
            onError: (errors) => {
                 const firstError = Object.values(errors)[0];
                 toast.error(firstError || "Mohon lengkapi data wajib");
            }
        });
    }
};

// Current step for wizard
const currentStep = ref(1);
const totalSteps = 3;

const nextStep = () => {
    if (currentStep.value < totalSteps) currentStep.value++;
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

// Mapped Options for SearchSelect
const kategoriOptions = computed(() => props.kategoris.map(k => ({ value: k.id, name: k.label })));
const provinsiOptions = computed(() => props.provinsis.map(p => ({ value: p.id, name: p.name })));
const kotaOptions = computed(() => kotas.value.map(k => ({ value: k.id, name: k.name })));
const ppksdOptions = computed(() => props.pemohonanList.map(p => ({ value: p.id, name: `${p.name} — ${p.jabatan}` })));
</script>

<template>
    <div class="h-full flex flex-col relative bg-white dark:bg-gray-800">
        <!-- Step Indicator -->
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
            
            <!-- Step 1: Data Instansi -->
            <div v-show="currentStep === 1">
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 border border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <Icon icon="solar:buildings-bold" class="w-5 h-5 mr-2 text-blue-500" />
                        Data Instansi / Perusahaan / Lembaga
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">(<span class="text-red-500 font-bold">*</span>) Tidak boleh kosong</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Judul Kerjasama <span class="text-red-500">*</span>
                            </label>
                            <input v-model="form.label" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Contoh: Kerja Sama Pengadaan Barang Dan Jasa..." />
                            <p v-if="form.errors.label" class="text-red-500 text-sm mt-1">{{ form.errors.label }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Urusan/Kategori <span class="text-red-500">*</span>
                            </label>
                            <SearchSelect 
                                v-model="form.id_kategori" 
                                :options="kategoriOptions" 
                                placeholder="-- Pilih Salah Satu --"
                            />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Nama Instansi <span class="text-red-500">*</span>
                            </label>
                            <input v-model="form.nama_instansi" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Contoh: Dinas Pariwisata" />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Provinsi <span class="text-red-500">*</span>
                            </label>
                            <SearchSelect 
                                v-model="form.id_provinsi" 
                                :options="provinsiOptions" 
                                placeholder="-- Pilih Provinsi --"
                            />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Kabupaten/Kota <span class="text-red-500">*</span>
                            </label>
                            <SearchSelect 
                                v-model="form.id_kota" 
                                :options="kotaOptions" 
                                :disabled="loadingKotas || !kotas.length"
                                placeholder="-- Pilih Kota --"
                            />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Kode Pos <span class="text-red-500">*</span>
                            </label>
                            <input v-model="form.kode_pos" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Contoh: 75123" />
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Alamat <span class="text-red-500">*</span>
                            </label>
                            <textarea v-model="form.alamat" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Contoh: Jl. Kusuma Bangsa No.66, Samarinda"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Telepon <span class="text-red-500">*</span>
                            </label>
                            <input v-model="form.telepon" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Contoh: 081259xxxx" />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input v-model="form.email" type="email" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Contoh: email@instansi.go.id" />
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Website</label>
                            <input v-model="form.website" type="url" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="https://www.instansi.go.id" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Data Pemohon (PPKSD-1 & PPKSD-2) -->
            <div v-show="currentStep === 2">
                
                <!-- PPKSD-1: User Profile Card -->
                <div class="mb-10">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                         <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mr-3">
                            <Icon icon="solar:user-id-bold" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                         </div>
                        Pihak Pertama (PPKSD-1)
                    </h3>
                    
                    <div class="relative group">
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-3xl opacity-100 shadow-xl shadow-blue-500/20 transition-all duration-300"></div>
                        <div class="absolute top-0 right-0 w-64 h-full overflow-hidden rounded-r-3xl">
                             <Icon icon="solar:shield-check-bold" class="w-64 h-64 text-white/5 absolute -top-10 -right-10 transform rotate-12" />
                        </div>

                        <div class="relative p-8 flex flex-col md:flex-row items-center gap-8 z-10">
                            <!-- Avatar -->
                            <div class="shrink-0 relative">
                                <div class="w-24 h-24 rounded-full bg-white/20 backdrop-blur-md border-[3px] border-white/30 flex items-center justify-center shadow-lg">
                                    <span class="text-3xl font-bold text-white">{{ pemohon?.name?.charAt(0) || 'U' }}</span>
                                </div>
                                <div class="absolute bottom-0 right-0 bg-green-500 w-6 h-6 rounded-full border-2 border-white shadow-sm flex items-center justify-center">
                                     <Icon icon="solar:check-read-bold" class="w-3 h-3 text-white" />
                                </div>
                            </div>
                            
                            <!-- Info -->
                            <div class="flex-1 text-center md:text-left text-white">
                                <div class="mb-6">
                                    <h4 class="text-2xl font-bold tracking-tight mb-1">{{ pemohon?.name }}</h4>
                                    <p class="text-blue-100 font-medium tracking-wide opacity-90 text-sm">{{ pemohon?.nip || 'NIP Tidak Tersedia' }}</p>
                                </div>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-12 gap-y-4 text-sm">
                                    <div class="flex items-center gap-3 bg-white/10 rounded-lg px-4 py-2.5 backdrop-blur-sm border border-white/5 hover:bg-white/20 transition-colors">
                                        <Icon icon="solar:briefcase-bold" class="w-5 h-5 text-blue-200" />
                                        <div class="text-left">
                                            <p class="text-[10px] text-blue-200 uppercase tracking-wider font-semibold">Jabatan</p>
                                            <p class="font-medium truncate">{{ pemohon?.jabatan || '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 bg-white/10 rounded-lg px-4 py-2.5 backdrop-blur-sm border border-white/5 hover:bg-white/20 transition-colors">
                                        <Icon icon="solar:buildings-bold" class="w-5 h-5 text-blue-200" />
                                        <div class="text-left">
                                            <p class="text-[10px] text-blue-200 uppercase tracking-wider font-semibold">Unit Kerja</p>
                                            <p class="font-medium truncate">{{ pemohon?.unit_kerja || '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 bg-white/10 rounded-lg px-4 py-2.5 backdrop-blur-sm border border-white/5 hover:bg-white/20 transition-colors">
                                        <Icon icon="solar:phone-bold" class="w-5 h-5 text-blue-200" />
                                        <div class="text-left">
                                            <p class="text-[10px] text-blue-200 uppercase tracking-wider font-semibold">Telepon</p>
                                            <p class="font-medium truncate">{{ pemohon?.phone || '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 bg-white/10 rounded-lg px-4 py-2.5 backdrop-blur-sm border border-white/5 hover:bg-white/20 transition-colors">
                                        <Icon icon="solar:letter-bold" class="w-5 h-5 text-blue-200" />
                                        <div class="text-left">
                                            <p class="text-[10px] text-blue-200 uppercase tracking-wider font-semibold">Email</p>
                                            <p class="font-medium truncate">{{ pemohon?.email || '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                     <p class="text-xs text-gray-400 mt-3 text-right flex justify-end items-center gap-1">
                        <Icon icon="solar:info-circle-bold" class="w-3 h-3" />
                        Data diambil otomatis dari profil akun Anda
                    </p>
                </div>

                <div class="flex items-center gap-4 my-10">
                    <div class="h-px bg-gray-200 dark:bg-gray-700 flex-1"></div>
                    <span class="text-sm font-medium text-gray-400 uppercase tracking-widest">Bekerjasama Dengan</span>
                    <div class="h-px bg-gray-200 dark:bg-gray-700 flex-1"></div>
                </div>

                <!-- PPKSD-2 Selection -->
                <div>
                     <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center mr-3">
                            <Icon icon="solar:users-group-two-rounded-bold" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        Pihak Kedua (PPKSD-2)
                    </h3>
                    
                    <div class="bg-emerald-50/50 dark:bg-emerald-900/10 rounded-3xl p-8 border border-emerald-100 dark:border-emerald-800/20 hover:border-emerald-200 dark:hover:border-emerald-700/50 transition-colors">
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-3">
                            Pilih Pejabat Penandatangan (PPKSD-2) <span class="text-red-500">*</span>
                        </label>
                        
                        <div class="relative">
                            <SearchSelect 
                                v-model="form.id_pemohon_1" 
                                :options="ppksdOptions" 
                                placeholder="-- Pilih Pejabat --"
                            />
                        </div>

                         <div v-if="form.id_pemohon_1" class="mt-6 p-4 bg-white dark:bg-gray-800 rounded-2xl border border-emerald-100 dark:border-emerald-800/30 flex items-start gap-4 animate-fade-in-up">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                                <Icon icon="solar:user-check-bold" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">Pejabat Terpilih</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ pemohonanList.find(p => p.id === form.id_pemohon_1)?.name }} 
                                    <span class="text-gray-300 mx-2">|</span> 
                                    {{ pemohonanList.find(p => p.id === form.id_pemohon_1)?.jabatan }}
                                </p>
                            </div>
                        </div>

                        <p v-else class="text-sm text-gray-500 mt-4 flex items-center gap-2">
                            <Icon icon="solar:info-circle-linear" class="w-4 h-4 text-emerald-500" />
                            Daftar ini memuat pejabat yang berwenang menandatangani kerjasama dari pihak kedua.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Step 3: Detail Kerjasama (Permendagri No.22 Tahun 2020) -->
            <div v-show="currentStep === 3">
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 border border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <Icon icon="solar:document-text-bold" class="w-5 h-5 mr-2 text-orange-500" />
                        Detail Kerjasama (Permendagri No.22 Tahun 2020 Pasal 7)
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Latar Belakang <span class="text-red-500">*</span> <span class="text-gray-400 text-xs">(300 Kata)</span>
                            </label>
                            <textarea v-model="form.latar_belakang" rows="4" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Masukan latar belakang pengajuan kerjasama..."></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Maksud dan Tujuan <span class="text-red-500">*</span> <span class="text-gray-400 text-xs">(500 Kata)</span>
                            </label>
                            <textarea v-model="form.maksud_tujuan" rows="4" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Masukan maksud dan tujuan kerjasama..."></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Lokasi Kerja Sama Daerah <span class="text-red-500">*</span> <span class="text-gray-400 text-xs">(100 Kata)</span>
                            </label>
                            <textarea v-model="form.lokasi_kerjasama" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Masukan lokasi kerjasama..."></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Ruang Lingkup <span class="text-red-500">*</span> <span class="text-gray-400 text-xs">(500 Kata)</span>
                            </label>
                            <textarea v-model="form.ruang_lingkup" rows="4" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Masukan ruang lingkup kerjasama..."></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Jangka Waktu <span class="text-red-500">*</span> <span class="text-gray-400 text-xs">(100 Kata)</span>
                            </label>
                            <textarea v-model="form.jangka_waktu" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Masukan jangka waktu kerjasama..."></textarea>
                        </div>

                         <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Tanggal Mulai <span class="text-red-500">*</span>
                                </label>
                                <input v-model="form.tanggal_mulai" type="date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Tanggal Berakhir <span class="text-red-500">*</span>
                                </label>
                                <input v-model="form.tanggal_berakhir" type="date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Manfaat <span class="text-red-500">*</span> <span class="text-gray-400 text-xs">(500 Kata)</span>
                            </label>
                            <textarea v-model="form.manfaat" rows="4" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Masukan manfaat kerjasama..."></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Analisis Dampak Sosial dan Lingkungan <span class="text-red-500">*</span> <span class="text-gray-400 text-xs">(1000 Kata)</span>
                            </label>
                            <textarea v-model="form.analisis_dampak" rows="5" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Analisis dampak sosial dan lingkungan sesuai bidang yang dikerjasamakan..."></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Pembiayaan <span class="text-red-500">*</span> <span class="text-gray-400 text-xs">(150 Kata)</span>
                            </label>
                            <textarea v-model="form.pembiayaan" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Masukan skema pembiayaan..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
        </form>

        <!-- Footer Actions -->
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
