# Requirements Document

## Introduction

Dokumen ini mendefinisikan kebutuhan untuk overhaul besar SiKerja V2 (Sistem Informasi Kerjasama Daerah Kota Samarinda). Overhaul mencakup perbaikan bug, penambahan step workflow baru, role baru (TKKSD Lokus Kerjasama), perubahan metode penjadwalan, template dokumen tetap, monitoring & evaluasi yang diperluas, infografis publik, dan inputan kerjasama manual. Aplikasi dibangun dengan Laravel 12 + Vue 3 + Inertia.js + TailwindCSS.

## Glossary

- **Sistem**: Aplikasi SiKerja V2 secara keseluruhan
- **Pemohon**: Pengguna yang mengajukan permohonan kerjasama daerah
- **Admin**: Pengguna dengan role Administrator atau Superadmin yang mengelola dan memvalidasi permohonan
- **TKKSD_Pembahasan**: Tim Koordinasi Kerjasama Daerah yang bertugas membahas dan mereview dokumen permohonan
- **TKKSD_Lokus**: Tim Koordinasi Kerjasama Daerah Lokus Kerjasama, role baru yang mewakili OPD tertentu. Setiap akun TKKSD_Lokus terhubung ke satu OPD dan hanya bertugas untuk monitoring dan evaluasi kerjasama yang melibatkan OPD tersebut
- **OPD**: Organisasi Perangkat Daerah yang terlibat dalam kerjasama
- **PKS**: Perjanjian Kerjasama, dokumen final yang ditandatangani oleh kedua belah pihak
- **KAK**: Kerangka Acuan Kerja
- **MOU**: Memorandum of Understanding / Nota Kesepahaman
- **Monev**: Monitoring dan Evaluasi pelaksanaan kerjasama
- **SSO**: Single Sign-On Pemerintah Kota Samarinda (novay/sso-client) — digunakan oleh semua user, baik dalam maupun luar daerah, melalui `sso.samarindakota.go.id`
- **Workflow_Engine**: Mekanisme perpindahan status permohonan dari awal hingga selesai
- **Penjadwalan_Penandatanganan**: Proses penjadwalan untuk penandatanganan dokumen kerjasama (bukan pembahasan)
- **Infografis_Publik**: Halaman publik yang menampilkan statistik dan informasi kerjasama tanpa login
- **Kerjasama_Manual**: Kerjasama yang diinput langsung oleh Admin tanpa melalui proses permohonan online

## Requirements

### Requirement 1: Koneksi User ke OPD via SSO

**User Story:** As a pengguna internal Samarinda, I want profil saya otomatis terhubung ke OPD saya saat login via SSO, so that saya tidak perlu memilih OPD lagi saat mengisi form permohonan.

#### Acceptance Criteria

1. WHEN pengguna berhasil login via SSO, THE Sistem SHALL membuat atau memperbarui record user dengan data dari SSO
2. WHEN user SSO memiliki data unit kerja yang cocok dengan OPD di master data, THE Sistem SHALL otomatis mengisi field `id_opd` pada record user tersebut
3. WHEN user yang sudah terhubung OPD membuat permohonan, THE Sistem SHALL otomatis mengisi field OPD dengan OPD user tersebut tanpa perlu dipilih manual
4. WHEN user tidak memiliki OPD terdaftar (mitra luar daerah), THE Sistem SHALL menampilkan field OPD sebagai pilihan manual di form permohonan
5. THE Sistem SHALL menggunakan SSO `sso.samarindakota.go.id` sebagai satu-satunya metode autentikasi untuk semua user (dalam maupun luar daerah)

### Requirement 2: Bug Fix - Error Revisi Permohonan Ditolak

**User Story:** As a Pemohon, I want to mengedit/merevisi permohonan yang ditolak tanpa error, so that saya dapat memperbaiki dan mengajukan kembali permohonan.

#### Acceptance Criteria

1. WHEN Pemohon mengedit permohonan yang ditolak, THE Sistem SHALL memvalidasi field id_provinsi terhadap tabel master_provinces (bukan tabel provinsi yang tidak ada)
2. WHEN Pemohon mengedit permohonan yang ditolak, THE Sistem SHALL memvalidasi field id_kota terhadap tabel master_cities (bukan tabel kota)
3. WHEN Pemohon mengedit permohonan dengan status ditolak (status 9), THE Sistem SHALL mengizinkan pengeditan dan mengembalikan status ke Permohonan Baru (status 0) setelah submit ulang

### Requirement 3: Template Dokumen Tetap

**User Story:** As a Admin , I want sistem menyediakan template dokumen tetap yang terstandarisasi, so that format dokumen kerjasama konsisten dan sesuai regulasi.

#### Acceptance Criteria

