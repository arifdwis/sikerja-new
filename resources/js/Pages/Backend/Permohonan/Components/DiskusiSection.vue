<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import axios from 'axios';
import moment from 'moment';

const props = defineProps({
    permohonan: Object,
    isAdmin: {
        type: Boolean,
        default: false
    }
});

const messages = ref([]);
const newMessage = ref('');
const isLoading = ref(false);
const isSending = ref(false);
const fileInput = ref(null);
const chatContainer = ref(null);

const fileTypes = [
    { id: 'surat_penawaran', label: 'Surat Penawaran Kerjasama', icon: 'solar:file-text-bold', color: 'text-blue-500' },
    { id: 'proposal', label: 'Proposal / KK', icon: 'solar:document-add-bold', color: 'text-purple-500' },
    { id: 'rancangan_ks', label: 'Rancangan Kesepakatan', icon: 'solar:pen-new-square-bold', color: 'text-green-500' },
];

const selectedFileType = ref(null);
const uploadDialog = ref(false);

const fetchMessages = async () => {
    try {
        const response = await axios.get(route('permohonan.diskusi.index', props.permohonan.uuid));
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
        const response = await axios.post(route('permohonan.diskusi.store', props.permohonan.uuid), {
            message: newMessage.value
        });
        messages.value.push(response.data);
        newMessage.value = '';
        scrollToBottom();
    } catch (error) {
        console.error('Error sending message:', error);
    } finally {
        isSending.value = false;
    }
};

const openUploadDialog = (type) => {
    selectedFileType.value = type;
    uploadDialog.value = true;
};

const handleFileUpload = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('file', file);
    formData.append('file_label', selectedFileType.value.label); // Use readable label
    formData.append('message', `Mengunggah dokumen: ${selectedFileType.value.label}`);

    isSending.value = true;
    uploadDialog.value = false; // Close dialog immediately

    try {
        const response = await axios.post(route('permohonan.diskusi.store', props.permohonan.uuid), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        messages.value.push(response.data);
        scrollToBottom();
    } catch (error) {
        console.error('Error uploading file:', error);
        alert('Gagal mengunggah file. Pastikan ukuran file di bawah 10MB.');
    } finally {
        isSending.value = false;
        selectedFileType.value = null;
    }
};

// File Review (Admin)
const rejectDialog = ref(false);
const selectedFileForReview = ref(null);
const rejectComment = ref('');
const isReviewing = ref(false);

const approveFile = async (file) => {
    isReviewing.value = true;
    try {
        const response = await axios.put(route('permohonan.file.review', file.uuid), {
            status: 1, // Approved
            komentar: null
        });
        
        // Update the file status in messages
        updateFileStatusInMessages(file.id, 1);
        alert('Dokumen berhasil disetujui');
    } catch (error) {
        console.error('Error approving file:', error);
        alert('Gagal menyetujui dokumen');
    } finally {
        isReviewing.value = false;
    }
};

const openRejectDialog = (file) => {
    selectedFileForReview.value = file;
    rejectComment.value = '';
    rejectDialog.value = true;
};

const submitReject = async () => {
    if (!selectedFileForReview.value) return;
    
    isReviewing.value = true;
    try {
        await axios.put(route('permohonan.file.review', selectedFileForReview.value.uuid), {
            status: 2, // Rejected
            komentar: rejectComment.value
        });
        
        // Update the file status in messages
        updateFileStatusInMessages(selectedFileForReview.value.id, 2);
        rejectDialog.value = false;
        alert('Dokumen berhasil ditolak');
    } catch (error) {
        console.error('Error rejecting file:', error);
        alert('Gagal menolak dokumen');
    } finally {
        isReviewing.value = false;
    }
};

const updateFileStatusInMessages = (fileId, newStatus) => {
    messages.value = messages.value.map(msg => {
        if (msg.file && msg.file.id === fileId) {
            return { ...msg, file: { ...msg.file, status: newStatus } };
        }
        return msg;
    });
};

onMounted(() => {
    fetchMessages();
    // Poll for new messages every 10 seconds
    setInterval(fetchMessages, 10000);
});
</script>

