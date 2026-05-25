<script setup>
import { useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import { computed, ref, watch } from 'vue';
import { useToast } from 'vue-toastification';
import Dialog from 'primevue/dialog';
import FileUpload from 'primevue/fileupload';
import MultiSelect from 'primevue/multiselect';
import { calculateJangkaWaktu } from '../utils';

const props = defineProps({
    visible: Boolean,
    kategoris: Array,
    opds: Array,
});

const emit = defineEmits(['update:visible']);
const toast = useToast();
const fileUpload = ref(null);

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

const opdOptions = computed(() => (props.opds || []).map((opd) => ({
    id: opd.id,
    label: opd.singkatan ? `${opd.nama} (${opd.singkatan})` : opd.nama,
})));
const automaticJangkaWaktu = computed(() => calculateJangkaWaktu(form.tanggal_mulai, form.tanggal_berakhir));

watch(
    automaticJangkaWaktu,
    (jangkaWaktu) => {
        form.jangka_waktu = jangkaWaktu;
    },
    { immediate: true },
);

watch(
    () => props.visible,
    (visible) => {
        if (visible) return;

        fileUpload.value?.clear();
        form.reset();
        form.clearErrors();
    },
);

const close = () => emit('update:visible', false);

const handleFileSelect = (event) => {
    form.file = event.files?.[0] || null;
};

const clearFile = () => {
    form.file = null;
};

const submit = () => {
    if (form.tanggal_mulai && form.tanggal_berakhir && form.tanggal_berakhir < form.tanggal_mulai) {
        toast.error('Tanggal berakhir tidak boleh lebih awal dari tanggal mulai.');
        return;
    }

    form.post(route('kerjasama-manual.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            close();
            toast.success('PKS manual berhasil ditambahkan.');
        },
    });
};
</script>

