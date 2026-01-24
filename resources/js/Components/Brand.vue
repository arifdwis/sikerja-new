<template>
    <div class="py-16 bg-white rounded-lg shadow-sm dark:bg-gray-800">
        <div class="container m-auto px-6 space-y-4">
            <div class="m-auto text-center lg:w-7/12 pb-8">
                <h2 class="text-2xl text-gray-700 dark:text-gray-100 font-bold md:text-4xl">Ecosystem</h2>
            </div>
            <div class="grid grid-cols-5 gap-4">
                <div v-for="brand in brands" :key="brand.name" class="flex flex-col items-center justify-center p-4">
                    <a :href="brand.url" target="_blank" class="flex flex-col">
                        <!-- <img :src="brand.logo" class="w-24 h-24 object-contain pb-2" :alt="brand.name"> -->
                        <h5 class="text-base uppercase text-gray-700 dark:text-gray-100 font-bold pb-2">{{ brand.name }}</h5>
                        <h6 class="text-sm text-gray-700 dark:text-gray-100 font-bold">{{ brand.description }}</h6>
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
import { ref, onMounted } from 'vue';

const brands = ref( [ ] );

onMounted( async ( ) => {
    try {
        const response = await fetch( '/brands.json' ); // Ambil data dari file JSON
        brands.value = await response.json( );
    } catch ( error ) {
        console.error( 'Gagal mengambil data:', error );
    }
} );

</script>
