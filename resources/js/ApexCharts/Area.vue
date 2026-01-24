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

const options = {
    chart: {
        height: "100%",
        width: "100%",
        type: "area",
        fontFamily: "Inter, sans-serif",
        dropShadow: {
            enabled: false,
        },
        toolbar: {
            show: false,
        },
    },
    tooltip: {
        enabled: true,
        x: {
            show: false,
        },
    },
    fill: {
        type: "gradient",
        gradient: {
            opacityFrom: 0.55,
            opacityTo: 0,
            shade: "#1C64F2",
            gradientToColors: ["#1C64F2"],
        },
    },
    dataLabels: {
        enabled: false,
    },
    stroke: {
        width: 4,
    },
    grid: {
        show: true,
        strokeDashArray: 4,
        padding: {
            left: 0,
            right: 0,
            top: 0
        },
    },
    series: props.series,
    xaxis: {
        categories: props.categories,
        labels: {
            show: true,
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
