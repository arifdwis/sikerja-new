<template>
    <div class="card w-full h-full">
        <!-- Title handled by parent -->
        <Chart type="bar" :data="chartData" :options="chartOptions" class="w-full h-full" />
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import Chart from 'primevue/chart';

const props = defineProps({
    data: Array, // [{bulan: 'Jan', total: 10}, ...]
    darkMode: Boolean
});

const chartData = ref();
const chartOptions = ref();

const setChartData = () => {
    const documentStyle = getComputedStyle(document.documentElement);
    
    return {
        labels: props.data.map(d => d.bulan),
        datasets: [
            {
                label: 'Jumlah Permohonan',
                backgroundColor: props.darkMode ? '#818cf8' : '#4f46e5', // Indigo-400 / Indigo-600
                borderColor: props.darkMode ? '#818cf8' : '#4f46e5',
                data: props.data.map(d => d.total),
                borderRadius: 4,
                barPercentage: 0.6
            }
        ]
    };
};

const setChartOptions = () => {
    const documentStyle = getComputedStyle(document.documentElement);
    const textColor = props.darkMode ? '#ffffff' : '#334155';
    const textColorSecondary = props.darkMode ? '#94a3b8' : '#64748b';
    const surfaceBorder = props.darkMode ? '#334155' : '#e2e8f0';

    return {
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
                    display: false,
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

watch(() => props.data, setChartData, { deep: true });
watch(() => props.darkMode, () => {
    chartData.value = setChartData();
    chartOptions.value = setChartOptions();
});

onMounted(() => {
    chartData.value = setChartData();
    chartOptions.value = setChartOptions();
});
</script>
