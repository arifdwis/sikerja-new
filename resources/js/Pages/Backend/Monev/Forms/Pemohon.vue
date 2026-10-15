<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import { Icon } from '@iconify/vue';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import Calendar from 'primevue/calendar';
import FileUpload from 'primevue/fileupload';
import { useToast } from 'vue-toastification';

const props = defineProps({
    share: Object,
    permohonans: Array,
    selectedPermohonan: Object,
    pemohon: Object,
    submitterRole: String,
});

const toast = useToast();

const form = useForm({
    id_permohonan: props.selectedPermohonan?.select_id || props.selectedPermohonan?.id || null,
    tanggal_evaluasi: new Date(),
    pmh_progress_persen: 0,
    pmh_realisasi_kegiatan: null,
    pmh_kesesuaian_output: null,
    pmh_pemanfaatan_hasil: '',
    pmh_kendala_lapangan: '',
    pmh_keberlanjutan: null,
    pmh_file_laporan: null,
    pmh_file_berita_acara: null,
    pmh_file_dokumentasi: null,
});

const options = {
    pmh_realisasi_kegiatan: ['Terlaksana penuh', 'Sebagian', 'Tidak'],
    pmh_kesesuaian_output: ['Ya', 'Sebagian', 'Tidak'],
    pmh_keberlanjutan: ['Perlu dilanjutkan', 'Cukup', 'Hentikan'],
};

const handleFileSelect = (event) => {
    form.pmh_file_laporan = event.files?.[0] || null;
};

const handleBaSelect = (event) => {
    form.pmh_file_berita_acara = event.files?.[0] || null;
};

const handleDokSelect = (event) => {
    form.pmh_file_dokumentasi = event.files?.[0] || null;
};

const submit = () => {
    if (form.processing) return;
    const opts = {
        onSuccess: () => {
            toast.success('Form Monev berhasil dikirim.');
            form.reset();
        },
        onError: () => {
            toast.error('Gagal menyimpan. Periksa kembali isian form.');
        },
    };
    if (
        form.pmh_file_laporan instanceof File || 
        form.pmh_file_berita_acara instanceof File || 
        form.pmh_file_dokumentasi instanceof File
    ) {
        opts.forceFormData = true;
    }
    form.post(route('monev.store'), opts);
};
</script>

