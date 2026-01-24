<script setup>
defineProps({
    title: {
        type: String,
        required: true
    },
    subtitle: {
        type: String,
        default: ''
    },
    align: {
        type: String,
        default: 'center',
        validator: (value) => ['left', 'center', 'right'].includes(value)
    },
    size: {
        type: String,
        default: 'lg',
        validator: (value) => ['md', 'lg', 'xl'].includes(value)
    }
})

const alignClasses = {
    left: 'text-left',
    center: 'text-center',
    right: 'text-right'
}

const sizeClasses = {
    md: { title: 'text-3xl lg:text-4xl', subtitle: 'text-base' },
    lg: { title: 'text-4xl lg:text-5xl', subtitle: 'text-lg' },
    xl: { title: 'text-5xl lg:text-6xl', subtitle: 'text-xl lg:text-2xl' }
}
</script>

<template>
    <div :class="[alignClasses[align], 'mb-12']" data-aos="fade-up">
        <h2
            :class="[
                sizeClasses[size].title,
                'font-black mb-4 bg-gradient-to-r from-emerald-600 to-green-600 bg-clip-text text-transparent'
            ]"
        >
            {{ title }}
        </h2>
        <p
            v-if="subtitle"
            :class="[
                sizeClasses[size].subtitle,
                'text-gray-600 dark:text-gray-300 max-w-3xl',
                align === 'center' ? 'mx-auto' : '',
                align === 'right' ? 'ml-auto' : ''
            ]"
        >
            {{ subtitle }}
        </p>
    </div>
</template>
