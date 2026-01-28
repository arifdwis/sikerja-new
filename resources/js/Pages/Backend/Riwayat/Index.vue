<script setup>
import { ref, computed, watch } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useToast } from 'vue-toastification';
import axios from 'axios';
import ControlBar from '../Permohonan/Components/ControlBar.vue';
import PermohonanList from '../Permohonan/Components/PermohonanList.vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import DetailModal from '../Permohonan/Components/DetailModal.vue';

const props = defineProps({
    permohonan: Object,
    share: Object,
    filters: Object,
});

const page = usePage();
// const toast = useToast(); // Unused for now

const filterQuery = ref(props.filters?.search || '');
const filteredData = computed(() => {
    if (!filterQuery.value) return props.permohonan?.data || [];
    const query = filterQuery.value.toLowerCase();
    return (props.permohonan?.data || []).filter(item =>
        item.label?.toLowerCase().includes(query) ||
        item.kode?.toLowerCase().includes(query) ||
        item.nomor_permohonan?.toLowerCase().includes(query) ||
        item.nama_instansi?.toLowerCase().includes(query)
    );
});

const viewMode = ref(localStorage.getItem('riwayatViewMode') || 'grid');
watch(viewMode, (newVal) => {
    localStorage.setItem('riwayatViewMode', newVal);
});

const groupBy = ref(localStorage.getItem('riwayatGroupBy') || 'latest');
watch(groupBy, (newVal) => {
    localStorage.setItem('riwayatGroupBy', newVal);
});

const detailDialog = ref(false);
const selectedItem = ref(null);
const detailData = ref(null);
const loadingDetail = ref(false);

const openDetailModal = async (item) => {
    selectedItem.value = item;
    detailDialog.value = true;
    loadingDetail.value = true;
    try {
        // Use permohonan.show route to get detail
        const response = await axios.get(route('permohonan.show', item.uuid));
        detailData.value = response.data;
    } catch (error) {
        console.error("Failed to fetch details", error);
    } finally {
        loadingDetail.value = false;
    }
};

const isAdmin = computed(() => {
    const userRole = page.props.auth?.user?.roles?.[0]?.name;
    return ['administrator', 'admin', 'superadmin', 'super-admin', 'verifikator', 'tkksd'].includes(userRole);
});

const groupedData = computed(() => {
    const data = filteredData.value;
    if (groupBy.value === 'latest') {
        return { "Riwayat Kerjasama": data };
    } else if (groupBy.value === 'kategori') {
        return data.reduce((acc, item) => {
            const key = item.kategori?.label || "Tanpa Kategori";
            (acc[key] = acc[key] || []).push(item);
            return acc;
        }, {});
    } else if (groupBy.value === 'status') {
        return data.reduce((acc, item) => {
            const key = item.status_label?.label || "Unknown";
            (acc[key] = acc[key] || []).push(item);
            return acc;
        }, {});
    }
    return { "Riwayat Kerjasama": data };
});
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

                <ControlBar 
                    v-model:filterQuery="filterQuery" 
                    v-model:viewMode="viewMode" 
                    v-model:groupBy="groupBy"
                    :showCreate="false"
                />

                <PermohonanList 
                    :groupedData="groupedData"
                    :viewMode="viewMode"
                    :isAdmin="isAdmin"
                    :hasData="filteredData.length > 0"
                    @detail="openDetailModal"
                />
            </div>
        </section>

        <DetailModal 
            :visible="detailDialog" 
            @update:visible="detailDialog = $event"
            :loading="loadingDetail"
            :data="detailData"
            :isAdmin="isAdmin"
            @refresh="openDetailModal(selectedItem)" 
        />
    </AuthenticatedLayout>
</template>
