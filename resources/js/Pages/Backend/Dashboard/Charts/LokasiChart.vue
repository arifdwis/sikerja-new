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
    data: Array, // [{kota: 'Samarinda', total: 10}, ...]
    darkMode: Boolean
});

const chartData = ref();
const chartOptions = ref();

const setChartData = () => {
    const documentStyle = getComputedStyle(document.documentElement);
    
    return {
        labels: props.data.map(d => d.kota),
        datasets: [
            {
                label: 'Jumlah Kerjasama',
                backgroundColor: props.darkMode ? documentStyle.getPropertyValue('--p-orange-400') : documentStyle.getPropertyValue('--p-orange-500'),
                borderColor: props.darkMode ? documentStyle.getPropertyValue('--p-orange-400') : documentStyle.getPropertyValue('--p-orange-500'),
                data: props.data.map(d => d.total),
                borderRadius: 4,
                barPercentage: 0.6
            }
        ]
    };
};

const setChartOptions = () => {
    const textColor = props.darkMode ? '#ffffff' : '#334155';
    const textColorSecondary = props.darkMode ? '#94a3b8' : '#64748b';
    const surfaceBorder = props.darkMode ? '#334155' : '#e2e8f0';

    return {
        indexAxis: 'y', // Horizontal
        maintainAspectRatio: false,
        aspectRatio: 0.8,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            x: {
                ticks: {
                    color: textColorSecondary,
                    font: {
                        weight: 500
                    }
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
                    display: false,
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
