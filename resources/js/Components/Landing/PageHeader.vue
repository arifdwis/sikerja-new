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
    <div class="relative bg-slate-950 pt-36 pb-20 overflow-hidden font-['Inter'] border-b border-slate-900">
        <!-- Abstract glowing orbs in the background -->
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_-20%,rgba(245,158,11,0.15),transparent_60%)]"></div>
        <div class="absolute inset-0 bg-[linear-gradient(to_bottom,transparent,rgba(15,23,42,0.85))]"></div>
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:32px_32px] opacity-20"></div>

        <div class="relative z-10 max-w-7xl mx-auto px-6 text-center">
            <h1 class="font-['Outfit'] text-4xl md:text-5xl lg:text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-white via-white to-amber-200 mb-4 leading-tight max-w-5xl mx-auto tracking-tight drop-shadow-sm">
                {{ title }}
            </h1>
            <p v-if="subtitle" class="text-slate-400 text-base md:text-lg max-w-3xl mx-auto mb-8 font-medium leading-relaxed">
                {{ subtitle }}
            </p>

            <!-- Breadcrumbs with glassmorphic badge -->
            <nav v-if="breadcrumbs.length" class="inline-flex justify-center items-center px-4 py-2 rounded-full bg-white/5 border border-white/10 backdrop-blur-md text-xs font-semibold text-slate-400 shadow-inner">
                <Link href="/" class="hover:text-amber-300 transition-colors flex items-center gap-1.5">
                    <Icon icon="solar:home-smile-bold-duotone" class="w-4 h-4 text-amber-400" />
                    Beranda
                </Link>
                <template v-for="(crumb, index) in breadcrumbs" :key="index">
                    <Icon icon="solar:alt-arrow-right-bold" class="w-3 h-3 mx-2 text-slate-600" />
                    <span v-if="index === breadcrumbs.length - 1" class="text-white font-bold">
                        {{ crumb.label }}
                    </span>
                    <Link v-else :href="crumb.url" class="hover:text-amber-300 transition-colors">
                        {{ crumb.label }}
                    </Link>
                </template>
            </nav>
        </div>
    </div>
</template>
