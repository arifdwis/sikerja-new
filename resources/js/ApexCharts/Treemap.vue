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
    }
});

const chartContainer = ref(null);
let chart = null;

/*
 Expected props.series format for Treemap:
 [
   {
     data: [
       { x: 'Instansi A', y: 10 },
       { x: 'Instansi B', y: 5 }
     ]
   }
 ]
*/

const options = {
    series: props.series,
    legend: {
        show: false
    },
    chart: {
        height: "100%",
        width: "100%",
        type: 'treemap',
        fontFamily: "Inter, sans-serif",
        toolbar: {
            show: false
        }
    },
    colors: [
        '#3B82F6',
        '#10B981',
        '#F59E0B',
        '#EF4444', 
        '#8B5CF6'
    ],
    plotOptions: {
        treemap: {
            distributed: true,
            enableShades: false
        }
    },
    dataLabels: {
        enabled: true,
        style: {
            fontSize: '12px',
        },
        formatter: function(text, op) {
            return [text, op.value]
        },
        offsetY: -4
    }
};

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

</script>
