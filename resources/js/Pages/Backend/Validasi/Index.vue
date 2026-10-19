<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Icon } from '@iconify/vue';
import { useToast } from 'vue-toastification';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import ControlBar from '../Permohonan/Components/ControlBar.vue';
import GridItem from '../Pembahasan/Components/PembahasanGridItem.vue';
import ListItem from '../Permohonan/Components/ListItem.vue';
import DetailModal from '../Permohonan/Components/DetailModal.vue';
import axios from 'axios';

const props = defineProps({
    datas: Object,
    share: Object,
    filters: Object,
    counts: Object,
    currentTab: String,
    isKabag: Boolean,
});

const toast = useToast();

// Pagination & Search
const filterQuery = ref(props.filters?.search || '');
let searchTimeout;
watch(filterQuery, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.visit(route(props.share.prefix + '.index'), {
            data: { search: val, tab: props.currentTab },
            preserveState: true,
            preserveScroll: true,
            only: ['datas']
        });
    }, 500);
});

const switchTab = (tabKey) => {
    router.visit(route(props.share.prefix + '.index'), {
        data: { search: filterQuery.value, tab: tabKey },
        preserveState: true,
        preserveScroll: true,
    });
};

// View mode
const viewMode = ref(localStorage.getItem('validasiViewMode') || 'grid');
watch(viewMode, (newVal) => localStorage.setItem('validasiViewMode', newVal));

const groupBy = ref(localStorage.getItem('validasiGroupBy') || 'latest');
watch(groupBy, (newVal) => localStorage.setItem('validasiGroupBy', newVal));

const groupedData = computed(() => {
    const data = props.datas?.data || [];
    if (groupBy.value === 'kategori') {
        return data.reduce((groups, item) => {
            const key = item.kategori?.label || 'Tanpa Kategori';
            (groups[key] = groups[key] || []).push(item);
            return groups;
        }, {});
    }

    if (groupBy.value === 'status') {
        return data.reduce((groups, item) => {
            const key = item.status_label?.label || 'Menunggu Validasi';
            (groups[key] = groups[key] || []).push(item);
            return groups;
        }, {});
    }

    return { 'Daftar Validasi': data };
});

// Dialog States
const detailDialog = ref(false);
const verifikasiStaffDialog = ref(false);
const validasiKabagDialog = ref(false);
const revisiDialog = ref(false);
const tolakDialog = ref(false);
const selectedItem = ref(null);
const detailData = ref(null);
const loadingDetail = ref(false);
const processing = ref(false);
const keterangan = ref('');

// Actions
const openDetailModal = async (item) => {
    selectedItem.value = item;
    detailDialog.value = true;
    loadingDetail.value = true;
    try {
        const response = await axios.get(route('permohonan.show', item.uuid), {
            headers: { 'Accept': 'application/json' }
        });
        detailData.value = response.data;
    } catch (error) {
        console.error("Error loading detail:", error);
        toast.error("Gagal memuat detail data");
    } finally {
        loadingDetail.value = false;
    }
};

const openVerifikasiStaff = (item) => {
    selectedItem.value = item;
    keterangan.value = 'Berkas permohonan telah diperiksa dan dinyatakan lengkap oleh staf.';
    verifikasiStaffDialog.value = true;
};

const openValidasiKabag = (item) => {
    selectedItem.value = item;
    keterangan.value = 'Disposisi: Disetujui untuk diagendakan pembahasan bersama TKKSD.';
    validasiKabagDialog.value = true;
};

const openRevisi = (item) => {
    selectedItem.value = item;
    keterangan.value = '';
    revisiDialog.value = true;
};

const openTolak = (item) => {
    selectedItem.value = item;
    keterangan.value = '';
    tolakDialog.value = true;
};

// Submits
const submitVerifikasiStaff = () => {
    processing.value = true;
    router.put(route(`${props.share.prefix}.update`, selectedItem.value.uuid), {
        status: 10,
        keterangan: keterangan.value
    }, {
        onSuccess: () => {
            verifikasiStaffDialog.value = false;
            detailDialog.value = false;
            processing.value = false;
            toast.success('Berkas diverifikasi dan diteruskan ke Kabag Kerja Sama');
        },
        onError: () => {
            processing.value = false;
            toast.error('Gagal memverifikasi berkas');
        }
    });
};

