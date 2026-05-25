# Implementation Tasks: SiKerja V2 Workflow Overhaul

Status: `[ ]` belum, `[x]` selesai, `[~]` sedang dikerjakan

---

## FASE 1: Database & Bug Fix (HIGH PRIORITY)

- [x] **1.1** Fix Bug Validasi Tabel Provinsi/Kota di `PermohonanController::update()` dan `edit()`
  - Ganti `exists:provinsi,id` → `exists:master_provinces,id`
  - Ganti `exists:kota,id` → `exists:master_cities,id`
  - Izinkan edit permohonan status 9 (ditolak), reset ke status 0 setelah submit
  - **Requirement:** 2.1, 2.2, 2.3

- [x] **4.4** Buat `OpdSeeder` dari data `opd.gz` (145 OPD setelah dedup)
  - **File baru:** `database/seeders/OpdSeeder.php`
  - **Requirement:** 12.4

- [x] **1.2** Migrate: buat tabel `opd`
  - File: `2026_05_21_000001_create_opd_table.php` ✅ sudah dibuat
  - Perintah: `php artisan migrate`

- [x] **1.3** Jalankan `OpdSeeder` → 145 OPD masuk database
  - Perintah: `php artisan db:seed --class=OpdSeeder`

- [x] **1.4** Migrate: tambah `id_opd` ke `users`
  - File: `2026_05_21_000002_add_id_opd_to_users_table.php` ✅ sudah dibuat

- [x] **1.5** Migrate: buat tabel `permohonan_opd`
  - File: `2026_05_21_000003_create_permohonan_opd_table.php` ✅ sudah dibuat

- [x] **1.6** Migrate: tambah `role_operator` ke `permohonan_histori`
  - File: `2026_05_21_000004_add_role_operator_to_permohonan_histori.php` ✅ sudah dibuat

- [x] **1.7** Migrate: update enum `tipe` di `penjadwalan` → `desk_to_desk`, `seremonial`, `hybrid`
  - File: `2026_05_21_000005_update_penjadwalan_tipe_column.php` ✅ sudah dibuat

- [x] **1.8** Migrate: tambah `rating`, `tipe`, `id_tkksd_lokus` ke `monevs`
  - File: `2026_05_21_000006_add_rating_tipe_to_monevs_table.php` ✅ sudah dibuat

- [x] **1.9** Migrate: buat tabel `permohonan_pks` dan `permohonan_ttd`
  - File: `2026_05_21_000007_create_permohonan_pks_table.php` ✅
  - File: `2026_05_21_000008_create_permohonan_ttd_table.php` ✅

- [x] **1.10** Migrate: buat tabel `kerjasama_manual` + role `tkksd_lokus`
  - File: `2026_05_21_000009_create_kerjasama_manual_table.php` ✅
  - File: `2026_05_21_000010_add_tkksd_lokus_role_and_permissions.php` ✅

---

## FASE 2: Backend Model & Controller

- [x] **2.1** Buat Model `Opd` dan `PermohonanOpd` + relasi di `Permohonan` & `User`
- [x] **2.2** Buat Model `PermohonanPks` dan `PermohonanTtd`
- [x] **2.3** Buat Model `KerjasamaManual`
- [x] **2.4** Update Model `Permohonan` — tambah konstanta status baru (3-7)
- [x] **2.5** Fix `PermohonanController` — bug fix + edit status 9 + lock berkas (sebagian sudah di Task 1.1, lock berkas akan ditambahkan di sini)
- [x] **2.6** Update `PermohonanController` — field OPD multiple + auto-fill dari user
- [x] **2.7** Buat `PksController` — upload & approve PKS
- [x] **2.8** Buat `PenandatangananController` — upload TTD + validasi checklist
- [x] **2.9** Update `PembahasanController` — identitas approver/rejector
- [x] **2.10** Update `PenjadwalanController` — metode baru + notifikasi
- [x] **2.11** Update `MonevController` — TKKSD Lokus + rating + manual
- [x] **2.12** Buat `KerjasamaManualController` — CRUD admin only
- [x] **2.13** Buat `Master/OpdController` — CRUD master OPD
- [x] **2.14** Update `SSOController` — auto-link OPD dari unit_id (sync user dari SSO + ProfileController dropdown OPD untuk first-time biodata)
- [x] **2.15** Update `UserController` — field OPD untuk role tkksd_lokus
- [x] **2.16** Buat scheduled job reminder kerjasama
- [x] **2.17** Update routes (pemohon, admin, tkksd, web)
- [x] **2.18** Update `PermohonanObserver` — teks notifikasi baru + log perubahan jangka waktu

---

## FASE 3: Frontend Vue Pages

- [x] **3.1** Update `Permohonan/Create.vue` & `Edit.vue` — field OPD multi-select + auto-fill
- [x] **3.2** Update `Permohonan/Show.vue` — section upload PKS, TTD, checklist, dokumen versi admin
- [x] **3.3** Update `Pembahasan/Index.vue` — tampilkan identitas approver/rejector (sudah dengan role_operator di histori)
- [x] **3.4** Update `Penjadwalan/Index.vue` — metode baru + ganti teks (JadwalForm.vue rewrite)
- [x] **3.5** Update `Monev/Create.vue` & `Show.vue` — rating bintang + status TKKSD Lokus
- [x] **3.6** Buat `KerjasamaManual/Index.vue`, `Create.vue`, `Show.vue` (+ Edit.vue)
- [x] **3.7** Buat `Master/Opd/Index.vue` — CRUD OPD
- [x] **3.8** Update `Settings/Users/Create.vue` & `Edit.vue` — field OPD kondisional
- [x] **3.9** Buat `Frontend/Landing/Infografis.vue` — halaman publik

---

## FASE 4: Fitur Tambahan

- [x] **4.1** Template Dokumen Tetap — upload template + `TemplateController::download`
- [x] **4.2** Update `HandleInertiaRequests` — menu khusus role tkksd_lokus
- [x] **4.3** `LandingController::infografis()` — endpoint publik

---

## Log Eksekusi

(akan diisi saat eksekusi berjalan)
