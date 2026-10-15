<script setup>
import { Head, Link } from '@inertiajs/vue3'
import Navbar from '@/Components/Landing/Navbar.vue'
import PageHeader from '@/Components/Landing/PageHeader.vue'
import Footer from '@/Components/Landing/Footer.vue'
import ChatWidget from '@/Components/Landing/ChatWidget.vue'
import { Icon } from '@iconify/vue'
import { ref } from 'vue'

const lastUpdated = '7 Oktober 2026'

const sections = [
    { id: 'ketentuan-umum', title: '1. Ketentuan Umum', icon: 'solar:document-text-bold-duotone' },
    { id: 'akun-autentikasi', title: '2. Akun & Autentikasi Pengguna', icon: 'solar:user-id-bold-duotone' },
    { id: 'pengajuan-kerjasama', title: '3. Tata Cara Pengajuan Kerja Sama', icon: 'solar:diploma-verified-bold-duotone' },
    { id: 'hak-kewajiban', title: '4. Hak & Kewajiban Para Pihak', icon: 'solar:shield-warning-bold-duotone' },
    { id: 'hki-sistem', title: '5. Kekayaan Intelektual & Batasan Sistem', icon: 'solar:server-square-bold-duotone' },
    { id: 'penafian', title: '6. Batasan Tanggung Jawab', icon: 'solar:danger-triangle-bold-duotone' },
    { id: 'perubahan', title: '7. Perubahan Syarat & Ketentuan', icon: 'solar:restart-bold-duotone' },
]

const activeSection = ref('ketentuan-umum')

const scrollTo = (id) => {
    activeSection.value = id
    const el = document.getElementById(id)
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' })
    }
}
</script>

