<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import { Icon } from '@iconify/vue';
import { computed, onMounted, ref } from 'vue';
import { useToast } from 'vue-toastification';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import axios from 'axios';
import CreateModal from './Components/CreateModal.vue';
import DetailModal from './Components/DetailModal.vue';
import EditModal from './Components/EditModal.vue';

const props = defineProps({
    datas: Object,
    kategoris: Array,
    opds: Array,
    filters: Object,
    share: Object,
});

const search = ref(props.filters?.search || '');
const createDialog = ref(false);
const detailDialog = ref(false);
const detailData = ref(null);
const loadingDetail = ref(false);
const editDialog = ref(false);
const editData = ref(null);
const editOpdIds = ref([]);
const loadingEdit = ref(false);
const deleteDialog = ref(false);
const deleteTarget = ref(null);
const deleteConfirmation = ref('');
const toast = useToast();

const filteredDatas = computed(() => props.datas?.data || []);

const submitSearch = () => {
    router.get(route('kerjasama-manual.index'), { search: search.value }, { preserveState: true });
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

onMounted(() => {
    if (props.filters?.create) {
        createDialog.value = true;
    }
    if (props.filters?.detail) {
        openDetail({ uuid: props.filters.detail });
    }
    if (props.filters?.edit) {
        openEdit({ uuid: props.filters.edit });
    }
});

const openDetail = async (item) => {
    detailDialog.value = true;
    loadingDetail.value = true;

    try {
        const response = await axios.get(route('kerjasama-manual.show', item.uuid), {
            headers: { Accept: 'application/json' },
        });
        detailData.value = response.data;
    } catch (error) {
        detailDialog.value = false;
        toast.error('Detail kerjasama manual gagal dimuat.');
    } finally {
        loadingDetail.value = false;
    }
};

const openEdit = async (item) => {
    detailDialog.value = false;
    editDialog.value = true;
    loadingEdit.value = true;

    try {
        const response = await axios.get(route('kerjasama-manual.edit', item.uuid), {
            headers: { Accept: 'application/json' },
        });
        editData.value = response.data.data;
        editOpdIds.value = response.data.selectedOpdIds || [];
    } catch (error) {
        editDialog.value = false;
        toast.error('Form edit kerjasama manual gagal dimuat.');
    } finally {
        loadingEdit.value = false;
    }
};

const requestDestroy = (item) => {
    deleteTarget.value = item;
    deleteConfirmation.value = '';
    deleteDialog.value = true;
    toast.warning('Konfirmasi penghapusan diperlukan. Ketik "hapus" untuk melanjutkan.');
};

const closeDeleteDialog = () => {
    deleteDialog.value = false;
    deleteTarget.value = null;
    deleteConfirmation.value = '';
};

const destroy = () => {
    if (!deleteTarget.value || deleteConfirmation.value.trim().toLowerCase() !== 'hapus') {
        toast.error('Ketik "hapus" untuk mengonfirmasi penghapusan.');
        return;
    }

    router.delete(route('kerjasama-manual.destroy', deleteTarget.value.uuid), {
        preserveScroll: true,
        onSuccess: () => {
            detailDialog.value = false;
            closeDeleteDialog();
            toast.success('Kerjasama manual berhasil dihapus.');
        },
    });
};

const formatOpd = (opd) => opd.singkatan || opd.nama;
</script>

<template>
    <Head title="Kerjasama Manual" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">{{ share.title }}</h2>
        </template>

        <section class="py-8">
            <div class="mx-auto max-w-full px-6 lg:px-8">
                <Breadcrumb class="mb-6" />

                <div class="mb-5 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <div class="flex items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-700 dark:bg-gray-700 dark:text-gray-100">
                                <Icon icon="solar:archive-bold" class="h-5 w-5" />
                            </span>
                            <div>
                                <h3 class="text-base font-semibold text-slate-900 dark:text-white">Register PKS Manual</h3>
                                <p class="text-sm text-slate-500 dark:text-gray-300">Arsip PKS final yang dicatat langsung di luar proses permohonan.</p>
                            </div>
                        </div>
                        <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center xl:w-auto">
                            <form @submit.prevent="submitSearch" class="flex w-full gap-2 sm:w-auto">
                                <div class="relative w-full sm:w-80">
                                    <Icon icon="lucide:search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                    <input
                                        v-model="search"
                                        type="text"
                                        placeholder="Cari instansi, perihal, nomor PKS..."
                                        class="h-11 w-full rounded-lg border border-gray-300 py-0 pl-10 pr-4 text-sm focus:border-slate-500 focus:ring-slate-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    />
                                </div>
                                <button type="submit" class="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-3 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600">
                                    Terapkan
                                </button>
                            </form>
                            <button @click="createDialog = true" class="inline-flex h-11 items-center justify-center gap-2 whitespace-nowrap rounded-lg bg-slate-900 px-4 text-sm font-medium text-white hover:bg-slate-700">
                                <Icon icon="lucide:plus" class="h-4 w-4" />
                                Tambah PKS Manual
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="!filteredDatas.length" class="rounded-xl border border-dashed border-slate-200 bg-white p-12 text-center dark:border-gray-700 dark:bg-gray-800">
                    <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-700/50">
                        <Icon icon="solar:document-text-bold-duotone" class="h-12 w-12 text-gray-400" />
                    </div>
                    <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">Belum ada Kerjasama Manual</h3>
                    <p class="mx-auto mb-8 max-w-md text-gray-500 dark:text-gray-400">Data PKS yang diinput manual akan muncul di halaman ini.</p>
                    <button @click="createDialog = true" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-6 py-3 text-sm font-medium text-white hover:bg-slate-700">
                        <Icon icon="lucide:plus" class="h-4 w-4" />
                        Tambah Kerjasama Manual
                    </button>
                </div>

                <div v-else class="space-y-3">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-slate-900 dark:text-white">Daftar PKS Manual</h3>
                            <p class="text-sm text-slate-500 dark:text-gray-300">Gunakan nomor PKS dan periode sebagai acuan arsip.</p>
                        </div>
                        <span class="text-sm font-medium text-slate-600 dark:text-gray-300">{{ datas.total }} Data</span>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <DataTable :value="filteredDatas" dataKey="id" showGridlines stripedRows tableStyle="min-width: 62rem" class="p-datatable-sm text-sm">
                            <template #empty>
                                <div class="p-4 text-center text-gray-500">Tidak ada PKS manual.</div>
                            </template>

                            <Column field="nomor_pks" header="No PKS" sortable style="width: 12rem">
                                <template #body="{ data }">
                                    <span class="font-mono text-xs font-semibold text-slate-700 dark:text-slate-100">{{ data.nomor_pks || '-' }}</span>
                                </template>
                            </Column>

                            <Column field="label" header="PKS Final" sortable>
                                <template #body="{ data }">
                                    <button @click="openDetail(data)" class="text-left text-sm font-semibold text-slate-900 hover:text-slate-600 dark:text-white">
                                        {{ data.label }}
                                    </button>
                                    <p class="mt-1 text-xs text-gray-500">{{ data.kategori?.label || 'Kerjasama Manual' }}</p>
                                </template>
                            </Column>

                            <Column field="nama_instansi" header="Mitra" sortable style="width: 15rem">
                                <template #body="{ data }">
                                    <span class="text-sm text-slate-700 dark:text-gray-100">{{ data.nama_instansi || '-' }}</span>
                                </template>
                            </Column>

                            <Column header="OPD" style="width: 18rem">
                                <template #body="{ data }">
                                    <div v-if="data.opds?.length" class="flex flex-wrap gap-1">
                                        <span v-for="opd in data.opds.slice(0, 2)" :key="opd.id" class="rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-xs text-slate-600 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                            {{ formatOpd(opd) }}
                                        </span>
                                        <span v-if="data.opds.length > 2" class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-500 dark:bg-gray-700 dark:text-gray-200">
                                            +{{ data.opds.length - 2 }}
                                        </span>
                                    </div>
                                    <span v-else class="text-xs italic text-gray-400">Belum ada OPD</span>
                                </template>
                            </Column>

                            <Column header="Periode" style="width: 13rem">
                                <template #body="{ data }">
                                    <div class="text-sm font-medium text-slate-700 dark:text-gray-100">{{ formatDate(data.tanggal_mulai) }}</div>
                                    <div class="mt-1 text-xs text-gray-500">s.d. {{ formatDate(data.tanggal_berakhir) }}</div>
                                </template>
                            </Column>

                            <Column header="Aksi" style="width: 9.5rem">
                                <template #body="{ data }">
                                    <div class="flex items-center gap-2">
                                        <Button icon="pi pi-eye" severity="secondary" rounded outlined size="small" v-tooltip="'Detail'" @click="openDetail(data)" />
                                        <Button icon="pi pi-pencil" severity="secondary" rounded outlined size="small" v-tooltip="'Edit'" @click="openEdit(data)" />
                                        <Button icon="pi pi-trash" severity="danger" rounded outlined size="small" v-tooltip="'Hapus'" @click="requestDestroy(data)" />
                                    </div>
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </div>

                <div v-if="datas.links?.length > 3" class="mt-6 flex flex-wrap justify-center gap-1">
                    <Link
                        v-for="link in datas.links"
                        :key="link.label"
                        :href="link.url || '#'"
                        v-html="link.label"
                        :class="['px-3 py-1 text-sm rounded',
                            link.active ? 'bg-slate-900 text-white' : 'bg-gray-100 dark:bg-gray-700 hover:bg-gray-200',
                            !link.url ? 'opacity-50 pointer-events-none' : '']"
                    />
                </div>
            </div>
        </section>

        <CreateModal
            :visible="createDialog"
            :kategoris="kategoris"
            :opds="opds"
            @update:visible="createDialog = $event"
        />

        <DetailModal
            :visible="detailDialog"
            :loading="loadingDetail"
            :data="detailData"
            @update:visible="detailDialog = $event"
            @delete="requestDestroy"
            @edit="openEdit"
        />

        <EditModal
            :visible="editDialog"
            :loading="loadingEdit"
            :data="editData"
            :kategoris="kategoris"
            :opds="opds"
            :selected-opd-ids="editOpdIds"
            @update:visible="editDialog = $event"
        />

        <Dialog v-model:visible="deleteDialog" modal header="Konfirmasi Hapus Kerjasama" :style="{ width: '480px' }" :breakpoints="{ '560px': '95vw' }">
            <div class="space-y-4">
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-100">
                    Data <span class="font-semibold">{{ deleteTarget?.label || 'kerjasama manual' }}</span> akan dihapus permanen.
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Ketik <span class="font-bold">hapus</span> untuk melanjutkan
                    </label>
                    <input
                        v-model="deleteConfirmation"
                        type="text"
                        autocomplete="off"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        @keyup.enter="destroy"
                    />
                </div>
                <div class="flex justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-700">
                    <button @click="closeDeleteDialog" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                        Batal
                    </button>
                    <button
                        @click="destroy"
                        :disabled="deleteConfirmation.trim().toLowerCase() !== 'hapus'"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:bg-gray-400"
                    >
                        Hapus
                    </button>
                </div>
            </div>
        </Dialog>
    </AuthenticatedLayout>
</template>
