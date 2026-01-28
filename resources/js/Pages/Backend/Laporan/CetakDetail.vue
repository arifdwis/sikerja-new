<script setup>
import { onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';

const props = defineProps({
    data: Object
});

// Auto print when page loads
onMounted(() => {
    // Small delay to ensure render
    setTimeout(() => {
        window.print();
    }, 500);
});

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};
</script>

<style>
@media print {
    @page { margin: 2cm; }
    body { font-family: 'Times New Roman', serif; color: black; background: white; }
    .no-print { display: none !important; }
    .page-break { page-break-before: always; }
}
</style>

<template>
    <Head :title="`Cetak Laporan - ${data.label}`" />
    
    <div class="max-w-[21cm] mx-auto bg-white p-8 min-h-screen text-black">
        <!-- Control Bar -->
        <div class="no-print fixed top-4 right-4 flex gap-2">
            <button @click="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 flex items-center gap-2">
                <Icon icon="solar:printer-bold" /> Cetak
            </button>
            <button @click="window.close()" class="bg-gray-600 text-white px-4 py-2 rounded shadow hover:bg-gray-700">
                Tutup
            </button>
        </div>

        <!-- Header -->
        <div class="text-center border-b-2 border-black pb-6 mb-8">
            <h1 class="text-xl font-bold uppercase tracking-wide">Pemerintah Kota Samarinda</h1>
            <h2 class="text-lg font-bold uppercase mt-1">Laporan Detail Kerjasama Daerah</h2>
        </div>

        <!-- Meta Info -->
        <div class="grid grid-cols-2 gap-4 mb-8 text-sm">
            <div>
                <table class="w-full">
                    <tr>
                        <td class="w-32 py-1 font-bold">Nomor</td>
                        <td class="py-1">: {{ data.nomor_permohonan || '-' }}</td>
                    </tr>
                    <tr>
                        <td class="w-32 py-1 font-bold">Tanggal</td>
                        <td class="py-1">: {{ formatDate(data.created_at) }}</td>
                    </tr>
                </table>
            </div>
            <div>
                 <table class="w-full">
                    <tr>
                        <td class="w-32 py-1 font-bold">Status</td>
                        <td class="py-1">: {{ data.status_label.label }}</td>
                    </tr>
                    <tr>
                        <td class="w-32 py-1 font-bold">Kategori</td>
                        <td class="py-1">: {{ data.kategori ? data.kategori.label : '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Content -->
        <div class="space-y-6">
            <!-- Judul -->
            <section>
                <h3 class="font-bold border-b border-gray-400 mb-2 pb-1">Judul Kerjasama</h3>
                <p class="text-justify leading-relaxed">{{ data.label }}</p>
            </section>

             <!-- Para Pihak -->
            <section>
                <h3 class="font-bold border-b border-gray-400 mb-4 pb-1">Para Pihak</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Pihak 1 (Pemda/User) -->
                    <div class="border p-4 rounded-sm">
                        <h4 class="font-bold text-sm mb-2 text-gray-600 uppercase">Pihak Pertama</h4>
                         <table class="w-full text-sm">
                            <tr><td class="py-1 w-24">Nama</td><td>: {{ data.operator ? data.operator.name : '-' }}</td></tr>
                            <tr><td class="py-1 w-24">Instansi</td><td>: Pemerintah Kota Samarinda</td></tr>
                        </table>
                    </div>
                     <!-- Pihak 2 (Mitra) -->
                    <div class="border p-4 rounded-sm">
                        <h4 class="font-bold text-sm mb-2 text-gray-600 uppercase">Pihak Kedua (Mitra)</h4>
                        <table class="w-full text-sm">
                            <tr><td class="py-1 w-24">Instansi</td><td>: {{ data.nama_instansi }}</td></tr>
                             <tr><td class="py-1 w-24">Alamat</td><td>: {{ data.alamat || '-' }}</td></tr>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Substansi -->
            <section>
                <h3 class="font-bold border-b border-gray-400 mb-2 pb-1">Substansi Kerjasama</h3>
                <table class="w-full text-sm border-collapse border border-gray-300">
                    <tr>
                        <td class="border border-gray-300 p-2 bg-gray-50 font-bold w-1/4">Latar Belakang</td>
                        <td class="border border-gray-300 p-2 text-justify">{{ data.latar_belakang || '-' }}</td>
                    </tr>
                    <tr>
                        <td class="border border-gray-300 p-2 bg-gray-50 font-bold">Maksud & Tujuan</td>
                        <td class="border border-gray-300 p-2 text-justify">{{ data.maksud_tujuan || '-' }}</td>
                    </tr>
                    <tr>
                        <td class="border border-gray-300 p-2 bg-gray-50 font-bold">Ruang Lingkup</td>
                        <td class="border border-gray-300 p-2 text-justify">{{ data.ruang_lingkup || '-' }}</td>
                    </tr>
                     <tr>
                        <td class="border border-gray-300 p-2 bg-gray-50 font-bold">Jangka Waktu</td>
                        <td class="border border-gray-300 p-2">
                            {{ formatDate(data.tanggal_mulai) }} s/d {{ formatDate(data.tanggal_berakhir) }}
                            ({{ data.jangka_waktu }} Tahun)
                        </td>
                    </tr>
                     <tr>
                        <td class="border border-gray-300 p-2 bg-gray-50 font-bold">Manfaat</td>
                        <td class="border border-gray-300 p-2 text-justify">{{ data.manfaat || '-' }}</td>
                    </tr>
                </table>
            </section>
        </div>

        <!-- Footer -->
        <div class="mt-12 pt-8 border-t border-black text-xs text-center text-gray-500">
            Dicetak melalui Sistem Informasi Kerjasama Daerah (SIKERJA) Kota Samarinda pada {{ new Date().toLocaleString() }}
        </div>
    </div>
</template>
