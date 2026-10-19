<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch, onMounted, computed } from 'vue';
import { Icon } from '@iconify/vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import { computeJangkaWaktu } from '@/utils/jangkaWaktu';

const props = defineProps({
    kategoris: Array,
    provinsis: Array,
    opds: Array,         // Daftar OPD aktif
    userOpd: Object,     // OPD user (null jika non-Pemkot)
    pemohon: Object,    // Logged in pemohon profile
    corporate: Object,  // Logged in corporate profile
    pemohonanList: Array, // For PPKSD-2 auto-fill
    kbInduk: Object,     // PKS Turunan dari KB
    share: Object,
});

const isPksTurunan = computed(() => !!props.kbInduk);

const form = useForm({
    tipe_dokumen: props.kbInduk ? 'PKS' : 'KB',
    id_kb_induk: props.kbInduk ? props.kbInduk.id : null,
    is_draft: false,

    id_kategori: props.kbInduk?.id_kategori || '',
    label: props.kbInduk ? `PKS Pelaksanaan: ${props.kbInduk.label}` : '',
    nama_instansi: props.kbInduk?.nama_instansi || props.corporate?.nama_instansi || props.pemohon?.nama_instansi || '',
    
    // Data Pimpinan Tertinggi
    nama_pimpinan: props.kbInduk?.nama_pimpinan || '',
    jabatan_pimpinan: props.kbInduk?.jabatan_pimpinan || '',
    nip_pimpinan: props.kbInduk?.nip_pimpinan || '',

    // Data PIC 1
    pic1_nama: props.kbInduk?.pic1_nama || '',
    pic1_jabatan: props.kbInduk?.pic1_jabatan || '',
    pic1_phone: props.kbInduk?.pic1_phone || '',
    pic1_email: props.kbInduk?.pic1_email || '',

    // Data PIC 2
    pic2_nama: props.kbInduk?.pic2_nama || '',
    pic2_jabatan: props.kbInduk?.pic2_jabatan || '',
    pic2_phone: props.kbInduk?.pic2_phone || '',
    pic2_email: props.kbInduk?.pic2_email || '',

    id_provinsi: props.kbInduk?.id_provinsi || props.corporate?.kota_ref?.province_id || props.pemohon?.kota_ref?.province_id || '',
    id_kota: props.kbInduk?.id_kota || props.corporate?.kota_id || props.pemohon?.kota_id || '',
    kode_pos: props.kbInduk?.kode_pos || '',
    alamat: props.kbInduk?.alamat || props.corporate?.alamat_instansi || props.pemohon?.alamat_instansi || '',
    email: props.kbInduk?.email || props.corporate?.email_instansi || props.pemohon?.email_instansi || '',
    telepon: props.kbInduk?.telepon || props.corporate?.telepon_instansi || props.pemohon?.telepon_instansi || '',
    website: props.kbInduk?.website || props.corporate?.website || props.pemohon?.website || '',
    
    // Legacy fields (optional)
    id_pemohon_1: '', 

    latar_belakang: '',
    maksud_tujuan: '',
    lokasi_kerjasama: props.kbInduk?.lokasi_kerjasama || '',
    ruang_lingkup: '',
    // OPD multiple - Req 12. Jika user SSO punya OPD, auto-isi
    opd_ids: props.kbInduk?.opds ? props.kbInduk.opds.map(o => o.id) : (props.userOpd ? [props.userOpd.id] : []),
    jangka_waktu: '',
    tanggal_mulai: '',
    tanggal_berakhir: '',
    manfaat: '',
    analisis_dampak: '',
    pembiayaan: '',
});

// Auto-hitung jangka waktu dari tanggal_mulai & tanggal_berakhir
const jangkaWaktuAuto = computed(() =>
    computeJangkaWaktu(form.tanggal_mulai, form.tanggal_berakhir)
);
watch([
    () => form.tanggal_mulai,
    () => form.tanggal_berakhir,
], () => {
    form.jangka_waktu = jangkaWaktuAuto.value;
}, { immediate: true });

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

