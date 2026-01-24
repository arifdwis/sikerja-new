<script setup>
import { Icon } from '@iconify/vue'

defineProps({
    icon: {
        type: String,
        required: true
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg', 'xl'].includes(value)
    },
    variant: {
        type: String,
        default: 'gradient',
        validator: (value) => ['gradient', 'white', 'transparent'].includes(value)
    },
    hover: {
        type: Boolean,
        default: false
    },
    ariaLabel: {
        type: String,
        default: ''
    }
})

const sizeClasses = {
    sm: { box: 'w-12 h-12', icon: 'w-6 h-6' },
    md: { box: 'w-16 h-16', icon: 'w-8 h-8' },
    lg: { box: 'w-20 h-20', icon: 'w-10 h-10' },
    xl: { box: 'w-24 h-24', icon: 'w-12 h-12' }
}

const variantClasses = {
    gradient: 'bg-gradient-to-br from-emerald-500 to-green-500 text-white',
    white: 'bg-white/20 backdrop-blur-md text-white',
    transparent: 'bg-white/10 backdrop-blur-md text-white'
}
</script>

<template>
    <div
        :class="[
            sizeClasses[size].box,
            variantClasses[variant],
            'rounded-2xl flex items-center justify-center shadow-lg',
            hover ? 'group-hover:scale-110 transition-transform' : ''
        ]"
        :aria-label="ariaLabel"
        :role="ariaLabel ? 'img' : undefined"
    >
        <Icon
            :icon="icon"
            :class="sizeClasses[size].icon"
            :aria-hidden="!ariaLabel"
        />
    </div>
</template>
