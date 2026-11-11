<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { Icon } from '@iconify/vue';
import MultiSelect from 'primevue/multiselect';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import { useToast } from "vue-toastification";
import { computeJangkaWaktu } from '@/utils/jangkaWaktu';

const toast = useToast();

const props = defineProps({
    permohonan: Object,
    kategoris: Array,
    provinsis: Array,
    kotas: Array,
    opds: Array,
    selectedOpdIds: Array,
    pemohonanList: Array,
    share: Object,
});

const opdOptions = computed(() => (props.opds || []).map(o => ({
    id: o.id,
    label: o.singkatan ? `${o.nama} (${o.singkatan})` : o.nama,
})));

const form = useForm({
    _method: 'put',
    is_draft: false,

    // Step 1 — Identitas instansi & kontak
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

    // Data Pimpinan & 2 PIC
    nama_pimpinan: props.permohonan.nama_pimpinan || '',
    jabatan_pimpinan: props.permohonan.jabatan_pimpinan || '',
    nip_pimpinan: props.permohonan.nip_pimpinan || '',
    pic1_nama: props.permohonan.pic1_nama || '',
    pic1_jabatan: props.permohonan.pic1_jabatan || '',
    pic1_phone: props.permohonan.pic1_phone || '',
    pic1_email: props.permohonan.pic1_email || '',
    pic2_nama: props.permohonan.pic2_nama || '',
    pic2_jabatan: props.permohonan.pic2_jabatan || '',
    pic2_phone: props.permohonan.pic2_phone || '',
    pic2_email: props.permohonan.pic2_email || '',

    id_pemohon_1: props.permohonan.id_pemohon_1 || '',

    // Detail kerjasama
    latar_belakang: props.permohonan.latar_belakang || '',
    maksud_tujuan: props.permohonan.maksud_tujuan || '',
    lokasi_kerjasama: props.permohonan.lokasi_kerjasama || '',
    ruang_lingkup: props.permohonan.ruang_lingkup || '',
    opd_ids: props.selectedOpdIds || [],
    jangka_waktu: props.permohonan.jangka_waktu || '',
    tanggal_mulai: props.permohonan.tanggal_mulai ? String(props.permohonan.tanggal_mulai).substring(0, 10) : '',
    tanggal_berakhir: props.permohonan.tanggal_berakhir ? String(props.permohonan.tanggal_berakhir).substring(0, 10) : '',

    // Manfaat & dampak
    manfaat: props.permohonan.manfaat || '',
    analisis_dampak: props.permohonan.analisis_dampak || '',
    pembiayaan: props.permohonan.pembiayaan || '',

    // Berkas kelengkapan
    surat_permohonan: null,
    proposal_kak: null,
    draft_mou: null,
    dokumen_legalitas: null,
    dokumen_pendukung: null,
});

const kotas = ref(props.kotas || []);
const loadingKotas = ref(false);

watch(() => form.id_provinsi, async (provinsiId, oldProvinsiId) => {
    if (provinsiId && provinsiId !== oldProvinsiId) {
        loadingKotas.value = true;
        try {
            const response = await fetch(route('api.kotas', provinsiId));
            kotas.value = await response.json();
            form.id_kota = '';
        } catch (e) {
            console.error(e);
        }
        loadingKotas.value = false;
    }
});

const jangkaWaktuAuto = computed(() =>
    computeJangkaWaktu(form.tanggal_mulai, form.tanggal_berakhir)
);
watch([
    () => form.tanggal_mulai,
    () => form.tanggal_berakhir,
], () => {
    form.jangka_waktu = jangkaWaktuAuto.value;
}, { immediate: true });

const docFields = [
    { key: 'surat_permohonan', label: 'Surat Permohonan Kerjasama', format: 'PDF', accept: '.pdf,application/pdf', required: true },
    { key: 'proposal_kak', label: 'Proposal / Kerangka Acuan Kerja (KAK)', format: 'PDF', accept: '.pdf,application/pdf', required: true },
    { key: 'draft_mou', label: 'Draft Kesepakatan Bersama (KB) / PKS', format: 'DOC / DOCX', accept: '.doc,.docx', required: true },
    { key: 'dokumen_legalitas', label: 'Dokumen Legalitas Instansi (Akta/SK/NPWP)', format: 'PDF / ZIP', accept: '.pdf,.zip', required: false },
    { key: 'dokumen_pendukung', label: 'Dokumen Pendukung Lainnya', format: 'PDF / ZIP', accept: '.pdf,.zip', required: false },
];

