<script setup>
import { computed } from 'vue'

// Fungsi untuk kapitalisasi awal kata
const capitalize = ( str ) => {
  return str
    .split( '-' ) // Pisahkan kata jika ada "-"
    .map( word => word.charAt( 0 )
      .toUpperCase( ) + word.slice( 1 ) ) // Kapitalisasi huruf pertama tiap kata
    .join( ' ' )
}

// Ambil path dari URL dan konversi ke breadcrumb
const breadcrumbs = computed( ( ) => {
  const pathArray = window.location.pathname
    .split( '/' ) // Pisahkan berdasarkan '/'
    .filter( segment => segment ) // Hilangkan string kosong
    .map( ( segment, index, arr ) => ( {
      label: segment,
      url: '/' + arr.slice( 0, index + 1 )
        .join( '/' )
    } ) )

  return pathArray
} )


const truncate = (text, length = 80) => {
    if(!text) return '';
    return text.length > length ? text.slice(0, length) + '…' : text;
};

</script>

<template>
  <fwb-breadcrumb solid>
    <fwb-breadcrumb-item home href="/dashboard">
      Dashboard
    </fwb-breadcrumb-item>
    <fwb-breadcrumb-item v-for="(crumb, index) in breadcrumbs" :key="index" :class="{ 'font-bold': index === breadcrumbs.length - 1 }">
      {{ capitalize(truncate(crumb.label)) }}
    </fwb-breadcrumb-item>
  </fwb-breadcrumb>
</template>
