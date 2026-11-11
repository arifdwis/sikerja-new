<script setup>
/**
 * PksSection - Upload Naskah Final (KB / PKS) & Approve
 *
 * Status mapping:
 *   3 STATUS_JADWAL_DISETUJUI     - Admin upload naskah final (KB/PKS)
 *   4 STATUS_MENUNGGU_TANDATANGAN - Naskah final terupload, menunggu TTD
 *   5 STATUS_PASCA_TANDATANGAN    - Dokumen TTD diupload
 *   6 STATUS_PELAKSANAAN          - Naskah final approved
 */
import { computed, ref } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import { useToast } from 'vue-toastification';
import Button from 'primevue/button';
import FileUpload from 'primevue/fileupload';
import Textarea from 'primevue/textarea';

const props = defineProps({
    permohonan: Object,
});

const emit = defineEmits(['refresh', 'uploaded']);
const toast = useToast();

const page = usePage();
const userRole = computed(() => page.props.auth?.role);
const userRoles = computed(() => page.props.auth?.roles || []);
const isPemohon = computed(() => userRole.value === 'pemohon' && !userRoles.value.some(r => ['administrator', 'superadmin', 'kabag_kerjasama'].includes(r)));
const isAdmin = computed(() => ['administrator', 'superadmin', 'kabag_kerjasama'].includes(userRole.value) || userRoles.value.some(r => ['administrator', 'superadmin', 'kabag_kerjasama'].includes(r)));

const isPksDoc = computed(() => props.permohonan?.tipe_dokumen === 'PKS');
const docLabel = computed(() => isPksDoc.value ? 'Perjanjian Kerja Sama (PKS) Final' : 'Naskah Kesepakatan Bersama (KB) / PKS Final');
const docShort = computed(() => isPksDoc.value ? 'PKS Final' : 'Naskah Final (KB/PKS)');

const STATUS_JADWAL_DISETUJUI = 3;
const STATUS_MENUNGGU_TANDATANGAN = 4;
const STATUS_PASCA_TANDATANGAN = 5;
const STATUS_PELAKSANAAN = 6;

const status = computed(() => Number(props.permohonan?.status));
const pksFiles = computed(() => props.permohonan?.pks_files || props.permohonan?.pksFiles || []);
const pksPemohon = computed(() => pksFiles.value.find(f => f.tipe === 'pemohon'));
const pksAdmin = computed(() => pksFiles.value.find(f => f.tipe === 'admin'));

// Visibility:
// - Admin upload naskah final di status 3 (Jadwal Disetujui)
// - Setelah upload → status 4 (Menunggu Penandatanganan), pemohon tinggal melihat/mengunduh
const showAdminUpload = computed(() =>
    isAdmin.value
    && status.value === STATUS_JADWAL_DISETUJUI
    && !pksAdmin.value
);
const showPemohonUpload = computed(() => false);

// Forms
const formPks = useForm({ file: null, catatan: '' });
const formAdminPks = useForm({ file: null, catatan: '' });
const adminPksUpload = ref(null);

const selectAdminPks = (event) => {
    formAdminPks.file = event.files?.[0] || null;
};

const resetAdminPksFile = () => {
    formAdminPks.file = null;
};

const submitPks = () => {
    formPks.post(route('permohonan.pks.store', props.permohonan.uuid), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`${docShort.value} berhasil diupload. Menunggu hari penandatanganan.`);
            formPks.reset();
            emit('uploaded');
        },
        onError: () => {
            toast.error(`Gagal mengupload ${docShort.value}. Periksa file dan coba lagi.`);
        },
    });
};

const submitAdminPks = () => {
    formAdminPks.post(route('permohonan.pks.store', props.permohonan.uuid), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`${docShort.value} berhasil diupload. Pemohon menunggu hari penandatanganan.`);
            formAdminPks.reset();
            emit('uploaded');
        },
        onError: () => {
            toast.error(`Gagal mengupload ${docShort.value}.`);
        },
    });
};
</script>

