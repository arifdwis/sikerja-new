<script setup>
defineProps({
    variant: {
        type: String,
        default: 'teal-to-sky',
        validator: (value) => ['emerald-to-green', 'green-to-emerald'].includes(value)
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
    'emerald-to-green': 'from-emerald-500 to-green-500',
    'green-to-emerald': 'from-green-500 to-emerald-500'
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
