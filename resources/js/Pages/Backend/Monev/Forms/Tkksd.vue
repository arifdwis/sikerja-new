<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import { Icon } from '@iconify/vue';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import Calendar from 'primevue/calendar';
import { useToast } from 'vue-toastification';

const props = defineProps({
    share: Object,
    permohonans: Array,
    selectedPermohonan: Object,
    pemohon: Object,
    submitterRole: String,
});

const toast = useToast();

const form = useForm({
    id_permohonan: props.selectedPermohonan?.id || null,
    tanggal_evaluasi: new Date(),
    tkl_kepatuhan_pks: null,
    tkl_koordinasi_mitra: null,
    tkl_kesesuaian_anggaran: null,
    tkl_temuan_pengawasan: '',
    tkl_rekomendasi_lokus: null,
    tkl_catatan: '',
});

const options = {
    tkl_kepatuhan_pks: ['Patuh', 'Sebagian', 'Tidak'],
    tkl_koordinasi_mitra: ['Sangat baik', 'Baik', 'Cukup', 'Kurang'],
    tkl_kesesuaian_anggaran: ['Sesuai', 'Sebagian', 'Tidak'],
    tkl_rekomendasi_lokus: ['Lanjutkan', 'Perbaiki', 'Hentikan'],
};

const submit = () => {
    if (form.processing) return;
    form.post(route('monev.store'), {
        onSuccess: () => {
            toast.success('Form Monev berhasil dikirim.');
            form.reset();
        },
        onError: () => {
            toast.error('Gagal menyimpan. Periksa kembali isian form.');
        },
    });
};
</script>

<template>
    <Head :title="share.title" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">{{ share.title }}</h2>
        </template>

        <section class="py-8">
            <div class="mx-auto max-w-4xl px-6 lg:px-8">
                <Breadcrumb class="mb-6" />

                <form @submit.prevent="submit" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-gray-800">
                    <div class="border-b border-slate-200 bg-slate-50 p-6 dark:border-slate-700 dark:bg-slate-800">
                        <div class="flex items-start gap-3">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-white text-slate-700 shadow-sm dark:bg-slate-700 dark:text-white">
                                <Icon icon="solar:shield-check-bold-duotone" class="h-6 w-6" />
                            </span>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Form Monev TKKSD Lokus</h3>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">Isi evaluasi pengawasan berdasarkan pelaksanaan pada OPD lokus.</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6 p-6">
                        <div class="grid gap-4 rounded-xl border border-slate-200 bg-slate-50/70 p-4 md:grid-cols-[minmax(0,1fr)_220px]">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Kerjasama <span class="text-red-500">*</span></label>
                                <Dropdown
                                    v-model="form.id_permohonan"
                                    :options="permohonans"
                                    optionLabel="label"
                                    optionValue="id"
                                    placeholder="Pilih kerjasama"
                                    class="w-full"
                                    :class="{ 'p-invalid': form.errors.id_permohonan }"
                                />
                                <small v-if="form.errors.id_permohonan" class="text-red-500">{{ form.errors.id_permohonan }}</small>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Evaluasi <span class="text-red-500">*</span></label>
                                <Calendar v-model="form.tanggal_evaluasi" dateFormat="dd/mm/yy" showIcon :manualInput="true" class="w-full" />
                            </div>
                        </div>

                        <div class="grid gap-4 rounded-xl border border-slate-200 p-4 md:grid-cols-[minmax(0,1fr)_280px] md:items-center">
                            <label class="text-sm text-slate-700">Kepatuhan pelaksanaan terhadap PKS <span class="text-red-500">*</span></label>
                            <div>
                                <Dropdown v-model="form.tkl_kepatuhan_pks" :options="options.tkl_kepatuhan_pks" placeholder="Pilih jawaban" class="w-full" :class="{ 'p-invalid': form.errors.tkl_kepatuhan_pks }" />
                                <small v-if="form.errors.tkl_kepatuhan_pks" class="text-red-500">{{ form.errors.tkl_kepatuhan_pks }}</small>
                            </div>
                        </div>

                        <div class="grid gap-4 rounded-xl border border-slate-200 p-4 md:grid-cols-[minmax(0,1fr)_280px] md:items-center">
                            <label class="text-sm text-slate-700">Kualitas koordinasi OPD–mitra <span class="text-red-500">*</span></label>
                            <div>
                                <Dropdown v-model="form.tkl_koordinasi_mitra" :options="options.tkl_koordinasi_mitra" placeholder="Pilih jawaban" class="w-full" :class="{ 'p-invalid': form.errors.tkl_koordinasi_mitra }" />
                                <small v-if="form.errors.tkl_koordinasi_mitra" class="text-red-500">{{ form.errors.tkl_koordinasi_mitra }}</small>
                            </div>
                        </div>

                        <div class="grid gap-4 rounded-xl border border-slate-200 p-4 md:grid-cols-[minmax(0,1fr)_280px] md:items-center">
                            <label class="text-sm text-slate-700">Kesesuaian realisasi terhadap anggaran <span class="text-red-500">*</span></label>
                            <div>
                                <Dropdown v-model="form.tkl_kesesuaian_anggaran" :options="options.tkl_kesesuaian_anggaran" placeholder="Pilih jawaban" class="w-full" :class="{ 'p-invalid': form.errors.tkl_kesesuaian_anggaran }" />
                                <small v-if="form.errors.tkl_kesesuaian_anggaran" class="text-red-500">{{ form.errors.tkl_kesesuaian_anggaran }}</small>
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-200 p-4">
                            <label class="mb-2 block text-sm font-medium text-slate-700">Temuan pengawasan</label>
                            <Textarea v-model="form.tkl_temuan_pengawasan" rows="4" class="w-full" placeholder="Jelaskan temuan pengawasan..." />
                            <small v-if="form.errors.tkl_temuan_pengawasan" class="text-red-500">{{ form.errors.tkl_temuan_pengawasan }}</small>
                        </div>

                        <div class="grid gap-4 rounded-xl border border-slate-200 p-4 md:grid-cols-[minmax(0,1fr)_280px] md:items-center">
                            <label class="text-sm text-slate-700">Rekomendasi lokus <span class="text-red-500">*</span></label>
                            <div>
                                <Dropdown v-model="form.tkl_rekomendasi_lokus" :options="options.tkl_rekomendasi_lokus" placeholder="Pilih jawaban" class="w-full" :class="{ 'p-invalid': form.errors.tkl_rekomendasi_lokus }" />
                                <small v-if="form.errors.tkl_rekomendasi_lokus" class="text-red-500">{{ form.errors.tkl_rekomendasi_lokus }}</small>
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-200 p-4">
                            <label class="mb-2 block text-sm font-medium text-slate-700">Catatan tambahan</label>
                            <Textarea v-model="form.tkl_catatan" rows="4" class="w-full" placeholder="Tambahkan catatan jika diperlukan..." />
                            <small v-if="form.errors.tkl_catatan" class="text-red-500">{{ form.errors.tkl_catatan }}</small>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 border-t border-slate-200 bg-slate-50 p-6 dark:border-slate-700 dark:bg-gray-700/50">
                        <Link :href="route('monev.index')">
                            <Button label="Batal" severity="secondary" text />
                        </Link>
                        <Button type="submit" label="Submit Evaluasi" icon="pi pi-check" :loading="form.processing" />
                    </div>
                </form>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
