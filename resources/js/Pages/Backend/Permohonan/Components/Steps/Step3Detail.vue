<script setup>
import { computed, watch } from 'vue';
import { Icon } from '@iconify/vue';
import MultiSelect from 'primevue/multiselect';
import { computeJangkaWaktu } from '@/utils/jangkaWaktu';

const props = defineProps({
    form: Object,
    opds: {
        type: Array,
        default: () => []
    },
    userOpd: {
        type: Object,
        default: null
    },
    isOpdLocked: {
        type: Boolean,
        default: false
    },
});

const opdOptions = computed(() => (props.opds || []).map(o => ({
    id: o.id,
    label: o.singkatan ? `${o.nama} (${o.singkatan})` : o.nama,
})));

const hasInvalidEndDate = computed(() => (
    props.form.tanggal_mulai &&
    props.form.tanggal_berakhir &&
    props.form.tanggal_berakhir < props.form.tanggal_mulai
));

const jangkaWaktuAuto = computed(() =>
    computeJangkaWaktu(props.form.tanggal_mulai, props.form.tanggal_berakhir)
);

// Auto-sync ke form.jangka_waktu setiap kali tanggal berubah
watch([
    () => props.form.tanggal_mulai,
    () => props.form.tanggal_berakhir,
], () => {
    props.form.jangka_waktu = jangkaWaktuAuto.value;
}, { immediate: true });
</script>

<template>
    <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 border border-gray-100 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
            <Icon icon="solar:document-text-bold" class="w-5 h-5 mr-2 text-orange-500" />
            Detail Kerjasama (Permendagri No.22 Tahun 2020 Pasal 7)
        </h3>
        
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Latar Belakang <span class="text-red-500">*</span> <span class="text-gray-400 text-xs">(300 Kata)</span>
                </label>
                <textarea v-model="form.latar_belakang" rows="4" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Masukan latar belakang pengajuan kerjasama..."></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Maksud dan Tujuan <span class="text-red-500">*</span> <span class="text-gray-400 text-xs">(500 Kata)</span>
                </label>
                <textarea v-model="form.maksud_tujuan" rows="4" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Masukan maksud dan tujuan kerjasama..."></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Lokasi Kerja Sama Daerah <span class="text-red-500">*</span> <span class="text-gray-400 text-xs">(100 Kata)</span>
                </label>
                <textarea v-model="form.lokasi_kerjasama" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Masukan lokasi kerjasama..."></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Ruang Lingkup <span class="text-red-500">*</span> <span class="text-gray-400 text-xs">(500 Kata)</span>
                </label>
                <textarea v-model="form.ruang_lingkup" rows="4" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Masukan ruang lingkup kerjasama..."></textarea>
            </div>

            <!-- OPD Terkait (Req 12) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    OPD Terkait <span class="text-red-500">*</span>
                    <span v-if="isOpdLocked" class="ml-2 text-xs text-blue-600 dark:text-blue-400">
                        <Icon icon="solar:lock-bold" class="inline w-3 h-3" />
                        Terhubung otomatis dari profil Anda
                    </span>
                </label>
                <p v-if="!isOpdLocked" class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                    Pilih satu atau lebih OPD yang terlibat dalam kerjasama ini
                </p>

                <!-- Auto-fill chip ketika user Pemkot dengan id_opd -->
                <div v-if="isOpdLocked && userOpd" class="flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-sm">
                        <Icon icon="solar:building-bold" class="w-4 h-4" />
                        {{ userOpd.nama }}{{ userOpd.singkatan ? ` (${userOpd.singkatan})` : '' }}
                    </span>
                </div>

                <!-- Multi-select untuk user yang tidak terkunci -->
                <MultiSelect
                    v-else
                    v-model="form.opd_ids"
                    :options="opdOptions"
                    option-label="label"
                    option-value="id"
                    placeholder="Pilih OPD terkait..."
                    display="chip"
                    filter
                    :max-selected-labels="3"
                    class="w-full"
                />

                <p v-if="form.errors?.opd_ids" class="text-red-500 text-sm mt-1">{{ form.errors.opd_ids }}</p>
            </div>

            <!-- Tanggal Mulai & Tanggal Berakhir (input langsung) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Tanggal Mulai <span class="text-red-500">*</span>
                    </label>
                    <input v-model="form.tanggal_mulai" type="date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Tanggal Berakhir <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.tanggal_berakhir"
                        type="date"
                        :min="form.tanggal_mulai || undefined"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': hasInvalidEndDate }"
                    />
                    <p v-if="hasInvalidEndDate" class="mt-1 text-sm text-red-500">
                        Tanggal berakhir tidak boleh lebih awal dari tanggal mulai.
                    </p>
                    <p v-else-if="form.errors?.tanggal_berakhir" class="mt-1 text-sm text-red-500">
                        {{ form.errors.tanggal_berakhir }}
                    </p>
                </div>
            </div>

            <!-- Jangka Waktu (otomatis terhitung dari tanggal mulai & berakhir) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Jangka Waktu
                    <span class="ml-2 text-xs font-normal text-blue-600 dark:text-blue-400">
                        <Icon icon="solar:magic-stick-3-bold" class="inline w-3 h-3" />
                        Otomatis dari tanggal mulai & berakhir
                    </span>
                </label>
                <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-blue-50/50 dark:bg-gray-700/40 text-sm">
                    <Icon icon="solar:clock-circle-bold-duotone" class="w-5 h-5 text-blue-600" />
                    <span v-if="jangkaWaktuAuto" class="font-semibold text-gray-800 dark:text-white">
                        {{ jangkaWaktuAuto }}
                    </span>
                    <span v-else class="italic text-gray-500 dark:text-gray-400">
                        Pilih tanggal mulai dan tanggal berakhir terlebih dahulu
                    </span>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Manfaat <span class="text-red-500">*</span> <span class="text-gray-400 text-xs">(500 Kata)</span>
                </label>
                <textarea v-model="form.manfaat" rows="4" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Masukan manfaat kerjasama..."></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Analisis Dampak Sosial dan Lingkungan <span class="text-red-500">*</span> <span class="text-gray-400 text-xs">(1000 Kata)</span>
                </label>
                <textarea v-model="form.analisis_dampak" rows="5" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Analisis dampak sosial dan lingkungan sesuai bidang yang dikerjasamakan..."></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Pembiayaan <span class="text-red-500">*</span> <span class="text-gray-400 text-xs">(150 Kata)</span>
                </label>
                <textarea v-model="form.pembiayaan" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Masukan skema pembiayaan..."></textarea>
            </div>
        </div>
    </div>
</template>
