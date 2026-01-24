<script setup>
import { Icon } from '@iconify/vue'
import IconBox from './IconBox.vue'

defineProps({
    icon: {
        type: String,
        required: true
    },
    title: {
        type: String,
        required: true
    },
    description: {
        type: String,
        required: true
    },
    iconSize: {
        type: String,
        default: 'md'
    },
    variant: {
        type: String,
        default: 'white',
        validator: (value) => ['white', 'gradient'].includes(value)
    },
    layout: {
        type: String,
        default: 'vertical',
        validator: (value) => ['vertical', 'horizontal'].includes(value)
    }
})

const variantClasses = {
    white: 'bg-white dark:bg-gray-800/80 backdrop-blur-xl border border-gray-200/50 dark:border-gray-700',
    gradient: 'bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 border border-emerald-100 dark:border-emerald-800'
}
</script>

<template>
    <div
        :class="[
            variantClasses[variant],
            'group p-8 rounded-2xl hover:border-emerald-500 transition-all duration-500 hover:shadow-2xl hover:-translate-y-2',
            layout === 'horizontal' ? 'flex items-start gap-6' : ''
        ]"
        data-aos="fade-up"
    >
        <IconBox
            :icon="icon"
            :size="iconSize"
            :hover="true"
            :aria-label="title"
            :class="layout === 'vertical' ? 'mb-5' : 'flex-shrink-0'"
        />
        <div :class="layout === 'vertical' ? 'text-center' : 'flex-1'">
            <h3
                :class="[
                    'font-black mb-3 group-hover:text-emerald-600 transition-colors',
                    layout === 'vertical' ? 'text-xl' : 'text-lg'
                ]"
            >
                {{ title }}
            </h3>
            <p class="text-base text-gray-600 dark:text-gray-300 leading-relaxed">
                {{ description }}
            </p>
        </div>
    </div>
</template>
