<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import { useToast } from 'vue-toastification';
import axios from 'axios';

const toast = useToast();

const props = defineProps({
    file: Object, // The file being discussed
    permohonan: Object,
    isAdmin: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['statusUpdated']);

const messages = ref([]);
const newMessage = ref('');
const isLoading = ref(false);
const isSending = ref(false);
const chatContainer = ref(null);

// File review state
const isReviewing = ref(false);
const rejectDialog = ref(false);
const rejectComment = ref('');

// File re-upload state (for pemohon)
const isUploading = ref(false);
const fileInput = ref(null);
const selectedNewFile = ref(null);

const fetchMessages = async () => {
    if (!props.file?.uuid) return;
    
    try {
        const response = await axios.get(route('permohonan.file.diskusi.index', props.file.uuid));
        messages.value = response.data;
        scrollToBottom();
    } catch (error) {
        console.error('Error fetching messages:', error);
    }
};

const scrollToBottom = () => {
    nextTick(() => {
        if (chatContainer.value) {
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
        }
    });
};

const sendMessage = async () => {
    if (!newMessage.value.trim()) return;

    isSending.value = true;
    try {
        const response = await axios.post(route('permohonan.file.diskusi.store', props.file.uuid), {
            message: newMessage.value
        });
        messages.value.push(response.data);
        newMessage.value = '';
        scrollToBottom();
    } catch (error) {
        console.error('Error sending message:', error);
        alert('Gagal mengirim pesan. Silakan coba lagi.');
    } finally {
        isSending.value = false;
    }
};

// File Review (Admin only)
const approveFile = async () => {
    isReviewing.value = true;
    try {
        const response = await axios.put(route('permohonan.file.review', props.file.uuid), {
            status: 1, // Approved
            komentar: null
        });
        emit('statusUpdated', response.data.file);
        await fetchMessages();
        toast.success('Dokumen berhasil disetujui');
    } catch (error) {
        console.error('Error approving file:', error);
        toast.error('Gagal menyetujui dokumen. Pastikan migration sudah dijalankan.');
    } finally {
        isReviewing.value = false;
    }
};

const openRejectDialog = () => {
    rejectComment.value = '';
    rejectDialog.value = true;
};

const submitReject = async () => {
    isReviewing.value = true;
    try {
        const response = await axios.put(route('permohonan.file.review', props.file.uuid), {
            status: 2, // Rejected
            komentar: rejectComment.value
        });
        emit('statusUpdated', response.data.file);
        rejectDialog.value = false;
        await fetchMessages();
        toast.success('Dokumen berhasil ditolak');
    } catch (error) {
        console.error('Error rejecting file:', error);
        toast.error('Gagal menolak dokumen');
    } finally {
        isReviewing.value = false;
    }
};

// File Re-upload (Pemohon only - when rejected)
const triggerFileInput = () => {
    fileInput.value?.click();
};

const onFileSelected = (event) => {
    const file = event.target.files[0];
    if (file) {
        selectedNewFile.value = file;
    }
};

const submitReupload = async () => {
    if (!selectedNewFile.value) return;
    
    isUploading.value = true;
    try {
        const formData = new FormData();
        formData.append('file', selectedNewFile.value);
        
        const response = await axios.post(
            route('permohonan.file.revision', props.file.uuid), 
            formData,
            { headers: { 'Content-Type': 'multipart/form-data' } }
        );
        
        emit('statusUpdated', response.data.file);
        selectedNewFile.value = null;
        if (fileInput.value) fileInput.value.value = '';
        await fetchMessages();
        toast.success('Berkas perbaikan berhasil diupload');
    } catch (error) {
        console.error('Error uploading file:', error);
        toast.error('Gagal mengupload berkas perbaikan');
    } finally {
        isUploading.value = false;
    }
};

const cancelReupload = () => {
    selectedNewFile.value = null;
    if (fileInput.value) fileInput.value.value = '';
};

const getStatusLabel = (status) => {
    const labels = {
        0: { label: 'Menunggu Review', color: 'bg-yellow-100 text-yellow-800 border-yellow-200' },
        1: { label: 'Disetujui', color: 'bg-green-100 text-green-800 border-green-200' },
        2: { label: 'Ditolak', color: 'bg-red-100 text-red-800 border-red-200' }
    };
    return labels[status] || labels[0];
};

const formatFileSize = (bytes) => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};

