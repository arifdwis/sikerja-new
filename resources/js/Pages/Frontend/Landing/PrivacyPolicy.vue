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
    { id: 'pendahuluan', title: '1. Pendahuluan & Komitmen', icon: 'solar:shield-check-bold-duotone' },
    { id: 'data-dikumpulkan', title: '2. Data yang Dikumpulkan', icon: 'solar:document-add-bold-duotone' },
    { id: 'google-oauth', title: '3. Kebijakan Data Pengguna Google', icon: 'logos:google-icon' },
    { id: 'tujuan-penggunaan', title: '4. Tujuan Penggunaan Informasi', icon: 'solar:target-bold-duotone' },
    { id: 'keamanan-data', title: '5. Keamanan & Penyimpanan Data', icon: 'solar:lock-keyhole-bold-duotone' },
    { id: 'pembagian-data', title: '6. Pengungkapan & Pembagian Data', icon: 'solar:users-group-rounded-bold-duotone' },
    { id: 'hak-pengguna', title: '7. Hak Pemilik Data Pribadi', icon: 'solar:user-check-bold-duotone' },
    { id: 'kontak', title: '8. Kontak & Pengaduan', icon: 'solar:letter-bold-duotone' },
]

const activeSection = ref('pendahuluan')

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
        <title>Kebijakan Privasi - SiKerja Samarinda</title>
        <meta name="description" content="Kebijakan Privasi Sistem Informasi Kerjasama Daerah (SiKerja) Pemerintah Kota Samarinda. Informasi perlindungan data pengguna dan kepatuhan Google OAuth." />
    </Head>

    <Navbar />

    <main class="font-['Inter'] antialiased bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 min-h-screen">
        <PageHeader 
            title="Kebijakan Privasi" 
            subtitle="Komitmen Pemerintah Kota Samarinda dalam melindungi privasi dan keamanan data pribadi Anda pada layanan SiKerja"
            :breadcrumbs="[{ label: 'Kebijakan Privasi', url: '/kebijakan-privasi' }]"
        />

        <div class="max-w-7xl mx-auto px-6 py-12 md:py-16">
            <!-- Header Badge & Quick Info -->
            <div class="mb-10 p-6 rounded-2xl bg-gradient-to-r from-teal-500/10 via-cyan-500/5 to-transparent border border-teal-500/20 dark:border-teal-400/20 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-teal-600/10 dark:bg-teal-400/10 text-teal-600 dark:text-teal-400 flex items-center justify-center flex-shrink-0">
                        <Icon icon="solar:shield-check-bold" class="w-6 h-6" />
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Dokumen Resmi Perlindungan Data</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Berlaku untuk seluruh pengguna aplikasi Sistem Informasi Kerjasama Daerah (SiKerja) Kota Samarinda
                        </p>
                    </div>
                </div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-medium text-slate-600 dark:text-slate-400 self-start md:self-auto">
                    <Icon icon="solar:calendar-date-bold" class="w-4 h-4 text-teal-500" />
                    <span>Terakhir diperbarui: <strong>{{ lastUpdated }}</strong></span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <!-- Sidebar / Navigation -->
                <aside class="lg:col-span-4 lg:sticky lg:top-28 space-y-4">
                    <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-sm">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-4 flex items-center gap-2">
                            <Icon icon="solar:list-check-bold" class="w-4 h-4 text-teal-500" />
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
                                        ? 'bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/30'
                                        : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60'
                                ]"
                            >
                                <Icon :icon="sec.icon" class="w-4 h-4 flex-shrink-0" />
                                <span class="truncate">{{ sec.title }}</span>
                            </button>
                        </nav>
                    </div>

                    <!-- Quick Support Card -->
                    <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-950 text-white border border-slate-800">
                        <div class="flex items-center gap-2 text-teal-400 text-xs font-bold mb-2">
                            <Icon icon="solar:info-circle-bold" class="w-4 h-4" />
                            Butuh Bantuan?
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed mb-4">
                            Ada pertanyaan atau permintaan terkait data pribadi Anda di SiKerja?
                        </p>
                        <a
                            href="mailto:kerjasama@samarindakota.go.id"
                            class="inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-xs transition-colors"
                        >
                            <Icon icon="solar:letter-bold" class="w-4 h-4" />
                            Hubungi Tim Privasi
                        </a>
                    </div>
                </aside>

                <!-- Content Column -->
                <div class="lg:col-span-8 space-y-8">
                    <!-- Section 1 -->
                    <section id="pendahuluan" class="p-6 md:p-8 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-sm scroll-mt-28">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-9 h-9 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center flex-shrink-0">
                                <Icon icon="solar:shield-check-bold" class="w-5 h-5" />
                            </div>
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">1. Pendahuluan & Komitmen</h2>
                        </div>
                        <div class="prose dark:prose-invert max-w-none text-sm text-slate-600 dark:text-slate-300 leading-relaxed space-y-3">
                            <p>
                                Selamat datang di <strong>SiKerja</strong> (Sistem Informasi Kerjasama Daerah Kota Samarinda), portal digital resmi yang dikelola oleh <strong>Bagian Kerjasama Sekretariat Daerah Pemerintah Kota Samarinda</strong>.
                            </p>
                            <p>
                                Pemerintah Kota Samarinda berkomitmen penuh dalam melindungi privasi, kerahasiaan, dan keamanan data pribadi setiap pihak yang menggunakan layanan kami (selanjutnya disebut "Pengguna"), mencakup aparatur pemerintah (OPD), mitra kerja sama badan usaha/swasta, institusi pendidikan, yayasan, organisasi kemasyarakatan, maupun masyarakat umum.
                            </p>
                            <p>
                                Kebijakan Privasi ini disusun sesuai dengan ketentuan peraturan perundang-undangan Republik Indonesia, khususnya:
                            </p>
                            <ul class="list-disc pl-5 space-y-1 text-xs md:text-sm">
                                <li><strong>Undang-Undang No. 27 Tahun 2022</strong> tentang Perlindungan Data Pribadi (UU PDP).</li>
                                <li><strong>Undang-Undang No. 11 Tahun 2008</strong> tentang Informasi dan Transaksi Elektronik sebagaimana diubah terakhir dengan <strong>UU No. 1 Tahun 2024</strong> (UU ITE).</li>
                                <li><strong>Peraturan Pemerintah No. 28 Tahun 2018</strong> tentang Kerja Sama Daerah.</li>
                                <li><strong>Peraturan Daerah Kota Samarinda No. 7 Tahun 2020</strong> tentang Penyelenggaraan Pelayanan Publik dan Kerja Sama Daerah.</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Section 2 -->
                    <section id="data-dikumpulkan" class="p-6 md:p-8 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-sm scroll-mt-28">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-9 h-9 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center flex-shrink-0">
                                <Icon icon="solar:document-add-bold" class="w-5 h-5" />
                            </div>
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">2. Data yang Kami Kumpulkan</h2>
                        </div>
                        <div class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed space-y-4">
                            <p>Untuk menyelenggarakan proses verifikasi, permohonan, dan administrasi kerja sama daerah, kami mengumpulkan jenis informasi berikut:</p>
                            
                            <div class="grid md:grid-cols-2 gap-4 pt-2">
                                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60">
                                    <h3 class="font-bold text-slate-900 dark:text-white text-xs uppercase tracking-wider mb-2 flex items-center gap-1.5 text-teal-600 dark:text-teal-400">
                                        <Icon icon="solar:user-circle-bold" class="w-4 h-4" />
                                        Data Identitas Akun
                                    </h3>
                                    <ul class="text-xs space-y-1.5 text-slate-600 dark:text-slate-400">
                                        <li>• Nama lengkap penanggung jawab / pemohon</li>
                                        <li>• Alamat surat elektronik (email aktif)</li>
                                        <li>• Nomor telepon seluler / WhatsApp aktif</li>
                                        <li>• Nama instansi, lembaga, atau korporasi mitra</li>
                                        <li>• Foto profil (bila login via akun Google)</li>
                                    </ul>
                                </div>

                                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60">
                                    <h3 class="font-bold text-slate-900 dark:text-white text-xs uppercase tracking-wider mb-2 flex items-center gap-1.5 text-teal-600 dark:text-teal-400">
                                        <Icon icon="solar:documents-minimalistic-bold" class="w-4 h-4" />
                                        Dokumen Usulan Kerja Sama
                                    </h3>
                                    <ul class="text-xs space-y-1.5 text-slate-600 dark:text-slate-400">
                                        <li>• Surat penawaran / permohonan resmi</li>
                                        <li>• Kerangka Acuan Kerja (KAK) / Studi Kelayakan</li>
                                        <li>• Salinan identitas legalitas (KTP Direksi, Akta Notaris, NIB/NPWP)</li>
                                        <li>• Draf Kesepakatan Bersama (MoU) atau Perjanjian Kerja Sama (PKS)</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60">
                                <h3 class="font-bold text-slate-900 dark:text-white text-xs uppercase tracking-wider mb-2 flex items-center gap-1.5 text-teal-600 dark:text-teal-400">
                                    <Icon icon="solar:server-square-bold" class="w-4 h-4" />
                                    Data Teknis & Penggunaan Sistem
                                </h3>
                                <p class="text-xs text-slate-600 dark:text-slate-400">
                                    Alamat IP (Internet Protocol), jenis dan versi peramban (browser), sistem operasi, waktu akses, serta riwayat interaksi dalam rangka pemeliharaan keamanan, audit jejak digital (audit trail), dan pencegahan akses tanpa hak.
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Section 3: GOOGLE OAUTH SPECIFIC (CRITICAL FOR GOOGLE AUDIT) -->
                    <section id="google-oauth" class="p-6 md:p-8 rounded-2xl bg-white dark:bg-slate-900 border-2 border-teal-500/30 dark:border-teal-400/30 shadow-md scroll-mt-28">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center flex-shrink-0">
                                <Icon icon="logos:google-icon" class="w-5 h-5" />
                            </div>
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">3. Kebijakan Data Pengguna Google (Google User Data Policy)</h2>
                        </div>

                        <div class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed space-y-4">
                            <div class="p-4 rounded-xl bg-teal-50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800/60 text-xs text-teal-900 dark:text-teal-200 space-y-2">
                                <p class="font-bold">
                                    Pemberitahuan Khusus Integrasi Google Sign-In:
                                </p>
                                <p>
                                    Aplikasi SiKerja menggunakan layanan autentikasi <strong>Google Identity Services (OAuth 2.0)</strong> untuk memudahkan pengguna masuk tanpa perlu mengingat kata sandi tambahan. Bagian ini menjelaskan secara transparan bagaimana data akun Google Anda diperlakukan.
                                </p>
                            </div>

                            <div class="space-y-3">
                                <h3 class="font-bold text-slate-900 dark:text-white text-sm">
                                    A. Informasi yang Diakses dari Akun Google Anda:
                                </h3>
                                <p class="text-xs md:text-sm">
                                    Saat Anda memilih opsi <em>"Login dengan Google"</em>, SiKerja hanya meminta izin untuk mengakses informasi profil dasar publik yang disediakan oleh Google, yaitu:
                                </p>
                                <ul class="list-disc pl-5 space-y-1.5 text-xs md:text-sm text-slate-600 dark:text-slate-400">
                                    <li><strong>Alamat Email:</strong> Digunakan sebagai identitas akun utama untuk korespondensi resmi, notifikasi tahapan permohonan, dan status kerja sama.</li>
                                    <li><strong>Nama Lengkap:</strong> Digunakan untuk menampilkan profil pengguna dan penanggung jawab permohonan pada sistem.</li>
                                    <li><strong>Foto Profil (Avatar URL):</strong> Digunakan sebagai gambar tampilan avatar akun Anda di dashboard.</li>
                                    <li><strong>ID Unik Google (Subject ID):</strong> Pengenal alfanumerik aman yang digunakan untuk mencocokkan sesi login Anda dengan akun di database SiKerja.</li>
                                </ul>
                            </div>

                            <div class="space-y-3">
                                <h3 class="font-bold text-slate-900 dark:text-white text-sm">
                                    B. Hal-hal yang TIDAK Dilakukan oleh SiKerja:
                                </h3>
                                <ul class="space-y-2 text-xs md:text-sm">
                                    <li class="flex items-start gap-2">
                                        <Icon icon="solar:close-circle-bold" class="w-4 h-4 text-rose-500 mt-0.5 flex-shrink-0" />
                                        <span><strong>TIDAK PERNAH</strong> meminta atau menyimpan kata sandi (password) akun Google Anda. Autentikasi dilakukan sepenuhnya di peladen aman milik Google.</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <Icon icon="solar:close-circle-bold" class="w-4 h-4 text-rose-500 mt-0.5 flex-shrink-0" />
                                        <span><strong>TIDAK MENGAKSES</strong> kontak (contacts), email pribadi (Gmail), file Google Drive, kalender, lokasi presisi, atau data privat Google lainnya di luar profil dasar.</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <Icon icon="solar:close-circle-bold" class="w-4 h-4 text-rose-500 mt-0.5 flex-shrink-0" />
                                        <span><strong>TIDAK MENJUAL</strong> atau menyewakan data pengguna Google kepada pihak ketiga mana pun untuk tujuan periklanan atau komersial.</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <Icon icon="solar:close-circle-bold" class="w-4 h-4 text-rose-500 mt-0.5 flex-shrink-0" />
                                        <span><strong>TIDAK MENGGUNAKAN</strong> data pengguna Google untuk melatih model kecerdasan buatan (AI) umum atau pihak ketiga.</span>
                                    </li>
                                </ul>
                            </div>

                            <div class="space-y-3">
                                <h3 class="font-bold text-slate-900 dark:text-white text-sm">
                                    C. Kepatuhan terhadap Google API Services User Data Policy:
                                </h3>
                                <p class="text-xs md:text-sm">
                                    Penggunaan dan pengalihan informasi yang diterima dari Google APIs oleh aplikasi SiKerja ke aplikasi lain mana pun akan sepenuhnya mematuhi 
                                    <a href="https://developers.google.com/terms/api-services-user-data-policy" target="_blank" rel="noopener noreferrer" class="text-teal-600 dark:text-teal-400 underline font-semibold hover:text-teal-500">
                                        Kebijakan Data Pengguna Layanan Google API
                                    </a>, termasuk persyaratan <em>Limited Use</em> (Penggunaan Terbatas).
                                </p>
                            </div>

                            <div class="space-y-3">
                                <h3 class="font-bold text-slate-900 dark:text-white text-sm">
                                    D. Cara Mencabut Izin Akses Google:
                                </h3>
                                <p class="text-xs md:text-sm">
                                    Anda dapat mencabut izin akses SiKerja terhadap akun Google Anda kapan saja melalui Pengaturan Akun Google Anda di:
                                    <a href="https://myaccount.google.com/permissions" target="_blank" rel="noopener noreferrer" class="text-teal-600 dark:text-teal-400 underline font-semibold ml-1">
                                        myaccount.google.com/permissions
                                    </a>.
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Section 4 -->
                    <section id="tujuan-penggunaan" class="p-6 md:p-8 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-sm scroll-mt-28">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-9 h-9 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center flex-shrink-0">
                                <Icon icon="solar:target-bold" class="w-5 h-5" />
                            </div>
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">4. Tujuan Penggunaan Informasi</h2>
                        </div>
                        <div class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed space-y-3">
                            <p>Data yang dikumpulkan hanya digunakan untuk keperluan pelayanan publik dan administrasi kedinasan, meliputi:</p>
                            <ol class="list-decimal pl-5 space-y-2 text-xs md:text-sm">
                                <li><strong>Autentikasi & Verifikasi Akun:</strong> Memastikan keabsahan pengguna yang mengajukan usulan kerja sama dengan Pemerintah Kota Samarinda.</li>
                                <li><strong>Pemrosesan Dokumen Kerja Sama:</strong> Memfasilitasi tahapan verifikasi berkas oleh Bagian Kerjasama, penelaahan substansi oleh Tim Koordinasi Kerjasama Daerah (TKKSD), hingga penandatanganan oleh Kepala Daerah / Walikota Samarinda.</li>
                                <li><strong>Notifikasi & Komunikasi:</strong> Mengirimkan pemberitahuan resmi mengenai progres telaah, permintaan kelengkapan berkas, jadwal rapat koordinasi, atau undangan penandatanganan.</li>
                                <li><strong>Pelaporan & Akuntabilitas Publik:</strong> Penyusunan statistik agregat kerja sama daerah (infografis publik tanpa menampilkan data rahasia/identitas pribadi).</li>
                            </ol>
                        </div>
                    </section>

                    <!-- Section 5 -->
                    <section id="keamanan-data" class="p-6 md:p-8 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-sm scroll-mt-28">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-9 h-9 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center flex-shrink-0">
                                <Icon icon="solar:lock-keyhole-bold" class="w-5 h-5" />
                            </div>
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">5. Keamanan & Penyimpanan Data</h2>
                        </div>
                        <div class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed space-y-3">
                            <p>
                                Keamanan data Anda adalah prioritas utama kami. Pemerintah Kota Samarinda menerapkan langkah-langkah pengamanan administratif, fisik, dan teknis yang ketat:
                            </p>
                            <div class="grid md:grid-cols-3 gap-3 pt-2">
                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60">
                                    <div class="text-teal-500 mb-1.5"><Icon icon="solar:shield-keyhole-bold" class="w-5 h-5" /></div>
                                    <h3 class="font-bold text-xs text-slate-900 dark:text-white mb-1">Enkripsi Data</h3>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-normal">
                                        Seluruh transmisi data dilindungi protokol TLS/HTTPS 256-bit berstandar industri.
                                    </p>
                                </div>
                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60">
                                    <div class="text-teal-500 mb-1.5"><Icon icon="solar:server-bold" class="w-5 h-5" /></div>
                                    <h3 class="font-bold text-xs text-slate-900 dark:text-white mb-1">Infrastruktur Aman</h3>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-normal">
                                        Server dikelola dengan firewall berlapis, backup terjadwal, dan pembatasan akses ketat.
                                    </p>
                                </div>
                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60">
                                    <div class="text-teal-500 mb-1.5"><Icon icon="solar:user-block-bold" class="w-5 h-5" /></div>
                                    <h3 class="font-bold text-xs text-slate-900 dark:text-white mb-1">Kontrol Hak Akses</h3>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-normal">
                                        Hanya aparatur berwenang (Bagian Kerjasama, TKKSD, Walikota) yang memiliki izin telaah berkas.
                                    </p>
                                </div>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 pt-2">
                                <strong>Retensi Data:</strong> Data dokumen kerja sama disimpan selama masa berlaku kerja sama ditambah masa retensi arsip kedinasan sesuai dengan ketentuan Peraturan Perundang-undangan Kearsipan Nasional.
                            </p>
                        </div>
                    </section>

                    <!-- Section 6 -->
                    <section id="pembagian-data" class="p-6 md:p-8 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-sm scroll-mt-28">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-9 h-9 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center flex-shrink-0">
                                <Icon icon="solar:users-group-rounded-bold" class="w-5 h-5" />
                            </div>
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">6. Pengungkapan & Pembagian Data</h2>
                        </div>
                        <div class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed space-y-3">
                            <p>
                                Kami <strong>tidak pernah menjual, memperdagangkan, atau memindahtangankan</strong> data pribadi Pengguna kepada pihak ketiga mana pun.
                            </p>
                            <p>
                                Data hanya dapat diteruskan dalam kondisi berikut:
                            </p>
                            <ul class="list-disc pl-5 space-y-1.5 text-xs md:text-sm text-slate-600 dark:text-slate-400">
                                <li><strong>Perangkat Daerah Terkait (OPD):</strong> Dokumen teknis kerja sama dibagikan kepada Dinas/Badan teknis pengampu untuk penelaahan substansi.</li>
                                <li><strong>Kewajiban Hukum:</strong> Apabila diwajibkan oleh penetapan pengadilan, aparat penegak hukum, atau ketentuan peraturan perundang-undangan Republik Indonesia.</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Section 7 -->
                    <section id="hak-pengguna" class="p-6 md:p-8 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-sm scroll-mt-28">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-9 h-9 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center flex-shrink-0">
                                <Icon icon="solar:user-check-bold" class="w-5 h-5" />
                            </div>
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">7. Hak Pemilik Data Pribadi</h2>
                        </div>
                        <div class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed space-y-3">
                            <p>Sesuai dengan UU Pelindungan Data Pribadi No. 27 Tahun 2022, Pengguna memiliki hak sebagai berikut:</p>
                            <ul class="list-disc pl-5 space-y-1.5 text-xs md:text-sm text-slate-600 dark:text-slate-400">
                                <li><strong>Hak Akses:</strong> Memperoleh informasi dan mengakses salinan data pribadi yang tersimpan dalam akun Anda.</li>
                                <li><strong>Hak Pembaruan/Koreksi:</strong> Mengubah atau memperbarui data profil akun Anda yang tidak akurat melalui menu profil.</li>
                                <li><strong>Hak Penghapusan Akun:</strong> Mengajukan permohonan penonaktifan atau penghapusan data akun Anda sepanjang tidak bertentangan dengan kewajiban retensi arsip resmi negara.</li>
                                <li><strong>Hak Pencabutan Persetujuan:</strong> Menarik kembali persetujuan pemrosesan data (misalnya dengan memutus integrasi Google Sign-In).</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Section 8 -->
                    <section id="kontak" class="p-6 md:p-8 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 shadow-sm scroll-mt-28">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-9 h-9 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center flex-shrink-0">
                                <Icon icon="solar:letter-bold" class="w-5 h-5" />
                            </div>
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">8. Kontak & Pengaduan</h2>
                        </div>
                        <div class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed space-y-4">
                            <p>
                                Apabila Anda memiliki pertanyaan, keberatan, atau permintaan terkait Kebijakan Privasi ini, silakan hubungi pengelola resmi SiKerja melalui kanal di bawah:
                            </p>

                            <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 space-y-3">
                                <div class="font-bold text-slate-900 dark:text-white text-sm">
                                    Bagian Kerjasama Sekretariat Daerah Kota Samarinda
                                </div>
                                <div class="grid md:grid-cols-2 gap-3 text-xs text-slate-600 dark:text-slate-400">
                                    <div class="flex items-start gap-2">
                                        <Icon icon="solar:map-point-bold" class="w-4 h-4 text-teal-500 mt-0.5 flex-shrink-0" />
                                        <span>Gedung Balai Kota Samarinda, Jl. Kesuma Bangsa No. 25, Kota Samarinda, Kalimantan Timur 75121</span>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2">
                                            <Icon icon="solar:phone-bold" class="w-4 h-4 text-teal-500 flex-shrink-0" />
                                            <span>(0541) 743322</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <Icon icon="solar:letter-bold" class="w-4 h-4 text-teal-500 flex-shrink-0" />
                                            <a href="mailto:kerjasama@samarindakota.go.id" class="text-teal-600 dark:text-teal-400 underline">kerjasama@samarindakota.go.id</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <p class="text-xs text-slate-400 dark:text-slate-500">
                                Pemerintah Kota Samarinda dapat memperbarui Kebijakan Privasi ini sewaktu-waktu untuk menyesuaikan perkembangan teknologi dan regulasi. Setiap perubahan akan diumumkan secara transparan pada halaman ini.
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