1. THE Sistem SHALL menyediakan template Surat Permohonan Kerjasama dalam format PDF yang dapat diunduh
2. THE Sistem SHALL menyediakan template KAK (Kerangka Acuan Kerja) dalam format PDF yang dapat diunduh
3. THE Sistem SHALL menyediakan template MOU dalam format DOCX yang dapat diunduh
4. WHEN Pemohon mengakses halaman upload dokumen, THE Sistem SHALL menampilkan link unduh untuk setiap template dokumen yang tersedia
5. THE Sistem SHALL menyimpan file template di storage yang dapat dikelola oleh Admin

### Requirement 4: Identitas Approver/Rejector di Pembahasan

**User Story:** As a Admin dan pemohona, I want melihat identitas siapa yang menyetujui atau menolak dokumen dalam pembahasan, so that terdapat akuntabilitas dan transparansi dalam proses review.

#### Acceptance Criteria

1. WHEN seorang TKKSD_Pembahasan menyetujui dokumen, THE Sistem SHALL mencatat nama, role, dan waktu persetujuan pada record pembahasan
2. WHEN seorang TKKSD_Pembahasan menolak dokumen, THE Sistem SHALL mencatat nama, role, dan waktu penolakan pada record pembahasan
3. WHEN Admin atau TKKSD_Pembahasan melihat detail pembahasan, THE Sistem SHALL menampilkan daftar approver dan rejector beserta nama, role, dan timestamp masing-masing
4. THE Sistem SHALL menyimpan identitas reviewer pada tabel permohonan_histori dengan field id_operator, dan menambahkan informasi role reviewer

### Requirement 5: Perubahan Step Setelah Pembahasan

**User Story:** As a Admin, I want tetap bisa mengedit jangka waktu perjanjian kerjasama setelah pembahasan dan mengirim notifikasi detail perubahan ke pemohon, so that proses pasca-pembahasan berjalan sesuai alur baru dan pemohon mengetahui perubahan jangka waktu.

#### Acceptance Criteria

1. WHILE status permohonan berada pada tahap Penjadwalan (status 2) atau setelahnya, THE Sistem SHALL mengizinkan Admin untuk mengedit jangka waktu perjanjian kerjasama (tanggal mulai, tanggal berakhir, durasi)
2. WHEN pembahasan selesai dan status berubah ke Penjadwalan, THE Sistem SHALL mengirim notifikasi ke Pemohon dengan pesan "Silakan ajukan jadwal penandatanganan"
3. THE Sistem SHALL menampilkan label "Penjadwalan Penandatanganan" (bukan "Penjadwalan") pada seluruh antarmuka pengguna
4. WHEN Pemohon menerima notifikasi pasca-pembahasan, THE Sistem SHALL mengarahkan Pemohon ke halaman penjadwalan penandatanganan
5. WHEN Admin mengedit jangka waktu perjanjian kerjasama, THE Sistem SHALL menyimpan riwayat perubahan jangka waktu (nilai lama dan nilai baru) ke tabel histori
6. WHEN Admin mengedit jangka waktu perjanjian kerjasama, THE Sistem SHALL mengirim notifikasi ke Pemohon yang mencantumkan jangka waktu sebelum dan jangka waktu sesudah perubahan

### Requirement 6: Metode Penjadwalan Baru

**User Story:** As a Pemohon, I want memilih metode penandatanganan (Desk to Desk, Ceremonial, atau Hybrid), so that proses penandatanganan dapat disesuaikan dengan kebutuhan.

#### Acceptance Criteria

1. WHEN Pemohon membuat jadwal penandatanganan, THE Sistem SHALL menampilkan 3 opsi metode: Desk to Desk, Ceremonial, dan Hybrid
2. WHEN Pemohon mengakses halaman penjadwalan, THE Sistem SHALL menampilkan teks "Silahkan ajukan jadwal penandatanganan kerjasama"
3. THE Sistem SHALL menyimpan metode penjadwalan yang dipilih pada field tipe di tabel penjadwalan
4. WHEN Admin mereview jadwal, THE Sistem SHALL menampilkan metode penandatanganan yang dipilih oleh Pemohon

### Requirement 7: Upload Berkas Final PKS oleh Pemohon

**User Story:** As a Pemohon, I want mengupload berkas PKS final dalam bentuk file PDF setelah penjadwalan, so that dokumen perjanjian kerjasama tersimpan dalam sistem.

#### Acceptance Criteria

1. WHEN proses penjadwalan penandatanganan disetujui, THE Sistem SHALL menampilkan form upload PKS kepada Pemohon
2. THE Sistem SHALL hanya menerima file PKS dalam format PDF (file dengan ekstensi .pdf dan MIME type application/pdf)
3. IF file yang diupload bukan format PDF, THEN THE Sistem SHALL menolak upload dengan pesan error "File harus berformat PDF"
4. WHILE status permohonan telah melewati tahap pembahasan, THE Sistem SHALL mengunci berkas-berkas yang diupload pada tahap sebelumnya sehingga Pemohon tidak dapat mengubahnya
5. WHEN Pemohon berhasil mengupload PKS, THE Sistem SHALL mencatat upload dalam histori permohonan

