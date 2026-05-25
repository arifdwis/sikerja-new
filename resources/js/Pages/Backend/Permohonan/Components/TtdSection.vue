<script setup>
/**
 * TtdSection - Upload Dokumen Tertandatangani + Checklist (Req 9)
 * Pemohon: upload dokumen TTD + checklist paraf, materai, stempel
 * Admin: validasi checklist + upload dokumen versi admin
 */
import { computed, ref } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import { useToast } from 'vue-toastification';
import Button from 'primevue/button';
import FileUpload from 'primevue/fileupload';
import Checkbox from 'primevue/checkbox';
import RadioButton from 'primevue/radiobutton';
import Textarea from 'primevue/textarea';

const props = defineProps({
    permohonan: Object,
});

const emit = defineEmits(['refresh', 'uploaded']);
const toast = useToast();

const page = usePage();
const userRole = computed(() => page.props.auth?.role);
const isPemohon = computed(() => userRole.value === 'pemohon');
const isAdmin = computed(() => ['administrator', 'superadmin'].includes(userRole.value));

const STATUS_MENUNGGU_TANDATANGAN = 4;
const STATUS_PASCA_TANDATANGAN = 5;

const status = computed(() => Number(props.permohonan?.status));
const ttdFiles = computed(() => props.permohonan?.ttd_files || []);
const ttdPemohon = computed(() => ttdFiles.value.find(f => f.tipe === 'pemohon'));
const ttdAdmin = computed(() => ttdFiles.value.find(f => f.tipe === 'admin'));

const showPemohonUpload = computed(() => isPemohon.value && status.value === STATUS_MENUNGGU_TANDATANGAN && !ttdPemohon.value);
const showAdminValidate = computed(() => isAdmin.value && status.value === STATUS_PASCA_TANDATANGAN && ttdPemohon.value);
const canApproveWithAdminFile = computed(() => Boolean(formAdmin.admin_file || ttdAdmin.value));

const formPemohon = useForm({
    file: null,
    checklist_paraf: false,
    checklist_materai: false,
    checklist_stempel: false,
});

const formAdmin = useForm({
    ttd_id: null,
    is_valid: true,
    catatan_admin: '',
    admin_file: null,
});

const submitTtd = () => {
    formPemohon.post(route('permohonan.ttd.store', props.permohonan.uuid), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Dokumen tertandatangani berhasil diupload. Menunggu validasi admin.');
            formPemohon.reset();
            emit('uploaded');
        },
        onError: () => toast.error('Gagal mengupload dokumen tertandatangani.'),
    });
};

const submitValidate = () => {
    formAdmin.ttd_id = ttdPemohon.value.id;
    formAdmin.put(route('permohonan.ttd.validate', props.permohonan.uuid), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Validasi dokumen tertandatangani berhasil disimpan.');
            if (formAdmin.is_valid) {
                approvePks();
                return;
            }
            emit('refresh');
        },
        onError: () => toast.error('Gagal menyimpan validasi dokumen.'),
    });
};

const approvePks = () => {
    router.put(route('permohonan.pks.approve', props.permohonan.uuid), {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('PKS final disetujui. Kerjasama memasuki tahap Pelaksanaan.');
            emit('refresh');
        },
        onError: () => toast.error('Gagal menyetujui PKS final.'),
    });
};

const pemohonTtdUpload = ref(null);
const adminTtdUpload = ref(null);

const allChecked = computed(() =>
    formPemohon.checklist_paraf && formPemohon.checklist_materai && formPemohon.checklist_stempel
);

const selectPemohonTtd = (event) => {
    formPemohon.file = event.files?.[0] || null;
};

const resetPemohonTtdFile = () => {
    formPemohon.file = null;
};

const selectAdminTtd = (event) => {
    formAdmin.admin_file = event.files?.[0] || null;
};

const resetAdminTtdFile = () => {
    formAdmin.admin_file = null;
};

</script>

