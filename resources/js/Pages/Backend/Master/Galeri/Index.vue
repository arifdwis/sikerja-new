<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useToast } from "vue-toastification";
import { Icon } from '@iconify/vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Paginator from 'primevue/paginator';
import Dialog from 'primevue/dialog';
import InputSwitch from 'primevue/inputswitch';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from "primevue/useconfirm";

const toast = useToast();
const confirm = useConfirm();
const props = defineProps({
    galeris: Object,
    share: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const showModal = ref(false);
const editMode = ref(false);
const currentItem = ref(null);
const fotoPreview = ref(null);

const form = useForm({
    judul: '',
    deskripsi: '',
    tanggal_kegiatan: '',
    kategori_kegiatan: 'Rapat Pembahasan TKKSD',
    lokasi: 'Kota Samarinda',
    order: 0,
    is_active: true,
    foto: null,
});

const handleFotoUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.foto = file;
        fotoPreview.value = URL.createObjectURL(file);
    }
};

const openCreate = () => {
    editMode.value = false;
    currentItem.value = null;
    fotoPreview.value = null;
    form.reset();
    form.tanggal_kegiatan = new Date().toISOString().split('T')[0];
    form.kategori_kegiatan = 'Rapat Pembahasan TKKSD';
    form.lokasi = 'Kota Samarinda';
    form.is_active = true;
    form.order = 0;
    showModal.value = true;
};

const openEdit = (item) => {
    editMode.value = true;
    currentItem.value = item;
    fotoPreview.value = item.foto ? '/' + item.foto.replace(/^\/+/, '') : null;
    form.judul = item.judul;
    form.deskripsi = item.deskripsi || '';
    form.tanggal_kegiatan = item.tanggal_kegiatan ? item.tanggal_kegiatan.split('T')[0] : '';
    form.kategori_kegiatan = item.kategori_kegiatan || 'Rapat Pembahasan TKKSD';
    form.lokasi = item.lokasi || '';
    form.order = item.order || 0;
    form.is_active = Boolean(item.is_active);
    form.foto = null;
    showModal.value = true;
};

const submitForm = () => {
    if (editMode.value) {
        form.transform((data) => ({
            ...data,
            _method: 'PUT'
        })).post(route('master.galeri-tkksd.update', currentItem.value.id), {
            onSuccess: () => {
                showModal.value = false;
                toast.success('Foto kegiatan TKKSD berhasil diperbarui');
            },
            onError: () => toast.error('Gagal memperbarui foto kegiatan'),
        });
    } else {
        form.post(route('master.galeri-tkksd.store'), {
            onSuccess: () => {
                showModal.value = false;
                toast.success('Foto kegiatan TKKSD berhasil ditambahkan');
            },
            onError: () => toast.error('Gagal menambahkan foto kegiatan. Pastikan memilih file foto.'),
        });
    }
};

const deleteItem = (item) => {
    confirm.require({
        message: `Hapus foto kegiatan "${item.judul}"?`,
        header: 'Konfirmasi Hapus Foto Kegiatan',
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Ya, Hapus',
        rejectLabel: 'Batal',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(route('master.galeri-tkksd.destroy', item.id), {
                onSuccess: () => toast.success('Foto kegiatan berhasil dihapus'),
                onError: () => toast.error('Gagal menghapus foto kegiatan'),
            });
        }
    });
};

let searchTimeout;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.visit(route('master.galeri-tkksd.index'), {
            data: { search: search.value || undefined },
            preserveState: true,
            preserveScroll: true,
        });
    }, 400);
});

const onPageChange = (event) => {
    router.visit(route('master.galeri-tkksd.index'), {
        data: {
            page: event.page + 1,
            search: search.value || undefined,
        },
        preserveState: true,
        preserveScroll: true,
    });
};

