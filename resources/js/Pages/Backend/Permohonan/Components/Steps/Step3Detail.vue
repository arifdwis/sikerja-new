<script setup>
import { Icon } from '@iconify/vue';

defineProps({
    form: Object
});
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
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Jangka Waktu <span class="text-red-500">*</span> <span class="text-gray-400 text-xs">(100 Kata)</span>
                </label>
                <textarea v-model="form.jangka_waktu" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Masukan jangka waktu kerjasama..."></textarea>
            </div>

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
                    <input v-model="form.tanggal_berakhir" type="date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
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
