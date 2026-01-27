<script setup>
import { Icon } from '@iconify/vue';

defineProps({
    summary: Object
});
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Berlangsung (Emerald) -->
        <div class="relative flex cursor-pointer flex-col bg-clip-border rounded-xl text-white shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300 bg-white dark:bg-gray-800">
            <div class="bg-gradient-to-tr from-emerald-800 to-emerald-600 px-6 py-4 h-full">
                <div class="flex justify-between items-start">
                    <div class="text-left">
                        <h1 class="text-3xl font-bold tracking-tight text-white/90">
                            {{ summary?.berlangsung || 0 }}
                        </h1>
                        <p class="mt-2 text-xs uppercase font-semibold text-white/80">
                            Berlangsung
                        </p>
                    </div>
                    <div class="flex items-center justify-center">
                        <Icon icon="solar:play-broken" class="w-10 h-10 text-white/60" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Segera Berakhir (Teal) -->
        <div class="relative flex cursor-pointer flex-col bg-clip-border rounded-xl text-white shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300 bg-white dark:bg-gray-800">
            <div class="bg-gradient-to-tr from-teal-800 to-teal-600 px-6 py-4 h-full">
                <div class="flex justify-between items-start">
                    <div class="text-left">
                        <h1 class="text-3xl font-bold tracking-tight text-white/90">
                            {{ summary?.segera_berakhir || 0 }}
                        </h1>
                        <p class="mt-2 text-xs uppercase font-semibold text-white/80">
                            Segera Berakhir
                        </p>
                    </div>
                    <div class="flex items-center justify-center">
                        <Icon icon="solar:alarm-broken" class="w-10 h-10 text-white/60" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Telah Berakhir (Red) -->
        <div class="relative flex cursor-pointer flex-col bg-clip-border rounded-xl text-white shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300 bg-white dark:bg-gray-800">
            <div class="bg-gradient-to-tr from-red-800 to-red-600 px-6 py-4 h-full">
                <div class="flex justify-between items-start">
                    <div class="text-left">
                        <h1 class="text-3xl font-bold tracking-tight text-white/90">
                            {{ summary?.berakhir || 0 }}
                        </h1>
                        <p class="mt-2 text-xs uppercase font-semibold text-white/80">
                            Telah Berakhir
                        </p>
                    </div>
                    <div class="flex items-center justify-center">
                        <Icon icon="solar:close-circle-broken" class="w-10 h-10 text-white/60" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