### Requirement 8: Notifikasi Selesai Diganti

**User Story:** As a Pemohon, I want menerima notifikasi yang informatif setelah jadwal penandatanganan disetujui, so that saya dapat mempersiapkan dokumen yang diperlukan.

#### Acceptance Criteria

1. WHEN jadwal penandatanganan disetujui oleh Admin, THE Sistem SHALL mengirim notifikasi ke Pemohon dengan pesan "Menunggu waktu penandatanganan, harap mempersiapkan seluruh dokumen yang akan ditandatangani"
2. WHEN notifikasi dikirim, THE Sistem SHALL mengirimkan notifikasi melalui sistem internal dan WhatsApp Gateway

### Requirement 9: Upload Dokumen Pasca-Tandatangan

**User Story:** As a Pemohon, I want mengupload dokumen yang telah ditandatangani dengan checklist kelengkapan, so that Admin dapat memvalidasi kelengkapan dokumen final.

#### Acceptance Criteria

1. WHEN proses penandatanganan selesai, THE Sistem SHALL menampilkan form upload dokumen pasca-tandatangan kepada Pemohon
2. THE Sistem SHALL menampilkan checklist yang wajib dicentang: paraf basah, materai, dan stempel
3. WHEN Pemohon mengupload dokumen pasca-tandatangan, THE Sistem SHALL memvalidasi bahwa semua item checklist telah dicentang sebelum mengizinkan upload
4. WHEN Pemohon berhasil mengupload dokumen pasca-tandatangan, THE Sistem SHALL mengirim notifikasi ke Admin untuk validasi
5. WHEN Admin memvalidasi dokumen pasca-tandatangan, THE Sistem SHALL menyediakan form upload bagi Admin untuk mengupload dokumen identik (versi Admin) yang akan diberikan ke Pemohon
6. WHEN Admin berhasil mengupload dokumen versi Admin, THE Sistem SHALL mengirim notifikasi ke Pemohon bahwa dokumen versi Admin tersedia untuk diunduh

### Requirement 10: Persetujuan PKS Final oleh Admin

**User Story:** As a Admin, I want menyetujui PKS final dan memberitahu pemohon bahwa kerjasama telah dimulai, so that proses permohonan dapat ditutup secara resmi.

#### Acceptance Criteria

1. WHEN Admin menyetujui PKS final, THE Sistem SHALL mengubah status permohonan menjadi Selesai (status 4)
2. WHEN PKS final disetujui, THE Sistem SHALL mengirim notifikasi ke Pemohon dengan pesan "Anda memasuki pelaksanaan kerjasama"
3. WHEN PKS final disetujui, THE Sistem SHALL mencatat tanggal persetujuan dan identitas Admin yang menyetujui dalam histori permohonan

### Requirement 11: Monitoring & Evaluasi - Perubahan Role dan Alur

**User Story:** As a Admin, I want form monev diisi oleh Pemohon dan TKKSD Lokus Kerjasama, serta form monev final dibuat oleh Admin, so that evaluasi kerjasama melibatkan semua pihak terkait.

#### Acceptance Criteria

1. THE Sistem SHALL menyediakan role baru TKKSD_Lokus yang terpisah dari TKKSD_Pembahasan
2. TKKSD_Lokus adalah akun yang mewakili OPD tertentu — setiap user TKKSD_Lokus terhubung ke satu OPD melalui field `id_opd` di tabel users
3. THE Sistem SHALL membatasi akses TKKSD_Lokus hanya pada modul monitoring dan evaluasi untuk kerjasama yang melibatkan OPD mereka
4. WHEN kerjasama memasuki tahap pelaksanaan, THE Sistem SHALL menampilkan form monev kepada Pemohon dan TKKSD_Lokus
5. WHEN Pemohon mengisi form monev, THE Sistem SHALL mengirim notifikasi ke TKKSD_Lokus yang OPD-nya terlibat dalam kerjasama tersebut untuk menyetujui hasil evaluasi
6. WHEN TKKSD_Lokus menyetujui hasil evaluasi Pemohon, THE Sistem SHALL mengirim notifikasi ke Admin
7. WHEN semua evaluasi dari Pemohon dan TKKSD_Lokus selesai, THE Sistem SHALL menampilkan form monev final kepada Admin untuk diisi
8. WHEN Admin membuat akun user baru dengan role TKKSD_Lokus, THE Sistem SHALL menyediakan field untuk memilih OPD yang diwakili oleh akun tersebut

