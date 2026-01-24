<template>
    <div class="card flex flex-col items-center w-full h-full justify-center">
        <!-- Title handled by parent -->
        <Chart type="doughnut" :data="chartData" :options="chartOptions" class="w-full h-full" />
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import Chart from 'primevue/chart';

const props = defineProps({
    data: Array, // [{status: 'Baru', total: 10, color: '#hex'}, ...]
    darkMode: Boolean
});

const chartData = ref();
const chartOptions = ref();

// Map Tailwind colors to Hex if needed, but Controller sends 'color' field which might be tailwind class string.
// We need to resolve that.
const resolveColor = (colorStr, darkMode) => {
    // Basic mapping for tailwind classes if passed directly
    const map = {
        'bg-teal-600': '#0d9488',
        'bg-cyan-600': '#0891b2',
        'bg-blue-600': '#2563eb',
        'bg-indigo-600': '#4f46e5',
        'bg-green-600': '#16a34a',
        'bg-red-600': '#dc2626',
        
        // Fallbacks for old values just in case
        'bg-yellow-500': '#0d9488', 
        'bg-blue-500': '#0891b2',
        'bg-indigo-500': '#2563eb',
        'bg-purple-500': '#4f46e5',
        'bg-green-500': '#16a34a',
        'bg-red-500': '#dc2626',
    };
    return map[colorStr] || colorStr || '#cccccc';
};

const setChartData = () => {
    return {
        labels: props.data.map(d => d.status),
        datasets: [
            {
                data: props.data.map(d => d.total),
                backgroundColor: props.data.map(d => resolveColor(d.color)),
                hoverBackgroundColor: props.data.map(d => resolveColor(d.color)),
                borderWidth: 2
            }
        ]
    };
};

const setChartOptions = () => {
    const textColor = props.darkMode ? '#ffffff' : '#334155';

    return {
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    usePointStyle: true,
                    color: textColor,
                    padding: 20
                }
            }
        },
        cutout: '70%'
    };
};

watch(() => props.data, () => {
    chartData.value = setChartData();
    chartOptions.value = setChartOptions();
}, { deep: true });

onMounted(() => {
    chartData.value = setChartData();
    chartOptions.value = setChartOptions();
});
</script>