const submitValidasiKabag = () => {
    processing.value = true;
    router.put(route(`${props.share.prefix}.update`, selectedItem.value.uuid), {
        status: 1,
        keterangan: keterangan.value
    }, {
        onSuccess: () => {
            validasiKabagDialog.value = false;
            detailDialog.value = false;
            processing.value = false;
            toast.success('Permohonan berhasil divalidasi dan masuk ke tahap Pembahasan');
        },
        onError: () => {
            processing.value = false;
            toast.error('Gagal memvalidasi permohonan');
        }
    });
};

const submitRevisi = () => {
    if (!keterangan.value || keterangan.value.trim().length < 3) {
        toast.error('Catatan revisi wajib diisi');
        return;
    }
    processing.value = true;
    router.put(route(`${props.share.prefix}.update`, selectedItem.value.uuid), {
        status: 11,
        keterangan: keterangan.value
    }, {
        onSuccess: () => {
            revisiDialog.value = false;
            detailDialog.value = false;
            processing.value = false;
            toast.warning('Permohonan dikembalikan untuk revisi pemohon');
        },
        onError: () => processing.value = false
    });
};

const submitTolak = () => {
    if (!keterangan.value || keterangan.value.trim().length < 3) {
        toast.error('Alasan penolakan wajib diisi');
        return;
    }
    processing.value = true;
    router.put(route(`${props.share.prefix}.update`, selectedItem.value.uuid), {
        status: 9,
        keterangan: keterangan.value
    }, {
        onSuccess: () => {
            tolakDialog.value = false;
            detailDialog.value = false;
            processing.value = false;
            toast.error('Permohonan ditolak');
        },
        onError: () => processing.value = false
    });
};
</script>

