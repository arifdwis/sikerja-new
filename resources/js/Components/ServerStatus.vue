<script setup>
import { ref, onMounted } from 'vue';
import ApexCharts from 'apexcharts';
import axios from 'axios';

const stats = ref( {} );

const memoryUsage = ref( 0 );
const freeMemory = ref( 0 );
const totalDisk = ref( 0 );
const cpuLoad = ref( 0 );

// Refs for server data
const memoryData = ref( [
  {
    x: new Date( )
      .getTime( ),
    y: 0
  } // Initial data point
] );
const cpuData = ref( [
  {
    x: new Date( )
      .getTime( ),
    y: 0
  } // Initial data point
] );

let lastDate = new Date( )
  .getTime( );

// Function to fetch server status (simulated or actual API)
const fetchServerStatus = async ( ) => {
  try {
    const response = await axios.get( 'https://des2024.test/api/server-status' );
    const dataFromApi = response.data;

    memoryUsage.value = dataFromApi.memoryUsage;
    freeMemory.value = dataFromApi.freeMemory;
    totalDisk.value = dataFromApi.totalDisk;
    cpuLoad.value = dataFromApi.cpuLoad;

    // Update data points (simulating memory and CPU usage)
    memoryData.value.push( {
      x: new Date( )
        .getTime( ),
      y: parseFloat( dataFromApi.memoryUsagePercentage ) // Update with actual data
    } );

    cpuData.value.push( {
      x: new Date( )
        .getTime( ),
      y: parseFloat( dataFromApi.cpuLoadPercentage ) // Update with actual data
    } );

    // Keep data length to avoid performance issues
    if ( memoryData.value.length > 50 ) memoryData.value.shift( );
    if ( cpuData.value.length > 50 ) cpuData.value.shift( );

    // Update chart with new data
    memoryChart.updateSeries( [ { data: memoryData.value } ] );
    cpuChart.updateSeries( [ { data: cpuData.value } ] );

  } catch ( error ) {
    console.error( 'Error fetching server status:', error );
  }
};

// Chart options for memory usage
const memoryChartOptions = {
  series: [ {
    data: memoryData.value
  } ],
  chart: {
    id: 'memoryRealtime',
    height: 350,
    type: 'line',
    animations: {
      enabled: true,
      easing: 'linear',
      dynamicAnimation: {
        speed: 1000
      }
    },
    toolbar: {
      show: false
    },
    zoom: {
      enabled: false
    }
  },
  dataLabels: {
    enabled: false
  },
  title: {
    text: 'Realtime Memory Usage',
    align: 'left'
  },
  xaxis: {
    type: 'datetime',
    labels: {
      formatter: function ( value ) {
        const date = new Date( value );
        return `${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
      }
    }
  },
  yaxis: {
    // max: 2
  },
  legend: {
    show: false
  },
};

// Chart options for CPU load
const cpuChartOptions = {
  series: [ {
    data: cpuData.value
  } ],
  chart: {
    id: 'cpuRealtime',
    height: 350,
    type: 'line',
    animations: {
      enabled: true,
      easing: 'linear',
      dynamicAnimation: {
        speed: 1000
      }
    },
    toolbar: {
      show: false
    },
    zoom: {
      enabled: false
    }
  },
  dataLabels: {
    enabled: false
  },
  title: {
    text: 'Realtime CPU Load',
    align: 'left'
  },
  xaxis: {
    type: 'datetime',
    labels: {
      formatter: function ( value ) {
        const date = new Date( value );
        return `${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
      }
    }
  },
  yaxis: {
    // max: 100
  },
  legend: {
    show: false
  },
};

// Initialize both charts when mounted
let memoryChart = null;
let cpuChart = null;

onMounted( ( ) => {
  // Create the memory usage realtime chart
  memoryChart = new ApexCharts( document.querySelector( '#memoryChart' ), memoryChartOptions );
  memoryChart.render( );

  // Create the CPU load realtime chart
  cpuChart = new ApexCharts( document.querySelector( '#cpuChart' ), cpuChartOptions );
  cpuChart.render( );

  // Fetch server status and update chart every 5 seconds
  fetchServerStatus( );
  setInterval( fetchServerStatus, 5000 ); // Update every 5 seconds
} );

</script>
<template>
  <div class="stats">
    <div class="h-9/10 max-w-full w-full bg-white rounded-lg shadow-sm dark:bg-gray-800 p-4 md:p-6">
      <p><strong>Total Disk:</strong> {{ totalDisk }} GB</p>
      <p><strong>Free Memory:</strong> {{ freeMemory }} MB</p>
      <p><strong>Memory Usage:</strong> {{ memoryUsage }} MB</p>
      <p><strong>CPU Load (1 min avg):</strong> {{ cpuLoad }}</p>
    </div>
  </div>
  <div class="grid grid-cols-2 gap-4">
    <div class="h-9/10 max-w-full w-full bg-white rounded-lg shadow-sm dark:bg-gray-800 p-4 md:p-6">
      <div id="memoryChart"></div>
    </div>
    <div class="h-9/10 max-w-full w-full bg-white rounded-lg shadow-sm dark:bg-gray-800 p-4 md:p-6">
      <div id="cpuChart"></div>
    </div>
  </div>
</template>
