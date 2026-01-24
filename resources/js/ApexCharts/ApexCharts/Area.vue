<template>
    <div class="flex justify-between mb-3">
        <div>
            <h5 class="leading-none text-lg font-extrabold text-gray-900 dark:text-white">Area Chart</h5>
            <p class="text-sm font-normal text-gray-500 dark:text-gray-400">ApexCharts contoh penggunaan</p>
        </div>
        <div class="flex items-center px-2.5 py-0.5 text-base font-semibold text-green-500 dark:text-green-500 text-center">
            12%
            <svg class="w-3 h-3 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13V1m0 0L1 5m4-4 4 4" />
            </svg>
        </div>
    </div>
    <div ref="chartContainer"></div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import ApexCharts from 'apexcharts';

const chartContainer = ref(null);


const options = {
    chart: {
        height: 360,
        maxWidth: "100%",
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
        enabled: false,
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
    series: [{
        name: "New users",
        data: [6500, 6418, 6456, 6526, 6356, 6456],
        color: "#1A56DB",
    }, ],
    xaxis: {
        categories: ['01 Feb', '02 Feb', '03 Feb', '04 Feb', '05 Feb', '06 Feb', '07 Feb'],
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
        const chart = new ApexCharts(chartContainer.value, options);
        chart.render();
    }
});

</script>
