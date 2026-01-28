<script setup>
import { onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';

const props = defineProps({
    data: Array
});

// Auto print when page loads
onMounted(() => {
    setTimeout(() => {
        window.print();
    }, 1000);
});

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const calculateDuration = (start, end) => {
    if (!start || !end) return '-';
    // ... logic remains ...
    const startDate = new Date(start);
    const endDate = new Date(end);
    const diffTime = Math.abs(endDate - startDate);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
    return diffDays;
};
</script>

<style>
/* ... style remains ... */
@media print {
    @page { size: landscape; margin: 1cm; }
    body { font-family: Arial, sans-serif; color: black; background: white; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .no-print { display: none !important; }
    table { width: 100%; border-collapse: collapse; font-size: 11px; }
    th, td { border: 1px solid black; padding: 6px; vertical-align: middle; }
    th { background-color: #f0f0f0 !important; font-weight: bold; text-align: center; white-space: nowrap; }
    td.text-center { text-align: center; }
}
</style>

<template>
    <Head title="Laporan Monitoring Kerjasama" />
    
    <div class="p-6 min-h-screen bg-white text-black">
        <!-- Control Bar -->
        <div class="no-print fixed top-4 right-4 flex gap-2 z-50">
            <button @click="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 flex items-center gap-2">
                <Icon icon="solar:printer-bold" /> Cetak
            </button>
            <button @click="window.close()" class="bg-gray-600 text-white px-4 py-2 rounded shadow hover:bg-gray-700">
                Tutup
            </button>
        </div>

        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-xl font-bold uppercase">Pemerintah Kota Samarinda</h1>
            <h2 class="text-lg font-bold uppercase mt-1">Laporan Monitoring Kerjasama Daerah</h2>
            <p class="text-sm mt-2">Dicetak pada: {{ new Date().toLocaleString() }}</p>
        </div>

        <!-- Table -->
        <table class="w-full border border-black text-sm">
            <thead>
                <tr>
                    <th class="w-10">No</th>
                    <th>Judul Kerjasama</th>
                    <th class="w-40">Kategori Kerjasama</th>
                    <th class="w-64">Mitra & Alamat</th>
                    <th class="w-32">Tanggal Mulai</th>
                    <th class="w-32">Tanggal Berakhir</th>
                    <th class="w-20">Durasi</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(item, index) in data" :key="item.id">
                    <td class="text-center">{{ index + 1 }}</td>
                    <td>
                        <div class="font-bold text-sm">{{ item.label }}</div>
                    </td>
                    <td>
                        <div class="font-bold">{{ item.kategori ? item.kategori.label : '-' }}</div>
                    </td>
                    <td>
                        <div class="font-bold">{{ item.pemohon ? item.pemohon.name : '-' }}</div>
                        <div class="font-bold">{{ item.nama_instansi }}</div>
                        <div class="text-xs mt-1">{{ item.alamat || '-' }}</div>
                    </td>
                    <td class="text-center">
                        {{ formatDate(item.tanggal_mulai) }}
                    </td>
                    <td class="text-center">
                        {{ formatDate(item.tanggal_berakhir) }}
                    </td>
                    <td class="text-center font-bold">
                        {{ calculateDuration(item.tanggal_mulai, item.tanggal_berakhir) }} Hari
                    </td>   
                </tr>
                <tr v-if="data.length === 0">
                    <td colspan="8" class="text-center py-4 italic">Tidak ada data kerjasama selesai.</td>
                </tr>
            </tbody>
        </table>

        <!-- Footer -->
        <div class="mt-8 flex justify-end">
            <div class="text-center w-64">
                <p class="mb-20">Mengetahui,</p>
                <p class="font-bold underline">H. Hero Mardanus Satyawan, MT</p>
                <p>Sekretaris Daerah</p>
            </div>
        </div>
    </div>
</template>
