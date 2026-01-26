<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import { Icon } from '@iconify/vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';

const props = defineProps({
    kategoris: Array,
    provinsis: Array,
    pemohon: Object,    // Logged in pemohon profile
    corporate: Object,  // Logged in corporate profile
    pemohonanList: Array, // For PPKSD-2 auto-fill
    share: Object,
});

const form = useForm({
    id_kategori: '',
    label: '',
    nama_instansi: props.corporate?.nama_instansi || props.pemohon?.nama_instansi || '',
    id_provinsi: props.corporate?.kota_ref?.province_id || props.pemohon?.kota_ref?.province_id || '',
    id_kota: props.corporate?.kota_id || props.pemohon?.kota_id || '',
    kode_pos: '',
    alamat: props.corporate?.alamat_instansi || props.pemohon?.alamat_instansi || '',
    email: props.corporate?.email_instansi || props.pemohon?.email_instansi || '',
    telepon: props.corporate?.telepon_instansi || props.pemohon?.telepon_instansi || '',
    website: props.corporate?.website || props.pemohon?.website || '',
    
    // Legacy fields (optional)
    id_pemohon_1: '', 

    latar_belakang: '',
    maksud_tujuan: '',
    lokasi_kerjasama: '',
    ruang_lingkup: '',
    jangka_waktu: '',
    tanggal_mulai: '',
    tanggal_berakhir: '',
    manfaat: '',
    analisis_dampak: '',
    pembiayaan: '',
});

const kotas = ref([]);
const loadingKotas = ref(false);

// Load initial kotas if province is pre-filled
onMounted(async () => {
    if (form.id_provinsi) {
        await loadKotas(form.id_provinsi);
    }
});

const loadKotas = async (provinsiId) => {
    loadingKotas.value = true;
    try {
        const response = await fetch(route('api.kotas', provinsiId));
        kotas.value = await response.json();
    } catch (error) {
        console.error('Failed to load kotas:', error);
    }
    loadingKotas.value = false;
};

// Load kotas when provinsi changes
watch(() => form.id_provinsi, async (provinsiId, oldProvinsiId) => {
    if (provinsiId && provinsiId !== oldProvinsiId) {
        await loadKotas(provinsiId);
        // Reset kota only if it doesn't match the new list (simplified logic: just reset if user changes it)
        // But on initial mount we want to keep the pre-filled value.
        // The watch triggers on mount too? Vue 3 watch immediate is false by default.
        if (oldProvinsiId) { 
             form.id_kota = ''; 
        }
    } else if (!provinsiId) {
        kotas.value = [];
        form.id_kota = '';
    }
});

import { useToast } from "vue-toastification";

const toast = useToast();

// ... existing code ...

const submit = () => {
    form.post(route('permohonan.store'), {
        onError: (errors) => {
             // Show first error or generic message
             const firstError = Object.values(errors)[0];
             toast.error(firstError || "Mohon lengkapi semua field yang wajib diisi (bertanda *).");
             // Optional: Scroll to top or first error
             window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
};
</script>

<template>
    <Head title="Buat Permohonan" />
    <AuthenticatedLayout>
        <div class="py-6">
            <div class="w-full px-4 sm:px-6 lg:px-8 space-y-4">
                
                <Breadcrumb :crumbs="[
                    { label: 'Dashboard', route: 'dashboard' },
                    { label: 'Daftar Permohonan', route: 'permohonan.index' },
                    { label: 'Buat Baru', route: null }
                ]" />

                <!-- Header -->
                <div class="mb-4">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Buat Permohonan Baru</h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Isi formulir untuk mengajukan permohonan kerjasama.</p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 overflow-hidden">
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <!-- Identitas -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 border-b pb-2">Identitas & Instansi</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Judul Kerjasama <span class="text-red-500">*</span></label>
                                    <input v-model="form.label" type="text" placeholder="Contoh: Kerjasama Pemanfaatan Lahan..." class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                    <p v-if="form.errors.label" class="text-red-500 text-sm mt-1">{{ form.errors.label }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori <span class="text-red-500">*</span></label>
                                    <select v-model="form.id_kategori" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                        <option value="">-- Pilih --</option>
                                        <option v-for="k in kategoris" :key="k.id" :value="k.id">{{ k.label }}</option>
                                    </select>
                                    <p v-if="form.errors.id_kategori" class="text-red-500 text-sm mt-1">{{ form.errors.id_kategori }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Instansi <span class="text-red-500">*</span></label>
                                    <input v-model="form.nama_instansi" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                    <p v-if="form.errors.nama_instansi" class="text-red-500 text-sm mt-1">{{ form.errors.nama_instansi }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Provinsi <span class="text-red-500">*</span></label>
                                    <select v-model="form.id_provinsi" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                        <option value="">-- Pilih --</option>
                                        <option v-for="p in provinsis" :key="p.id" :value="p.id">{{ p.name }}</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kota/Kabupaten <span class="text-red-500">*</span></label>
                                    <select v-model="form.id_kota" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" :disabled="loadingKotas">
                                        <option value="">-- Pilih --</option>
                                        <option v-for="k in kotas" :key="k.id" :value="k.id">{{ k.name }}</option>
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alamat</label>
                                    <textarea v-model="form.alamat" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Telepon</label>
                                    <input v-model="form.telepon" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                                    <input v-model="form.email" type="email" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Website</label>
                                    <input v-model="form.website" type="url" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                </div>
                            </div>
                        </div>

                        <!-- Detail -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 border-b pb-2">Detail Kerjasama</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Latar Belakang <span class="text-red-500">*</span></label>
                                    <textarea v-model="form.latar_belakang" rows="4" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                                    <p v-if="form.errors.latar_belakang" class="text-red-500 text-sm mt-1">{{ form.errors.latar_belakang }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Maksud & Tujuan <span class="text-red-500">*</span></label>
                                    <textarea v-model="form.maksud_tujuan" rows="4" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                                    <p v-if="form.errors.maksud_tujuan" class="text-red-500 text-sm mt-1">{{ form.errors.maksud_tujuan }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lokasi Kerjasama <span class="text-red-500">*</span></label>
                                    <textarea v-model="form.lokasi_kerjasama" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ruang Lingkup <span class="text-red-500">*</span></label>
                                    <textarea v-model="form.ruang_lingkup" rows="4" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                                    <p v-if="form.errors.ruang_lingkup" class="text-red-500 text-sm mt-1">{{ form.errors.ruang_lingkup }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jangka Waktu</label>
                                    <textarea v-model="form.jangka_waktu" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Contoh: 12 Bulan / 1 Tahun"></textarea>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Mulai</label>
                                        <input v-model="form.tanggal_mulai" type="date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Berakhir</label>
                                        <input v-model="form.tanggal_berakhir" type="date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Manfaat</label>
                                    <textarea v-model="form.manfaat" rows="4" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Analisis Dampak Sosial & Lingkungan</label>
                                    <textarea v-model="form.analisis_dampak" rows="4" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pembiayaan</label>
                                    <textarea v-model="form.pembiayaan" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t dark:border-gray-700">
                             <Link 
                                :href="route('permohonan.index')" 
                                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition"
                            >
                                Batal
                            </Link>
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center"
                            >
                                <Icon v-if="form.processing" icon="solar:refresh-circle-line-duotone" class="w-5 h-5 mr-2 animate-spin" />
                                Simpan / Ajukan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