const formatTanggal = (d) => {
    if (!d) return '-';
    try {
        const date = new Date(d);
        return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    } catch {
        return d;
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Master Galeri Kegiatan TKKSD" />

        <div class="p-4 sm:p-6 lg:p-8 space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white font-['Outfit'] flex items-center gap-2">
                        <Icon icon="solar:camera-bold-duotone" class="w-7 h-7 text-teal-600" />
                        Galeri Kegiatan TKKSD
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Manajemen dokumentasi visual foto kegiatan Tim Koordinasi Kerja Sama Daerah Kota Samarinda.
                    </p>
                </div>

                <button 
                    @click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold rounded-xl shadow-md transition-colors"
                >
                    <Icon icon="solar:add-circle-bold" class="w-5 h-5" />
                    Tambah Foto Kegiatan
                </button>
            </div>

            <!-- Toolbar Search -->
            <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <div class="relative w-full max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <Icon icon="solar:magnifer-linear" class="w-4 h-4" />
                    </span>
                    <input 
                        v-model="search"
                        type="text"
                        placeholder="Cari judul kegiatan, lokasi, atau kategori..."
                        class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none"
                    />
                </div>
                <div class="text-xs font-semibold text-slate-500">
                    Total: {{ galeris?.total || 0 }} foto
                </div>
            </div>

            <!-- Grid Galeri Cards -->
            <div v-if="galeris?.data?.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <div 
                    v-for="item in galeris.data" 
                    :key="item.id"
                    class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow"
                >
                    <div>
                        <!-- Foto Preview -->
                        <div class="relative aspect-[16/10] bg-slate-100 dark:bg-slate-900 overflow-hidden">
                            <img 
                                v-if="item.foto"
                                :src="'/' + item.foto.replace(/^\/+/, '')" 
                                :alt="item.judul"
                                class="w-full h-full object-cover"
                            />
                            <div class="absolute top-2 left-2">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-black/60 backdrop-blur-md text-white border border-white/20">
                                    {{ item.kategori_kegiatan || 'TKKSD' }}
                                </span>
                            </div>
                            <div class="absolute top-2 right-2">
                                <span v-if="item.is_active" class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-500 text-white">
                                    Aktif
                                </span>
                                <span v-else class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-500 text-white">
                                    Draft
                                </span>
                            </div>
                        </div>

                        <!-- Card Info -->
                        <div class="p-4">
                            <h3 class="font-bold text-sm text-slate-900 dark:text-white line-clamp-2 mb-1">
                                {{ item.judul }}
                            </h3>
                            <p v-if="item.deskripsi" class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 mb-3">
                                {{ item.deskripsi }}
                            </p>
                            <div class="space-y-1 text-xs text-slate-400">
                                <div class="flex items-center gap-1.5">
                                    <Icon icon="solar:calendar-bold-duotone" class="w-3.5 h-3.5 text-teal-600" />
                                    <span>{{ formatTanggal(item.tanggal_kegiatan) }}</span>
                                </div>
                                <div v-if="item.lokasi" class="flex items-center gap-1.5">
                                    <Icon icon="solar:map-point-bold-duotone" class="w-3.5 h-3.5 text-amber-500" />
                                    <span class="truncate">{{ item.lokasi }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Footer -->
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
                        <span class="text-[11px] font-semibold text-slate-400">
                            Urutan: {{ item.order }}
                        </span>
                        <div class="flex items-center gap-1">
                            <button 
                                @click="openEdit(item)"
                                class="p-1.5 text-amber-600 hover:bg-amber-100/50 rounded-lg transition-colors"
                                title="Edit"
                            >
                                <Icon icon="solar:pen-bold" class="w-4 h-4" />
                            </button>
                            <button 
                                @click="deleteItem(item)"
                                class="p-1.5 text-red-600 hover:bg-red-100/50 rounded-lg transition-colors"
                                title="Hapus"
                            >
                                <Icon icon="solar:trash-bin-trash-bold" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700">
                <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-400 mb-3">
                    <Icon icon="solar:gallery-bold-duotone" class="w-8 h-8" />
                </div>
                <h3 class="text-base font-bold text-slate-700 dark:text-slate-300">Belum ada foto kegiatan</h3>
                <p class="text-xs text-slate-500 mt-1">Silakan klik "Tambah Foto Kegiatan" untuk mengunggah dokumentasi rapat atau kegiatan TKKSD.</p>
            </div>

            <!-- Pagination -->
            <div v-if="galeris?.total > galeris?.per_page" class="p-4 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700">
                <Paginator 
                    :rows="galeris.per_page" 
                    :totalRecords="galeris.total" 
                    :first="(galeris.current_page - 1) * galeris.per_page"
                    @page="onPageChange"
                />
            </div>
        </div>

        <!-- MODAL FORM FOTO KEGIATAN -->
        <Dialog 
            v-model:visible="showModal" 
            :modal="true" 
            :header="editMode ? 'Edit Foto Kegiatan TKKSD' : 'Tambah Foto Kegiatan TKKSD'" 
            :style="{ width: '550px' }"
            class="p-fluid"
        >
            <form @submit.prevent="submitForm" class="space-y-4 pt-2">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                        Judul Kegiatan *
                    </label>
                    <InputText v-model="form.judul" placeholder="Contoh: Rapat Pembahasan Kesepakatan Bersama..." class="w-full" required />
                    <span v-if="form.errors.judul" class="text-xs text-red-500">{{ form.errors.judul }}</span>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                            Kategori Kegiatan
                        </label>
                        <InputText v-model="form.kategori_kegiatan" placeholder="Rapat Pembahasan / Penandatanganan" class="w-full" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                            Tanggal Kegiatan
                        </label>
                        <input type="date" v-model="form.tanggal_kegiatan" class="w-full rounded-lg border-slate-300 text-sm" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                        Lokasi Kegiatan
                    </label>
                    <InputText v-model="form.lokasi" placeholder="Contoh: Ruang Rapat Sekretariat Daerah Samarinda" class="w-full" />
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                        Upload Foto Kegiatan {{ editMode ? '(Opsional jika tidak diganti)' : '*' }}
                    </label>
                    <div class="flex items-center gap-4">
                        <div v-if="fotoPreview" class="w-24 h-16 rounded-xl bg-slate-100 overflow-hidden border border-slate-200">
                            <img :src="fotoPreview" class="w-full h-full object-cover" />
                        </div>
                        <input 
                            type="file" 
                            accept="image/*" 
                            @change="handleFotoUpload"
                            :required="!editMode"
                            class="text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100"
                        />
                    </div>
                    <span v-if="form.errors.foto" class="text-xs text-red-500">{{ form.errors.foto }}</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                        Deskripsi Kegiatan
                    </label>
                    <Textarea v-model="form.deskripsi" rows="3" placeholder="Uraian ringkas agenda kegiatan TKKSD..." class="w-full" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                            Urutan Tampil
                        </label>
                        <input type="number" v-model="form.order" class="w-full rounded-lg border-slate-300 text-sm" />
                    </div>
                    <div class="flex items-center justify-between pt-5">
                        <span class="text-xs font-bold uppercase text-slate-700 dark:text-slate-300">Status Aktif</span>
                        <InputSwitch v-model="form.is_active" />
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-200 dark:border-slate-700">
                    <Button label="Batal" icon="pi pi-times" class="p-button-text" @click="showModal = false" />
                    <Button type="submit" label="Simpan" icon="pi pi-check" :loading="form.processing" class="bg-teal-600 hover:bg-teal-700 text-white" />
                </div>
            </form>
        </Dialog>

        <ConfirmDialog />
    </AuthenticatedLayout>
</template>
