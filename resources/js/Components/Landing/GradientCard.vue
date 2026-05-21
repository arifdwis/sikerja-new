<script setup>
defineProps({
    variant: {
        type: String,
        default: 'teal-to-sky',
        validator: (value) => ['teal-to-cyan', 'cyan-to-teal'].includes(value)
    },
    padding: {
        type: String,
        default: 'p-8'
    },
    rounded: {
        type: String,
        default: 'rounded-2xl'
    },
    hover: {
        type: Boolean,
        default: true
    }
})

const gradientClasses = {
    'teal-to-cyan': 'from-teal-500 to-cyan-500',
    'cyan-to-teal': 'from-cyan-500 to-teal-500'
}
</script>

<template>
    <div
        :class="[
            'bg-gradient-to-br',
            gradientClasses[variant],
            padding,
            rounded,
            'shadow-xl',
            hover ? 'hover:shadow-2xl hover:scale-[1.03] transition-all duration-300' : ''
        ]"
    >
        <slot />
    </div>
</template>
