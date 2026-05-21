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
    <div class="relative bg-slate-900 pt-36 pb-20 overflow-hidden font-['Inter'] border-b border-slate-800">
        <div class="relative z-10 max-w-7xl mx-auto px-6 text-center">
            <h1 class="font-['Outfit'] text-3xl md:text-5xl lg:text-6xl font-black text-white mb-6 leading-tight max-w-5xl mx-auto">
                {{ title }}
            </h1>
            <p v-if="subtitle" class="text-slate-300 text-lg md:text-xl max-w-3xl mx-auto mb-8 font-light leading-relaxed">
                {{ subtitle }}
            </p>

            <!-- Breadcrumbs -->
            <nav v-if="breadcrumbs.length" class="flex justify-center items-center text-sm font-medium text-slate-400">
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