<template>
    <div class="flex flex-col h-[600px] bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        
        <!-- Header -->
        <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-700/50">
            <div>
                <h3 class="font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <Icon icon="solar:chat-line-bold-duotone" class="w-5 h-5 text-blue-600" />
                    Ruang Diskusi & Negosiasi
                </h3>
                <p class="text-xs text-gray-500 mt-1">Diskusikan detail kerjasama dan unggah dokumen revisi di sini.</p>
            </div>
        </div>

        <!-- Chat Area -->
        <div ref="chatContainer" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50 dark:bg-gray-900/50">
            <div v-if="messages.length === 0" class="text-center py-10 text-gray-400">
                <Icon icon="solar:chat-round-line-duotone" class="w-12 h-12 mx-auto mb-2 opacity-50" />
                <p class="text-sm">Belum ada diskusi. Mulai percakapan sekarang.</p>
            </div>

            <div v-for="msg in messages" :key="msg.id" 
                class="flex flex-col max-w-[80%]"
                :class="msg.is_me ? 'ml-auto items-end' : 'mr-auto items-start'"
            >
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-bold text-gray-600 dark:text-gray-300">{{ msg.operator?.name || 'User' }}</span>
                    <span class="text-[10px] text-gray-400">{{ msg.formatted_time }}</span>
                </div>
                
                <div class="p-3 rounded-2xl text-sm relative group"
                     :class="msg.is_me 
                        ? 'bg-blue-600 text-white rounded-tr-none shadow-md shadow-blue-500/20' 
                        : 'bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-tl-none border border-gray-200 dark:border-gray-600 shadow-sm'"
                >
                    <!-- Message Text -->
                    <p v-if="msg.komentar" class="whitespace-pre-wrap">{{ msg.komentar }}</p>
                    
                    <!-- File Attachment with Status and Admin Review -->
                    <div v-if="msg.file" class="mt-2 p-2 bg-white/10 rounded-lg flex items-center gap-3 border border-white/20">
                        <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                            <Icon icon="solar:file-text-bold" class="w-6 h-6" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold truncate">{{ msg.file.label }}</p>
                            <a :href="msg.file.file_url" target="_blank" class="text-[10px] underline decoration-dotted hover:no-underline opacity-90">
                                Unduh File
                            </a>
                            <!-- File Status Badge -->
                            <span 
                                v-if="msg.file.status !== undefined"
                                class="ml-2 text-[10px] px-1.5 py-0.5 rounded-full"
                                :class="{
                                    'bg-yellow-100 text-yellow-700': msg.file.status === 0,
                                    'bg-green-100 text-green-700': msg.file.status === 1,
                                    'bg-red-100 text-red-700': msg.file.status === 2
                                }"
                            >
                                {{ msg.file.status === 0 ? 'Menunggu' : (msg.file.status === 1 ? 'Disetujui' : 'Ditolak') }}
                            </span>
                        </div>
                        <!-- Admin Review Buttons -->
                        <div v-if="isAdmin && msg.file.status === 0" class="flex gap-1 shrink-0">
                            <button 
                                @click="approveFile(msg.file)"
                                class="p-1.5 bg-green-500 hover:bg-green-600 text-white rounded-lg transition text-[10px]"
                                title="Setujui"
                            >
                                <Icon icon="solar:check-circle-bold" class="w-4 h-4" />
                            </button>
                            <button 
                                @click="openRejectDialog(msg.file)"
                                class="p-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg transition text-[10px]"
                                title="Tolak"
                            >
                                <Icon icon="solar:close-circle-bold" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-4 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700">
            <!-- File Upload Suggestions (Hidden if Admin or Status Selesai/Ditolak) -->
            <div v-if="!isAdmin && ![4, 9].includes(permohonan.status)" class="flex gap-2 mb-3 overflow-x-auto pb-2">
                <button v-for="type in fileTypes" :key="type.id" 
                    @click="openUploadDialog(type)"
                    class="flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium border transition-all shrink-0"
                    :class="`bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 hover:border-${type.color.split('-')[1]}-400 hover:bg-gray-50`"
                >
                    <Icon :icon="type.icon" :class="type.color" />
                    Upload {{ type.label }}
                </button>
            </div>

            <div class="flex gap-2 items-end">
                <div class="flex-1 relative">
                    <textarea 
                        v-model="newMessage" 
                        @keydown.enter.prevent="sendMessage"
                        placeholder="Ketik pesan..." 
                        rows="1"
                        class="w-full rounded-xl border-gray-200 dark:bg-gray-700 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 resize-none py-3 px-4 max-h-32"
                    ></textarea>
                </div>
                <button 
                    @click="sendMessage" 
                    :disabled="!newMessage.trim() || isSending"
                    class="p-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-lg shadow-blue-600/20 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                >
                    <Icon v-if="isSending" icon="svg-spinners:ring-resize" class="w-5 h-5" />
                    <Icon v-else icon="solar:plain-bold" class="w-5 h-5" />
                </button>
            </div>
        </div>

        <!-- Upload Dialog -->
        <div v-if="uploadDialog" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 w-full max-w-md shadow-xl border border-gray-100 dark:border-gray-700">
                <div class="flex justify-between items-start mb-4">
                    <h4 class="font-bold text-lg dark:text-white">Unggah {{ selectedFileType?.label }}</h4>
                    <button @click="uploadDialog = false" class="text-gray-400 hover:text-gray-600">
                        <Icon icon="solar:close-circle-bold" class="w-6 h-6" />
                    </button>
                </div>
                
                <p class="text-sm text-gray-500 mb-4 dark:text-gray-400">Pilih dokumen yang ingin diunggah. Dokumen akan dikirim ke ruang diskusi.</p>

                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-8 text-center bg-gray-50 dark:bg-gray-900/50 hover:bg-gray-100 transition-colors cursor-pointer relative">
                    <input type="file" ref="fileInput" @change="handleFileUpload" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".pdf,.doc,.docx" />
                    <Icon icon="solar:cloud-upload-bold-duotone" class="w-12 h-12 mx-auto text-blue-500 mb-2" />
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Klik untuk pilih file</p>
                    <p class="text-xs text-gray-400 mt-1">PDF, DOCX (Max 10MB)</p>
                </div>
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
                    Dokumen: <strong>{{ selectedFileForReview?.label }}</strong>
                </p>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2 dark:text-white">Alasan Penolakan (Opsional)</label>
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
/* Custom Scrollbar for Chat */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}
.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 20px;
}
</style>
