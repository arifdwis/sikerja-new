<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
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
        <template #header>
            <div class="flex flex-col gap-4">
                <Breadcrumb :crumbs="[
                    { label: 'Dashboard', route: 'dashboard' },
                    { label: 'Daftar Permohonan', route: 'permohonan.index' },
                    { label: 'Detail', route: null }
                ]" />

                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-white">
                            {{ permohonan.label }}
                        </h2>
                    </div>
                    <div class="flex items-center gap-2">
                        <span :class="statusColors[permohonan.status]" class="px-3 py-1.5 text-sm font-medium rounded-full border">
                            {{ statusLabels[permohonan.status]?.label }}
                        </span>
                        <Link 
                            :href="route('permohonan.edit', permohonan.uuid)" 
                            class="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium rounded-lg transition"
                        >
                            <Icon icon="solar:pen-bold" class="w-4 h-4 mr-2" />
                            Edit
                        </Link>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- Diskusi Section (Only in Pembahasan Status) -->
                        <div v-if="permohonan.status === 1"> <!-- STATUS_PEMBAHASAN -->
                            <DiskusiSection :permohonan="permohonan" />
                        </div>
                        
                        <!-- Info Utama -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                <Icon icon="solar:document-text-bold" class="w-5 h-5 mr-2 text-blue-500" />
                                Informasi Permohonan
                            </h3>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Kode Permohonan</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ permohonan.kode }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Nomor Permohonan</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ permohonan.nomor_permohonan || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Kategori</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ permohonan.kategori?.label || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Jangka Waktu</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ permohonan.jangka_waktu || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Tanggal Dibuat</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $formatDate(permohonan.created_at) }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Terakhir Diupdate</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $diffForHumans(permohonan.updated_at) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Instansi -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                <Icon icon="solar:buildings-bold" class="w-5 h-5 mr-2 text-teal-500" />
                                Data Instansi
                            </h3>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div class="col-span-2">
                                    <p class="text-gray-500 dark:text-gray-400">Nama Instansi</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ permohonan.nama_instansi }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Provinsi</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ permohonan.provinsi?.nama || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Kota</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ permohonan.kota?.nama || '-' }}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-gray-500 dark:text-gray-400">Alamat</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ permohonan.alamat || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Email</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ permohonan.email || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Telepon</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ permohonan.telepon || '-' }}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-gray-500 dark:text-gray-400">Website</p>
                                    <a v-if="permohonan.website" :href="permohonan.website" target="_blank" class="font-medium text-blue-600 hover:underline">
                                        {{ permohonan.website }}
                                    </a>
                                    <p v-else class="font-medium text-gray-900 dark:text-white">-</p>
                                </div>
                            </div>
                        </div>

                        <!-- Detail Kerjasama -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                <Icon icon="solar:clipboard-list-bold" class="w-5 h-5 mr-2 text-purple-500" />
                                Detail Kerjasama
                            </h3>
                            <div class="space-y-4 text-sm">
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400 font-medium mb-1">Latar Belakang</p>
                                    <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300" v-html="permohonan.latar_belakang || '-'"></div>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400 font-medium mb-1">Maksud & Tujuan</p>
                                    <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300" v-html="permohonan.maksud_tujuan || '-'"></div>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400 font-medium mb-1">Ruang Lingkup</p>
                                    <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300" v-html="permohonan.ruang_lingkup || '-'"></div>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400 font-medium mb-1">Manfaat</p>
                                    <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300" v-html="permohonan.manfaat || '-'"></div>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400 font-medium mb-1">Pembiayaan</p>
                                    <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300" v-html="permohonan.pembiayaan || '-'"></div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        
                        <!-- Files -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                <Icon icon="solar:folder-bold" class="w-5 h-5 mr-2 text-orange-500" />
                                Dokumen Lampiran
                            </h3>
                            <div v-if="permohonan.files?.length" class="space-y-2">
                                <a 
                                    v-for="file in permohonan.files" 
                                    :key="file.id"
                                    :href="file.file_url"
                                    target="_blank"
                                    class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition"
                                >
                                    <Icon icon="solar:file-text-bold" class="w-8 h-8 text-blue-500 mr-3" />
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900 dark:text-white text-sm truncate">{{ file.label }}</p>
                                        <p class="text-xs text-gray-500">{{ file.deskripsi || 'Tidak ada deskripsi' }}</p>
                                    </div>
                                    <Icon icon="solar:download-bold" class="w-5 h-5 text-gray-400" />
                                </a>
                            </div>
                            <p v-else class="text-gray-500 text-sm text-center py-4">
                                Belum ada dokumen
                            </p>
                        </div>

                        <!-- Timeline/Riwayat -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                <Icon icon="solar:history-bold" class="w-5 h-5 mr-2 text-green-500" />
                                Riwayat
                            </h3>
                            <ol v-if="permohonan.historis?.length" class="relative border-l border-gray-200 dark:border-gray-700">
                                <li 
                                    v-for="histori in permohonan.historis.slice(0, 5)" 
                                    :key="histori.id"
                                    class="mb-6 ml-4"
                                >
                                    <div class="absolute w-3 h-3 bg-blue-500 rounded-full -left-1.5 border border-white dark:border-gray-800"></div>
                                    <time class="text-xs text-gray-500 dark:text-gray-400">{{ $formatDate(histori.created_at) }}</time>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ histori.deskripsi }}</p>
                                    <p v-if="histori.operator" class="text-xs text-gray-500">oleh {{ histori.operator.name }}</p>
                                </li>
                            </ol>
                            <p v-else class="text-gray-500 text-sm text-center py-4">
                                Belum ada riwayat
                            </p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
