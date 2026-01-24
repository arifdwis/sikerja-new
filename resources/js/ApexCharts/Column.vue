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
	colors: [ "#1A56DB", "#FDBA8C" ],
	series: props.series,
	chart: {
		height: "100%",
		width: "100%",
		type: "bar",
		fontFamily: "Inter, sans-serif",
		toolbar: {
			show: false,
		},
	},
	plotOptions: {
		bar: {
			horizontal: true,
			barHeight: "70%",
			borderRadiusApplication: "end",
			borderRadius: 4,
		},
	},
	tooltip: {
		shared: true,
		intersect: false,
		style: {
			fontFamily: "Inter, sans-serif",
		},
	},
	states: {
		hover: {
			filter: {
				type: "darken",
				value: 1,
			},
		},
	},
	stroke: {
		show: true,
		width: 0,
		colors: [ "transparent" ],
	},
	grid: {
		show: true,
		strokeDashArray: 4,
		padding: {
			left: 10,
			right: 10,
			top: -10
		},
	},
	dataLabels: {
		enabled: true,
        textAnchor: 'start',
        style: {
            colors: ['#fff']
        },
        offsetX: 0,
	},
	legend: {
		show: false,
	},
	xaxis: {
		floating: false,
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
		show: true,
        labels: {
            style: {
                fontFamily: "Inter, sans-serif",
                cssClass: 'text-xs font-normal fill-gray-500 dark:fill-gray-400',
            },
            maxWidth: 200, // Allow more width for labels
        }
	},
	fill: {
		opacity: 1,
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
