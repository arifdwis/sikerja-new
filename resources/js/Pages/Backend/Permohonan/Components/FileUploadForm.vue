<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Icon } from '@iconify/vue';
import Button from 'primevue/button';
import FileUpload from 'primevue/fileupload';
import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
    permohonan: Object,
});

const emit = defineEmits(['close', 'success']);

const alreadyUploaded = computed(() => (props.permohonan?.files?.length ?? 0) > 0);

const form = useForm({
    surat_permohonan: null,
    proposal_kak: null,
    draft_mou: null,
});

// File refs for display
const suratPermohonanFile = ref(null);
const proposalKakFile = ref(null);
const draftMouFile = ref(null);

const handleFileSelect = (event, field) => {
    const file = event.files ? event.files[0] : (event.target?.files ? event.target.files[0] : null);
    if (!file) return;

    // Validasi format ditetapkan (Req 3): PDF untuk Surat & KAK, DOC/DOCX untuk MoU
    const fileType = fileTypes.find(ft => ft.key === field);
    if (fileType) {
        const ext = '.' + (file.name.split('.').pop() || '').toLowerCase();
        const allowed = fileType.accept.split(',');
        if (!allowed.includes(ext)) {
            toast.error(
                `${fileType.label} hanya menerima format ${fileType.format}. ` +
                `File "${file.name}" tidak diizinkan.`
            );
            return;
        }
        // Validasi ukuran 10 MB
        if (file.size > 10 * 1024 * 1024) {
            toast.error(`Ukuran file maksimal 10 MB. File ${file.name} (${formatFileSize(file.size)}) terlalu besar.`);
            return;
        }
    }

    form[field] = file;

    if (field === 'surat_permohonan') suratPermohonanFile.value = file;
    if (field === 'proposal_kak') proposalKakFile.value = file;
    if (field === 'draft_mou') draftMouFile.value = file;
};

const removeFile = (field) => {
    form[field] = null;
    if (field === 'surat_permohonan') suratPermohonanFile.value = null;
    if (field === 'proposal_kak') proposalKakFile.value = null;
    if (field === 'draft_mou') draftMouFile.value = null;
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const submit = () => {
    if (alreadyUploaded.value) {
        toast.error('Berkas sudah diupload. Upload perbaikan hanya tersedia pada dokumen yang diminta revisi.');
        return;
    }

    form.post(route('permohonan.upload', props.permohonan.uuid), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            emit('success');
        }
    });
};

const fileTypes = [
    {
        key: 'surat_permohonan',
        label: 'Surat Permohonan Kerjasama',
        description: 'Surat resmi permohonan kerjasama dari instansi/lembaga',
        format: 'PDF',
        accept: '.pdf',
        mimes: ['application/pdf'],
        icon: 'solar:document-bold',
        color: 'blue',
        required: true,
    },
    {
        key: 'proposal_kak',
        label: 'Proposal / Kerangka Acuan Kerja (KAK)',
        description: 'Dokumen proposal atau KAK yang menjelaskan detail kerjasama',
        format: 'PDF',
        accept: '.pdf',
        mimes: ['application/pdf'],
        icon: 'solar:clipboard-list-bold',
        color: 'orange',
        required: true,
    },
    {
        key: 'draft_mou',
        label: 'Draft MoU / Perjanjian Kerjasama',
        description: 'Draft kesepakatan kerjasama yang akan ditinjau bersama',
        format: 'DOC / DOCX',
        accept: '.doc,.docx',
        mimes: ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        icon: 'solar:document-add-bold',
        color: 'emerald',
        required: true,
    },
];

const getFileRef = (key) => {
    if (key === 'surat_permohonan') return suratPermohonanFile.value;
    if (key === 'proposal_kak') return proposalKakFile.value;
    if (key === 'draft_mou') return draftMouFile.value;
    return null;
};
</script>

