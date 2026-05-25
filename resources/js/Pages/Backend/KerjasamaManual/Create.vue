<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import { Icon } from '@iconify/vue';
import { useToast } from 'vue-toastification';

const props = defineProps({
    kategoris: Array,
    opds: Array,
    share: Object,
});

const form = useForm({
    nomor_pks: '',
    nama_instansi: '',
    label: '',
    id_kategori: '',
    ruang_lingkup: '',
    tanggal_mulai: '',
    tanggal_berakhir: '',
    jangka_waktu: '',
    file: null,
    opd_ids: [],
});

const toast = useToast();

const submit = () => {
    if (form.tanggal_mulai && form.tanggal_berakhir && form.tanggal_berakhir < form.tanggal_mulai) {
        toast.error('Tanggal berakhir tidak boleh lebih awal dari tanggal mulai.');
        return;
    }

    form.post(route('kerjasama-manual.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Tambah Kerjasama Manual" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Tambah {{ share.title }}</h2>
        </template>

        <section class="py-6">
            <div class="w-full space-y-4 px-4 sm:px-6 lg:px-8">
                <Breadcrumb :crumbs="[{ label: share.title, route: 'kerjasama-manual.index' }, { label: 'Tambah', route: null }]" />

                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Tambah Kerjasama Manual</h2>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Input PKS final yang sudah tersedia beserta OPD pelaksana dan periodenya.</p>
                    </div>
                    <Link :href="route('kerjasama-manual.index')" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700">
                        <Icon icon="lucide:arrow-left" class="h-4 w-4" />
                        Kembali
                    </Link>
                </div>

                <form @submit.prevent="submit" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800" enctype="multipart/form-data">
                    <div class="space-y-6 p-6">
                        <section>
                            <h3 class="mb-4 border-b border-gray-200 pb-2 text-lg font-semibold text-gray-900 dark:border-gray-700 dark:text-white">Informasi PKS</h3>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor PKS</label>
                                    <input v-model="form.nomor_pks" type="text" placeholder="Masukkan nomor PKS" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                    <p v-if="form.errors.nomor_pks" class="mt-1 text-xs text-red-500">{{ form.errors.nomor_pks }}</p>
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Kategori</label>
                                    <select v-model="form.id_kategori" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                        <option value="">Pilih Kategori</option>
                                        <option v-for="k in kategoris" :key="k.id" :value="k.id">{{ k.label }}</option>
                                    </select>
                                    <p v-if="form.errors.id_kategori" class="mt-1 text-xs text-red-500">{{ form.errors.id_kategori }}</p>
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Instansi <span class="text-red-500">*</span></label>
                                    <input v-model="form.nama_instansi" type="text" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                    <p v-if="form.errors.nama_instansi" class="mt-1 text-xs text-red-500">{{ form.errors.nama_instansi }}</p>
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Perihal Kerjasama <span class="text-red-500">*</span></label>
                                    <input v-model="form.label" type="text" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                    <p v-if="form.errors.label" class="mt-1 text-xs text-red-500">{{ form.errors.label }}</p>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Ruang Lingkup</label>
                                    <textarea v-model="form.ruang_lingkup" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                                    <p v-if="form.errors.ruang_lingkup" class="mt-1 text-xs text-red-500">{{ form.errors.ruang_lingkup }}</p>
                                </div>
                            </div>
                        </section>

                        <section>
                            <h3 class="mb-4 border-b border-gray-200 pb-2 text-lg font-semibold text-gray-900 dark:border-gray-700 dark:text-white">Periode & OPD</h3>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Mulai</label>
                                    <input v-model="form.tanggal_mulai" type="date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                    <p v-if="form.errors.tanggal_mulai" class="mt-1 text-xs text-red-500">{{ form.errors.tanggal_mulai }}</p>
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Berakhir</label>
                                    <input v-model="form.tanggal_berakhir" type="date" :min="form.tanggal_mulai || undefined" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                    <p v-if="form.tanggal_mulai && form.tanggal_berakhir && form.tanggal_berakhir < form.tanggal_mulai" class="mt-1 text-xs text-red-500">Tanggal berakhir tidak boleh lebih awal dari tanggal mulai.</p>
                                    <p v-else-if="form.errors.tanggal_berakhir" class="mt-1 text-xs text-red-500">{{ form.errors.tanggal_berakhir }}</p>
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Jangka Waktu</label>
                                    <input v-model="form.jangka_waktu" type="text" placeholder="Contoh: 1 Tahun" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                    <p v-if="form.errors.jangka_waktu" class="mt-1 text-xs text-red-500">{{ form.errors.jangka_waktu }}</p>
                                </div>
                                <div class="md:col-span-3">
                                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">OPD Terkait</label>
                                    <select multiple v-model="form.opd_ids" class="min-h-[132px] w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                        <option v-for="opd in opds" :key="opd.id" :value="opd.id">{{ opd.nama }}{{ opd.singkatan ? ` (${opd.singkatan})` : '' }}</option>
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Tahan Ctrl di Windows atau Cmd di Mac untuk memilih lebih dari satu OPD.</p>
                                    <p v-if="form.errors.opd_ids" class="mt-1 text-xs text-red-500">{{ form.errors.opd_ids }}</p>
                                </div>
                            </div>
                        </section>

                        <section>
                            <h3 class="mb-4 border-b border-gray-200 pb-2 text-lg font-semibold text-gray-900 dark:border-gray-700 dark:text-white">Dokumen PKS</h3>
                            <label class="block rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-700/40">
                                <span class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-200">
                                    <Icon icon="lucide:file-up" class="h-4 w-4 text-blue-600" />
                                    Upload PKS Final PDF <span class="text-red-500">*</span>
                                </span>
                                <input @change="form.file = $event.target.files[0]" type="file" accept="application/pdf" required class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-600 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-blue-700 dark:text-gray-300" />
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Maksimal 20 MB, format PDF.</p>
                            </label>
                            <p v-if="form.errors.file" class="mt-1 text-xs text-red-500">{{ form.errors.file }}</p>
                        </section>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-800/70 sm:flex-row sm:justify-end">
                        <Link :href="route('kerjasama-manual.index')" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600">
                            Batal
                        </Link>
                        <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-gray-400">
                            <Icon :icon="form.processing ? 'lucide:loader-circle' : 'lucide:save'" class="h-4 w-4" :class="{ 'animate-spin': form.processing }" />
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Kerjasama' }}
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