<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        modal
        :style="{ width: '980px' }"
        :breakpoints="{ '1040px': '95vw' }"
        contentClass="p-0"
    >
        <template #header>
            <div class="flex min-w-0 items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-700 dark:bg-gray-700 dark:text-gray-100">
                    <Icon icon="lucide:plus" class="h-5 w-5" />
                </span>
                <div class="min-w-0">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Tambah PKS Manual</h3>
                    <p class="text-sm font-normal text-slate-500 dark:text-gray-300">Catat PKS final yang tidak berasal dari alur permohonan.</p>
                </div>
            </div>
        </template>

        <form @submit.prevent="submit" class="bg-white dark:bg-gray-800" enctype="multipart/form-data">
            <div class="max-h-[72vh] overflow-y-auto">
                <section class="border-b border-slate-200 px-6 py-5 dark:border-gray-700">
                    <div class="mb-4">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-gray-300">Informasi PKS</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">Isi identitas dokumen dan mitra kerjasama.</p>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor PKS</label>
                            <input v-model="form.nomor_pks" type="text" placeholder="Nomor PKS" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                            <p v-if="form.errors.nomor_pks" class="mt-1 text-xs text-red-500">{{ form.errors.nomor_pks }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Kategori</label>
                            <select v-model="form.id_kategori" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <option value="">Pilih Kategori</option>
                                <option v-for="kategori in kategoris" :key="kategori.id" :value="kategori.id">{{ kategori.label }}</option>
                            </select>
                            <p v-if="form.errors.id_kategori" class="mt-1 text-xs text-red-500">{{ form.errors.id_kategori }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Instansi <span class="text-red-500">*</span></label>
                            <input v-model="form.nama_instansi" type="text" required placeholder="Nama mitra" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                            <p v-if="form.errors.nama_instansi" class="mt-1 text-xs text-red-500">{{ form.errors.nama_instansi }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Perihal Kerjasama <span class="text-red-500">*</span></label>
                            <input v-model="form.label" type="text" required placeholder="Judul PKS" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                            <p v-if="form.errors.label" class="mt-1 text-xs text-red-500">{{ form.errors.label }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Ruang Lingkup</label>
                            <textarea v-model="form.ruang_lingkup" rows="3" placeholder="Ringkasan ruang lingkup PKS" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                            <p v-if="form.errors.ruang_lingkup" class="mt-1 text-xs text-red-500">{{ form.errors.ruang_lingkup }}</p>
                        </div>
                    </div>
                </section>

                <div class="grid lg:grid-cols-[minmax(0,1fr)_320px]">
                    <section class="border-b border-slate-200 px-6 py-5 lg:border-b-0 lg:border-r dark:border-gray-700">
                        <div class="mb-4">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-gray-300">Periode & OPD</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">Pilih satu atau beberapa OPD pelaksana.</p>
                        </div>
                        <div class="grid gap-4 md:grid-cols-3">
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
                                <input :value="automaticJangkaWaktu" type="text" readonly placeholder="Otomatis dari tanggal" class="w-full cursor-default rounded-lg border-gray-300 bg-slate-50 text-slate-600 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Dihitung otomatis.</p>
                                <p v-if="form.errors.jangka_waktu" class="mt-1 text-xs text-red-500">{{ form.errors.jangka_waktu }}</p>
                            </div>
                            <div class="md:col-span-3">
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">OPD Terkait</label>
                                <MultiSelect
                                    v-model="form.opd_ids"
                                    :options="opdOptions"
                                    option-label="label"
                                    option-value="id"
                                    placeholder="Pilih OPD terkait"
                                    display="chip"
                                    filter
                                    :max-selected-labels="2"
                                    class="w-full"
                                />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Bisa memilih lebih dari satu OPD.</p>
                                <p v-if="form.errors.opd_ids" class="mt-1 text-xs text-red-500">{{ form.errors.opd_ids }}</p>
                            </div>
                        </div>
                    </section>

                    <section class="bg-slate-50/70 px-6 py-5 dark:bg-gray-900/25">
                        <div class="mb-4">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-gray-300">Dokumen PKS</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">Upload PDF final wajib sebelum disimpan.</p>
                        </div>
                        <FileUpload
                            ref="fileUpload"
                            name="file"
                            accept="application/pdf,.pdf"
                            :multiple="false"
                            :showUploadButton="false"
                            chooseLabel="Pilih PDF"
                            cancelLabel="Bersihkan"
                            @select="handleFileSelect"
                            @clear="clearFile"
                            @remove="clearFile"
                            class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-600 dark:bg-gray-800"
                        >
                            <template #header="{ chooseCallback, clearCallback, files }">
                                <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-3 py-2 dark:border-gray-700">
                                    <span class="text-xs font-medium text-slate-500">PDF, maks. 20 MB</span>
                                    <div class="flex items-center gap-1">
                                        <button type="button" @click="chooseCallback()" class="inline-flex items-center gap-1 rounded-md bg-slate-900 px-2.5 py-1.5 text-xs font-medium text-white hover:bg-slate-700">
                                            <Icon icon="lucide:plus" class="h-3.5 w-3.5" />
                                            Pilih
                                        </button>
                                        <button v-if="files?.length" type="button" @click="clearCallback()" aria-label="Bersihkan file" class="rounded-md p-1.5 text-slate-500 hover:bg-slate-100 dark:hover:bg-gray-700">
                                            <Icon icon="lucide:x" class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </div>
                            </template>
                            <template #empty>
                                <div class="flex items-center gap-3 px-3 py-5">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600 dark:bg-gray-700 dark:text-gray-100">
                                        <Icon icon="solar:cloud-upload-bold-duotone" class="h-5 w-5" />
                                    </span>
                                    <div>
                                        <p class="text-sm font-medium text-slate-800 dark:text-gray-100">Tarik PDF PKS ke sini</p>
                                        <p class="text-xs text-slate-500 dark:text-gray-300">atau pilih file dari perangkat.</p>
                                    </div>
                                </div>
                            </template>
                        </FileUpload>
                        <p v-if="form.errors.file" class="mt-1 text-xs text-red-500">{{ form.errors.file }}</p>
                    </section>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-gray-200 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
                <button type="button" @click="close" class="rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">Batal</button>
                <button type="submit" :disabled="form.processing || !form.file" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-slate-700 disabled:cursor-not-allowed disabled:bg-gray-400">
                    <Icon :icon="form.processing ? 'lucide:loader-circle' : 'lucide:save'" class="h-4 w-4" :class="{ 'animate-spin': form.processing }" />
                    {{ form.processing ? 'Menyimpan...' : 'Simpan PKS Manual' }}
                </button>
            </div>
        </form>
    </Dialog>
</template>
