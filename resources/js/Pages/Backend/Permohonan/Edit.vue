<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { Icon } from '@iconify/vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import { useToast } from "vue-toastification";
import { computeJangkaWaktu } from '@/utils/jangkaWaktu';

const toast = useToast();

const props = defineProps({
    permohonan: Object,
    kategoris: Array,
    provinsis: Array,
    kotas: Array,            // Initial loading based on existing provinsi
    opds: Array,             // Daftar OPD aktif
    selectedOpdIds: Array,   // OPD yang sudah dipilih sebelumnya
    pemohonanList: Array,    // For PPKSD-2 auto-fill
    share: Object,
});

const form = useForm({
    // Step 1 (read-only di edit) — Identitas instansi pemohon
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

    // Step 2 — PPKSD-2 (Pihak Kedua)
    id_pemohon_1: props.permohonan.id_pemohon_1 || '',

    // Step 3 — Detail kerjasama (editable)
    latar_belakang: props.permohonan.latar_belakang || '',
    maksud_tujuan: props.permohonan.maksud_tujuan || '',
    lokasi_kerjasama: props.permohonan.lokasi_kerjasama || '',
    ruang_lingkup: props.permohonan.ruang_lingkup || '',
    opd_ids: props.selectedOpdIds || [],
    jangka_waktu: props.permohonan.jangka_waktu || '',
    tanggal_mulai: props.permohonan.tanggal_mulai || '',
    tanggal_berakhir: props.permohonan.tanggal_berakhir || '',

    // Step 4 — Manfaat & dampak
    manfaat: props.permohonan.manfaat || '',
    analisis_dampak: props.permohonan.analisis_dampak || '',
    pembiayaan: props.permohonan.pembiayaan || '',
});

const kotas = ref(props.kotas || []);
const loadingKotas = ref(false);

// Resolve kategori dan provinsi label untuk display
const kategoriName = (props.kategoris || []).find(k => k.id == props.permohonan.id_kategori)?.label || '-';
const provinsiName = (props.provinsis || []).find(p => p.id == props.permohonan.id_provinsi)?.name || '-';
const kotaName = (props.kotas || []).find(k => k.id == props.permohonan.id_kota)?.name || '-';