const getExistingDoc = (key) => {
    return (props.permohonan?.files || []).find(f => f.kategori_dokumen === key);
};

const onFileChange = (event, doc) => {
    const file = event.target?.files?.[0] || null;
    if (!file) {
        form[doc.key] = null;
        return;
    }
    if (file.size > 15 * 1024 * 1024) {
        toast.error(`Ukuran berkas ${file.name} melebihi batas 15 MB.`);
        event.target.value = '';
        return;
    }
    form[doc.key] = file;
};

const submit = (asDraft = false) => {
    form.is_draft = asDraft;

    if (!asDraft && form.tanggal_mulai && form.tanggal_berakhir && form.tanggal_berakhir < form.tanggal_mulai) {
        toast.error('Tanggal berakhir tidak boleh lebih awal dari tanggal mulai.');
        return;
    }

    form.post(route('permohonan.update', props.permohonan.uuid), {
        forceFormData: true,
        onError: (errors) => {
            const firstError = Object.values(errors)[0];
            toast.error(firstError || "Mohon lengkapi semua field yang wajib diisi (bertanda *).");
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
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
                    { label: 'Edit / Revisi', route: null }
                ]" />

                <!-- Header -->
                <div class="mb-4 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Edit / Revisi Permohonan</h2>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">
                            Perbarui data instansi, pimpinan, PIC, substansi kerja sama, maupun berkas kelengkapan.
                        </p>
                    </div>
                    <span class="text-xs font-mono px-2.5 py-1 bg-gray-100 dark:bg-gray-700 rounded font-bold">
                        {{ permohonan.nomor_permohonan || permohonan.kode }}
                    </span>
                </div>

                <!-- Banner alasan tolak / revisi (status 9 atau 11) -->
                <div v-if="[9, 11].includes(Number(permohonan.status)) && permohonan.alasan_tolak"
                    class="bg-amber-50 border-l-4 border-amber-500 rounded-lg p-4 flex items-start gap-3">
                    <Icon icon="solar:danger-triangle-bold" class="w-6 h-6 text-amber-600 flex-shrink-0" />
                    <div>
                        <p class="font-bold text-amber-900">
                            {{ Number(permohonan.status) === 11 ? 'Permohonan Perlu Perbaikan / Revisi' : 'Permohonan Sebelumnya Ditolak' }}
                        </p>
                        <p class="text-sm text-amber-800 mt-1">
                            <span class="font-semibold">Catatan:</span> {{ permohonan.alasan_tolak }}
                        </p>
                        <p class="text-xs text-amber-700 mt-1">
                            Silakan perbaiki data dan unggah ulang berkas yang diminta di bawah ini, lalu klik Simpan & Ajukan Ulang.
                        </p>
                    </div>
                </div>

                <form @submit.prevent="submit(false)" class="space-y-6">

                    <!-- ==================== BAGIAN 1: IDENTITAS INSTANSI & PIMPINAN/PIC ==================== -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700 space-y-6">
                        <div class="flex items-center gap-2 pb-2 border-b border-gray-200 dark:border-gray-700">
                            <span class="w-7 h-7 rounded-full bg-blue-600 text-white font-bold text-sm flex items-center justify-center">1</span>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Identitas Instansi, Pimpinan & PIC</h3>
                        </div>

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
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Provinsi</label>
                                <select v-model="form.id_provinsi" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <option value="">-- Pilih --</option>
                                    <option v-for="p in provinsis" :key="p.id" :value="p.id">{{ p.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kota/Kabupaten</label>
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

                        <!-- Pimpinan & 2 PIC -->
                        <div class="pt-4 border-t border-gray-200 dark:border-gray-700 space-y-4">
                            <div class="p-4 bg-slate-50 dark:bg-slate-700/40 rounded-xl border border-slate-200 dark:border-slate-600 space-y-3">
                                <p class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase flex items-center gap-2">
                                    <Icon icon="solar:crown-star-bold" class="w-4 h-4 text-amber-500" />
                                    Pimpinan Tertinggi Instansi / Mitra
                                </p>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Nama Pimpinan</label>
                                        <input v-model="form.nama_pimpinan" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Jabatan</label>
                                        <input v-model="form.jabatan_pimpinan" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">NIP / NIK</label>
                                        <input v-model="form.nip_pimpinan" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- PIC 1 -->
                                <div class="p-4 bg-blue-50/50 dark:bg-blue-900/10 rounded-xl border border-blue-200 dark:border-blue-800/40 space-y-2">
                                    <p class="text-xs font-bold text-blue-700 dark:text-blue-300 uppercase">PIC 1 (Penanggung Jawab Utama)</p>
                                    <input v-model="form.pic1_nama" type="text" placeholder="Nama PIC 1" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
                                    <input v-model="form.pic1_jabatan" type="text" placeholder="Jabatan PIC 1" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
                                    <div class="grid grid-cols-2 gap-2">
                                        <input v-model="form.pic1_phone" type="text" placeholder="No. WA PIC 1" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
                                        <input v-model="form.pic1_email" type="email" placeholder="Email PIC 1" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
                                    </div>
                                </div>

                                <!-- PIC 2 -->
                                <div class="p-4 bg-purple-50/50 dark:bg-purple-900/10 rounded-xl border border-purple-200 dark:border-purple-800/40 space-y-2">
                                    <p class="text-xs font-bold text-purple-700 dark:text-purple-300 uppercase">PIC 2 (Pendamping Teknis)</p>
                                    <input v-model="form.pic2_nama" type="text" placeholder="Nama PIC 2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
                                    <input v-model="form.pic2_jabatan" type="text" placeholder="Jabatan PIC 2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
                                    <div class="grid grid-cols-2 gap-2">
                                        <input v-model="form.pic2_phone" type="text" placeholder="No. WA PIC 2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
                                        <input v-model="form.pic2_email" type="email" placeholder="Email PIC 2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== BAGIAN 2: SUBSTANSI & OPD TERKAIT ==================== -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-2 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                            <span class="w-7 h-7 rounded-full bg-blue-600 text-white font-bold text-sm flex items-center justify-center">2</span>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Substansi & Detail Kerjasama</h3>
                        </div>

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
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lokasi Kerjasama</label>
                                <textarea v-model="form.lokasi_kerjasama" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ruang Lingkup <span class="text-red-500">*</span></label>
                                <textarea v-model="form.ruang_lingkup" rows="4" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                                <p v-if="form.errors.ruang_lingkup" class="text-red-500 text-sm mt-1">{{ form.errors.ruang_lingkup }}</p>
                            </div>

                            <!-- OPD Multiple dengan PrimeVue MultiSelect -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">OPD Terkait</label>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Pilih satu atau lebih OPD Pemerintah Kota Samarinda yang terlibat</p>
                                <MultiSelect
                                    v-model="form.opd_ids"
                                    :options="opdOptions"
                                    option-label="label"
                                    option-value="id"
                                    placeholder="Cari dan pilih OPD terkait..."
                                    display="chip"
                                    filter
                                    :max-selected-labels="3"
                                    class="w-full"
                                />
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

                    <!-- ==================== BAGIAN 3: LAMPIRAN DOKUMEN ==================== -->
                    <div class="bg-blue-50/50 dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-blue-200 dark:border-gray-700">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-7 h-7 rounded-full bg-blue-600 text-white font-bold text-sm flex items-center justify-center">3</span>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Lampiran Berkas Kelengkapan</h3>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                            Unggah berkas baru atau ganti berkas yang diminta perbaikan.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div
                                v-for="doc in docFields"
                                :key="doc.key"
                                class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-700/50 p-4 space-y-2"
                            >
                                <div class="flex items-center justify-between gap-2">
                                    <label class="text-xs font-semibold text-gray-800 dark:text-gray-100">
                                        {{ doc.label }}
                                    </label>
                                    <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-gray-600 text-[10px] font-bold text-slate-600 dark:text-gray-200">
                                        {{ doc.format }}
                                    </span>
                                </div>

                                <p v-if="getExistingDoc(doc.key)" class="text-xs text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                                    <Icon icon="solar:check-circle-bold" class="w-3.5 h-3.5 shrink-0" />
                                    <span class="truncate">Saat ini: {{ getExistingDoc(doc.key).file_name || getExistingDoc(doc.key).label }}</span>
                                </p>

                                <input
                                    type="file"
                                    :accept="doc.accept"
                                    @change="(e) => onFileChange(e, doc)"
                                    class="block w-full text-xs text-gray-600 dark:text-gray-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                />
                                <p v-if="form.errors?.[doc.key]" class="text-red-500 text-xs">{{ form.errors[doc.key] }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Action -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 flex justify-end gap-3">
                        <Link :href="route('permohonan.index')" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition">
                            Batal
                        </Link>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition flex items-center disabled:bg-gray-400">
                            <Icon v-if="form.processing" icon="svg-spinners:90-ring-with-bg" class="w-5 h-5 mr-2" />
                            <span>{{ [9, 11].includes(Number(permohonan.status)) ? 'Simpan & Ajukan Ulang' : 'Simpan Perubahan' }}</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
