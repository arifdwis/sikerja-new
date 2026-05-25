<script setup>
/**
 * PksSection - Upload PKS Final & Approve PKS
 *
 * Status mapping:
 *   3 STATUS_JADWAL_DISETUJUI     - Pemohon upload PKS
 *   4 STATUS_MENUNGGU_TANDATANGAN - PKS terupload, menunggu TTD
 *   5 STATUS_PASCA_TANDATANGAN    - Dokumen TTD diupload
 *   6 STATUS_PELAKSANAAN          - PKS final approved
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
const isPemohon = computed(() => userRole.value === 'pemohon');
const isAdmin = computed(() => ['administrator', 'superadmin'].includes(userRole.value));

const STATUS_JADWAL_DISETUJUI = 3;
const STATUS_MENUNGGU_TANDATANGAN = 4;
const STATUS_PASCA_TANDATANGAN = 5;
const STATUS_PELAKSANAAN = 6;

const status = computed(() => Number(props.permohonan?.status));
const pksFiles = computed(() => props.permohonan?.pks_files || []);
const pksPemohon = computed(() => pksFiles.value.find(f => f.tipe === 'pemohon'));
const pksAdmin = computed(() => pksFiles.value.find(f => f.tipe === 'admin'));

// Visibility (sesuai workflow baru):
// - Admin upload PKS final di status 3 (Jadwal Disetujui)
// - Setelah upload → status 4 (Menunggu Penandatanganan), pemohon tinggal melihat
// - Pemohon tidak upload PKS final lagi
const showAdminUpload = computed(() =>
    isAdmin.value
    && status.value === STATUS_JADWAL_DISETUJUI
    && !pksAdmin.value
);
// Form upload pemohon dimatikan (workflow baru)
const showPemohonUpload = computed(() => false);
// Tombol "Setujui PKS Final" sudah dipindah ke TtdSection (digabung dengan validasi dokumen tertandatangani).
const showApproveBtn = computed(() => false);

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

const clearAdminPks = () => {
    resetAdminPksFile();
    adminPksUpload.value?.clear();
};

const submitPks = () => {
    formPks.post(route('permohonan.pks.store', props.permohonan.uuid), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            toast.success('PKS Final berhasil diupload. Menunggu hari penandatanganan.');
            formPks.reset();
            emit('uploaded');
        },
        onError: () => {
            toast.error('Gagal mengupload PKS. Periksa file dan coba lagi.');
        },
    });
};

const submitAdminPks = () => {
    formAdminPks.post(route('permohonan.pks.store', props.permohonan.uuid), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            toast.success('PKS final berhasil diupload. Pemohon menunggu hari penandatanganan.');
            formAdminPks.reset();
            emit('uploaded');
        },
        onError: () => {
            toast.error('Gagal mengupload PKS final.');
        },
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
</script>

<template>
    <div v-if="status >= STATUS_JADWAL_DISETUJUI" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <Icon icon="solar:document-add-bold" class="w-5 h-5 text-slate-600" />
                Perjanjian Kerjasama (PKS) Final
            </h3>
        </div>
        <div class="p-5 space-y-4">
            <!-- PKS Pemohon (existing) -->
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

            <!-- Form upload PKS oleh Pemohon -->
            <div v-if="showPemohonUpload" class="p-4 bg-violet-50 dark:bg-violet-900/20 rounded-lg border border-violet-200 dark:border-violet-700">
                <h4 class="text-sm font-semibold text-violet-900 dark:text-violet-200 mb-2">Upload PKS Final (PDF)</h4>
                <p class="text-xs text-violet-700 dark:text-violet-300 mb-3">Jadwal penandatanganan telah disetujui. Silakan upload PKS final dalam format PDF.</p>
                <form @submit.prevent="submitPks" enctype="multipart/form-data" class="space-y-3">
                    <input type="file" accept="application/pdf" @change="formPks.file = $event.target.files[0]" required class="block w-full text-sm text-gray-700 dark:text-gray-300 file:mr-3 file:py-2 file:px-3 file:rounded file:border-0 file:bg-violet-600 file:text-white" />
                    <textarea v-model="formPks.catatan" rows="2" placeholder="Catatan (opsional)" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"></textarea>
                    <button :disabled="formPks.processing || !formPks.file" type="submit" class="w-full px-4 py-2 bg-violet-600 hover:bg-violet-700 disabled:bg-gray-400 text-white text-sm font-medium rounded-lg">
                        {{ formPks.processing ? 'Mengupload...' : 'Upload PKS' }}
                    </button>
                </form>
            </div>

            <!-- PKS Admin (PKS final) -->
            <div v-if="pksAdmin" class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-700/40">
                <div class="flex items-center gap-3">
                    <Icon icon="solar:file-check-bold" class="w-8 h-8 text-slate-600" />
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ pksAdmin.file_name }}</p>
                        <p class="text-xs text-gray-500">PKS final disiapkan oleh admin</p>
                    </div>
                </div>
                <a :href="pksAdmin.file_url" target="_blank" class="text-sm text-slate-600 hover:text-slate-900 hover:underline">Unduh</a>
            </div>

            <!-- Pemohon: pesan menunggu admin upload PKS final -->
            <div
                v-if="isPemohon && status === STATUS_JADWAL_DISETUJUI && !pksAdmin"
                class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 flex items-start gap-3"
            >
                <Icon icon="solar:clock-circle-bold-duotone" class="w-6 h-6 text-amber-600 mt-0.5 shrink-0" />
                <div>
                    <p class="text-sm font-semibold text-amber-900 dark:text-amber-200">Menunggu PKS Final dari Admin</p>
                    <p class="text-xs text-amber-700 dark:text-amber-300 mt-1">
                        Jadwal penandatanganan sudah disetujui. Admin akan menyiapkan PKS final.
                        Setelah PKS final tersedia, Anda akan diminta upload dokumen yang sudah ditandatangani.
                    </p>
                </div>
            </div>

            <!-- Form upload PKS Admin -->
            <div v-if="showAdminUpload" class="rounded-xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-700 dark:bg-gray-700/30">
                <div class="mb-3 flex items-start gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white text-slate-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                        <Icon icon="solar:upload-square-bold" class="h-4 w-4" />
                    </span>
                    <div>
                        <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Upload PKS Final</h4>
                        <p class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-300">
                            Upload PDF yang sudah disiapkan untuk proses penandatanganan. File maksimal 10 MB.
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
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Tarik PDF PKS ke sini</p>
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
                            label="Upload PKS Final"
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
