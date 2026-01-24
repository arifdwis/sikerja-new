<template>
    <div class="card w-full h-full">
        <!-- Title removed, handled by parent -->
        <Chart type="bar" :data="chartData" :options="chartOptions" class="w-full h-full" />
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import Chart from 'primevue/chart';

const props = defineProps({
    data: Array, // [{kategori: 'Name', total: 10}, ...]
    darkMode: Boolean
});

const chartData = ref();
const chartOptions = ref();

const setChartData = () => {
    const documentStyle = getComputedStyle(document.documentElement);
    
    return {
        labels: props.data.map(d => d.kategori),
        datasets: [
            {
                label: 'Jumlah',
                backgroundColor: props.darkMode ? documentStyle.getPropertyValue('--p-indigo-400') : documentStyle.getPropertyValue('--p-indigo-500'),
                borderColor: props.darkMode ? documentStyle.getPropertyValue('--p-indigo-400') : documentStyle.getPropertyValue('--p-indigo-500'),
                data: props.data.map(d => d.total),
                borderRadius: 4,
                barPercentage: 0.5
            }
        ]
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
                display: false
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                backgroundColor: props.darkMode ? 'rgba(0,0,0,0.8)' : 'rgba(255,255,255,0.9)',
                titleColor: props.darkMode ? '#fff' : '#000',
                bodyColor: props.darkMode ? '#fff' : '#000',
                borderColor: surfaceBorder,
                borderWidth: 1,
                padding: 10,
                callbacks: {
                     title: (tooltipItems) => {
                        // Return full label in tooltip
                        return tooltipItems[0].label;
                     }
                }
            }
        },
        layout: {
            padding: {
                bottom: 10 // Extra padding for labels
            }
        },
        scales: {
            x: {
                ticks: {
                    color: textColorSecondary,
                    font: {
                        weight: 500,
                        size: 11
                    },
                    autoSkip: false,
                    maxRotation: 45,
                    minRotation: 45,
                    callback: function(value) {
                         // Truncate long labels on axis
                         const label = this.getLabelForValue(value);
                         if (label.length > 15) {
                             return label.substring(0, 15) + '...';
                         }
                         return label;
                    }
                },
                grid: {
                    display: false,
                    drawBorder: false
                }
            },
            y: {
                ticks: {
                    color: textColorSecondary,
                    stepSize: 1
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
    chartOptions.value = setChartOptions();
}, { deep: true });

onMounted(() => {
    chartData.value = setChartData();
    chartOptions.value = setChartOptions();
});
</script>
