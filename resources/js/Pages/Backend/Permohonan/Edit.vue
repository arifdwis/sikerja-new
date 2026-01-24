<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import { Icon } from '@iconify/vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';

const props = defineProps({
    permohonan: Object,
    kategoris: Array,
    provinsis: Array,
    kotas: Array, // Initial loading based on existing provinsi
    share: Object,
});

const form = useForm({
    id_kategori: props.permohonan.id_kategori || '',
    label: props.permohonan.label || '',
    nama_instansi: props.permohonan.nama_instansi || '',
    id_provinsi: props.permohonan.id_provinsi || '',
    id_kota: props.permohonan.id_kota || '',
    kode_pos: props.permohonan.kode_pos || '',
    alamat: props.permohonan.alamat || '',
    email: props.permohonan.email || '',
    telepon: props.permohonan.telepon || '',
    website: props.permohonan.website || '',
    latar_belakang: props.permohonan.latar_belakang || '',
    maksud_tujuan: props.permohonan.maksud_tujuan || '',
    lokasi_kerjasama: props.permohonan.lokasi_kerjasama || '',
    ruang_lingkup: props.permohonan.ruang_lingkup || '',
    jangka_waktu: props.permohonan.jangka_waktu || '',
    manfaat: props.permohonan.manfaat || '',
    analisis_dampak: props.permohonan.analisis_dampak || '',
    pembiayaan: props.permohonan.pembiayaan || '',
});

const kotas = ref(props.kotas || []);
const loadingKotas = ref(false);

// Load kotas when provinsi changes
watch(() => form.id_provinsi, async (provinsiId, oldProvinsiId) => {
    if (provinsiId) {
        // Prevent reload on initial mount if already loaded by props
        if (oldProvinsiId !== undefined && oldProvinsiId !== provinsiId) {
            loadingKotas.value = true;
            try {
                const response = await fetch(route('api.kotas', provinsiId));
                kotas.value = await response.json();
                form.id_kota = null; // Reset kota only if manually changed
            } catch (error) {
                console.error('Failed to load kotas:', error);
            }
            loadingKotas.value = false;
        }
    } else {
        kotas.value = [];
        form.id_kota = null;
    }
});

const submit = () => {
    form.put(route('permohonan.update', props.permohonan.uuid));
};
</script>

<template>
    <Head title="Edit Permohonan" />
    <AuthenticatedLayout>
        <div class="py-6">
            <div class="w-full px-4 sm:px-6 lg:px-8 space-y-4">
                
                <Breadcrumb :crumbs="[
                    { label: 'Dashboard', route: 'dashboard' },
                    { label: 'Daftar Permohonan', route: 'permohonan.index' },
                    { label: 'Edit', route: null }
                ]" />

                <!-- Header -->
                <div class="mb-4">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Permohonan</h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Perbarui data permohonan kerjasama.</p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 overflow-hidden">
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <!-- Identitas -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 border-b pb-2">Identitas & Instansi</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Judul Kerjasama <span class="text-red-500">*</span></label>
                                    <input v-model="form.label" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                    <p v-if="form.errors.label" class="text-red-500 text-sm mt-1">{{ form.errors.label }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori <span class="text-red-500">*</span></label>
                                    <select v-model="form.id_kategori" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                        <option value="">-- Pilih --</option>
                                        <option v-for="k in kategoris" :key="k.id" :value="k.id">{{ k.label }}</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Instansi <span class="text-red-500">*</span></label>
                                    <input v-model="form.nama_instansi" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
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
                                    <select v-model="form.id_kota" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
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
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Maksud & Tujuan <span class="text-red-500">*</span></label>
                                    <textarea v-model="form.maksud_tujuan" rows="4" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lokasi Kerjasama <span class="text-red-500">*</span></label>
                                    <textarea v-model="form.lokasi_kerjasama" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ruang Lingkup <span class="text-red-500">*</span></label>
                                    <textarea v-model="form.ruang_lingkup" rows="4" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jangka Waktu</label>
                                    <textarea v-model="form.jangka_waktu" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
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
                                :href="route('permohonan.show', permohonan.uuid)" 
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
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
