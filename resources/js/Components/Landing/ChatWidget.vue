<script setup>
import { ref, nextTick, onMounted } from 'vue'
import { Icon } from '@iconify/vue'
import axios from 'axios'

const isOpen = ref(false)
const userInput = ref('')
const isLoading = ref(false)
const chatContainer = ref(null)

const messages = ref([
    {
        role: 'assistant',
        content: 'Halo! Saya asisten virtual **SiKerja**. Saya menjawab berdasarkan data aplikasi (alur, FAQ, dan laman informasi SiKerja).\n\nSilakan tulis pertanyaan spesifik agar jawaban lebih akurat.'
    }
])

const quickActions = [
    { label: '📋 Cara mengajukan kerjasama', message: 'Bagaimana cara mengajukan kerjasama daerah?' },
    { label: '📄 Syarat dokumen', message: 'Apa saja syarat dokumen untuk mengajukan kerjasama?' },
    { label: '🔄 Alur proses', message: 'Bagaimana alur proses kerjasama daerah dari awal sampai akhir?' },
]

const showQuickActions = ref(true)

const toggle = () => {
    isOpen.value = !isOpen.value
    if (isOpen.value) {
        nextTick(() => scrollToBottom())
    }
}

const scrollToBottom = () => {
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight
    }
}

const sendMessage = async (text = null) => {
    const msg = text || userInput.value.trim()
    if (!msg || isLoading.value) return

    showQuickActions.value = false
    messages.value.push({ role: 'user', content: msg })
    userInput.value = ''
    isLoading.value = true

    await nextTick()
    scrollToBottom()

    // Build history (exclude system/welcome message)
    const history = messages.value
        .slice(1, -1) // exclude welcome message and current message
        .map(m => ({ role: m.role, content: m.content }))

    try {
        const response = await axios.post('/api/chatbot', {
            message: msg,
            history: history,
        })

        if (response.data.success) {
            messages.value.push({ role: 'assistant', content: response.data.reply })
        } else {
            messages.value.push({ role: 'assistant', content: response.data.reply || 'Maaf, terjadi kesalahan.' })
        }
    } catch (error) {
        const errorMsg = error.response?.data?.reply || 'Maaf, layanan AI sedang tidak tersedia. Pastikan Ollama sudah berjalan.'
        messages.value.push({ role: 'assistant', content: errorMsg })
    }

    isLoading.value = false
    await nextTick()
    scrollToBottom()
}

const handleKeydown = (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault()
        sendMessage()
    }
}

const escapeHtml = (text) => {
    return String(text ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;')
}

// Simple markdown-like formatting after escaping untrusted message content.
const formatMessage = (text) => {
    return escapeHtml(text)
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\n/g, '<br>')
        .replace(/^(\d+)\.\s/gm, '<span class="font-semibold">$1.</span> ')
}
</script>