<template>
    <Head>
        <title>Syarat & Ketentuan Layanan - SiKerja Samarinda</title>
        <meta name="description" content="Syarat dan Ketentuan Penggunaan Sistem Informasi Kerjasama Daerah (SiKerja) Pemerintah Kota Samarinda." />
    </Head>

    <Navbar />

    <main class="font-['Inter'] antialiased bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 min-h-screen">
        <PageHeader 
            title="Syarat & Ketentuan" 
            subtitle="Panduan dan ketentuan hukum penggunaan aplikasi Sistem Informasi Kerjasama Daerah (SiKerja) Kota Samarinda"
            :breadcrumbs="[{ label: 'Syarat & Ketentuan', url: '/syarat-ketentuan' }]"
        />

        <div class="max-w-7xl mx-auto px-6 py-12 md:py-16">
            <!-- Header Badge & Quick Info -->
            <div class="mb-10 p-6 rounded-2xl bg-gradient-to-r from-amber-500/10 via-teal-500/5 to-transparent border border-amber-500/20 dark:border-amber-400/20 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 dark:bg-amber-400/10 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0">
                        <Icon icon="solar:diploma-verified-bold" class="w-6 h-6" />
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Ketentuan Layanan Resmi</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Pedoman penggunaan sistem bagi pemohon, mitra kerja sama, dan aparatur pemerintah
                        </p>
                    </div>
                </div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-medium text-slate-600 dark:text-slate-400 self-start md:self-auto">
                    <Icon icon="solar:calendar-date-bold" class="w-4 h-4 text-amber-500" />
                    <span>Terakhir diperbarui: <strong>{{ lastUpdated }}</strong></span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <!-- Sidebar -->
                <aside class="lg:col-span-4 lg:sticky lg:top-28 space-y-4">
                    <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-sm">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-4 flex items-center gap-2">
                            <Icon icon="solar:list-check-bold" class="w-4 h-4 text-amber-500" />
                            Daftar Isi
                        </h3>
                        <nav class="space-y-1.5">
                            <button
                                v-for="sec in sections"
                                :key="sec.id"
                                @click="scrollTo(sec.id)"
                                :class="[
                                    'w-full text-left px-3 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-2.5 transition-all',
                                    activeSection === sec.id
                                        ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30'
                                        : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60'
                                ]"
                            >
                                <Icon :icon="sec.icon" class="w-4 h-4 flex-shrink-0" />
                                <span class="truncate">{{ sec.title }}</span>
                            </button>
                        </nav>
                    </div>

                    <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-950 text-white border border-slate-800">
                        <div class="flex items-center gap-2 text-teal-400 text-xs font-bold mb-2">
                            <Icon icon="solar:shield-check-bold" class="w-4 h-4" />
                            Kebijakan Privasi
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed mb-4">
                            Ingin mengetahui bagaimana data pribadi Anda dikelola dan dilindungi di SiKerja?
                        </p>
                        <Link
                            href="/kebijakan-privasi"
                            class="inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs transition-colors"
                        >
                            <Icon icon="solar:document-text-bold" class="w-4 h-4" />
                            Baca Kebijakan Privasi
                        </Link>
                    </div>
                </aside>

                <!-- Content -->
                <div class="lg:col-span-8 space-y-8">
                    <section id="ketentuan-umum" class="p-6 md:p-8 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-sm scroll-mt-28">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0">
                                <Icon icon="solar:document-text-bold" class="w-5 h-5" />
                            </div>
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">1. Ketentuan Umum</h2>
                        </div>
                        <div class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed space-y-3">
                            <p>
                                Dengan mengakses dan menggunakan aplikasi <strong>SiKerja</strong> (Sistem Informasi Kerjasama Daerah Kota Samarinda), Anda menyatakan telah membaca, memahami, dan menyetujui untuk terikat oleh Syarat dan Ketentuan ini.
                            </p>
                            <p>
                                Layanan ini dioperasikan oleh <strong>Bagian Kerjasama Sekretariat Daerah Kota Samarinda</strong> sebagai media resmi pelayanan, pemrosesan dokumen, telaahan teknis, dan pemantauan kerja sama antara Pemerintah Kota Samarinda dengan para mitra.
                            </p>
                        </div>
                    </section>

                    <section id="akun-autentikasi" class="p-6 md:p-8 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-sm scroll-mt-28">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0">
                                <Icon icon="solar:user-id-bold" class="w-5 h-5" />
                            </div>
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">2. Akun & Autentikasi Pengguna</h2>
                        </div>
                        <div class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed space-y-3">
                            <ul class="list-disc pl-5 space-y-2 text-xs md:text-sm">
                                <li>Setiap pengguna wajib memberikan data identitas yang valid, benar, dan dapat dipertanggungjawabkan saat melakukan pendaftaran atau login (termasuk via Google Sign-In).</li>
                                <li>Pengguna bertanggung jawab penuh atas keamanan kredensial akun dan seluruh aktivitas yang dilakukan melalui akun tersebut.</li>
                                <li>Pemerintah Kota Samarinda berhak menonaktifkan akun yang terindikasi menyalahgunakan wewenang, memalsukan identitas, atau melanggar ketentuan perundang-undangan.</li>
                            </ul>
                        </div>
                    </section>

                    <section id="pengajuan-kerjasama" class="p-6 md:p-8 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-sm scroll-mt-28">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0">
                                <Icon icon="solar:diploma-verified-bold" class="w-5 h-5" />
                            </div>
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">3. Tata Cara Pengajuan Kerja Sama</h2>
                        </div>
                        <div class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed space-y-3">
                            <p>
                                Seluruh usulan kerja sama wajib mematuhi mekanisme tata kelola kerja sama daerah yang diatur dalam Permendagri No. 22 Tahun 2020 dan Perda Kota Samarinda No. 7 Tahun 2020:
                            </p>
                            <ul class="list-disc pl-5 space-y-2 text-xs md:text-sm">
                                <li>Dokumen usulan wajib diunggah dalam format resmi (.pdf/.docx) dengan ukuran yang telah ditentukan.</li>
                                <li>Usulan yang diajukan akan melalui tahapan verifikasi administrasi oleh Bagian Kerjasama dan telaahan teknis oleh Tim Koordinasi Kerjasama Daerah (TKKSD).</li>
                                <li>Persetujuan akhir kerja sama merupakan hak diskresi pimpinan daerah sesuai peraturan yang berlaku.</li>
                            </ul>
                        </div>
                    </section>

                    <section id="hak-kewajiban" class="p-6 md:p-8 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-sm scroll-mt-28">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0">
                                <Icon icon="solar:shield-warning-bold" class="w-5 h-5" />
                            </div>
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">4. Hak & Kewajiban Para Pihak</h2>
                        </div>
                        <div class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed space-y-3">
                            <p>
                                <strong>Kewajiban Pengguna:</strong> Tidak mengunggah konten yang mengandung muatan SARA, pornografi, ujaran kebencian, dokumen palsu, atau file yang mengandung kode berbahaya (virus/malware).
                            </p>
                            <p>
                                <strong>Kewajiban Pengelola:</strong> Menjaga ketersediaan sistem, memberikan informasi tahapan pemrosesan berkas secara transparan, serta menjaga kerahasiaan data sesuai Kebijakan Privasi.
                            </p>
                        </div>
                    </section>

                    <section id="hki-sistem" class="p-6 md:p-8 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-sm scroll-mt-28">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0">
                                <Icon icon="solar:server-square-bold" class="w-5 h-5" />
                            </div>
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">5. Kekayaan Intelektual & Batasan Sistem</h2>
                        </div>
                        <div class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed space-y-3">
                            <p>
                                Seluruh hak cipta, merek, desain antarmuka, kode sumber, dan database SiKerja adalah hak milik Pemerintah Kota Samarinda. Dilarang melakukan rekayasa balik (reverse engineering), penggandaan sistem, atau tindakan peretasan dalam bentuk apa pun.
                            </p>
                        </div>
                    </section>

                    <section id="penafian" class="p-6 md:p-8 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-sm scroll-mt-28">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0">
                                <Icon icon="solar:danger-triangle-bold" class="w-5 h-5" />
                            </div>
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">6. Batasan Tanggung Jawab</h2>
                        </div>
                        <div class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed space-y-3">
                            <p>
                                Pengelola berupaya maksimal menjaga keandalan sistem selama 24/7, namun tidak bertanggung jawab atas gangguan layanan yang disebabkan oleh keadaan kahar (force majeure), bencana alam, kegagalan koneksi jaringan pihak ketiga, atau gangguan infrastruktur internet nasional di luar kendali wajar kami.
                            </p>
                        </div>
                    </section>

                    <section id="perubahan" class="p-6 md:p-8 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-sm scroll-mt-28">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0">
                                <Icon icon="solar:restart-bold" class="w-5 h-5" />
                            </div>
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">7. Perubahan Syarat & Ketentuan</h2>
                        </div>
                        <div class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed space-y-3">
                            <p>
                                Syarat & Ketentuan ini dapat diubah atau disesuaikan dari waktu ke waktu sesuai kebijakan Pemerintah Kota Samarinda dan regulasi perundang-undangan yang berlaku. Perubahan akan berlaku efektif setelah ditayangkan pada halaman ini.
                            </p>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </main>

    <Footer />
    <ChatWidget />
</template>