<template>
    <Head :title="share.title" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">{{ share.title }}</h2>
        </template>

        <section class="py-8">
            <div class="mx-auto max-w-4xl px-6 lg:px-8">
                <Breadcrumb class="mb-6" />

                <form @submit.prevent="submit" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-gray-800">
                    <div class="border-b border-slate-200 bg-slate-50 p-6 dark:border-slate-700 dark:bg-slate-800">
                        <div class="flex items-start gap-3">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-white text-slate-700 shadow-sm dark:bg-slate-700 dark:text-white">
                                <Icon icon="solar:user-check-bold-duotone" class="h-6 w-6" />
                            </span>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Form Monev Pemohon</h3>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">Isi pelaporan realisasi pelaksanaan kerja sama dari sisi pemohon.</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6 p-6">
                        <div class="grid gap-4 rounded-xl border border-slate-200 bg-slate-50/70 p-4 md:grid-cols-[minmax(0,1fr)_220px]">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Kerjasama <span class="text-red-500">*</span></label>
                                <Dropdown
                                    v-model="form.id_permohonan"
                                    :options="permohonans"
                                    optionLabel="label"
                                    :optionValue="(opt) => opt.select_id ?? opt.id"
                                    placeholder="Pilih kerjasama"
                                    class="w-full"
                                    :class="{ 'p-invalid': form.errors.id_permohonan }"
                                >
                                    <template #option="slotProps">
                                        <div class="flex items-center justify-between gap-2 w-full">
                                            <div class="min-w-0">
                                                <div class="text-sm font-medium text-slate-800 dark:text-slate-100 truncate">{{ slotProps.option.label }}</div>
                                                <div class="text-xs text-gray-500 truncate">{{ slotProps.option.nama_instansi }}</div>
                                            </div>
                                            <span v-if="slotProps.option.is_manual" class="shrink-0 rounded bg-sky-100 px-1.5 py-0.5 text-[10px] font-semibold text-sky-800">
                                                PKS Manual
                                            </span>
                                        </div>
                                    </template>
                                </Dropdown>
                                <small v-if="form.errors.id_permohonan" class="text-red-500">{{ form.errors.id_permohonan }}</small>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Evaluasi <span class="text-red-500">*</span></label>
                                <Calendar v-model="form.tanggal_evaluasi" dateFormat="dd/mm/yy" showIcon :manualInput="true" class="w-full" />
                            </div>
                        </div>

                        <div class="grid gap-4 rounded-xl border border-slate-200 p-4 md:grid-cols-[minmax(0,1fr)_280px] md:items-center">
                            <label class="text-sm text-slate-700">Apakah kegiatan kerjasama terlaksana sesuai rencana? <span class="text-red-500">*</span></label>
                            <div>
                                <Dropdown v-model="form.pmh_realisasi_kegiatan" :options="options.pmh_realisasi_kegiatan" placeholder="Pilih jawaban" class="w-full" :class="{ 'p-invalid': form.errors.pmh_realisasi_kegiatan }" />
                                <small v-if="form.errors.pmh_realisasi_kegiatan" class="text-red-500">{{ form.errors.pmh_realisasi_kegiatan }}</small>
                            </div>
                        </div>

                        <div class="grid gap-4 rounded-xl border border-slate-200 p-4 md:grid-cols-[minmax(0,1fr)_280px] md:items-center">
                            <label class="text-sm text-slate-700">Apakah output sesuai target perjanjian? <span class="text-red-500">*</span></label>
                            <div>
                                <Dropdown v-model="form.pmh_kesesuaian_output" :options="options.pmh_kesesuaian_output" placeholder="Pilih jawaban" class="w-full" :class="{ 'p-invalid': form.errors.pmh_kesesuaian_output }" />
                                <small v-if="form.errors.pmh_kesesuaian_output" class="text-red-500">{{ form.errors.pmh_kesesuaian_output }}</small>
                            </div>
                        </div>

                        <!-- Progress Capaian Persen (Proses Bisnis Perda 7/2020) -->
                        <div class="rounded-xl border border-blue-200 bg-blue-50/40 p-4 dark:border-blue-800 dark:bg-blue-900/10">
                            <div class="flex items-center justify-between gap-4 mb-2">
                                <label class="text-sm font-semibold text-blue-950 dark:text-blue-100 flex items-center gap-2">
                                    <Icon icon="solar:chart-bold" class="w-4 h-4 text-blue-600" />
                                    Progress Capaian Realisasi Kerja Sama (%)
                                </label>
                                <span class="text-base font-bold text-blue-600">{{ form.pmh_progress_persen }}%</span>
                            </div>
                            <input 
                                type="range" 
                                min="0" 
                                max="100" 
                                step="5" 
                                v-model.number="form.pmh_progress_persen" 
                                class="w-full h-2 bg-blue-200 rounded-lg appearance-none cursor-pointer dark:bg-blue-900" 
                            />
                            <div class="flex justify-between text-[11px] text-gray-500 mt-1">
                                <span>0% (Awal)</span>
                                <span>50% (Berjalan)</span>
                                <span>100% (Tuntas)</span>
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-200 p-4">
                            <label class="mb-2 block text-sm font-medium text-slate-700">Bagaimana hasil kerjasama dimanfaatkan?</label>
                            <Textarea v-model="form.pmh_pemanfaatan_hasil" rows="3" class="w-full" placeholder="Jelaskan pemanfaatan hasil..." />
                            <small v-if="form.errors.pmh_pemanfaatan_hasil" class="text-red-500">{{ form.errors.pmh_pemanfaatan_hasil }}</small>
                        </div>

                        <div class="rounded-xl border border-slate-200 p-4">
                            <label class="mb-2 block text-sm font-medium text-slate-700">Kendala selama pelaksanaan</label>
                            <Textarea v-model="form.pmh_kendala_lapangan" rows="3" class="w-full" placeholder="Jelaskan kendala lapangan..." />
                            <small v-if="form.errors.pmh_kendala_lapangan" class="text-red-500">{{ form.errors.pmh_kendala_lapangan }}</small>
                        </div>

                        <div class="grid gap-4 rounded-xl border border-slate-200 p-4 md:grid-cols-[minmax(0,1fr)_280px] md:items-center">
                            <label class="text-sm text-slate-700">Usulan keberlanjutan kerja sama <span class="text-red-500">*</span></label>
                            <div>
                                <Dropdown v-model="form.pmh_keberlanjutan" :options="options.pmh_keberlanjutan" placeholder="Pilih jawaban" class="w-full" :class="{ 'p-invalid': form.errors.pmh_keberlanjutan }" />
                                <small v-if="form.errors.pmh_keberlanjutan" class="text-red-500">{{ form.errors.pmh_keberlanjutan }}</small>
                            </div>
                        </div>

                        <!-- Upload Berkas Monev (Laporan, Berita Acara, Dokumentasi) -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Laporan -->
                            <div class="space-y-2 rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Laporan Pelaksanaan</label>
                                <FileUpload mode="basic" accept=".pdf,.jpg,.jpeg,.png" :maxFileSize="10000000" @select="handleFileSelect" chooseLabel="Pilih File" class="w-full" />
                                <small class="text-[11px] text-gray-500">PDF, Maks 10MB.</small>
                                <small v-if="form.errors.pmh_file_laporan" class="text-red-500 block">{{ form.errors.pmh_file_laporan }}</small>
                            </div>

                            <!-- Berita Acara -->
                            <div class="space-y-2 rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Berita Acara Monev</label>
                                <FileUpload mode="basic" accept=".pdf" :maxFileSize="10000000" @select="handleBaSelect" chooseLabel="Pilih Berita Acara" class="w-full" />
                                <small class="text-[11px] text-gray-500">PDF, Maks 10MB.</small>
                                <small v-if="form.errors.pmh_file_berita_acara" class="text-red-500 block">{{ form.errors.pmh_file_berita_acara }}</small>
                            </div>

                            <!-- Dokumentasi -->
                            <div class="space-y-2 rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Foto & Dokumentasi</label>
                                <FileUpload mode="basic" accept=".pdf,.jpg,.jpeg,.png,.zip" :maxFileSize="20000000" @select="handleDokSelect" chooseLabel="Pilih Dokumentasi" class="w-full" />
                                <small class="text-[11px] text-gray-500">JPG/PNG/ZIP, Maks 20MB.</small>
                                <small v-if="form.errors.pmh_file_dokumentasi" class="text-red-500 block">{{ form.errors.pmh_file_dokumentasi }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 border-t border-slate-200 bg-slate-50 p-6 dark:border-slate-700 dark:bg-gray-700/50">
                        <Link :href="route('monev.index')">
                            <Button label="Batal" severity="secondary" text />
                        </Link>
                        <Button type="submit" label="Submit Evaluasi" icon="pi pi-check" :loading="form.processing" />
                    </div>
                </form>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