const submit = (asDraft = false) => {
    form.is_draft = asDraft;

    if (!asDraft && form.tanggal_mulai && form.tanggal_berakhir && form.tanggal_berakhir < form.tanggal_mulai) {
        toast.error('Tanggal berakhir tidak boleh lebih awal dari tanggal mulai.');
        return;
    }

    form.post(route('permohonan.store'), {
        onError: (errors) => {
             const firstError = Object.values(errors)[0];
             toast.error(firstError || "Mohon lengkapi semua field yang wajib diisi (bertanda *).");
             window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
};
</script>

<template>
    <Head :title="isPksTurunan ? 'Buat PKS Turunan' : 'Buat Permohonan'" />
    <AuthenticatedLayout>
        <div class="py-6">
            <div class="w-full px-4 sm:px-6 lg:px-8 space-y-4">
                
                <Breadcrumb :crumbs="[
                    { label: 'Dashboard', route: 'dashboard' },
                    { label: 'Daftar Permohonan', route: 'permohonan.index' },
                    { label: isPksTurunan ? 'Buat PKS Turunan' : 'Buat Baru', route: null }
                ]" />

                <!-- PKS Turunan Notice Banner -->
                <div v-if="isPksTurunan" class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl p-6 text-white shadow-md flex items-start gap-4">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center shrink-0">
                        <Icon icon="solar:link-circle-bold" class="w-7 h-7 text-white" />
                    </div>
                    <div>
                        <span class="inline-block px-2.5 py-0.5 bg-white/20 rounded-full text-xs font-semibold uppercase tracking-wider mb-1">
                            Perjanjian Kerja Sama (PKS) Turunan
                        </span>
                        <h3 class="text-xl font-bold">Turunan dari KB: {{ kbInduk?.label }}</h3>
                        <p class="text-xs text-blue-100 mt-1">
                            Nomor KB Induk: <strong class="text-white">{{ kbInduk?.nomor_permohonan || kbInduk?.kode || '-' }}</strong> · 
                            Instansi: <strong class="text-white">{{ kbInduk?.nama_instansi }}</strong>
                        </p>
                    </div>
                </div>

                <!-- Header -->
                <div v-else class="mb-4">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Buat Permohonan Baru</h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Isi formulir untuk mengajukan permohonan kerjasama (Kesepakatan Bersama / MoU).</p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 overflow-hidden">
                    <form @submit.prevent="submit(false)" class="space-y-6">
                        
                        <!-- Identitas & Instansi -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 border-b pb-2 flex items-center gap-2">
                                <Icon icon="solar:buildings-bold" class="w-5 h-5 text-blue-600" />
                                Identitas Lembaga & Instansi
                            </h3>
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
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Instansi / Mitra <span class="text-red-500">*</span></label>
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

                        <!-- Data Pimpinan & PIC 2 Orang (Proses Bisnis Perda 7/2020) -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 border-b pb-2 flex items-center gap-2">
                                <Icon icon="solar:user-id-bold" class="w-5 h-5 text-blue-600" />
                                Data Pimpinan & Narahubung (PIC)
                            </h3>
                            <div class="space-y-4">
                                <div class="p-4 bg-slate-50 dark:bg-slate-700/40 rounded-xl border border-slate-200 dark:border-slate-600 space-y-3">
                                    <p class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase flex items-center gap-2">
                                        <Icon icon="solar:crown-star-bold" class="w-4 h-4 text-amber-500" />
                                        Pimpinan Tertinggi Instansi / Lembaga
                                    </p>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Nama Pimpinan</label>
                                            <input v-model="form.nama_pimpinan" type="text" placeholder="Nama Lengkap & Gelar" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Jabatan</label>
                                            <input v-model="form.jabatan_pimpinan" type="text" placeholder="Rektor / Direktur / Ketua" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">NIP (Opsional)</label>
                                            <input v-model="form.nip_pimpinan" type="text" placeholder="NIP jika ASN" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- PIC 1 -->
                                    <div class="p-4 bg-blue-50/50 dark:bg-blue-900/10 rounded-xl border border-blue-200 dark:border-blue-800/40 space-y-3">
                                        <p class="text-xs font-bold text-blue-700 dark:text-blue-300 uppercase flex items-center gap-2">
                                            <Icon icon="solar:user-bold" class="w-4 h-4 text-blue-600" />
                                            PIC 1 (Penanggung Jawab Utama)
                                        </p>
                                        <div class="space-y-2">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Nama PIC 1</label>
                                                <input v-model="form.pic1_nama" type="text" placeholder="Nama Lengkap" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Jabatan</label>
                                                <input v-model="form.pic1_jabatan" type="text" placeholder="Jabatan PIC" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
                                            </div>
                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">No. WhatsApp</label>
                                                    <input v-model="form.pic1_phone" type="text" placeholder="08..." class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Email</label>
                                                    <input v-model="form.pic1_email" type="email" placeholder="email@instansi.id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- PIC 2 -->
                                    <div class="p-4 bg-purple-50/50 dark:bg-purple-900/10 rounded-xl border border-purple-200 dark:border-purple-800/40 space-y-3">
                                        <p class="text-xs font-bold text-purple-700 dark:text-purple-300 uppercase flex items-center gap-2">
                                            <Icon icon="solar:user-bold" class="w-4 h-4 text-purple-600" />
                                            PIC 2 (Pendamping / Teknis)
                                        </p>
                                        <div class="space-y-2">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Nama PIC 2</label>
                                                <input v-model="form.pic2_nama" type="text" placeholder="Nama Lengkap" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Jabatan</label>
                                                <input v-model="form.pic2_jabatan" type="text" placeholder="Jabatan PIC" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
                                            </div>
                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">No. WhatsApp</label>
                                                    <input v-model="form.pic2_phone" type="text" placeholder="08..." class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Email</label>
                                                    <input v-model="form.pic2_email" type="email" placeholder="email@instansi.id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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

                                <!-- Field OPD Multiple (Req 12) -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        OPD Terkait <span class="text-red-500">*</span>
                                    </label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                        Pilih satu atau lebih OPD yang terlibat dalam kerjasama ini
                                    </p>

                                    <div class="space-y-2">
                                        <select
                                            multiple
                                            v-model="form.opd_ids"
                                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white min-h-[120px]"
                                        >
                                            <option v-for="opd in opds" :key="opd.id" :value="opd.id">
                                                {{ opd.nama }}{{ opd.singkatan ? ` (${opd.singkatan})` : '' }}
                                            </option>
                                        </select>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Tahan Ctrl (Windows) atau Cmd (Mac) untuk pilih lebih dari satu
                                        </p>
                                    </div>

                                    <p v-if="form.errors.opd_ids" class="text-red-500 text-sm mt-1">{{ form.errors.opd_ids }}</p>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Mulai</label>
                                        <input v-model="form.tanggal_mulai" type="date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Berakhir</label>
                                        <input v-model="form.tanggal_berakhir" type="date" :min="form.tanggal_mulai || undefined" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                        <p v-if="form.tanggal_mulai && form.tanggal_berakhir && form.tanggal_berakhir < form.tanggal_mulai" class="text-red-500 text-sm mt-1">
                                            Tanggal berakhir tidak boleh lebih awal dari tanggal mulai.
                                        </p>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Jangka Waktu
                                        <span class="ml-2 text-xs font-normal text-blue-600 dark:text-blue-400">
                                            <Icon icon="solar:magic-wand-bold" class="inline w-3 h-3" />
                                            Otomatis dari tanggal
                                        </span>
                                    </label>
                                    <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-blue-50/50 dark:bg-gray-700/40 text-sm">
                                        <Icon icon="solar:clock-circle-bold-duotone" class="w-5 h-5 text-blue-600" />
                                        <span v-if="jangkaWaktuAuto" class="font-semibold text-gray-800 dark:text-white">{{ jangkaWaktuAuto }}</span>
                                        <span v-else class="italic text-gray-500 dark:text-gray-400">Pilih tanggal mulai dan berakhir terlebih dahulu</span>
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

                        <div class="flex items-center justify-end gap-3 pt-4 border-t dark:border-gray-700">
                             <Link 
                                :href="route('permohonan.index')" 
                                class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl transition dark:bg-gray-700 dark:text-gray-300"
                            >
                                Batal
                            </Link>

                            <button 
                                type="button" 
                                @click="submit(true)"
                                :disabled="form.processing"
                                class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition flex items-center gap-2 border border-slate-300 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
                            >
                                <Icon icon="solar:diskette-bold" class="w-4 h-4 text-slate-500" />
                                Simpan Draft
                            </button>

                            <button 
                                type="button" 
                                @click="submit(false)"
                                :disabled="form.processing"
                                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition flex items-center gap-2 shadow-sm"
                            >
                                <Icon v-if="form.processing" icon="solar:refresh-circle-line-duotone" class="w-4 h-4 animate-spin" />
                                <Icon v-else icon="solar:send-square-bold" class="w-4 h-4" />
                                Kirim Permohonan Resmi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
