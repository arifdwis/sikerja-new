<template>
    <div ref="chartContainer" class="w-full h-full"></div>
</template>
<script setup>
import { ref, onMounted, watch } from 'vue';
import ApexCharts from 'apexcharts';

const props = defineProps({
    series: {
        type: Array,
        required: true
    },
    categories: {
        type: Array,
        required: true
    }
});

const chartContainer = ref(null);
let chart = null;

// Basic Line Chart Options
const options = {
    chart: {
        height: "100%",
        width: "100%",
        type: "line",
        fontFamily: "Inter, sans-serif",
        dropShadow: {
            enabled: true,
            color: '#000',
            top: 18,
            left: 7,
            blur: 10,
            opacity: 0.2
        },
        toolbar: {
            show: false,
        },
    },
    colors: ['#28C76F', '#7367F0'],
    dataLabels: {
        enabled: true,
    },
    stroke: {
        curve: 'smooth'
    },
    grid: {
        borderColor: '#e7e7e7',
        row: {
            colors: ['#f3f3f3', 'transparent'],
            opacity: 0.5
        },
    },
    series: props.series,
    xaxis: {
        categories: props.categories,
        labels: {
            style: {
                fontFamily: "Inter, sans-serif",
                cssClass: 'text-xs font-normal fill-gray-500 dark:fill-gray-400'
            }
        },
        axisBorder: {
            show: false,
        },
        axisTicks: {
            show: false,
        },
    },
    yaxis: {
        show: false,
    },
    tooltip: {
        theme: 'light'
    },
    legend: {
        position: 'top',
        horizontalAlign: 'right',
        floating: true,
        offsetY: -25,
        offsetX: -5
    }
}

onMounted(() => {
    if (chartContainer.value) {
        chart = new ApexCharts(chartContainer.value, options);
        chart.render();
    }
});

watch(() => props.series, (newSeries) => {
    if (chart) {
        chart.updateSeries(newSeries);
    }
}, { deep: true });

watch(() => props.categories, (newCategories) => {
    if (chart) {
        chart.updateOptions({
            xaxis: {
                categories: newCategories
            }
        });
    }
}, { deep: true });

</script>
