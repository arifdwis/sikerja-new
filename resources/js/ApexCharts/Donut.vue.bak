<template>
	<div ref="chartContainer" class="flex justify-center w-full h-full"></div>
</template>
<script setup>
import { ref, onMounted, watch } from 'vue';
import ApexCharts from 'apexcharts';

const props = defineProps({
    series: {
        type: Array,
        required: true
    },
    labels: {
        type: Array,
        required: true
    },
    colors: {
        type: Array,
        default: () => []
    }
});

const chartContainer = ref(null);
let chart = null;

// Simple mapping for Tailwind classes to Hex if needed, 
// or hope user passes hex. 
// Given the context, the backend sends 'bg-yellow-500'. 
// We should map these to hex codes.
const getColorHex = (tailwindClass) => {
    const map = {
        'bg-yellow-500': '#EAB308',
        'bg-blue-500': '#3B82F6',
        'bg-indigo-500': '#6366F1',
        'bg-purple-500': '#A855F7',
        'bg-green-500': '#22C55E',
        'bg-red-500': '#EF4444',
    };
    return map[tailwindClass] || tailwindClass; // Return mapped hex or original if not found
};

const getChartColors = () => {
    if (props.colors && props.colors.length) {
        return props.colors.map(c => getColorHex(c));
    }
    return undefined;
}

const options = {
	series: props.series,
	colors: getChartColors(),
	chart: {
		height: 320,
		width: "100%",
		type: "donut",
        fontFamily: "Inter, sans-serif",
	},
	stroke: {
		colors: ["transparent"],
		lineCap: "",
	},
	plotOptions: {
		pie: {
			donut: {
				labels: {
					show: true,
					name: {
						show: true,
						fontFamily: "Inter, sans-serif",
						offsetY: 20,
					},
					total: {
						showAlways: true,
						show: true,
						label: "Total",
						fontFamily: "Inter, sans-serif",
						formatter: function (w) {
							const sum = w.globals.seriesTotals.reduce((a, b) => {
								return a + b
							}, 0)
							return sum
						},
					},
					value: {
						show: true,
						fontFamily: "Inter, sans-serif",
						offsetY: -20,
						formatter: function (value) {
							return value
						},
					},
				},
				size: "80%",
			},
		},
	},
	grid: {
		padding: {
			top: -2,
		},
	},
	labels: props.labels,
	dataLabels: {
		enabled: true,
        formatter: function (val) {
            return val.toFixed(1) + "%"
        },
	},
	legend: {
		position: "bottom",
		fontFamily: "Inter, sans-serif",
        formatter: function(seriesName, opts) {
            return seriesName + ": " + opts.w.globals.series[opts.seriesIndex]
        }
	},
	yaxis: {
		labels: {
			formatter: function (value) {
				return value
			},
		},
	},
	xaxis: {
		labels: {
			formatter: function (value) {
				return value 
			},
		},
		axisTicks: {
			show: false,
		},
		axisBorder: {
			show: false,
		},
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

watch(() => props.labels, (newLabels) => {
    if (chart) {
        chart.updateOptions({
            labels: newLabels
        });
    }
}, { deep: true });

</script>