<template>
    <div v-if="status >= STATUS_MENUNGGU_TANDATANGAN" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <Icon icon="solar:document-text-bold" class="w-5 h-5 text-slate-600" />
                Dokumen Tertandatangani
            </h3>
        </div>
        <div class="p-5 space-y-4">
            <!-- TTD Pemohon (sudah upload) -->
            <div v-if="ttdPemohon" class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <Icon icon="solar:file-text-bold" class="w-8 h-8 text-slate-600" />
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ ttdPemohon.file_name }}</p>
                            <p class="text-xs text-gray-500">Diupload pemohon</p>
                        </div>
                    </div>
                    <a :href="ttdPemohon.file_url" target="_blank" class="text-sm text-slate-600 hover:text-slate-900 hover:underline">Lihat</a>
                </div>
                <div class="flex flex-wrap gap-2 text-xs">
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded" :class="ttdPemohon.checklist_paraf ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                        <Icon :icon="ttdPemohon.checklist_paraf ? 'lucide:check' : 'lucide:x'" class="h-3.5 w-3.5 shrink-0" />
                        Paraf Basah
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded" :class="ttdPemohon.checklist_materai ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                        <Icon :icon="ttdPemohon.checklist_materai ? 'lucide:check' : 'lucide:x'" class="h-3.5 w-3.5 shrink-0" />
                        Materai
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded" :class="ttdPemohon.checklist_stempel ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                        <Icon :icon="ttdPemohon.checklist_stempel ? 'lucide:check' : 'lucide:x'" class="h-3.5 w-3.5 shrink-0" />
                        Stempel
                    </span>
                    <span v-if="ttdPemohon.is_validated" class="inline-flex items-center gap-1 px-2 py-1 rounded bg-emerald-100 text-emerald-700">
                        <Icon icon="solar:verified-check-bold" class="inline w-3 h-3" />
                        Tervalidasi Admin
                    </span>
                    <span v-else class="inline-flex items-center gap-1 px-2 py-1 rounded bg-amber-100 text-amber-700">
                        <Icon icon="solar:clock-circle-bold" class="inline w-3 h-3" />
                        Menunggu Validasi Admin
                    </span>
                </div>
            </div>

            <!-- Banner: dokumen menunggu validasi admin (pemohon, status 5, belum tervalidasi) -->
            <div
                v-if="isPemohon && status === STATUS_PASCA_TANDATANGAN && ttdPemohon && !ttdPemohon.is_validated"
                class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 flex items-start gap-3"
            >
                <Icon icon="solar:clock-circle-bold-duotone" class="w-6 h-6 text-amber-600 mt-0.5 shrink-0" />
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-amber-900 dark:text-amber-200">Menunggu Validasi Admin</p>
                    <p class="text-xs text-amber-700 dark:text-amber-300 mt-1">
                        Dokumen tertandatangani Anda telah diterima dan sedang ditinjau oleh admin. Anda akan diberitahu setelah dokumen divalidasi atau bila perlu perbaikan.
                    </p>
                </div>
            </div>

            <!-- Banner: TTD tervalidasi, menunggu admin approve PKS final → pelaksanaan (status 5 + ttd valid) -->
            <div
                v-if="isPemohon && status === STATUS_PASCA_TANDATANGAN && ttdPemohon && ttdPemohon.is_validated"
                class="p-4 bg-sky-50 dark:bg-sky-900/20 rounded-lg border border-sky-200 flex items-start gap-3"
            >
                <Icon icon="solar:verified-check-bold-duotone" class="w-6 h-6 text-sky-600 mt-0.5 shrink-0" />
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-sky-900 dark:text-sky-200">Dokumen Tervalidasi</p>
                    <p class="text-xs text-sky-700 dark:text-sky-300 mt-1">
                        Dokumen tertandatangani Anda telah dinyatakan valid oleh admin. Saat ini admin akan menyetujui PKS final untuk memulai tahap pelaksanaan kerja sama. Anda akan menerima notifikasi setelahnya.
                    </p>
                </div>
            </div>

            <!-- Dokumen versi admin (untuk pemohon) -->
            <div v-if="ttdAdmin" class="rounded-lg border border-emerald-200 bg-emerald-50/70 dark:border-emerald-800 dark:bg-emerald-900/20 p-3">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <Icon icon="solar:file-check-bold" class="h-5 w-5" />
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-emerald-900 dark:text-emerald-100">{{ ttdAdmin.file_name }}</p>
                            <p class="text-xs text-emerald-700 dark:text-emerald-300">Dokumen versi admin (untuk pemohon)</p>
                        </div>
                    </div>
                    <a :href="ttdAdmin.file_url" target="_blank" class="shrink-0 inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors">
                        <Icon icon="solar:download-bold" class="w-3.5 h-3.5" />
                        Unduh
                    </a>
                </div>
            </div>

            <!-- Banner: dokumen sebelumnya ditolak admin → minta upload ulang -->
            <div
                v-if="isPemohon && status === STATUS_MENUNGGU_TANDATANGAN && permohonan.alasan_tolak"
                class="p-4 bg-rose-50 dark:bg-rose-900/20 rounded-lg border border-rose-200 flex items-start gap-3"
            >
                <Icon icon="solar:close-circle-bold-duotone" class="w-6 h-6 text-rose-600 mt-0.5 shrink-0" />
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-rose-900 dark:text-rose-200">Dokumen sebelumnya DITOLAK admin</p>
                    <p class="text-xs text-rose-700 dark:text-rose-300 mt-1">
                        <strong>Alasan:</strong> {{ permohonan.alasan_tolak }}
                    </p>
                    <p class="text-xs text-rose-600 dark:text-rose-300 mt-1">
                        Silakan upload ulang dokumen yang sudah diperbaiki.
                    </p>
                </div>
            </div>

            <!-- Form upload Pemohon -->
            <div v-if="showPemohonUpload" class="rounded-xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-700 dark:bg-gray-700/30">
                <div class="mb-3 flex items-start gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white text-slate-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                        <Icon icon="solar:pen-new-square-bold" class="h-4 w-4" />
                    </span>
                    <div>
                        <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Upload Dokumen Tertandatangani</h4>
                        <p class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-300">Pastikan dokumen sudah lengkap sebelum dikirim untuk validasi admin.</p>
                    </div>
                </div>

                <form @submit.prevent="submitTtd" enctype="multipart/form-data" class="space-y-3">
                    <div class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-600 dark:bg-gray-800">
                        <p class="mb-2 text-xs font-semibold uppercase text-gray-500 dark:text-gray-300">Kelengkapan Dokumen</p>
                        <div class="grid gap-2 md:grid-cols-3">
                            <label class="flex items-center gap-2 rounded-md bg-gray-50 px-2.5 py-2 text-sm text-gray-700 dark:bg-gray-700/40 dark:text-gray-100">
                                <Checkbox v-model="formPemohon.checklist_paraf" binary inputId="checklist_paraf" />
                                <span>Paraf basah</span>
                            </label>
                            <label class="flex items-center gap-2 rounded-md bg-gray-50 px-2.5 py-2 text-sm text-gray-700 dark:bg-gray-700/40 dark:text-gray-100">
                                <Checkbox v-model="formPemohon.checklist_materai" binary inputId="checklist_materai" />
                                <span>Materai sah</span>
                            </label>
                            <label class="flex items-center gap-2 rounded-md bg-gray-50 px-2.5 py-2 text-sm text-gray-700 dark:bg-gray-700/40 dark:text-gray-100">
                                <Checkbox v-model="formPemohon.checklist_stempel" binary inputId="checklist_stempel" />
                                <span>Stempel instansi</span>
                            </label>
                        </div>
                    </div>

                    <FileUpload
                        ref="pemohonTtdUpload"
                        name="dokumen_ttd"
                        accept="application/pdf,.pdf"
                        :maxFileSize="10000000"
                        :multiple="false"
                        :showUploadButton="false"
                        chooseLabel="Pilih PDF"
                        cancelLabel="Bersihkan"
                        @select="selectPemohonTtd"
                        @clear="resetPemohonTtdFile"
                        @remove="resetPemohonTtdFile"
                        class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-600 dark:bg-gray-800"
                    >
                        <template #header="{ chooseCallback, clearCallback, files }">
                            <div class="flex items-center justify-between gap-3 border-b border-gray-100 bg-white px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-300">PDF, maks. 10 MB</p>
                                <div class="flex items-center gap-1">
                                    <Button type="button" icon="pi pi-plus" label="Pilih" size="small" outlined @click="chooseCallback()" />
                                    <Button
                                        v-if="files?.length"
                                        type="button"
                                        icon="pi pi-times"
                                        severity="secondary"
                                        text
                                        size="small"
                                        aria-label="Bersihkan file"
                                        @click="clearCallback()"
                                    />
                                </div>
                            </div>
                        </template>
                        <template #empty>
                            <div class="flex min-h-28 items-center justify-center gap-3 px-4 py-4 text-left">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-700 dark:bg-gray-700 dark:text-gray-100">
                                    <Icon icon="solar:cloud-upload-bold-duotone" class="h-6 w-6" />
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Tarik PDF tertandatangani ke sini</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-300">atau pilih file dari perangkat.</p>
                                </div>
                            </div>
                        </template>
                    </FileUpload>

                    <div class="flex justify-end border-t border-gray-200 pt-3 dark:border-gray-700">
                        <Button
                            type="submit"
                            label="Upload Dokumen"
                            icon="pi pi-upload"
                            size="small"
                            :loading="formPemohon.processing"
                            :disabled="!allChecked || !formPemohon.file"
                        />
                    </div>
                </form>
            </div>

            <!-- Form validasi Admin -->
            <div v-if="showAdminValidate" class="rounded-xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-700 dark:bg-gray-700/30">
                <div class="mb-3 flex items-start gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white text-slate-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                        <Icon icon="solar:shield-check-bold" class="h-4 w-4" />
                    </span>
                    <div>
                        <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Validasi Dokumen Tertandatangani</h4>
                        <p class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-300">Validasi dan siapkan dokumen versi admin sebelum permohonan masuk pelaksanaan.</p>
                    </div>
                </div>

                <form @submit.prevent="submitValidate" enctype="multipart/form-data" class="space-y-3">
                    <div class="grid gap-2 md:grid-cols-2">
                        <label
                            class="flex cursor-pointer items-start gap-2.5 rounded-lg border bg-white p-3 text-sm transition dark:bg-gray-800"
                            :class="formAdmin.is_valid ? 'border-slate-900 ring-1 ring-slate-900/10 dark:border-gray-300' : 'border-gray-200 dark:border-gray-600'"
                        >
                            <RadioButton v-model="formAdmin.is_valid" inputId="ttd_valid" :value="true" />
                            <span>
                                <span class="block font-semibold text-slate-900 dark:text-white">Valid</span>
                                <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-300">Kelengkapan dokumen sudah sesuai.</span>
                            </span>
                        </label>
                        <label
                            class="flex cursor-pointer items-start gap-2.5 rounded-lg border bg-white p-3 text-sm transition dark:bg-gray-800"
                            :class="!formAdmin.is_valid ? 'border-slate-900 ring-1 ring-slate-900/10 dark:border-gray-300' : 'border-gray-200 dark:border-gray-600'"
                        >
                            <RadioButton v-model="formAdmin.is_valid" inputId="ttd_invalid" :value="false" />
                            <span>
                                <span class="block font-semibold text-slate-900 dark:text-white">Perlu Perbaikan</span>
                                <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-300">Pemohon harus mengunggah ulang.</span>
                            </span>
                        </label>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Catatan Validasi</label>
                        <Textarea
                            v-model="formAdmin.catatan_admin"
                            rows="2"
                            autoResize
                            placeholder="Catatan validasi untuk pemohon (opsional)"
                            class="w-full"
                        />
                    </div>

                    <div v-if="formAdmin.is_valid && !ttdAdmin" class="space-y-1.5">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200">Dokumen Versi Admin</p>
                        <FileUpload
                            ref="adminTtdUpload"
                            name="admin_ttd"
                            accept="application/pdf,.pdf"
                            :maxFileSize="20000000"
                            :multiple="false"
                            :showUploadButton="false"
                            chooseLabel="Pilih PDF"
                            cancelLabel="Bersihkan"
                            @select="selectAdminTtd"
                            @clear="resetAdminTtdFile"
                            @remove="resetAdminTtdFile"
                            class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-600 dark:bg-gray-800"
                        >
                            <template #header="{ chooseCallback, clearCallback, files }">
                                <div class="flex items-center justify-between gap-3 border-b border-gray-100 bg-white px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-300">PDF untuk pemohon, maks. 20 MB</p>
                                    <div class="flex items-center gap-1">
                                        <Button type="button" icon="pi pi-plus" label="Pilih" size="small" outlined @click="chooseCallback()" />
                                        <Button
                                            v-if="files?.length"
                                            type="button"
                                            icon="pi pi-times"
                                            severity="secondary"
                                            text
                                            size="small"
                                            aria-label="Bersihkan file admin"
                                            @click="clearCallback()"
                                        />
                                    </div>
                                </div>
                            </template>
                            <template #empty>
                                <div class="flex min-h-24 items-center justify-center gap-3 px-4 py-4 text-left">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-700 dark:bg-gray-700 dark:text-gray-100">
                                        <Icon icon="solar:cloud-upload-bold-duotone" class="h-6 w-6" />
                                    </span>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Tarik PDF versi admin ke sini</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-300">Berkas ini wajib sebelum PKS disetujui.</p>
                                    </div>
                                </div>
                            </template>
                        </FileUpload>
                    </div>

                    <div
                        v-else-if="formAdmin.is_valid"
                        class="flex items-start gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-xs text-slate-600 dark:border-gray-600 dark:bg-gray-800 dark:text-slate-300"
                    >
                        <Icon icon="solar:verified-check-bold" class="mt-0.5 h-4 w-4 shrink-0 text-slate-700 dark:text-slate-100" />
                        Dokumen versi admin sudah tersedia. Persetujuan PKS dapat dilanjutkan.
                    </div>

                    <div class="flex flex-col gap-2 border-t border-gray-200 pt-3 sm:flex-row sm:items-center sm:justify-between dark:border-gray-700">
                        <p v-if="formAdmin.is_valid && !canApproveWithAdminFile" class="text-xs text-slate-500 dark:text-slate-300">
                            Upload PDF versi admin untuk mengaktifkan persetujuan PKS.
                        </p>
                        <span v-else class="hidden sm:block"></span>
                        <Button
                            type="submit"
                            :label="formAdmin.is_valid ? 'Setujui PKS & Mulai Pelaksanaan' : 'Kirim Perbaikan'"
                            :icon="formAdmin.is_valid ? 'pi pi-check-circle' : 'pi pi-times-circle'"
                            size="small"
                            :loading="formAdmin.processing"
                            :severity="formAdmin.is_valid ? undefined : 'danger'"
                            :disabled="formAdmin.is_valid && !canApproveWithAdminFile"
                        />
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
