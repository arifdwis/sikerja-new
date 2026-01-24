<template>
	<div class="flex justify-between mb-3">
		<div>
			<h5 class="leading-none text-lg font-extrabold text-gray-900 dark:text-white">Column Chart</h5>
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

const chartContainer = ref( null );



const options = {
	colors: [ "#1A56DB", "#FDBA8C" ],
	series: [
		{
			name: "Organic",
			color: "#1A56DB",
			data: [
				{ x: "Mon", y: 231 },
				{ x: "Tue", y: 122 },
				{ x: "Wed", y: 63 },
				{ x: "Thu", y: 421 },
				{ x: "Fri", y: 122 },
				{ x: "Sat", y: 323 },
				{ x: "Sun", y: 111 },
      ],
    },
		{
			name: "Social media",
			color: "#FDBA8C",
			data: [
				{ x: "Mon", y: 232 },
				{ x: "Tue", y: 113 },
				{ x: "Wed", y: 341 },
				{ x: "Thu", y: 224 },
				{ x: "Fri", y: 522 },
				{ x: "Sat", y: 411 },
				{ x: "Sun", y: 243 },
      ],
    },
  ],
	chart: {
		height: 360,
		maxWidth: "100%",
		type: "bar",
		fontFamily: "Inter, sans-serif",
		toolbar: {
			show: false,
		},
	},
	plotOptions: {
		bar: {
			horizontal: false,
			columnWidth: "70%",
			borderRadiusApplication: "end",
			borderRadius: 8,
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
			left: 2,
			right: 2,
			top: -14
		},
	},
	dataLabels: {
		enabled: false,
	},
	legend: {
		show: false,
	},
	xaxis: {
		floating: false,
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
	fill: {
		opacity: 1,
	},
}

onMounted( ( ) => {
	if ( chartContainer.value ) {
		const chart = new ApexCharts( chartContainer.value, options );
		chart.render( );
	}
} );

</script>