<template>
    <div class="p-6 space-y-6">
        <!-- Header -->
        <div class="text-center pb-4 border-b border-gray-100 dark:border-gray-700">
            <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                <Icon icon="solar:cloud-upload-bold-duotone" class="w-8 h-8 text-blue-600" />
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Upload Dokumen Kerjasama</h3>
            <p class="text-sm text-gray-500 mt-2">
                Silakan upload dokumen yang diperlukan untuk melanjutkan proses pembahasan kerjasama.
            </p>
        </div>

        <div v-if="alreadyUploaded" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/30 rounded-xl p-4 flex gap-3">
            <Icon icon="solar:lock-keyhole-bold" class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" />
            <div class="text-sm">
                <p class="font-medium text-blue-800 dark:text-blue-200">Berkas awal sudah terkirim</p>
                <p class="text-blue-700 dark:text-blue-300 mt-1">
                    Tunggu hasil review. Upload perbaikan hanya tersedia pada dokumen yang diminta revisi.
                </p>
            </div>
        </div>

        <!-- Info Alert -->
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/30 rounded-xl p-4 flex gap-3">
            <Icon icon="solar:info-circle-bold" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" />
            <div class="text-sm">
                <p class="font-medium text-amber-800 dark:text-amber-200">Perhatian</p>
                <p class="text-amber-700 dark:text-amber-300 mt-1">
                    Format file yang diterima: PDF, DOC, DOCX (Maks. 10MB per file). 
                    Pastikan dokumen yang diupload sudah sesuai dan lengkap.
                </p>
            </div>
        </div>

        <!-- Requirement Note -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/30 rounded-xl p-4 flex gap-3">
            <Icon icon="solar:checklist-minimalistic-bold" class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" />
            <div class="text-sm">
                <p class="font-medium text-blue-800 dark:text-blue-200">Semua dokumen wajib diupload</p>
                <p class="text-blue-700 dark:text-blue-300 mt-1">
                    Anda harus mengupload <strong>ketiga dokumen</strong> di bawah ini sebelum dapat melanjutkan ke tahap pembahasan. Tombol "Upload &amp; Lanjutkan" akan aktif setelah semua berkas terisi.
                </p>
            </div>
        </div>

        <!-- File Upload Cards -->
        <form @submit.prevent="submit" class="space-y-4">
            <div v-for="fileType in fileTypes" :key="fileType.key" 
                class="border rounded-xl p-5 transition-all"
                :class="[
                    getFileRef(fileType.key) 
                        ? 'border-green-300 dark:border-green-700 bg-green-50/50 dark:bg-green-900/10' 
                        : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800'
                ]"
            >
                <div class="flex items-start gap-4">
                    <!-- Icon -->
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0"
                        :class="`bg-${fileType.color}-100 dark:bg-${fileType.color}-900/30`"
                    >
                        <Icon :icon="fileType.icon" class="w-6 h-6" :class="`text-${fileType.color}-600`" />
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                            <h4 class="font-semibold text-gray-900 dark:text-white">{{ fileType.label }}</h4>
                            <span v-if="fileType.required" class="text-red-500 text-xs font-bold">*Wajib</span>
                            <span v-else class="text-gray-400 text-xs">(Opsional)</span>
                            <!-- Format ditetapkan (Req 3) -->
                            <span class="ml-auto inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider"
                                :class="`bg-${fileType.color}-100 text-${fileType.color}-700 dark:bg-${fileType.color}-900/30 dark:text-${fileType.color}-300`">
                                <Icon icon="solar:lock-bold" class="w-3 h-3" />
                                {{ fileType.format }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ fileType.description }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            Format file yang diterima: <span class="font-semibold">{{ fileType.format }}</span> · Maks. 10 MB
                        </p>

                        <!-- File Preview or Upload Button -->
                        <div class="mt-3">
                            <div v-if="getFileRef(fileType.key)" 
                                class="flex items-center gap-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-3"
                            >
                                <Icon icon="solar:file-check-bold-duotone" class="w-8 h-8 text-green-500" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ getFileRef(fileType.key)?.name }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ formatFileSize(getFileRef(fileType.key)?.size || 0) }}
                                    </p>
                                </div>
                                <button type="button" @click="removeFile(fileType.key)" 
                                    class="p-1.5 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition text-red-500"
                                >
                                    <Icon icon="solar:trash-bin-trash-bold" class="w-5 h-5" />
                                </button>
                            </div>

                            <div v-else>
                                <FileUpload 
                                    mode="basic" 
                                    :auto="false"
                                    :disabled="alreadyUploaded"
                                    :accept="fileType.accept"
                                    :maxFileSize="10000000"
                                    @select="(e) => handleFileSelect(e, fileType.key)"
                                    chooseLabel="Pilih File"
                                    class="w-full"
                                />
                            </div>
                        </div>

                        <!-- Error Message -->
                        <p v-if="form.errors[fileType.key]" class="text-red-500 text-sm mt-2 flex items-center gap-1">
                            <Icon icon="solar:danger-circle-bold" class="w-4 h-4" />
                            {{ form.errors[fileType.key] }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                <Button 
                    type="button" 
                    label="Batal" 
                    severity="secondary" 
                    outlined
                    @click="emit('close')"
                />
                <Button 
                    type="submit" 
                    label="Upload & Lanjutkan" 
                    icon="pi pi-upload"
                    :loading="form.processing"
                    :disabled="alreadyUploaded || !form.surat_permohonan || !form.proposal_kak || !form.draft_mou"
                />
            </div>
        </form>
    </div>
</template>
