# SIKERJA V2 — Sistem Informasi Kerja Sama Daerah Kota Samarinda

SIKERJA V2 adalah aplikasi berbasis web yang digunakan oleh Pemerintah Kota Samarinda untuk mengelola alur Kerja Sama Daerah (KSD), mulai dari pengajuan permohonan, verifikasi, pembahasan, penandatanganan, hingga monitoring & evaluasi (Monev) secara terpadu.

---

## 🛠️ Teknologi & Stack Utama

- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Vue 3 (Composition API), Inertia.js, PrimeVue, Tailwind CSS
- **Database**: MySQL / MariaDB
- **Notifikasi**: SMTP Email (`mail.samarindakota.go.id`) & WhatsApp Service
- **Autentikasi**: Single Sign-On (SSO Kota Samarinda), Google OAuth, & Local Auth (Laravel Breeze)

---

## 🚀 Panduan Instalasi & Setup Lokal

### 1. Prasyarat Sistem
- PHP >= 8.2 (ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `cURL`)
- Node.js >= 18.x & npm
- Composer >= 2.x
- Database Server (MySQL 8.0+ / MariaDB 10.5+)

### 2. Kloning Repository & Install Dependencies
```bash
git clone https://github.com/arifdwis/sikerja-new.git sikerja-v2
cd sikerja-v2

# Install dependensi PHP
composer install

# Install dependensi Frontend
npm install
```

### 3. Konfigurasi Environment (`.env`)
Salin file `.env.example` ke `.env` dan atur kredensial database & mail:
```bash
cp .env.example .env
php artisan key:generate
```

Ubah parameter utama di `.env`:
```env
APP_NAME="SIKERJA Samarinda"
APP_ENV=local
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sikerja_v2
DB_USERNAME=root
DB_PASSWORD=

# SMTP Email Notification Settings
MAIL_MAILER=smtp
MAIL_SCHEME=smtps
MAIL_HOST=mail.samarindakota.go.id
MAIL_PORT=465
MAIL_USERNAME=sikerja@samarindakota.go.id
MAIL_PASSWORD=****************
MAIL_FROM_ADDRESS="sikerja@samarindakota.go.id"
MAIL_FROM_NAME="SIKERJA Samarinda"
```

### 4. Migrasi Database & Seeding
```bash
php artisan migrate --seed
```

### 5. Menjalankan Server Lokal
Jalankan server Laravel & Vite secara berdampingan:
```bash
# Terminal 1: Laravel Server
php artisan serve

# Terminal 2: Asset Compiler (Hot Reload)
npm run dev
```

Aplikasi dapat diakses di `http://localhost:8000`.

---

## 👥 Struktur Role & Hak Akses

1. **Pemohon**: Pengguna eksternal / Mitra / Perangkat Daerah yang mengajukan permohonan kerjasama.
2. **Administrator**: Pengelola utama sistem SIKERJA (Bagian Kerja Sama Daerah).
3. **Superadmin**: Administrator tingkat tinggi dengan akses penuh manajemen pengguna & sistem.
4. **TKKSD**: Tim Koordinasi Kerja Sama Daerah Kota Samarinda (Verifikasi & Pembahasan).
5. **TKKSD Lokus**: Anggota TKKSD khusus Perangkat Daerah penanggung jawab lokasi/substansi.

---

## 📑 Fitur Utama

- **Management Permohonan**: Alur pengajuan dokumen, disposisi, dan verifikasi berkas.
- **Pembahasan & Diskusi**: Modul obrolan permohonan dengan fitur lampiran berkas dan riwayat revisi.
- **Penjadwalan Desk-to-Desk & Hybrid**: Pengaturan jadwal verifikasi langsung maupun daring.
- **Monitoring & Evaluasi (Monev)**: Evaluasi berkala pelaksanaan kerjasama antar daerah/pihak ketiga.
- **Auto Email Notification**: Pengiriman pemberitahuan otomatis via SMTP Mail Server ke email notifikasi terdaftar.
- **Mandatory Notification Email Enforcement**: Penguncian sistem bagi akun tanpa email notifikasi aktif.

---

## 🧪 Pengujian & Build Production

```bash
# Menjalankan Test Suite
php artisan test

# Build Bundle Production
npm run build
```

---

## 📄 Dokumentasi Tambahan

- [STRUKTUR_MODUL.md](STRUKTUR_MODUL.md) — Rincian modul & arsitektur sistem.
- [TESTING_FLOW.md](TESTING_FLOW.md) — Panduan skenario pengujian workflow.
- [NOTIFIKASI.md](NOTIFIKASI.md) — Dokumentasi template & skema notifikasi.
- [hallmark_audit.md](hallmark_audit.md) — Laporan Hallmark Audit Kualitas Kode & Keamanan.
