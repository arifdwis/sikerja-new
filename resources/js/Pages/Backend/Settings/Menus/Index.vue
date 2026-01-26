<script setup>
import { ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import { useToast } from 'vue-toastification';
import { useConfirm } from 'primevue/useconfirm';
import { Icon } from '@iconify/vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Checkbox from 'primevue/checkbox';
import ConfirmDialog from 'primevue/confirmdialog';
import NestedMenu from '@/Components/NestedMenu.vue';
import axios from 'axios';

const props = defineProps({
    nodes: Array,
    share: Object,
    roles: Array,
});

const toast = useToast();
const confirm = useConfirm();

// Tree Data Reaktif
const treeValue = ref(props.nodes);
const isSaving = ref(false);

// Auto-Save Watcher
let autoSaveTimeout;
watch(treeValue, () => {
    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(() => {
        saveOrder();
    }, 1000); // 1 second debounce
}, { deep: true });

// Modal
const displayModal = ref(false);
const modalTitle = ref('');
const isEdit = ref(false);
const editingId = ref(null);
const parentIdForNew = ref(null);

const form = useForm({
    title: '',
    route: '',
    icon: '',
    roles: '', // simple string for comma separated
    is_active: true,
    parent_id: null,
});

// Actions
const openAddRoot = () => {
    isEdit.value = false;
    modalTitle.value = 'Tambah Menu Utama';
    parentIdForNew.value = null;
    form.reset();
    form.parent_id = null;
    displayModal.value = true;
};

const openAddChild = (node) => {
    isEdit.value = false;
    modalTitle.value = `Tambah Submenu untuk ${node.label}`;
    parentIdForNew.value = node.key;
    form.reset();
    form.parent_id = node.key;
    displayModal.value = true;
};

const openEdit = (node) => {
    isEdit.value = true;
    modalTitle.value = 'Edit Menu';
    editingId.value = node.key;
    const data = node.data;
    
    form.title = data.title;
    form.route = data.route;
    form.icon = data.icon;
    form.roles = data.roles;
    form.is_active = Boolean(data.is_active);
    form.parent_id = data.parent_id;
    
    displayModal.value = true;
};

const closeModal = () => {
    displayModal.value = false;
    form.reset();
};

const submitForm = () => {
    if (isEdit.value) {
        form.put(route('settings.menu.update', editingId.value), {
            onSuccess: () => {
                toast.success('Menu berhasil diperbarui');
                closeModal();
                // Refresh full page to reload tree data properly
                router.reload({ only: ['nodes'] }); 
            },
            onError: () => toast.error('Gagal memperbarui menu'),
        });
    } else {
        form.post(route('settings.menu.store'), {
            onSuccess: () => {
                toast.success('Menu berhasil ditambahkan');
                closeModal();
                router.reload({ only: ['nodes'] });
            },
            onError: () => toast.error('Gagal menambahkan menu'),
        });
    }
};

const deleteNode = (node) => {
    confirm.require({
        message: `Apakah Anda yakin ingin menghapus menu "${node.label}"? Submenu (jika ada) juga akan terhapus.`,
        header: 'Konfirmasi Hapus',
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Ya Hapus',
        rejectLabel: 'Batal',
        acceptClass: 'p-button-danger',
        rejectClass: 'p-button-secondary',
        accept: () => {
            router.delete(route('settings.menu.destroy', node.key), {
                onSuccess: () => {
                    toast.success('Menu berhasil dihapus');
                    router.reload({ only: ['nodes'] });
                },
            });
        }
    });
};

const flattenTree = (nodes, parentId = null) => {
    let flat = [];
    nodes.forEach((node, index) => {
        flat.push({
            id: node.key,
            parent_id: parentId,
            order: index + 1
        });
        if (node.children && node.children.length > 0) {
            flat = flat.concat(flattenTree(node.children, node.key));
        }
    });
    return flat;
};

const saveOrder = async () => {
    isSaving.value = true;
    try {
        if (!treeValue.value || treeValue.value.length === 0) {
            // alert('Data menu kosong, tidak ada yang disimpan.');
            return;
        }

        // Debug: Log tree value to see if it changed
        // console.log('Tree Structure before Save:', JSON.parse(JSON.stringify(treeValue.value)));

        const payload = flattenTree(treeValue.value);
        // console.log('Sending Flattened Payload:', payload);
        
        await axios.post('/settings/menu/reorder', { items: payload });
        
        toast.success('Urutan menu berhasil disimpan');
        
        // Full visit to ensure Shared Props (Sidebar menu) are refreshed
        // Force state reload to ensure Sidebar receives new order
        router.visit(window.location.pathname, { 
            preserveScroll: true,
            preserveState: false
        });
        
    } catch (e) {
        console.error('Save Error:', e);
        const msg = e.response?.data?.message || e.message || 'Unknown error';
        toast.error('Gagal menyimpan: ' + msg);
        // Fallback alert ensures user sees error even if toast fails
        // alert('Gagal menyimpan: ' + msg); 
    } finally {
        isSaving.value = false;
    }
};

</script>

<template>
    <Head :title="share.title" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">{{ share.title }}</h2>
                <div class="flex gap-2">
                     <button 
                        @click="saveOrder" 
                        :disabled="isSaving"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition-colors bg-green-600 rounded-lg hover:bg-green-700 focus:ring-4 focus:ring-green-300 disabled:opacity-50 disabled:cursor-not-allowed hidden md:flex"
                    >
                        <i class="pi" :class="isSaving ? 'pi-spinner pi-spin' : 'pi-check'"></i>
                        {{ isSaving ? 'Menyimpan...' : 'Simpan Urutan' }}
                    </button>
                    
                    <button 
                        @click="openAddRoot"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300"
                    >
                        <i class="pi pi-plus"></i>
                        Tambah Menu
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-full sm:px-6 lg:px-8 space-y-4">
                <Breadcrumb :crumbs="[
                    { label: 'Pengaturan', route: 'settings.users.index' },
                    { label: share.title, route: share.prefix + '.index' }
                ]" />
                
                <!-- Tree Container -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700 p-6">
                    <p class="mb-4 text-sm text-gray-500 dark:text-gray-400 border-b pb-2">
                        <Icon icon="solar:info-circle-linear" class="inline w-4 h-4 mr-1"/>
                        Geser (Drag & Drop) untuk mengatur urutan dan struktur hirarki menu. Perubahan akan disimpan secara otomatis.
                    </p>

                    <div v-if="treeValue && treeValue.length > 0">
                        <NestedMenu 
                            v-model="treeValue" 
                            @add-child="openAddChild" 
                            @edit="openEdit" 
                            @delete="deleteNode"
                        />
                    </div>

                    <div v-else class="text-center py-12 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                        <Icon icon="solar:hamburger-menu-linear" class="w-12 h-12 mx-auto text-gray-300 mb-2" />
                        <p>Belum ada menu.</p>
                        <Button label="Tambah Menu Utama" text size="small" @click="openAddRoot" class="mt-2" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Dialog Form -->
        <Dialog v-model:visible="displayModal" :header="modalTitle" modal class="w-full md:w-1/2 lg:w-1/3">
            <div class="flex flex-col gap-4 mt-2">
                <div class="flex flex-col gap-2">
                    <label for="title" class="text-sm font-medium text-gray-700 dark:text-gray-300">Judul Menu</label>
                    <InputText id="title" v-model="form.title" class="w-full" placeholder="Contoh: Dashboard" autofocus />
                    <small v-if="form.errors.title" class="text-red-500">{{ form.errors.title }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="route" class="text-sm font-medium text-gray-700 dark:text-gray-300">Route Name / URL</label>
                    <InputText id="route" v-model="form.route" class="w-full" placeholder="Contoh: dashboard atau /custom-path" />
                    <small class="text-xs text-gray-500">Kosongkan jika hanya sebagai parent dropdown.</small>
                     <small v-if="form.errors.route" class="text-red-500">{{ form.errors.route }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="icon" class="text-sm font-medium text-gray-700 dark:text-gray-300">Icon (Solar Icon / Iconify)</label>
                    <div class="flex gap-2">
                        <InputText id="icon" v-model="form.icon" class="w-full" placeholder="Contoh: solar:home-2-bold" />
                        <div v-if="form.icon" class="w-10 h-10 flex-shrink-0 flex items-center justify-center bg-gray-100 rounded border">
                            <Icon :icon="form.icon" class="w-6 h-6" />
                        </div>
                    </div>
                    <small class="text-xs text-gray-500">Cari icon di <a href="https://icon-sets.iconify.design/" target="_blank" class="text-blue-500 hover:underline">Iconify</a></small>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="roles" class="text-sm font-medium text-gray-700 dark:text-gray-300">Akses Roles</label>
                    <InputText id="roles" v-model="form.roles" class="w-full" placeholder="Contoh: administrator, tkksd" />
                    <small class="text-xs text-gray-500">Pisahkan dengan koma. Kosongkan untuk akses publik.</small>
                </div>

                <div class="flex items-center gap-2 mt-2 p-3 bg-gray-50 rounded border border-gray-100 dark:bg-gray-700 dark:border-gray-600">
                    <Checkbox id="is_active" v-model="form.is_active" :binary="true" />
                    <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer select-none">Aktifkan Menu Tampil di Sidebar</label>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2 pt-4">
                    <Button label="Batal" icon="pi pi-times" text @click="closeModal" severity="secondary" />
                    <Button label="Simpan" icon="pi pi-check" @click="submitForm" :loading="form.processing" />
                </div>
            </template>
        </Dialog>

    </AuthenticatedLayout>
    <ConfirmDialog></ConfirmDialog>
</template>
