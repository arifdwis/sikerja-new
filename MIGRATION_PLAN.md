# RENCANA MIGRASI SIKERJA V1 KE V2
## Sistem Informasi Kerjasama Daerah Kota Samarinda

**Tanggal:** 20 Januari 2026  
**Versi Target:** SiKerja V2 (Laravel 12 + Vue 3 + Inertia.js)

---

## 1. RINGKASAN PERUBAHAN TEKNOLOGI

| Aspek | SiKerja V1 | SiKerja V2 |
|-------|------------|------------|
| **Laravel** | 8.75 | 12.x |
| **PHP** | ^7.3/^8.0 | ^8.2 |
| **Frontend** | Blade + jQuery | Vue 3 + Inertia.js |
| **CSS** | Bootstrap (Nue UI) | TailwindCSS 3.x |
| **Build Tool** | Laravel Mix | Vite 6.x |
| **Admin Panel** | Nue UI | Custom Vue Components |
| **Authentication** | SSO + Nue Auth | SSO + Laravel Breeze |
| **Permission** | Nue Permissions | Spatie/Laravel-Permission |
| **Components** | Blade Components | Flowbite Vue |

---

## 2. STRUKTUR DATABASE (TIDAK BERUBAH)

SiKerja V2 akan menggunakan database yang **sama** dengan V1. Berikut tabel-tabel utama:

### 2.1 Tabel Utama

| Tabel | Deskripsi |
|-------|-----------|
| `users` | Data pengguna (SSO) |
| `permohonan` | Permohonan kerjasama |
| `pemohon` | Data pemohon individu |
| `corporate` | Data pemohon korporat |
| `kategori` | Kategori kerjasama |
| `permohonan_file` | File lampiran |
| `permohonan_histori` | Riwayat permohonan |
| `permohonan_histori_pembahasan` | Diskusi/pembahasan |
| `notifikasi` | Notifikasi pengguna |
| `penjadwalan` | Jadwal pembahasan |
| `provinsi` | Data provinsi |
| `kota` | Data kota |

### 2.2 Tabel Spatie Permission (Baru)

Migrasi akan membuat tabel baru untuk Spatie:
- `roles`
- `permissions`
- `model_has_roles`
- `model_has_permissions`
- `role_has_permissions`

**Catatan:** Data role dari tabel `role_users` perlu dimigrasikan ke tabel Spatie.

---

## 3. MAPPING MODEL

### 3.1 Model yang Dibuat

| Model V2 | Tabel | Referensi V1 |
|----------|-------|--------------|
| `User` | `users` | `App\Models\User` |
| `Permohonan` | `permohonan` | `Modules\Formulir\Entities\Permohonan` |
| `Pemohon` | `pemohon` | `Modules\Pemohon\Entities\Pemohon` |
| `Corporate` | `corporate` | `Modules\Pemohon\Entities\Corporate` |
| `Kategori` | `kategori` | `Modules\Formulir\Entities\Kategori` |
| `PermohonanFile` | `permohonan_file` | `Modules\Core\Entities\File` |
| `PermohonanHistori` | `permohonan_histori` | `Modules\Core\Entities\Histori` |
| `PermohonanPembahasan` | `permohonan_histori_pembahasan` | `Modules\Core\Entities\Pembahasan` |
| `Notifikasi` | `notifikasi` | `App\Models\Notifikasi` |
| `Penjadwalan` | `penjadwalan` | `Modules\Core\Entities\Penjadwalan` |
| `Provinsi` | `provinsi` | `Modules\Wilayah\Entities\Provinsi` |
| `Kota` | `kota` | `Modules\Wilayah\Entities\Kota` |

---

## 4. MAPPING MENU/ROUTES

### 4.1 Menu Publik (Pemohon)

| Menu V1 | Route V2 | Status |
|---------|----------|--------|
| Dashboard | `/dashboard` | ✅ Dibuat |
| Permohonan | `/permohonan` | ✅ Dibuat |
| Riwayat | `/riwayat` | 🔄 Placeholder |
| Notifikasi | `/notifikasi` | ✅ Dibuat |
| Profile | `/profile` | ✅ Breeze Default |

### 4.2 Menu Admin/TKSD

| Menu V1 | Route V2 | Status |
|---------|----------|--------|
| Dashboard Admin | `/dashboard` | ✅ Dengan filter |
| Kelola Permohonan | `/permohonan` | ✅ Dibuat |
| Validasi | `/validasi` | ⏳ TODO |
| Pembahasan | `/pembahasan` | ⏳ TODO |
| Persetujuan | `/persetujuan` | ⏳ TODO |
| Penjadwalan | `/penjadwalan` | ⏳ TODO |
| Master Kategori | `/master/kategori` | ⏳ TODO |
| Laporan | `/laporan` | 🔄 Placeholder |
| Pengaturan | `/settings` | ⏳ TODO |

---