<template>
    <!-- Floating Chat Button -->
    <button
        @click="toggle"
        class="fixed bottom-6 right-6 z-50 w-14 h-14 rounded-full bg-gradient-to-br from-teal-500 to-cyan-600 text-white shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300 flex items-center justify-center group"
        :class="{ 'rotate-0': !isOpen, 'rotate-90': isOpen }"
    >
        <Icon v-if="!isOpen" icon="solar:chat-round-dots-bold" class="w-7 h-7 group-hover:scale-110 transition-transform" />
        <Icon v-else icon="solar:close-circle-bold" class="w-7 h-7" />

        <!-- Pulse animation when closed -->
        <span v-if="!isOpen" class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full animate-pulse border-2 border-white"></span>
    </button>

    <!-- Chat Panel -->
    <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-4 scale-95"
        enter-to-class="opacity-100 translate-y-0 scale-100"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0 scale-100"
        leave-to-class="opacity-0 translate-y-4 scale-95"
    >
        <div
            v-if="isOpen"
            class="fixed bottom-24 right-6 z-50 w-[400px] max-w-[calc(100vw-2rem)] bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 flex flex-col overflow-hidden"
            style="height: 550px; max-height: calc(100vh - 8rem);"
        >
            <!-- Header -->
            <div class="bg-gradient-to-r from-teal-600 to-cyan-600 p-4 flex items-center gap-3 shrink-0">
                <div class="w-10 h-10 bg-white/20 backdrop-blur rounded-full flex items-center justify-center overflow-hidden">
                    <img src="/foto/logo-sikerja.svg" alt="SiKerja" class="w-8 h-8 rounded-full" />
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-white font-bold text-sm">Asisten SiKerja</h3>
                    <p class="text-teal-100 text-xs flex items-center gap-1">
                        <span class="w-2 h-2 bg-amber-300 rounded-full animate-pulse"></span>
                        AI-Powered Assistant
                    </p>
                </div>
                <button @click="toggle" class="text-white/70 hover:text-white transition-colors">
                    <Icon icon="solar:minimize-bold" class="w-5 h-5" />
                </button>
            </div>

            <!-- Chat Messages -->
            <div ref="chatContainer" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50 dark:bg-gray-900/50">
                <div v-for="(msg, i) in messages" :key="i"
                    class="flex"
                    :class="msg.role === 'user' ? 'justify-end' : 'justify-start'"
                >
                    <!-- Assistant avatar -->
                    <div v-if="msg.role === 'assistant'" class="shrink-0 mr-2">
                        <div class="w-7 h-7 rounded-full bg-teal-100 dark:bg-teal-900/50 flex items-center justify-center">
                            <Icon icon="solar:cpu-bolt-bold" class="w-4 h-4 text-teal-600" />
                        </div>
                    </div>

                    <div class="max-w-[80%] px-4 py-2.5 rounded-2xl text-sm leading-relaxed"
                        :class="msg.role === 'user'
                            ? 'bg-teal-600 text-white rounded-br-md'
                            : 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-gray-100 dark:border-gray-700 rounded-bl-md shadow-sm'"
                    >
                        <div v-html="formatMessage(msg.content)"></div>
                    </div>
                </div>

                <!-- Typing Indicator -->
                <div v-if="isLoading" class="flex justify-start">
                    <div class="shrink-0 mr-2">
                        <div class="w-7 h-7 rounded-full bg-teal-100 flex items-center justify-center">
                            <Icon icon="solar:cpu-bolt-bold" class="w-4 h-4 text-teal-600 animate-spin" />
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl rounded-bl-md px-4 py-3 shadow-sm">
                        <div class="flex gap-1.5">
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms;"></span>
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms;"></span>
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms;"></span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div v-if="showQuickActions && !isLoading" class="space-y-2 pt-2">
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Pertanyaan Populer</p>
                    <button
                        v-for="(action, i) in quickActions"
                        :key="i"
                        @click="sendMessage(action.message)"
                        class="w-full text-left px-3 py-2.5 text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl hover:border-teal-300 hover:bg-teal-50 dark:hover:bg-teal-900/20 transition-all duration-200 text-gray-700 dark:text-gray-300"
                    >
                        {{ action.label }}
                    </button>
                </div>
            </div>

            <!-- Input -->
            <div class="p-3 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shrink-0">
                <div class="flex gap-2">
                    <input
                        v-model="userInput"
                        @keydown="handleKeydown"
                        type="text"
                        placeholder="Ketik pertanyaan Anda..."
                        class="flex-1 px-4 py-2.5 text-sm bg-gray-100 dark:bg-gray-800 border-0 rounded-xl focus:ring-2 focus:ring-teal-500 focus:bg-white dark:focus:bg-gray-700 transition-all placeholder-gray-400"
                        :disabled="isLoading"
                    />
                    <button
                        @click="sendMessage()"
                        :disabled="isLoading || !userInput.trim()"
                        class="px-4 py-2.5 bg-teal-600 hover:bg-teal-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white rounded-xl transition-colors shrink-0"
                    >
                        <Icon icon="solar:plain-bold" class="w-5 h-5" />
                    </button>
                </div>
                <p class="text-[10px] text-gray-400 text-center mt-2">Didukung AI berbasis data SiKerja · Tulis pertanyaan spesifik untuk hasil paling akurat</p>
            </div>
        </div>
    </Transition>
</template>