watch(() => props.file, (newFile) => {
    if (newFile?.uuid) {
        fetchMessages();
        selectedNewFile.value = null;
    }
}, { immediate: true });

onMounted(() => {
    // Poll for new messages every 15 seconds
    setInterval(fetchMessages, 15000);
});
</script>

<template>
    <div class="flex flex-col h-full bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        
        <!-- File Header -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                        <Icon icon="solar:file-text-bold-duotone" class="w-6 h-6 text-blue-600" />
                    </div>
                    <div class="min-w-0">
                        <h4 class="font-bold text-gray-900 dark:text-white truncate">{{ file?.label }}</h4>
                        <a :href="file?.file_url" target="_blank" class="text-xs text-blue-600 hover:underline">
                            Lihat/Unduh Dokumen
                        </a>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <!-- Status Badge -->
                    <span class="px-3 py-1 text-xs font-bold rounded-full border" :class="getStatusLabel(file?.status).color">
                        {{ getStatusLabel(file?.status).label }}
                    </span>
                    <!-- Admin Review Buttons -->
                    <div v-if="isAdmin && file?.status === 0" class="flex gap-1">
                        <button 
                            @click="approveFile"
                            :disabled="isReviewing"
                            class="p-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition disabled:opacity-50"
                            title="Setujui Dokumen"
                        >
                            <Icon icon="solar:check-circle-bold" class="w-5 h-5" />
                        </button>
                        <button 
                            @click="openRejectDialog"
                            :disabled="isReviewing"
                            class="p-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition disabled:opacity-50"
                            title="Tolak Dokumen"
                        >
                            <Icon icon="solar:close-circle-bold" class="w-5 h-5" />
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Admin Comment (if rejected) -->
            <div v-if="file?.komentar && file?.status === 2" class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                <p class="text-sm text-red-700 dark:text-red-300 flex items-start gap-2">
                    <Icon icon="solar:danger-triangle-bold" class="w-5 h-5 mt-0.5 shrink-0" />
                    <span><strong>Alasan Penolakan:</strong> {{ file?.komentar }}</span>
                </p>
            </div>

            <!-- Pemohon Re-upload Section (when rejected) -->
            <div v-if="!isAdmin && file?.status === 2" class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800">
                <p class="text-sm text-amber-800 dark:text-amber-300 mb-3 flex items-start gap-2">
                    <Icon icon="solar:upload-twice-bold" class="w-5 h-5 mt-0.5 shrink-0" />
                    <span>Silakan upload berkas perbaikan untuk ditinjau ulang</span>
                </p>
                
                <input 
                    ref="fileInput"
                    type="file"
                    @change="onFileSelected"
                    class="hidden"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                />
                
                <!-- Selected file preview -->
                <div v-if="selectedNewFile" class="mb-3 p-2 bg-white dark:bg-gray-800 rounded-lg border border-amber-300 flex items-center gap-3">
                    <Icon icon="solar:file-check-bold" class="w-8 h-8 text-amber-600" />
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ selectedNewFile.name }}</p>
                        <p class="text-xs text-gray-500">{{ formatFileSize(selectedNewFile.size) }}</p>
                    </div>
                    <button @click="cancelReupload" class="text-gray-400 hover:text-red-500">
                        <Icon icon="solar:close-circle-bold" class="w-5 h-5" />
                    </button>
                </div>
                
                <div class="flex gap-2">
                    <button 
                        v-if="!selectedNewFile"
                        @click="triggerFileInput"
                        class="flex-1 flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition"
                    >
                        <Icon icon="solar:upload-bold" class="w-4 h-4" />
                        Pilih Berkas Perbaikan
                    </button>
                    <button 
                        v-if="selectedNewFile"
                        @click="submitReupload"
                        :disabled="isUploading"
                        class="flex-1 flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium bg-green-500 hover:bg-green-600 text-white rounded-lg transition disabled:opacity-50"
                    >
                        <Icon v-if="isUploading" icon="svg-spinners:ring-resize" class="w-4 h-4" />
                        <Icon v-else icon="solar:upload-bold" class="w-4 h-4" />
                        {{ isUploading ? 'Mengupload...' : 'Upload Perbaikan' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Chat Area -->
        <div ref="chatContainer" class="flex-1 overflow-y-auto p-4 space-y-3 min-h-[200px] max-h-[300px] bg-gray-50 dark:bg-gray-900/50">
            <div v-if="messages.length === 0" class="text-center py-8 text-gray-400">
                <Icon icon="solar:chat-round-line-duotone" class="w-10 h-10 mx-auto mb-2 opacity-50" />
                <p class="text-sm">Belum ada diskusi untuk dokumen ini.</p>
            </div>

            <div v-for="msg in messages" :key="msg.id" 
                class="flex flex-col max-w-[85%]"
                :class="msg.is_me ? 'ml-auto items-end' : 'mr-auto items-start'"
            >
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-bold" :class="msg.is_me ? 'text-blue-600' : 'text-gray-600 dark:text-gray-400'">
                        {{ msg.operator?.name || 'User' }}
                    </span>
                    <span class="text-[10px] text-gray-400">{{ msg.formatted_time }}</span>
                </div>
                
                <div class="p-3 rounded-xl text-sm"
                     :class="msg.is_me 
                        ? 'bg-blue-600 text-white rounded-br-none' 
                        : 'bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-bl-none border border-gray-200 dark:border-gray-600'"
                >
                    <p class="whitespace-pre-wrap">{{ msg.komentar }}</p>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
            <div class="flex gap-2 items-end">
                <textarea 
                    v-model="newMessage" 
                    @keydown.enter.exact.prevent="sendMessage"
                    placeholder="Ketik pesan tentang dokumen ini..." 
                    rows="1"
                    class="flex-1 rounded-lg border-gray-200 dark:bg-gray-700 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 resize-none py-2 px-3 text-sm"
                ></textarea>
                <button 
                    @click="sendMessage" 
                    :disabled="!newMessage.trim() || isSending"
                    class="p-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                >
                    <Icon v-if="isSending" icon="svg-spinners:ring-resize" class="w-5 h-5" />
                    <Icon v-else icon="solar:plain-bold" class="w-5 h-5" />
                </button>
            </div>
        </div>

        <!-- Reject Dialog -->
        <div v-if="rejectDialog" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 w-full max-w-md shadow-xl border border-gray-100 dark:border-gray-700">
                <div class="flex justify-between items-start mb-4">
                    <h4 class="font-bold text-lg dark:text-white text-red-600">Tolak Dokumen</h4>
                    <button @click="rejectDialog = false" class="text-gray-400 hover:text-gray-600">
                        <Icon icon="solar:close-circle-bold" class="w-6 h-6" />
                    </button>
                </div>
                
                <p class="text-sm text-gray-500 mb-4 dark:text-gray-400">
                    Dokumen: <strong>{{ file?.label }}</strong>
                </p>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2 dark:text-white">Alasan Penolakan</label>
                    <textarea 
                        v-model="rejectComment" 
                        placeholder="Berikan catatan mengapa dokumen ditolak..."
                        rows="3"
                        class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 focus:ring-red-500 focus:border-red-500"
                    ></textarea>
                </div>

                <div class="flex justify-end gap-2">
                    <button 
                        @click="rejectDialog = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition"
                    >
                        Batal
                    </button>
                    <button 
                        @click="submitReject"
                        :disabled="isReviewing"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition disabled:opacity-50"
                    >
                        {{ isReviewing ? 'Memproses...' : 'Tolak Dokumen' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}
.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.4);
    border-radius: 20px;
}
</style>
