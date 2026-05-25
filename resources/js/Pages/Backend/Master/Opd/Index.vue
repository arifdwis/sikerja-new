<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import { Icon } from '@iconify/vue';
import { ref } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';

const props = defineProps({
    datas: Object,
    filters: Object,
    share: Object,
});

const search = ref(props.filters?.search || '');
const showModal = ref(false);
const editingOpd = ref(null);

const form = useForm({
    nama: '',
    singkatan: '',
    is_active: true,
});

const submitSearch = () => {
    router.get(route('master.opd.index'), { search: search.value }, { preserveState: true });
};

const openCreate = () => {
    editingOpd.value = null;
    form.reset();
    form.is_active = true;
    showModal.value = true;
};

const openEdit = (opd) => {
    editingOpd.value = opd;
    form.nama = opd.nama;
    form.singkatan = opd.singkatan || '';
    form.is_active = !!opd.is_active;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    if (editingOpd.value) {
        form.put(route('master.opd.update', editingOpd.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('master.opd.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const destroy = (opd) => {
    if (!confirm(`Hapus OPD "${opd.nama}"?`)) return;
    router.delete(route('master.opd.destroy', opd.id));
};
</script>

<template>
    <Head title="Master OPD" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">{{ share.title }}</h2>
        </template>

        <section class="py-8">
            <div class="mx-auto max-w-full px-6 lg:px-8">
                <Breadcrumb class="mb-6" :crumbs="[{ label: 'Master Data', route: 'master.opd.index' }, { label: share.title, route: null }]" />

                <div class="mb-5 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-start gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-700 dark:bg-gray-700 dark:text-gray-100">
                                <Icon icon="solar:buildings-bold" class="h-5 w-5" />
                            </span>
                            <div>
                                <h3 class="text-base font-semibold text-slate-900 dark:text-white">Master OPD</h3>
                                <p class="text-sm text-slate-500 dark:text-gray-300">Kelola perangkat daerah yang dapat dihubungkan ke kerjasama.</p>
                            </div>
                        </div>
                        <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
                        <form @submit.prevent="submitSearch" class="relative w-full sm:w-80">
                            <Icon icon="lucide:search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                            <input v-model="search" type="text" placeholder="Cari nama atau singkatan..." class="h-11 w-full rounded-lg border border-gray-300 py-0 pl-10 pr-4 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        </form>
                        <button @click="openCreate" class="inline-flex h-11 items-center justify-center gap-2 whitespace-nowrap rounded-lg bg-slate-900 px-4 text-sm font-medium text-white shadow-sm hover:bg-slate-700">
                        <Icon icon="solar:add-circle-bold" class="w-5 h-5" />
                        Tambah OPD
                        </button>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-left">
                            <tr>
                                <th class="px-4 py-3 font-semibold">Nama OPD</th>
                                <th class="px-4 py-3 font-semibold">Singkatan</th>
                                <th class="px-4 py-3 font-semibold">Status</th>
                                <th class="px-4 py-3 font-semibold w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="opd in datas.data" :key="opd.id" class="border-t border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 text-gray-900 dark:text-white">{{ opd.nama }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ opd.singkatan || '-' }}</td>
                                <td class="px-4 py-3">
                                    <span :class="['px-2 py-0.5 rounded text-xs', opd.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600']">
                                        {{ opd.is_active ? 'Aktif' : 'Non-aktif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-1">
                                        <button @click="openEdit(opd)" class="p-1.5 text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/30 rounded">
                                            <Icon icon="solar:pen-bold" class="w-4 h-4" />
                                        </button>
                                        <button @click="destroy(opd)" class="p-1.5 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded">
                                            <Icon icon="solar:trash-bin-trash-bold" class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!datas.data.length">
                                <td colspan="4" class="px-4 py-12 text-center text-gray-400">Tidak ada data OPD</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="datas.links?.length > 3" class="flex justify-center gap-1">
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

        <!-- Modal Create/Edit -->
        <Dialog v-model:visible="showModal" modal :header="editingOpd ? 'Edit OPD' : 'Tambah OPD'" :style="{ width: '460px' }" :breakpoints="{ '520px': '95vw' }" @hide="closeModal">
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama OPD <span class="text-red-500">*</span></label>
                        <input v-model="form.nama" type="text" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        <p v-if="form.errors.nama" class="text-red-500 text-xs mt-1">{{ form.errors.nama }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Singkatan</label>
                        <input v-model="form.singkatan" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                    </div>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" v-model="form.is_active" class="rounded" />
                        <span>Aktif</span>
                    </label>
                    <div class="flex justify-end gap-2 border-t border-gray-200 pt-4 dark:border-gray-700">
                        <Button type="button" label="Batal" severity="secondary" text @click="closeModal" />
                        <Button type="submit" label="Simpan" icon="pi pi-check" :loading="form.processing" />
                    </div>
                </form>
        </Dialog>
    </AuthenticatedLayout>
</template>
