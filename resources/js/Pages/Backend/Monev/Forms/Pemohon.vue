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
    id_permohonan: props.selectedPermohonan?.id || null,
    tanggal_evaluasi: new Date(),
    pmh_realisasi_kegiatan: null,
    pmh_kesesuaian_output: null,
    pmh_pemanfaatan_hasil: '',
    pmh_kendala_lapangan: '',
    pmh_keberlanjutan: null,
    pmh_file_laporan: null,
});

const options = {
    pmh_realisasi_kegiatan: ['Terlaksana penuh', 'Sebagian', 'Tidak'],
    pmh_kesesuaian_output: ['Ya', 'Sebagian', 'Tidak'],
    pmh_keberlanjutan: ['Perlu dilanjutkan', 'Cukup', 'Hentikan'],
};

const handleFileSelect = (event) => {
    form.pmh_file_laporan = event.files?.[0] || null;
};

const submit = () => {
    if (form.processing) return;
    // forceFormData hanya kalau ada file. Kalau dipakai saat field file null,
    // Inertia kirim "null" sebagai string dan rule `nullable|file` reject.
    const opts = {
        onSuccess: () => {
            toast.success('Form Monev berhasil dikirim.');
            form.reset();
        },
        onError: () => {
            toast.error('Gagal menyimpan. Periksa kembali isian form.');
        },
    };
    if (form.pmh_file_laporan instanceof File) {
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
                                    optionValue="id"
                                    placeholder="Pilih kerjasama"
                                    class="w-full"
                                    :class="{ 'p-invalid': form.errors.id_permohonan }"
                                />
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

                        <div class="rounded-xl border border-slate-200 p-4">
                            <label class="mb-2 block text-sm font-medium text-slate-700">Bagaimana hasil kerjasama dimanfaatkan?</label>
                            <Textarea v-model="form.pmh_pemanfaatan_hasil" rows="4" class="w-full" placeholder="Jelaskan pemanfaatan hasil..." />
                            <small v-if="form.errors.pmh_pemanfaatan_hasil" class="text-red-500">{{ form.errors.pmh_pemanfaatan_hasil }}</small>
                        </div>

                        <div class="rounded-xl border border-slate-200 p-4">
                            <label class="mb-2 block text-sm font-medium text-slate-700">Kendala selama pelaksanaan</label>
                            <Textarea v-model="form.pmh_kendala_lapangan" rows="4" class="w-full" placeholder="Jelaskan kendala lapangan..." />
                            <small v-if="form.errors.pmh_kendala_lapangan" class="text-red-500">{{ form.errors.pmh_kendala_lapangan }}</small>
                        </div>

                        <div class="grid gap-4 rounded-xl border border-slate-200 p-4 md:grid-cols-[minmax(0,1fr)_280px] md:items-center">
                            <label class="text-sm text-slate-700">Usulan keberlanjutan kerja sama <span class="text-red-500">*</span></label>
                            <div>
                                <Dropdown v-model="form.pmh_keberlanjutan" :options="options.pmh_keberlanjutan" placeholder="Pilih jawaban" class="w-full" :class="{ 'p-invalid': form.errors.pmh_keberlanjutan }" />
                                <small v-if="form.errors.pmh_keberlanjutan" class="text-red-500">{{ form.errors.pmh_keberlanjutan }}</small>
                            </div>
                        </div>

                        <div class="space-y-2 rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Upload Laporan Pelaksanaan (opsional)</label>
                            <FileUpload mode="basic" accept=".pdf,.jpg,.jpeg,.png" :maxFileSize="5000000" @select="handleFileSelect" chooseLabel="Pilih File" class="w-full" />
                            <small class="text-gray-500">Format: PDF, JPG, PNG. Maks 5MB.</small>
                            <small v-if="form.errors.pmh_file_laporan" class="text-red-500">{{ form.errors.pmh_file_laporan }}</small>
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