## 5. LANGKAH MIGRASI

### Fase 1: Setup Dasar ✅
- [x] Inisialisasi Laravel 12
- [x] Install Laravel Breeze + Vue + Inertia
- [x] Konfigurasi Vite + TailwindCSS
- [x] Install Flowbite Vue components
- [x] Install Spatie/Laravel-Permission
- [x] Buat Models untuk database existing
- [x] Setup routes dasar
- [x] Buat Dashboard dengan statistik

### Fase 2: Core Features (NEXT)
- [ ] Integrasi SSO Samarinda
- [ ] CRUD Permohonan lengkap (Create, Edit, Delete)
- [ ] Upload & manajemen file
- [ ] Sistem notifikasi real-time
- [ ] WhatsApp Gateway integration

### Fase 3: Workflow
- [ ] Workflow validasi
- [ ] Workflow pembahasan
- [ ] Workflow persetujuan
- [ ] Penjadwalan rapat

### Fase 4: Reporting & Admin
- [ ] Dashboard admin lanjutan
- [ ] Export PDF/Excel
- [ ] Master data management
- [ ] Pengaturan sistem

### Fase 5: Testing & Deployment
- [ ] Unit testing
- [ ] Integration testing
- [ ] User acceptance testing
- [ ] Deployment ke production

---

## 6. KONFIGURASI SSO

### 6.1 Install SSO Client
```bash
composer require novay/sso-client
```

### 6.2 Konfigurasi .env
```env
SSO_SERVER_URL=https://sso.samarindakota.go.id
SSO_BROKER_NAME=sikerja-v2
SSO_BROKER_SECRET=<secret_key>
```

### 6.3 Middleware SSO
Buat middleware di `app/Http/Middleware/SSOAutoLogin.php` untuk auto-login dari SSO.

---

## 7. KONFIGURASI WHATSAPP GATEWAY

### 7.1 Helper Functions
Buat helper di `app/Helpers/WhatsApp.php`:
- `send_whatsapp($phone, $message)`
- `send_whatsapp_group($message, $groupId)`
- `normalize_phone($phone)`

### 7.2 Konfigurasi .env
```env
WA_GATEWAY_RUN=true
WA_GATEWAY_URL=https://gowa.samarindakota.go.id/send/message
WA_GATEWAY_USER=admingowa
WA_GATEWAY_PASS=<password>
WA_GROUP_ID=<group_id>
WA_ADMIN_GROUP_ID=<admin_group_id>
```

---

## 8. FILE STRUCTURE V2

```
/sikerja-v2.samarindakota.go.id
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php ✅
│   │   │   ├── PermohonanController.php ✅
│   │   │   ├── NotifikasiController.php ✅
│   │   │   └── ProfileController.php (Breeze)
│   │   └── Middleware/
│   │       └── SSOAutoLogin.php (TODO)
│   ├── Models/
│   │   ├── User.php ✅
│   │   ├── Permohonan.php ✅
│   │   ├── Pemohon.php ✅
│   │   ├── Corporate.php ✅
│   │   ├── Kategori.php ✅
│   │   ├── PermohonanFile.php ✅
│   │   ├── PermohonanHistori.php ✅
│   │   ├── PermohonanPembahasan.php ✅
│   │   ├── Notifikasi.php ✅
│   │   ├── Penjadwalan.php ✅
│   │   ├── Provinsi.php ✅
│   │   └── Kota.php ✅
│   └── Helpers/ (TODO)
├── resources/js/
│   ├── Pages/
│   │   ├── Dashboard.vue ✅
│   │   ├── Welcome.vue ✅
│   │   ├── Permohonan/ (TODO)
│   │   ├── Notifikasi/ (TODO)
│   │   ├── Riwayat/ (TODO)
│   │   └── Laporan/ (TODO)
│   ├── Layouts/
│   │   ├── AuthenticatedLayout.vue ✅
│   │   └── GuestLayout.vue ✅
│   └── Components/ ✅
└── routes/
    └── web.php ✅
```

---

## 9. TESTING

### 9.1 Jalankan Development Server
```bash
# Terminal 1: Laravel Server
php artisan serve

# Terminal 2: Vite Dev Server
npm run dev
```

### 9.2 Akses Aplikasi
- URL: http://localhost:8000
- Login: Gunakan SSO atau buat user test

---

## 10. CATATAN PENTING

1. **Database tidak dimigrasikan**, hanya koneksi ke database existing
2. **Session domain** diset ke `.samarindakota.go.id` untuk sharing dengan SSO
3. **Spatie Permission** perlu disesuaikan dengan struktur role existing
4. **File upload** path tetap menggunakan format yang sama dengan V1
5. **API endpoint** untuk mobile app perlu dibuat terpisah jika diperlukan

---

**Dibuat oleh:** AI Assistant  
**Status:** Fase 1 Selesai, Lanjut ke Fase 2
