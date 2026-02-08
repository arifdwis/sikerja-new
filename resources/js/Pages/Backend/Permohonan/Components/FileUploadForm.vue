<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Icon } from '@iconify/vue';
import Button from 'primevue/button';
import FileUpload from 'primevue/fileupload';

const props = defineProps({
    permohonan: Object,
});

const emit = defineEmits(['close', 'success']);

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
        icon: 'solar:document-bold',
        color: 'blue',
        required: true,
    },
    {
        key: 'proposal_kak',
        label: 'Proposal / Kerangka Acuan Kerja (KAK)',
        description: 'Dokumen proposal atau KAK yang menjelaskan detail kerjasama',
        icon: 'solar:clipboard-list-bold',
        color: 'orange',
        required: true,
    },
    {
        key: 'draft_mou',
        label: 'Draft MoU / Perjanjian Kerjasama',
        description: 'Draft kesepakatan kerjasama (opsional pada tahap ini)',
        icon: 'solar:document-add-bold',
        color: 'emerald',
        required: false,
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
                        <div class="flex items-center gap-2 mb-1">
                            <h4 class="font-semibold text-gray-900 dark:text-white">{{ fileType.label }}</h4>
                            <span v-if="fileType.required" class="text-red-500 text-xs font-bold">*Wajib</span>
                            <span v-else class="text-gray-400 text-xs">(Opsional)</span>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ fileType.description }}</p>

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
                                    accept=".pdf,.doc,.docx"
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
                    :disabled="!form.surat_permohonan || !form.proposal_kak"
                />
            </div>
        </form>
    </div>
</template>
