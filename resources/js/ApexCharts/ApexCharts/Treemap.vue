<template>
	<div class="flex justify-between mb-3">
		<div>
			<h5 class="leading-none text-lg font-extrabold text-gray-900 dark:text-white">Treemap Chart</h5>
			<p class="text-sm font-normal text-gray-500 dark:text-gray-400">ApexCharts contoh penggunaan</p>
		</div>
	</div>
	<div ref="chartContainer"></div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import ApexCharts from 'apexcharts';

const chartContainer = ref( null );

const options = {
	chart: {
		height: 460,
		maxWidth: "100%",
		type: "treemap",
		fontFamily: "Inter, sans-serif",
		toolbar: { show: false },
	},
	series: [
		{
			name: "Kategori",
			data: [
				{ x: "Kategori A", y: 10 },
				{ x: "Kategori B", y: 20 },
				{ x: "Kategori C", y: 30 },
				{ x: "Kategori D", y: 40 },
				{ x: "Kategori E", y: 50 },
			],
		},
	],
	legend: { show: false },
	dataLabels: {
		enabled: true,
		style: { fontSize: "14px" },
	},
	colors: [ "#FF4560", "#008FFB", "#00E396", "#FEB019", "#775DD0" ],
};

onMounted( ( ) => {
	if ( chartContainer.value ) {
		const chart = new ApexCharts( chartContainer.value, options );
		chart.render( );
	}
} );

</script>
