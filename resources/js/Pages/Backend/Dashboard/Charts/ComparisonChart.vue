<template>
    <div class="card w-full h-full">
        <Chart type="line" :data="chartData" :options="chartOptions" class="w-full h-full" />
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

const setChartData = () => {
    const documentStyle = getComputedStyle(document.documentElement);
    
    return {
        labels: props.data?.labels || [],
        datasets: (props.data?.datasets || []).map(ds => ({
            label: ds.label,
            data: ds.data,
            fill: false,
            borderColor: ds.color === 'teal' 
                ? (props.darkMode ? documentStyle.getPropertyValue('--p-teal-400') : documentStyle.getPropertyValue('--p-teal-500'))
                : (props.darkMode ? documentStyle.getPropertyValue('--p-gray-400') : documentStyle.getPropertyValue('--p-gray-400')),
            tension: 0.4,
            borderWidth: 2
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
            }
        },
        scales: {
            x: {
                ticks: {
                    color: textColorSecondary
                },
                grid: {
                    color: surfaceBorder,
                    drawBorder: false
                }
            },
            y: {
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