<template>
    <Head :title="share.title" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold leading-tight text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <span>{{ share.title }}</span>
                        <span v-if="isKabag" class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                            Kabag Kerja Sama
                        </span>
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Alur Verifikasi Bertingkat Administrasi & Telaah Kepala Bagian Kerja Sama</p>
                </div>
            </div>
        </template>

        <section class="py-8">
            <div class="mx-auto max-w-full px-6 lg:px-8 space-y-6">
                <Breadcrumb :crumbs="[{ label: 'Dashboard', route: 'dashboard' }, { label: share.title, route: null }]" />

                <!-- Stage Tab Badges -->
                <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-gray-200 dark:border-gray-700">
                    <button
                        @click="switchTab('all')"
                        type="button"
                        class="px-4 py-2.5 rounded-xl text-xs font-semibold transition-all flex items-center gap-2"
                        :class="currentTab === 'all' 
                            ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-sm' 
                            : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 border border-gray-200 dark:border-gray-700'"
                    >
                        <Icon icon="solar:documents-bold" class="w-4 h-4" />
                        <span>Semua Permohonan</span>
                        <span class="px-1.5 py-0.5 rounded-md text-[10px]" :class="currentTab === 'all' ? 'bg-white/20 text-white dark:bg-slate-900/20 dark:text-slate-900' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'">
                            {{ counts?.all ?? 0 }}
                        </span>
                    </button>

                    <button
                        @click="switchTab('menunggu_staff')"
                        type="button"
                        class="px-4 py-2.5 rounded-xl text-xs font-semibold transition-all flex items-center gap-2"
                        :class="currentTab === 'menunggu_staff' 
                            ? 'bg-blue-600 text-white shadow-sm' 
                            : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 border border-gray-200 dark:border-gray-700'"
                    >
                        <Icon icon="solar:file-check-bold" class="w-4 h-4" />
                        <span>1. Verifikasi Staf</span>
                        <span class="px-1.5 py-0.5 rounded-md text-[10px]" :class="currentTab === 'menunggu_staff' ? 'bg-white/20 text-white' : 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'">
                            {{ counts?.menunggu_staff ?? 0 }}
                        </span>
                    </button>

                    <button
                        @click="switchTab('menunggu_kabag')"
                        type="button"
                        class="px-4 py-2.5 rounded-xl text-xs font-semibold transition-all flex items-center gap-2"
                        :class="currentTab === 'menunggu_kabag' 
                            ? 'bg-purple-600 text-white shadow-sm' 
                            : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 border border-gray-200 dark:border-gray-700'"
                    >
                        <Icon icon="solar:shield-user-bold" class="w-4 h-4" />
                        <span>2. Disposisi Kabag Kerja Sama</span>
                        <span class="px-1.5 py-0.5 rounded-md text-[10px]" :class="currentTab === 'menunggu_kabag' ? 'bg-white/20 text-white' : 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300'">
                            {{ counts?.menunggu_kabag ?? 0 }}
                        </span>
                    </button>

                    <button
                        @click="switchTab('revisi')"
                        type="button"
                        class="px-4 py-2.5 rounded-xl text-xs font-semibold transition-all flex items-center gap-2"
                        :class="currentTab === 'revisi' 
                            ? 'bg-amber-600 text-white shadow-sm' 
                            : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 border border-gray-200 dark:border-gray-700'"
                    >
                        <Icon icon="solar:restart-bold" class="w-4 h-4" />
                        <span>Perlu Revisi</span>
                        <span class="px-1.5 py-0.5 rounded-md text-[10px]" :class="currentTab === 'revisi' ? 'bg-white/20 text-white' : 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'">
                            {{ counts?.revisi ?? 0 }}
                        </span>
                    </button>
                </div>

                <ControlBar
                    v-model:filterQuery="filterQuery"
                    v-model:viewMode="viewMode"
                    v-model:groupBy="groupBy"
                    searchPlaceholder="Cari permohonan, pimpinan, atau instansi..."
                    :showCreate="false"
                />

                <!-- Empty State -->
                <div v-if="!datas.data.length" class="bg-white dark:bg-gray-800 rounded-2xl p-16 text-center border-2 border-dashed border-gray-200 dark:border-gray-700">
                     <div class="w-20 h-20 bg-gray-50 dark:bg-gray-700/50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <Icon icon="solar:inbox-line-bold-duotone" class="w-10 h-10 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Tidak ada permohonan pada tahap ini</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                        Semua permohonan telah diproses atau belum ada berkas baru yang masuk.
                    </p>
                </div>

                <!-- Content -->
                <div v-else v-for="(items, groupTitle) in groupedData" :key="groupTitle" class="space-y-4">
                    <div class="flex justify-between gap-2">
                        <div class="border-l-4 border-blue-600 pl-2">
                            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider">{{ groupTitle }}</h3>
                        </div>
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ items.length }} Permohonan</span>
                    </div>

                    <div v-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <GridItem 
                            v-for="item in items" 
                            :key="item.id" 
                            :item="item"
                            @detail="openDetailModal($event)"
                        />
                    </div>

                    <div v-else class="space-y-3">
                        <ListItem 
                            v-for="item in items" 
                            :key="item.id" 
                            :item="item"
                            @detail="openDetailModal($event)"
                        />
                    </div>
                </div>
            </div>
        </section>

        <!-- Detail Modal with Workflow Actions -->
        <DetailModal v-model:visible="detailDialog" :loading="loadingDetail" :data="detailData">
            <template #footer>
                <div class="flex shrink-0 items-center justify-between gap-3 border-t border-gray-200 bg-white p-4 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] dark:border-gray-700 dark:bg-gray-800 flex-wrap">
                    <div class="text-xs text-gray-500">
                        Status: <span class="font-bold text-gray-800 dark:text-gray-200">{{ detailData?.status_label?.label || '-' }}</span>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <Button label="Tutup" severity="secondary" text @click="detailDialog = false" />

                        <!-- Tombol Revisi (tersedia di status 0 dan 10) -->
                        <Button 
                            v-if="[0, 10].includes(Number(detailData?.status))"
                            label="Kembalikan Revisi" 
                            icon="pi pi-replay" 
                            severity="warning" 
                            outlined
                            @click="openRevisi(detailData)" 
                        />

                        <!-- Tombol Tolak (khusus Kabag / Admin) -->
                        <Button 
                            v-if="isKabag && [0, 10].includes(Number(detailData?.status))"
                            label="Tolak" 
                            icon="pi pi-times" 
                            severity="danger" 
                            outlined
                            @click="openTolak(detailData)" 
                        />

                        <!-- Status 0: Menunggu Verifikasi Staf -->
                        <Button 
                            v-if="Number(detailData?.status) === 0"
                            label="Verifikasi Berkas Lengkap (Teruskan ke Kabag)" 
                            icon="pi pi-arrow-right" 
                            severity="info" 
                            @click="openVerifikasiStaff(detailData)" 
                        />

                        <!-- Status 10 atau Kabag Direct: Validasi & Disposisi ke Pembahasan -->
                        <Button 
                            v-if="[0, 10].includes(Number(detailData?.status)) && isKabag"
                            label="Validasi & Disposisi ke Pembahasan" 
                            icon="pi pi-check" 
                            severity="success" 
                            @click="openValidasiKabag(detailData)" 
                        />
                    </div>
                </div>
            </template>
        </DetailModal>

        <!-- Dialog Verifikasi Berkas Staf (Status -> 10) -->
        <Dialog v-model:visible="verifikasiStaffDialog" modal header="Verifikasi Administrasi Berkas Staf" :style="{ width: '500px' }">
            <div class="space-y-4 p-2">
                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800 flex gap-3 text-sm text-blue-800 dark:text-blue-200">
                    <Icon icon="solar:file-check-bold" class="w-6 h-6 text-blue-600 shrink-0 mt-0.5" />
                    <div>
                        <p class="font-bold">Verifikasi Kelengkapan Berkas</p>
                        <p class="text-xs mt-1">
                            Pastikan Surat Permohonan, Proposal/KAK, dan Draft MoU telah sesuai ketentuan Perda No. 7/2020. Berkas akan diteruskan ke <strong>Kepala Bagian Kerja Sama</strong>.
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">Catatan Telaah Staf (Opsional)</label>
                    <Textarea v-model="keterangan" rows="3" class="w-full border-gray-300 rounded-lg text-sm" placeholder="Catatan hasil verifikasi berkas..." />
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <Button label="Batal" text @click="verifikasiStaffDialog = false" />
                    <Button label="Verifikasi & Teruskan ke Kabag" icon="pi pi-arrow-right" severity="info" @click="submitVerifikasiStaff" :loading="processing" />
                </div>
            </div>
        </Dialog>

        <!-- Dialog Validasi & Disposisi Kabag Kerja Sama (Status -> 1) -->
        <Dialog v-model:visible="validasiKabagDialog" modal header="Validasi & Disposisi Kabag Kerja Sama" :style="{ width: '520px' }">
            <div class="space-y-4 p-2">
                <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl border border-emerald-200 dark:border-emerald-800 flex gap-3 text-sm text-emerald-800 dark:text-emerald-200">
                    <Icon icon="solar:verified-check-bold" class="w-6 h-6 text-emerald-600 shrink-0 mt-0.5" />
                    <div>
                        <p class="font-bold">Persetujuan & Disposisi Pembahasan TKKSD</p>
                        <p class="text-xs mt-1">
                            Permohonan ini akan disetujui untuk diagendakan pada tahap <strong>Pembahasan bersama Tim Koordinasi Kerja Sama Daerah (TKKSD)</strong>.
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">Arahan / Disposisi Kabag Kerja Sama</label>
                    <Textarea v-model="keterangan" rows="3" class="w-full border-gray-300 rounded-lg text-sm" placeholder="Arahan disposisi untuk TKKSD..." />
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <Button label="Batal" text @click="validasiKabagDialog = false" />
                    <Button label="Setujui & Lanjutkan ke Pembahasan" icon="pi pi-check" severity="success" @click="submitValidasiKabag" :loading="processing" />
                </div>
            </div>
        </Dialog>

        <!-- Dialog Revisi (Status -> 11) -->
        <Dialog v-model:visible="revisiDialog" modal header="Kembalikan Permohonan untuk Revisi" :style="{ width: '500px' }">
            <div class="space-y-4 p-2">
                <p class="text-xs text-gray-600 dark:text-gray-300">
                    Tuliskan secara jelas poin berkas yang kurang atau perlu diperbaiki oleh pemohon:
                </p>
                <div>
                    <Textarea v-model="keterangan" rows="4" class="w-full border-gray-300 rounded-lg text-sm" placeholder="Contoh: Lampiran KAK belum memuat estimasi anggaran dan draft MoU belum sesuai format standar..." />
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <Button label="Batal" text @click="revisiDialog = false" />
                    <Button label="Kirim Revisi ke Pemohon" icon="pi pi-send" severity="warning" @click="submitRevisi" :loading="processing" />
                </div>
            </div>
        </Dialog>

        <!-- Dialog Tolak (Status -> 9) -->
        <Dialog v-model:visible="tolakDialog" modal header="Penolakan Permohonan Kerja Sama" :style="{ width: '500px' }">
            <div class="space-y-4 p-2">
                <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800 text-xs text-red-800 dark:text-red-200">
                    Perhatian: Penolakan ini akan menghentikan proses kerja sama. Berikan alasan penolakan secara resmi.
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">Alasan Penolakan Resmi</label>
                    <Textarea v-model="keterangan" rows="4" class="w-full border-gray-300 rounded-lg text-sm" placeholder="Alasan permohonan tidak dapat diproses..." />
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <Button label="Batal" text @click="tolakDialog = false" />
                    <Button label="Tolak Permohonan" icon="pi pi-times" severity="danger" @click="submitTolak" :loading="processing" />
                </div>
            </div>
        </Dialog>
    </AuthenticatedLayout>
</template>
