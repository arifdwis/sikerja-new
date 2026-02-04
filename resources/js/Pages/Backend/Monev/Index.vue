<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Icon } from '@iconify/vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import Button from 'primevue/button';

const props = defineProps({
    datas: Object,
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
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <Breadcrumb class="mb-6" />

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
                    <Link v-if="!isAdmin" :href="route('monev.create')">
                        <Button label="Isi Form Monev" icon="pi pi-plus" />
                    </Link>
                </div>

                <!-- Data Grid -->
                <div v-if="datas.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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
                    <p class="text-gray-500">Belum ada data Monev</p>
                    <Link v-if="!isAdmin" :href="route('monev.create')" class="mt-4 inline-block">
                        <Button label="Isi Form Monev Pertama" icon="pi pi-plus" size="small" />
                    </Link>
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
        </section>
    </AuthenticatedLayout>
</template>
