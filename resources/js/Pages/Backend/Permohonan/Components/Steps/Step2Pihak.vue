<script setup>
import { Icon } from '@iconify/vue';
import SearchSelect from '@/Components/SearchSelect.vue';

defineProps({
    form: Object,
    pemohon: Object,
    ppksdOptions: Array,
    pemohonanList: Array
});
</script>

<template>
    <div>
        <!-- PPKSD-1: User Profile Card -->
        <div class="mb-10">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mr-3">
                    <Icon icon="solar:user-id-bold" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    </div>
                Pihak Pertama (PPKSD-1)
            </h3>
            
            <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-3xl opacity-100 shadow-xl shadow-blue-500/20 transition-all duration-300"></div>
                <div class="absolute top-0 right-0 w-64 h-full overflow-hidden rounded-r-3xl">
                        <Icon icon="solar:shield-check-bold" class="w-64 h-64 text-white/5 absolute -top-10 -right-10 transform rotate-12" />
                </div>

                <div class="relative p-8 flex flex-col md:flex-row items-center gap-8 z-10">
                    <!-- Avatar -->
                    <div class="shrink-0 relative">
                        <div class="w-24 h-24 rounded-full bg-white/20 backdrop-blur-md border-[3px] border-white/30 flex items-center justify-center shadow-lg">
                            <span class="text-3xl font-bold text-white">{{ pemohon?.name?.charAt(0) || 'U' }}</span>
                        </div>
                        <div class="absolute bottom-0 right-0 bg-green-500 w-6 h-6 rounded-full border-2 border-white shadow-sm flex items-center justify-center">
                                <Icon icon="solar:check-read-bold" class="w-3 h-3 text-white" />
                        </div>
                    </div>
                    
                    <!-- Info -->
                    <div class="flex-1 text-center md:text-left text-white">
                        <div class="mb-6">
                            <h4 class="text-2xl font-bold tracking-tight mb-1">{{ pemohon?.name }}</h4>
                            <p class="text-blue-100 font-medium tracking-wide opacity-90 text-sm">{{ pemohon?.nip || 'NIP Tidak Tersedia' }}</p>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-12 gap-y-4 text-sm">
                            <div class="flex items-center gap-3 bg-white/10 rounded-lg px-4 py-2.5 backdrop-blur-sm border border-white/5 hover:bg-white/20 transition-colors">
                                <Icon icon="solar:briefcase-bold" class="w-5 h-5 text-blue-200" />
                                <div class="text-left">
                                    <p class="text-[10px] text-blue-200 uppercase tracking-wider font-semibold">Jabatan</p>
                                    <p class="font-medium truncate">{{ pemohon?.jabatan || '-' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 bg-white/10 rounded-lg px-4 py-2.5 backdrop-blur-sm border border-white/5 hover:bg-white/20 transition-colors">
                                <Icon icon="solar:buildings-bold" class="w-5 h-5 text-blue-200" />
                                <div class="text-left">
                                    <p class="text-[10px] text-blue-200 uppercase tracking-wider font-semibold">Unit Kerja</p>
                                    <p class="font-medium truncate">{{ pemohon?.unit_kerja || '-' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 bg-white/10 rounded-lg px-4 py-2.5 backdrop-blur-sm border border-white/5 hover:bg-white/20 transition-colors">
                                <Icon icon="solar:phone-bold" class="w-5 h-5 text-blue-200" />
                                <div class="text-left">
                                    <p class="text-[10px] text-blue-200 uppercase tracking-wider font-semibold">Telepon</p>
                                    <p class="font-medium truncate">{{ pemohon?.phone || '-' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 bg-white/10 rounded-lg px-4 py-2.5 backdrop-blur-sm border border-white/5 hover:bg-white/20 transition-colors">
                                <Icon icon="solar:letter-bold" class="w-5 h-5 text-blue-200" />
                                <div class="text-left">
                                    <p class="text-[10px] text-blue-200 uppercase tracking-wider font-semibold">Email</p>
                                    <p class="font-medium truncate">{{ pemohon?.email || '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                <p class="text-xs text-gray-400 mt-3 text-right flex justify-end items-center gap-1">
                <Icon icon="solar:info-circle-bold" class="w-3 h-3" />
                Data diambil otomatis dari profil akun Anda
            </p>
        </div>

        <div class="flex items-center gap-4 my-10">
            <div class="h-px bg-gray-200 dark:bg-gray-700 flex-1"></div>
            <span class="text-sm font-medium text-gray-400 uppercase tracking-widest">Bekerjasama Dengan</span>
            <div class="h-px bg-gray-200 dark:bg-gray-700 flex-1"></div>
        </div>

        <!-- PPKSD-2 Selection -->
        <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center mr-3">
                    <Icon icon="solar:users-group-two-rounded-bold" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                </div>
                Pihak Kedua (PPKSD-2)
            </h3>
            
            <div class="bg-emerald-50/50 dark:bg-emerald-900/10 rounded-3xl p-8 border border-emerald-100 dark:border-emerald-800/20 hover:border-emerald-200 dark:hover:border-emerald-700/50 transition-colors">
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-3">
                    Pilih Pejabat Penandatangan (PPKSD-2) <span class="text-red-500">*</span>
                </label>
                
                <div class="relative">
                    <SearchSelect 
                        v-model="form.id_pemohon_1" 
                        :options="ppksdOptions" 
                        placeholder="-- Pilih Pejabat --"
                    />
                </div>

                    <div v-if="form.id_pemohon_1" class="mt-6 p-4 bg-white dark:bg-gray-800 rounded-2xl border border-emerald-100 dark:border-emerald-800/30 flex items-start gap-4 animate-fade-in-up">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                        <Icon icon="solar:user-check-bold" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Pejabat Terpilih</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                            {{ pemohonanList.find(p => p.id === form.id_pemohon_1)?.name }} 
                            <span class="text-gray-300 mx-2">|</span> 
                            {{ pemohonanList.find(p => p.id === form.id_pemohon_1)?.jabatan }}
                        </p>
                    </div>
                </div>

                <p v-else class="text-sm text-gray-500 mt-4 flex items-center gap-2">
                    <Icon icon="solar:info-circle-linear" class="w-4 h-4 text-emerald-500" />
                    Daftar ini memuat pejabat yang berwenang menandatangani kerjasama dari pihak kedua.
                </p>
            </div>
        </div>
    </div>
</template>