// PPKSD-2 selection helper
const selectedPpksd2 = ref(
    (props.pemohonanList || []).find(p => p.id == props.permohonan.id_pemohon_1) || null
);
watch(selectedPpksd2, (val) => {
    form.id_pemohon_1 = val?.id || '';
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

const submit = () => {
    if (form.tanggal_mulai && form.tanggal_berakhir && form.tanggal_berakhir < form.tanggal_mulai) {
        toast.error('Tanggal berakhir tidak boleh lebih awal dari tanggal mulai.');
        return;
    }

    form.put(route('permohonan.update', props.permohonan.uuid), {
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
                    { label: 'Edit', route: null }
                ]" />

                <!-- Header -->
                <div class="mb-4 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Edit / Revisi Permohonan</h2>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">
                            Perbarui data permohonan kerjasama. Step 1 (Identitas Instansi) tidak dapat diubah.
                        </p>
                    </div>
                    <span class="text-xs font-mono px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">
                        {{ permohonan.nomor_permohonan || permohonan.kode }}
                    </span>
                </div>

                <!-- Banner alasan tolak (jika status 9) -->
                <div v-if="permohonan.status == 9 && permohonan.alasan_tolak"
                    class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4 flex items-start gap-3">
                    <Icon icon="solar:danger-triangle-bold" class="w-6 h-6 text-red-600 flex-shrink-0" />
                    <div>
                        <p class="font-bold text-red-800">Permohonan Sebelumnya Ditolak</p>
                        <p class="text-sm text-red-700 mt-1">
                            <span class="font-semibold">Alasan:</span> {{ permohonan.alasan_tolak }}
                        </p>
                        <p class="text-xs text-red-600 mt-1">
                            Silakan revisi data di bawah ini sesuai catatan, lalu klik Simpan untuk mengajukan ulang.
                        </p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-4">

                    <!-- ==================== STEP 1 (READ-ONLY) ==================== -->
                    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-2">
                                <span class="w-7 h-7 rounded-full bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold text-sm flex items-center justify-center">1</span>
                                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Identitas Instansi Pemohon</h3>
                            </div>
                            <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-1 bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded">
                                <Icon icon="solar:lock-bold" class="w-3 h-3" />
                                Tidak Dapat Diubah
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div class="md:col-span-2">
                                <span class="text-xs text-gray-500 uppercase tracking-wide">Judul Kerjasama</span>
                                <p class="font-medium text-gray-900 dark:text-white mt-0.5">{{ permohonan.label }}</p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500 uppercase tracking-wide">Kategori</span>
                                <p class="font-medium text-gray-900 dark:text-white mt-0.5">{{ kategoriName }}</p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500 uppercase tracking-wide">Nama Instansi</span>
                                <p class="font-medium text-gray-900 dark:text-white mt-0.5">{{ permohonan.nama_instansi }}</p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500 uppercase tracking-wide">Provinsi</span>
                                <p class="font-medium text-gray-900 dark:text-white mt-0.5">{{ provinsiName }}</p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500 uppercase tracking-wide">Kota/Kabupaten</span>
                                <p class="font-medium text-gray-900 dark:text-white mt-0.5">{{ kotaName }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <span class="text-xs text-gray-500 uppercase tracking-wide">Alamat</span>
                                <p class="font-medium text-gray-900 dark:text-white mt-0.5">{{ permohonan.alamat || '-' }}</p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500 uppercase tracking-wide">Telepon</span>
                                <p class="font-medium text-gray-900 dark:text-white mt-0.5">{{ permohonan.telepon || '-' }}</p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500 uppercase tracking-wide">Email</span>
                                <p class="font-medium text-gray-900 dark:text-white mt-0.5">{{ permohonan.email || '-' }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <span class="text-xs text-gray-500 uppercase tracking-wide">Website</span>
                                <p class="font-medium text-gray-900 dark:text-white mt-0.5">{{ permohonan.website || '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== STEP 2: PIHAK KEDUA (PPKSD-2) ==================== -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-2 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                            <span class="w-7 h-7 rounded-full bg-blue-600 text-white font-bold text-sm flex items-center justify-center">2</span>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pihak Kedua (PPKSD-2)</h3>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Pejabat Penandatangan Pihak Kedua
                            </label>
                            <select v-model="form.id_pemohon_1" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <option value="">— Pilih Pejabat —</option>
                                <option v-for="p in pemohonanList" :key="p.id" :value="p.id">
                                    {{ p.name }} — {{ p.jabatan || '-' }} ({{ p.unit_kerja || '-' }})
                                </option>
                            </select>
                            <p v-if="form.errors.id_pemohon_1" class="text-red-500 text-sm mt-1">{{ form.errors.id_pemohon_1 }}</p>
                            <p class="text-xs text-gray-500 mt-1">Pilih pejabat dari daftar yang akan menandatangani sebagai pihak kedua.</p>
                        </div>
                    </div>

                    <!-- ==================== STEP 3: DETAIL KERJASAMA ==================== -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-2 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                            <span class="w-7 h-7 rounded-full bg-blue-600 text-white font-bold text-sm flex items-center justify-center">3</span>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Detail Kerjasama</h3>
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

                            <!-- OPD Multiple -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">OPD Terkait</label>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Pilih OPD yang terlibat dalam kerjasama ini</p>
                                <select multiple v-model="form.opd_ids" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white min-h-[120px]">
                                    <option v-for="opd in opds" :key="opd.id" :value="opd.id">
                                        {{ opd.nama }}{{ opd.singkatan ? ` (${opd.singkatan})` : '' }}
                                    </option>
                                </select>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Tahan Ctrl/Cmd untuk pilih lebih dari satu</p>
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
                                        <Icon icon="solar:magic-stick-3-bold" class="inline w-3 h-3" />
                                        Otomatis dari tanggal
                                    </span>
                                </label>
                                <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-blue-50/50 dark:bg-gray-700/40 text-sm">
                                    <Icon icon="solar:clock-circle-bold-duotone" class="w-5 h-5 text-blue-600" />
                                    <span v-if="jangkaWaktuAuto" class="font-semibold text-gray-800 dark:text-white">{{ jangkaWaktuAuto }}</span>
                                    <span v-else class="italic text-gray-500 dark:text-gray-400">Pilih tanggal mulai dan berakhir terlebih dahulu</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== STEP 4: MANFAAT & PEMBIAYAAN ==================== -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-2 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                            <span class="w-7 h-7 rounded-full bg-blue-600 text-white font-bold text-sm flex items-center justify-center">4</span>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Manfaat, Dampak & Pembiayaan</h3>
                        </div>
                        <div class="space-y-4">
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

                    <!-- Action -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 flex justify-end gap-3">
                        <Link :href="route('permohonan.show', permohonan.uuid)" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition">
                            Batal
                        </Link>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center disabled:bg-gray-400">
                            <Icon v-if="form.processing" icon="svg-spinners:90-ring-with-bg" class="w-5 h-5 mr-2" />
                            <span>{{ permohonan.status == 9 ? 'Simpan & Ajukan Ulang' : 'Simpan Perubahan' }}</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
