<script setup>
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

defineProps({
    title: {
        type: String,
        required: true
    },
    subtitle: {
        type: String,
        default: ''
    },
    breadcrumbs: {
        type: Array,
        default: () => []
    }
})
</script>

<template>
    <div class="relative bg-emerald-900 pt-48 pb-32 overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-900 via-green-900 to-teal-900"></div>
        <div class="absolute inset-0 opacity-20">
            <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <path d="M0 100 C 20 0 50 0 100 100 Z" fill="white" fill-opacity="0.1" />
            </svg>
        </div>
        <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-500/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-green-500/20 rounded-full blur-3xl animate-pulse" style="animation-delay:1s;"></div>

        <div class="relative z-10 max-w-7xl mx-auto px-6 text-center">
            <h1 class="font-['Outfit'] text-3xl md:text-5xl lg:text-6xl font-black text-white mb-6 drop-shadow-lg leading-tight max-w-5xl mx-auto">
                {{ title }}
            </h1>
            <p v-if="subtitle" class="text-emerald-100 text-lg md:text-xl max-w-3xl mx-auto mb-8 font-light leading-relaxed">
                {{ subtitle }}
            </p>

            <!-- Breadcrumbs -->
            <nav v-if="breadcrumbs.length" class="flex justify-center items-center text-sm font-medium text-emerald-200/80">
                <Link href="/" class="hover:text-white transition-colors flex items-center gap-1">
                    <Icon icon="solar:home-smile-bold" class="w-4 h-4" />
                    Beranda
                </Link>
                <template v-for="(crumb, index) in breadcrumbs" :key="index">
                    <Icon icon="solar:alt-arrow-right-bold" class="w-4 h-4 mx-2 opacity-50" />
                    <span v-if="index === breadcrumbs.length - 1" class="text-white">
                        {{ crumb.label }}
                    </span>
                    <Link v-else :href="crumb.url" class="hover:text-white transition-colors">
                        {{ crumb.label }}
                    </Link>
                </template>
            </nav>
        </div>
    </div>
</template>
