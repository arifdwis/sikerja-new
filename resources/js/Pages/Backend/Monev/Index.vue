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
        0: { label: 'Draft', class: 'bg-gray-100 text-gray-700 border-gray-200' },
        1: { label: 'Menunggu Review', class: 'bg-orange-100 text-orange-700 border-orange-200' },
        2: { label: 'Sudah Direview', class: 'bg-green-100 text-green-700 border-green-200' },
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
                            {{ pendingPermohonans.length }} Menunggu
                        </span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        <div 
                            v-for="item in pendingPermohonans" 
                            :key="item.id"
                            class="bg-gradient-to-br from-orange-50 to-amber-50 dark:from-gray-800 dark:to-gray-700 rounded-xl border border-orange-200 dark:border-gray-600 p-5 hover:shadow-lg hover:border-orange-300 transition-all"
                        >
                            <div class="flex items-start justify-between mb-3">
                                <span class="text-xs font-mono text-gray-500 bg-white/50 px-2 py-0.5 rounded">{{ item.nomor_permohonan || item.kode }}</span>
                                <span class="px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 rounded-full border border-green-200">
                                    Selesai
                                </span>
                            </div>
                            
                            <h3 class="font-bold text-gray-900 dark:text-white mb-1 line-clamp-2">
                                {{ item.label || 'Tanpa Judul' }}
                            </h3>
                            <p class="text-sm text-gray-500 mb-3">{{ item.nama_instansi }}</p>
                            
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-400">
                                    <Icon icon="solar:tag-linear" class="w-4 h-4 inline-block mr-1" />
                                    {{ item.kategori?.label || 'Umum' }}
                                </span>
                                <Link 
                                    :href="route('monev.create', { permohonan: item.uuid })"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white text-xs font-medium rounded-lg transition-colors"
                                >
                                    <Icon icon="solar:pen-new-square-linear" class="w-4 h-4" />
                                    Isi Monev
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <div v-if="!isAdmin && hasPendingPermohonans && hasMonevData" class="border-t border-gray-200 dark:border-gray-700 my-8"></div>

                <!-- Riwayat Monev Section -->
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <Icon icon="solar:document-text-bold-duotone" class="w-6 h-6 text-blue-500" />
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Riwayat Monev</h3>
                    </div>

                    <!-- Control Bar -->
                    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
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

                    <!-- Data Grid -->
                    <div v-if="hasMonevData" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        <Link 
                            v-for="item in datas.data" 
                            :key="item.id"
                            :href="route('monev.show', item.uuid)"
                            class="block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 hover:shadow-lg hover:border-blue-300 transition-all group"
                        >
                            <div class="flex items-start justify-between mb-3">
                                <span class="text-xs font-mono text-gray-500">{{ item.kode_monev }}</span>
                                <span 
                                    class="px-2 py-0.5 text-xs font-medium rounded-full border"
                                    :class="getStatusBadge(item.status).class"
                                >
                                    {{ getStatusBadge(item.status).label }}
                                </span>
                            </div>
                            
                            <h3 class="font-bold text-gray-900 dark:text-white mb-1 group-hover:text-blue-600 transition-colors line-clamp-2">
                                {{ item.permohonan?.label || 'Tanpa Judul' }}
                            </h3>
                            <p class="text-sm text-gray-500 mb-3">{{ item.permohonan?.nama_instansi }}</p>
                            
                            <div class="flex items-center justify-between text-xs text-gray-400">
                                <span>{{ formatDate(item.tanggal_evaluasi) }}</span>
                                <span v-if="item.rekomendasi_lanjutan" class="font-medium" :class="{
                                    'text-green-600': item.rekomendasi_lanjutan === 'Dilanjutkan',
                                    'text-blue-600': item.rekomendasi_lanjutan === 'Diperluas',
                                    'text-red-600': item.rekomendasi_lanjutan === 'Dihentikan',
                                }">
                                    {{ item.rekomendasi_lanjutan }}
                                </span>
                            </div>
                        </Link>
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
