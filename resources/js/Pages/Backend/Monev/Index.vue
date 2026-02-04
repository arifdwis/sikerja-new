<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Icon } from '@iconify/vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import Button from 'primevue/button';

const props = defineProps({
    datas: Object,
    pendingPermohonans: Array,
    share: Object,
    filters: Object,
    isAdmin: Boolean,
});

const filterQuery = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status ?? '');

let searchTimeout;
const doSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.visit(route('monev.index'), {
            data: { search: filterQuery.value, status: statusFilter.value },
            preserveState: true,
            preserveScroll: true,
            only: ['datas']
        });
    }, 500);
};

const getStatusBadge = (status) => {
    const badges = {
        0: { label: 'Draft', class: 'bg-gray-100 text-gray-700' },
        1: { label: 'Menunggu Review', class: 'bg-orange-100 text-orange-700' },
        2: { label: 'Sudah Direview', class: 'bg-green-100 text-green-700' },
    };
    return badges[status] || badges[0];
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
};

const hasPendingPermohonans = computed(() => props.pendingPermohonans?.length > 0);
const hasMonevData = computed(() => props.datas?.data?.length > 0);
</script>

<template>
    <Head :title="share.title" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                {{ share.title }}
            </h2>
        </template>

        <section class="py-12">
            <div class="mx-auto max-w-full px-6 lg:px-8">
                <Breadcrumb class="mb-6" />

                <!-- Pending Permohonan Section (For Non-Admin Users) -->
                <div v-if="!isAdmin && hasPendingPermohonans" class="mb-8">
                    <div class="flex items-center gap-3 mb-4">
                        <Icon icon="solar:clipboard-check-bold-duotone" class="w-6 h-6 text-orange-500" />
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Kerjasama Perlu Diisi Monev</h3>
                        <span class="px-2 py-0.5 text-xs font-medium bg-orange-100 text-orange-600 rounded-full">
                            {{ pendingPermohonans.length }}
                        </span>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">No</th>
                                    <th class="px-4 py-3">Kode</th>
                                    <th class="px-4 py-3">Judul Kerjasama</th>
                                    <th class="px-4 py-3">Instansi</th>
                                    <th class="px-4 py-3">Kategori</th>
                                    <th class="px-4 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="(item, index) in pendingPermohonans" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-3 text-gray-500">{{ index + 1 }}</td>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ item.nomor_permohonan || item.kode }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ item.label || '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ item.nama_instansi }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ item.kategori?.label || 'Umum' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <Link 
                                            :href="route('monev.create', { permohonan: item.uuid })"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white text-xs font-medium rounded-lg transition-colors"
                                        >
                                            <Icon icon="solar:pen-new-square-linear" class="w-4 h-4" />
                                            Isi Monev
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Divider -->
                <div v-if="!isAdmin && hasPendingPermohonans" class="border-t border-gray-200 dark:border-gray-700 my-8"></div>

                <!-- Riwayat Monev Section -->
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <Icon icon="solar:document-text-bold-duotone" class="w-6 h-6 text-blue-500" />
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Riwayat Monev</h3>
                    </div>

                    <!-- Control Bar -->
                    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <Icon icon="solar:magnifer-linear" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 w-5 h-5" />
                                <input 
                                    v-model="filterQuery"
                                    @input="doSearch"
                                    type="text" 
                                    placeholder="Cari kode, instansi..."
                                    class="pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-64"
                                />
                            </div>
                            <select 
                                v-model="statusFilter"
                                @change="doSearch"
                                class="border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">Semua Status</option>
                                <option value="0">Draft</option>
                                <option value="1">Menunggu Review</option>
                                <option value="2">Sudah Direview</option>
                            </select>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div v-if="hasMonevData" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">No</th>
                                    <th class="px-4 py-3">Kode Monev</th>
                                    <th class="px-4 py-3">Kerjasama</th>
                                    <th class="px-4 py-3">Instansi</th>
                                    <th class="px-4 py-3">Tgl Evaluasi</th>
                                    <th class="px-4 py-3">Rekomendasi</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="(item, index) in datas.data" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-3 text-gray-500">{{ (datas.current_page - 1) * datas.per_page + index + 1 }}</td>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ item.kode_monev }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ item.permohonan?.label || '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ item.permohonan?.nama_instansi }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ formatDate(item.tanggal_evaluasi) }}</td>
                                    <td class="px-4 py-3">
                                        <span v-if="item.rekomendasi_lanjutan" class="text-xs font-medium px-2 py-0.5 rounded" :class="{
                                            'bg-green-100 text-green-700': item.rekomendasi_lanjutan === 'Dilanjutkan',
                                            'bg-blue-100 text-blue-700': item.rekomendasi_lanjutan === 'Diperluas',
                                            'bg-red-100 text-red-700': item.rekomendasi_lanjutan === 'Dihentikan',
                                        }">
                                            {{ item.rekomendasi_lanjutan }}
                                        </span>
                                        <span v-else class="text-gray-400">-</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span 
                                            class="px-2 py-0.5 text-xs font-medium rounded"
                                            :class="getStatusBadge(item.status).class"
                                        >
                                            {{ getStatusBadge(item.status).label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <Link 
                                            :href="route('monev.show', item.uuid)"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-xs font-medium rounded-lg transition-colors"
                                        >
                                            <Icon icon="solar:eye-linear" class="w-4 h-4" />
                                            Detail
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl border border-gray-200">
                        <Icon icon="solar:clipboard-list-bold-duotone" class="w-16 h-16 mx-auto text-gray-300 mb-4" />
                        <p class="text-gray-500 mb-2">Belum ada riwayat Monev</p>
                        <p v-if="!isAdmin && hasPendingPermohonans" class="text-sm text-gray-400">
                            Silakan isi Monev untuk kerjasama yang sudah selesai di atas
                        </p>
                    </div>

                    <!-- Pagination -->
                    <div v-if="datas.links && datas.links.length > 3" class="mt-6 flex justify-center gap-1">
                        <template v-for="link in datas.links" :key="link.label">
                            <Link 
                                v-if="link.url"
                                :href="link.url"
                                class="px-3 py-1 rounded border text-sm"
                                :class="link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white border-gray-200 hover:bg-gray-50'"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
