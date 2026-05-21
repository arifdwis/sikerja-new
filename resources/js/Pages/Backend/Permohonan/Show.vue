<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Icon } from '@iconify/vue';
import DiskusiSection from './Components/DiskusiSection.vue';

const props = defineProps({
    permohonan: Object,
    statusLabels: Object,
});

const statusColors = {
    0: 'bg-yellow-100 text-yellow-800 border-yellow-300 dark:bg-yellow-900 dark:text-yellow-300',
    1: 'bg-blue-100 text-blue-800 border-blue-300 dark:bg-blue-900 dark:text-blue-300',
    2: 'bg-indigo-100 text-indigo-800 border-indigo-300 dark:bg-indigo-900 dark:text-indigo-300',
    3: 'bg-purple-100 text-purple-800 border-purple-300 dark:bg-purple-900 dark:text-purple-300',
    4: 'bg-green-100 text-green-800 border-green-300 dark:bg-green-900 dark:text-green-300',
    9: 'bg-red-100 text-red-800 border-red-300 dark:bg-red-900 dark:text-red-300',
};
</script>

<template>
    <Head :title="permohonan.label" />
    <AuthenticatedLayout>

        <div class="pt-6 pb-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <!-- Breadcrumb -->
                <nav class="mb-5">
                    <ol class="flex items-center text-sm text-gray-500 dark:text-gray-400 flex-wrap gap-y-1">
                        <li class="flex items-center">
                            <Link :href="route('dashboard')" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors flex items-center gap-1.5">
                                <Icon icon="solar:home-2-bold" class="w-4 h-4" />
                                Dashboard
                            </Link>
                        </li>
                        <li class="flex items-center">
                            <Icon icon="solar:alt-arrow-right-linear" class="w-4 h-4 mx-2 text-gray-300 dark:text-gray-600" />
                            <Link :href="route('permohonan.index')" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                Permohonan
                            </Link>
                        </li>
                        <li class="flex items-center">
                            <Icon icon="solar:alt-arrow-right-linear" class="w-4 h-4 mx-2 text-gray-300 dark:text-gray-600" />
                            <span class="font-medium text-gray-800 dark:text-gray-200">Detail</span>
                        </li>
                    </ol>
                </nav>

                <!-- Page Header -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/20 shrink-0">
                                <Icon icon="solar:document-text-bold" class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h1 class="text-xl font-bold text-gray-900 dark:text-white leading-tight">{{ permohonan.label }}</h1>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ permohonan.kode }} &middot; {{ permohonan.nomor_permohonan || 'Belum ada nomor' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span :class="statusColors[permohonan.status]" class="px-4 py-2 text-sm font-semibold rounded-xl border inline-flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-current opacity-60"></span>
                                {{ statusLabels[permohonan.status]?.label }}
                            </span>
                            <Link 
                                v-if="permohonan.status == 0"
                                :href="route('permohonan.edit', permohonan.uuid)" 
                                class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-xl shadow-sm shadow-amber-500/20 hover:shadow-amber-500/30 transition-all duration-200"
                            >
                                <Icon icon="solar:pen-bold" class="w-4 h-4 mr-2" />
                                Edit
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- Diskusi Section (Only in Pembahasan Status) -->
                        <div v-if="permohonan.status === 1">
                            <DiskusiSection :permohonan="permohonan" />
                        </div>
                        
                        <!-- Info Utama -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mr-3">
                                        <Icon icon="solar:document-text-bold" class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    Informasi Permohonan
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-2 gap-x-6 gap-y-5 text-sm">
                                    <div>
                                        <p class="text-gray-400 dark:text-gray-500 text-xs uppercase tracking-wider font-semibold mb-1">Kode Permohonan</p>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ permohonan.kode }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400 dark:text-gray-500 text-xs uppercase tracking-wider font-semibold mb-1">Nomor Permohonan</p>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ permohonan.nomor_permohonan || '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400 dark:text-gray-500 text-xs uppercase tracking-wider font-semibold mb-1">Kategori</p>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ permohonan.kategori?.label || '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400 dark:text-gray-500 text-xs uppercase tracking-wider font-semibold mb-1">Jangka Waktu</p>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ permohonan.jangka_waktu || '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400 dark:text-gray-500 text-xs uppercase tracking-wider font-semibold mb-1">Tanggal Dibuat</p>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $formatDate(permohonan.created_at) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400 dark:text-gray-500 text-xs uppercase tracking-wider font-semibold mb-1">Terakhir Diupdate</p>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $diffForHumans(permohonan.updated_at) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Instansi -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center">
                                    <div class="w-8 h-8 rounded-lg bg-teal-100 dark:bg-teal-900/30 flex items-center justify-center mr-3">
                                        <Icon icon="solar:buildings-bold" class="w-4 h-4 text-teal-600 dark:text-teal-400" />
                                    </div>
                                    Data Instansi
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-2 gap-x-6 gap-y-5 text-sm">
                                    <div class="col-span-2">
                                        <p class="text-gray-400 dark:text-gray-500 text-xs uppercase tracking-wider font-semibold mb-1">Nama Instansi</p>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ permohonan.nama_instansi }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400 dark:text-gray-500 text-xs uppercase tracking-wider font-semibold mb-1">Provinsi</p>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ permohonan.provinsi?.nama || '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400 dark:text-gray-500 text-xs uppercase tracking-wider font-semibold mb-1">Kota</p>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ permohonan.kota?.nama || '-' }}</p>
                                    </div>
                                    <div class="col-span-2">
                                        <p class="text-gray-400 dark:text-gray-500 text-xs uppercase tracking-wider font-semibold mb-1">Alamat</p>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ permohonan.alamat || '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400 dark:text-gray-500 text-xs uppercase tracking-wider font-semibold mb-1">Email</p>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ permohonan.email || '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400 dark:text-gray-500 text-xs uppercase tracking-wider font-semibold mb-1">Telepon</p>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ permohonan.telepon || '-' }}</p>
                                    </div>
                                    <div class="col-span-2">
                                        <p class="text-gray-400 dark:text-gray-500 text-xs uppercase tracking-wider font-semibold mb-1">Website</p>
                                        <a v-if="permohonan.website" :href="permohonan.website" target="_blank" class="font-medium text-blue-600 hover:text-blue-700 hover:underline transition-colors">
                                            {{ permohonan.website }}
                                        </a>
                                        <p v-else class="font-medium text-gray-900 dark:text-white">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Detail Kerjasama -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center">
                                    <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center mr-3">
                                        <Icon icon="solar:clipboard-list-bold" class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                                    </div>
                                    Detail Kerjasama
                                </h3>
                            </div>
                            <div class="p-6 space-y-5 text-sm">
                                <div>
                                    <p class="text-gray-400 dark:text-gray-500 text-xs uppercase tracking-wider font-semibold mb-2">Latar Belakang</p>
                                    <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4 border border-gray-100 dark:border-gray-700" v-html="permohonan.latar_belakang || '-'"></div>
                                </div>
                                <div>
                                    <p class="text-gray-400 dark:text-gray-500 text-xs uppercase tracking-wider font-semibold mb-2">Maksud & Tujuan</p>
                                    <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4 border border-gray-100 dark:border-gray-700" v-html="permohonan.maksud_tujuan || '-'"></div>
                                </div>
                                <div>
                                    <p class="text-gray-400 dark:text-gray-500 text-xs uppercase tracking-wider font-semibold mb-2">Ruang Lingkup</p>
                                    <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4 border border-gray-100 dark:border-gray-700" v-html="permohonan.ruang_lingkup || '-'"></div>
                                </div>
                                <div>
                                    <p class="text-gray-400 dark:text-gray-500 text-xs uppercase tracking-wider font-semibold mb-2">Jangka Waktu</p>
                                    <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300">
                                        {{ permohonan.jangka_waktu || '-' }}
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-gray-400 dark:text-gray-500 text-xs uppercase tracking-wider font-semibold mb-2">Tanggal Mulai</p>
                                        <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300">
                                            {{ permohonan.tanggal_mulai ? $formatDate(permohonan.tanggal_mulai) : '-' }}
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-gray-400 dark:text-gray-500 text-xs uppercase tracking-wider font-semibold mb-2">Tanggal Berakhir</p>
                                        <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300">
                                            {{ permohonan.tanggal_berakhir ? $formatDate(permohonan.tanggal_berakhir) : '-' }}
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-gray-400 dark:text-gray-500 text-xs uppercase tracking-wider font-semibold mb-2">Manfaat</p>
                                    <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4 border border-gray-100 dark:border-gray-700" v-html="permohonan.manfaat || '-'"></div>
                                </div>
                                <div>
                                    <p class="text-gray-400 dark:text-gray-500 text-xs uppercase tracking-wider font-semibold mb-2">Pembiayaan</p>
                                    <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4 border border-gray-100 dark:border-gray-700" v-html="permohonan.pembiayaan || '-'"></div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        
                        <!-- Kontak Pemohon -->
                        <div v-if="permohonan.pemohon1 || permohonan.pemohon2" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center">
                                    <div class="w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center mr-3">
                                        <Icon icon="solar:phone-bold" class="w-4 h-4 text-green-600 dark:text-green-400" />
                                    </div>
                                    Kontak Pemohon
                                </h3>
                            </div>
                            <div class="p-5 space-y-3">
                                <!-- Pemohon 1 -->
                                <div v-if="permohonan.pemohon1" class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-100 dark:border-gray-600">
                                    <p class="text-[11px] uppercase tracking-wider font-semibold text-gray-400 dark:text-gray-500 mb-1">Pemohon 1</p>
                                    <p class="font-semibold text-gray-900 dark:text-white text-sm mb-3">{{ permohonan.pemohon1.name }}</p>
                                    <a 
                                        v-if="permohonan.pemohon1.phone"
                                        :href="`https://wa.me/${permohonan.pemohon1.phone.replace(/^0/, '62').replace(/[^0-9]/g, '')}`"
                                        target="_blank"
                                        class="inline-flex items-center gap-2 px-3.5 py-2 bg-green-500 hover:bg-green-600 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm shadow-green-500/20"
                                    >
                                        <Icon icon="logos:whatsapp-icon" class="w-4 h-4" />
                                        Hubungi via WhatsApp
                                    </a>
                                    <p v-else class="text-xs text-gray-400 italic">No. HP tidak tersedia</p>
                                </div>
                                <!-- Pemohon 2 -->
                                <div v-if="permohonan.pemohon2" class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-100 dark:border-gray-600">
                                    <p class="text-[11px] uppercase tracking-wider font-semibold text-gray-400 dark:text-gray-500 mb-1">Pemohon 2</p>
                                    <p class="font-semibold text-gray-900 dark:text-white text-sm mb-3">{{ permohonan.pemohon2.name }}</p>
                                    <a 
                                        v-if="permohonan.pemohon2.phone"
                                        :href="`https://wa.me/${permohonan.pemohon2.phone.replace(/^0/, '62').replace(/[^0-9]/g, '')}`"
                                        target="_blank"
                                        class="inline-flex items-center gap-2 px-3.5 py-2 bg-green-500 hover:bg-green-600 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm shadow-green-500/20"
                                    >
                                        <Icon icon="logos:whatsapp-icon" class="w-4 h-4" />
                                        Hubungi via WhatsApp
                                    </a>
                                    <p v-else class="text-xs text-gray-400 italic">No. HP tidak tersedia</p>
                                </div>
                            </div>
                        </div>

                        <!-- Files -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center">
                                    <div class="w-8 h-8 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center mr-3">
                                        <Icon icon="solar:folder-bold" class="w-4 h-4 text-orange-600 dark:text-orange-400" />
                                    </div>
                                    Dokumen Lampiran
                                </h3>
                            </div>
                            <div class="p-5">
                                <div v-if="permohonan.files?.length" class="space-y-2">
                                    <a 
                                        v-for="file in permohonan.files" 
                                        :key="file.id"
                                        :href="file.file_url"
                                        target="_blank"
                                        class="flex items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-100 dark:border-gray-600 hover:bg-blue-50 dark:hover:bg-gray-600 hover:border-blue-200 dark:hover:border-blue-500/30 transition-all group"
                                    >
                                        <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mr-3 shrink-0 group-hover:bg-blue-200 dark:group-hover:bg-blue-900/50 transition-colors">
                                            <Icon icon="solar:file-text-bold" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-gray-900 dark:text-white text-sm truncate">{{ file.label }}</p>
                                            <p class="text-xs text-gray-400 truncate">{{ file.deskripsi || 'Tidak ada deskripsi' }}</p>
                                        </div>
                                        <Icon icon="solar:download-bold" class="w-5 h-5 text-gray-300 group-hover:text-blue-500 transition-colors shrink-0" />
                                    </a>
                                </div>
                                <div v-else class="text-center py-6">
                                    <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
                                        <Icon icon="solar:folder-open-bold" class="w-6 h-6 text-gray-300 dark:text-gray-500" />
                                    </div>
                                    <p class="text-gray-400 dark:text-gray-500 text-sm">Belum ada dokumen</p>
                                </div>
                            </div>
                        </div>

                        <!-- Timeline/Riwayat -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center mr-3">
                                        <Icon icon="solar:history-bold" class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                                    </div>
                                    Riwayat
                                </h3>
                            </div>
                            <div class="p-5">
                                <ol v-if="permohonan.historis?.length" class="relative border-l-2 border-gray-200 dark:border-gray-700 ml-2">
                                    <li 
                                        v-for="histori in permohonan.historis.slice(0, 5)" 
                                        :key="histori.id"
                                        class="mb-6 ml-5 last:mb-0"
                                    >
                                        <div class="absolute w-3 h-3 bg-blue-500 rounded-full -left-[7px] border-2 border-white dark:border-gray-800 ring-4 ring-blue-50 dark:ring-blue-900/20"></div>
                                        <time class="text-[11px] uppercase tracking-wider font-semibold text-gray-400 dark:text-gray-500">{{ $formatDate(histori.created_at) }}</time>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ histori.deskripsi }}</p>
                                        <p v-if="histori.operator" class="text-xs text-gray-400 mt-0.5">oleh {{ histori.operator.name }}</p>
                                    </li>
                                </ol>
                                <div v-else class="text-center py-6">
                                    <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
                                        <Icon icon="solar:history-bold" class="w-6 h-6 text-gray-300 dark:text-gray-500" />
                                    </div>
                                    <p class="text-gray-400 dark:text-gray-500 text-sm">Belum ada riwayat</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
