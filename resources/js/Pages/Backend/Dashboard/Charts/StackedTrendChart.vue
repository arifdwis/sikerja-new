<template>
    <div class="card w-full h-full">
        <!-- Title handled by parent -->
        <Chart type="bar" :data="chartData" :options="chartOptions" class="w-full h-full" />
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import Chart from 'primevue/chart';

const props = defineProps({
    data: Object, // { labels: [], datasets: [{label, data, color}, ...] }
    darkMode: Boolean
});

const chartData = ref();
const chartOptions = ref();

const resolveColor = (colorStr, darkMode) => {
    const map = {
        'bg-teal-600': '#0d9488',
        'bg-cyan-600': '#0891b2',
        'bg-blue-600': '#2563eb',
        'bg-indigo-600': '#4f46e5',
        'bg-green-600': '#16a34a',
        'bg-red-600': '#dc2626',

        // Fallbacks
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
        labels: props.data.labels,
        datasets: props.data.datasets.map(ds => ({
            type: 'bar',
            label: ds.label,
            backgroundColor: resolveColor(ds.color, props.darkMode),
            data: ds.data,
            stack: 'Stack 0',
            barPercentage: 0.6
        }))
    };
};

const setChartOptions = () => {
    const textColor = props.darkMode ? '#ffffff' : '#334155';
    const textColorSecondary = props.darkMode ? '#94a3b8' : '#64748b';
    const surfaceBorder = props.darkMode ? '#334155' : '#e2e8f0';

    return {
        maintainAspectRatio: false,
        aspectRatio: 0.8,
        plugins: {
            legend: {
                labels: {
                    color: textColor
                }
            },
            tooltip: {
                mode: 'index',
                intersect: false
            }
        },
        scales: {
            x: {
                stacked: true,
                ticks: {
                    color: textColorSecondary
                },
                grid: {
                    display: false,
                    drawBorder: false
                }
            },
            y: {
                stacked: true,
                ticks: {
                    color: textColorSecondary
                },
                grid: {
                    color: surfaceBorder,
                    drawBorder: false
                }
            }
        }
    };
};

watch(() => props.data, () => {
    chartData.value = setChartData();
}, { deep: true });

watch(() => props.darkMode, () => {
    chartData.value = setChartData();
    chartOptions.value = setChartOptions();
});

onMounted(() => {
    chartData.value = setChartData();
    chartOptions.value = setChartOptions();
});
</script>