### Requirement 12: Field OPD di Form Permohonan

**User Story:** As a Pemohon, I want memilih OPD yang terlibat dalam kerjasama pada step 3 form permohonan, so that informasi OPD terkait tercatat dalam sistem.

#### Acceptance Criteria

1. WHEN Pemohon mengisi step 3 form permohonan, THE Sistem SHALL menampilkan field OPD dengan pilihan multiple (dapat memilih lebih dari satu)
2. THE Sistem SHALL menyimpan relasi permohonan dengan OPD yang dipilih dalam tabel pivot
3. WHEN Admin melihat detail permohonan, THE Sistem SHALL menampilkan daftar OPD yang terlibat
4. THE Sistem SHALL menyediakan master data OPD yang dapat dikelola oleh Admin

### Requirement 13: Form Monev Final dengan Rating

**User Story:** As a Admin, I want menambahkan rating pada form monev final, so that terdapat penilaian kuantitatif terhadap pelaksanaan kerjasama.

#### Acceptance Criteria

1. WHEN Admin mengisi form monev final, THE Sistem SHALL menampilkan field rating dengan skala 1 sampai 5
2. THE Sistem SHALL menyimpan nilai rating pada record monev
3. WHEN pengguna melihat detail monev, THE Sistem SHALL menampilkan rating dalam format visual (bintang atau angka)

### Requirement 14: Reminder 3 Pihak

**User Story:** As a Admin, I want sistem mengirim reminder otomatis 3 bulan sebelum kerjasama berakhir, so that semua pihak terkait dapat mempersiapkan perpanjangan atau penutupan kerjasama.

#### Acceptance Criteria

1. WHEN tanggal berakhir kerjasama tinggal 3 bulan, THE Sistem SHALL mengirim reminder otomatis ke OPD terkait
2. WHEN tanggal berakhir kerjasama tinggal 3 bulan, THE Sistem SHALL mengirim reminder otomatis ke Perangkat Daerah terkait
3. WHEN tanggal berakhir kerjasama tinggal 3 bulan, THE Sistem SHALL mengirim reminder otomatis ke Admin (Bagian Kerjasama)
4. THE Sistem SHALL mengirim reminder melalui notifikasi sistem internal dan WhatsApp Gateway
5. THE Sistem SHALL menjalankan pengecekan reminder secara terjadwal (scheduled task) setiap hari

### Requirement 15: Infografis Publik

**User Story:** As a pengunjung publik, I want melihat infografis kerjasama daerah tanpa login, so that informasi kerjasama dapat diakses secara transparan oleh masyarakat.

#### Acceptance Criteria

1. THE Sistem SHALL menyediakan halaman publik yang menampilkan infografis kerjasama daerah
2. THE Sistem SHALL menampilkan statistik jumlah kerjasama aktif, selesai, dan dalam proses
3. THE Sistem SHALL menampilkan data infografis tanpa memerlukan autentikasi (login)
4. WHEN data kerjasama diperbarui, THE Sistem SHALL memperbarui infografis secara otomatis

### Requirement 16: Monev Kerjasama Manual (Admin Only)

**User Story:** As a Admin, I want menginput dan mengevaluasi monev secara manual untuk kerjasama tertentu, so that kerjasama yang tidak melalui proses online tetap dapat dimonitor.

#### Acceptance Criteria

1. THE Sistem SHALL menyediakan form monev manual yang hanya dapat diakses oleh Admin
2. WHEN Admin mengisi form monev manual, THE Sistem SHALL menyimpan data evaluasi tanpa melibatkan Pemohon dan OPD
3. THE Sistem SHALL menandai monev manual sebagai tipe berbeda dari monev reguler dalam database
4. WHEN Admin melihat daftar monev, THE Sistem SHALL menampilkan monev manual dan monev reguler secara terpisah atau dengan penanda yang jelas

### Requirement 17: Menu Inputan Kerjasama Manual

**User Story:** As a Admin, I want menginput kerjasama dan PKS final secara manual, so that kerjasama yang tidak melalui proses permohonan online tetap tercatat dalam sistem.

#### Acceptance Criteria

1. THE Sistem SHALL menyediakan menu "Kerjasama Manual" yang hanya dapat diakses oleh Admin
2. WHEN Admin membuat kerjasama manual, THE Sistem SHALL menyediakan form input data kerjasama lengkap (instansi, perihal, tanggal, OPD terkait)
3. WHEN Admin membuat kerjasama manual, THE Sistem SHALL menyediakan form upload PKS final bertandatangan dalam format PDF
4. THE Sistem SHALL menyimpan kerjasama manual dengan penanda bahwa data tersebut tidak melalui proses permohonan online
5. WHEN kerjasama manual disimpan, THE Sistem SHALL memasukkan data tersebut ke dalam laporan dan infografis publik