<template>
    <div v-if="status >= STATUS_JADWAL_DISETUJUI || pksFiles.length > 0" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <Icon icon="solar:document-add-bold" class="w-5 h-5 text-slate-600" />
                {{ docLabel }}
            </h3>
        </div>
        <div class="p-5 space-y-4">
            <!-- PKS Pemohon (legacy existing) -->
            <div v-if="pksPemohon" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div class="flex items-center gap-3">
                    <Icon icon="solar:file-text-bold" class="w-8 h-8 text-slate-600" />
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ pksPemohon.file_name }}</p>
                        <p class="text-xs text-gray-500">Diupload oleh pemohon</p>
                    </div>
                </div>
                <a :href="pksPemohon.file_url" target="_blank" class="text-sm text-slate-600 hover:text-slate-900 hover:underline">Lihat</a>
            </div>

            <!-- Naskah Final Admin -->
            <div v-if="pksAdmin" class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-700/40">
                <div class="flex items-center gap-3">
                    <Icon icon="solar:file-check-bold" class="w-8 h-8 text-slate-600" />
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ pksAdmin.file_name }}</p>
                        <p class="text-xs text-gray-500">{{ docShort }} disiapkan oleh Bagian Kerja Sama</p>
                    </div>
                </div>
                <a :href="pksAdmin.file_url" target="_blank" class="text-sm text-slate-600 hover:text-slate-900 hover:underline">Unduh</a>
            </div>

            <!-- Pemohon: pesan menunggu admin upload naskah final -->
            <div
                v-if="isPemohon && status === STATUS_JADWAL_DISETUJUI && !pksAdmin"
                class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 flex items-start gap-3"
            >
                <Icon icon="solar:clock-circle-bold-duotone" class="w-6 h-6 text-amber-600 mt-0.5 shrink-0" />
                <div>
                    <p class="text-sm font-semibold text-amber-900 dark:text-amber-200">Menunggu {{ docShort }} dari Admin</p>
                    <p class="text-xs text-amber-700 dark:text-amber-300 mt-1">
                        Jadwal penandatanganan sudah disetujui. Bagian Kerja Sama akan menyiapkan {{ docShort }}.
                        Setelah naskah final tersedia, Anda akan diminta mengunggah dokumen yang sudah ditandatangani.
                    </p>
                </div>
            </div>

            <!-- Form upload Naskah Final Admin -->
            <div v-if="showAdminUpload" class="rounded-xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-700 dark:bg-gray-700/30">
                <div class="mb-3 flex items-start gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white text-slate-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                        <Icon icon="solar:upload-square-bold" class="h-4 w-4" />
                    </span>
                    <div>
                        <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Upload {{ docShort }}</h4>
                        <p class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-300">
                            Upload PDF {{ docShort }} yang sudah siap untuk proses penandatanganan. File maksimal 10 MB.
                        </p>
                    </div>
                </div>

                <form @submit.prevent="submitAdminPks" enctype="multipart/form-data" class="space-y-3">
                    <FileUpload
                        ref="adminPksUpload"
                        name="pks_final"
                        accept="application/pdf,.pdf"
                        :maxFileSize="10000000"
                        :multiple="false"
                        :showUploadButton="false"
                        chooseLabel="Pilih PDF"
                        cancelLabel="Bersihkan"
                        @select="selectAdminPks"
                        @clear="resetAdminPksFile"
                        @remove="resetAdminPksFile"
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
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Tarik PDF {{ docShort }} ke sini</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-300">atau pilih file dari perangkat.</p>
                                </div>
                            </div>
                        </template>
                    </FileUpload>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Catatan Admin</label>
                        <Textarea
                            v-model="formAdminPks.catatan"
                            rows="2"
                            autoResize
                            placeholder="Catatan untuk pemohon (opsional)"
                            class="w-full"
                        />
                    </div>

                    <div class="flex justify-end border-t border-gray-200 pt-3 dark:border-gray-700">
                        <Button
                            type="submit"
                            :label="`Upload ${docShort}`"
                            icon="pi pi-upload"
                            size="small"
                            :loading="formAdminPks.processing"
                            :disabled="!formAdminPks.file"
                        />
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
