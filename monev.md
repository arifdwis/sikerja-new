# Spesifikasi: 3 Form Monev per Role (Pemohon / TKKSD Lokus / Admin)

## Latar belakang

Modul Monitoring & Evaluasi (Monev) sekarang punya **satu** form generik dengan 11 pertanyaan yang sama untuk semua role. Kebutuhan baru: tiap role mengisi form dengan **pertanyaan berbeda total**, karena sudut pandang penilaiannya beda:

- **Pemohon** — pelapor pelaksana, self-report realisasi kerjasama.
- **TKKSD Lokus** — pengawas pada OPD lokus, menilai kepatuhan & koordinasi.
- **Admin** (Bagian Kerjasama) — penilai final lintas hasil.

> **Keputusan penting:** **Form Admin = form yang ADA SEKARANG, tidak diubah.** Hanya **Pemohon** dan **TKKSD Lokus** yang dibuatkan form baru dengan kolom DB baru.

## Kondisi sekarang (yang sudah jalan)

- `MonevController@create` sudah deteksi role & kirim prop `submitterRole` (`admin`/`tkksd_lokus`/`pemohon`).
- `MonevController@store` sudah set `submitter_role`, `tipe`, `submitter_user_id`, cek otorisasi per role, anti double-submit, notif ke admin.
- Tabel `monevs` punya 11 kolom evaluasi + `rating` + `file_bukti` (dipakai form sekarang).
- Index admin meng-grup 1 baris per kerjasama, kumpulkan `monevs[]` dari ketiga role (monev mandiri-paralel).
- Bug kecil: `Create.vue` deklarasi prop `operator`, padahal controller kirim `pemohon` + `submitterRole` (saat ini tidak dipakai). Diperbaiki saat refactor.

---

## Ruang lingkup perubahan

| Role | Form | Kolom DB |
|---|---|---|
| **Admin** | **Tetap (Create.vue sekarang)** — tidak diubah | Kolom existing (`kesesuaian_tujuan`…`rekomendasi_lanjutan`, `rating`, `file_bukti`) |
| **Pemohon** | **Baru** — `Forms/Pemohon.vue` | Kolom baru `pmh_*` |
| **TKKSD Lokus** | **Baru** — `Forms/Tkksd.vue` | Kolom baru `tkl_*` |

---

## A. Skema DB — kolom baru (pemohon & tkksd saja)

Tetap 1 tabel `monevs`. Migration baru tambah kolom nullable (tiap baris hanya isi set sesuai role-nya). **Tidak menyentuh kolom admin/existing.**

File: `database/migrations/2026_05_26_xxxxxx_add_pemohon_tkksd_monev_columns.php`

### Pemohon — prefix `pmh_`  *(DRAFT — perlu konfirmasi teks & jumlah)*

| Kolom | Tipe | Pertanyaan |
|---|---|---|
| `pmh_realisasi_kegiatan` | enum | Apakah kegiatan kerjasama terlaksana sesuai rencana? (Terlaksana penuh / Sebagian / Tidak) |
| `pmh_kesesuaian_output` | enum | Apakah output sesuai target perjanjian? (Ya / Sebagian / Tidak) |
| `pmh_pemanfaatan_hasil` | text | Bagaimana hasil kerjasama dimanfaatkan? |
| `pmh_kendala_lapangan` | text | Kendala selama pelaksanaan? |
| `pmh_keberlanjutan` | enum | Usulan keberlanjutan (Perlu dilanjutkan / Cukup / Hentikan) |
| `pmh_file_laporan` | string | Upload laporan pelaksanaan (opsional) |

### TKKSD Lokus — prefix `tkl_`  *(DRAFT — perlu konfirmasi)*

| Kolom | Tipe | Pertanyaan |
|---|---|---|
| `tkl_kepatuhan_pks` | enum | Kepatuhan pelaksanaan terhadap PKS (Patuh / Sebagian / Tidak) |
| `tkl_koordinasi_mitra` | enum | Kualitas koordinasi OPD–mitra (Sangat baik / Baik / Cukup / Kurang) |
| `tkl_kesesuaian_anggaran` | enum | Kesesuaian realisasi vs anggaran (Sesuai / Sebagian / Tidak) |
| `tkl_temuan_pengawasan` | text | Temuan pengawasan |
| `tkl_rekomendasi_lokus` | enum | Rekomendasi lokus (Lanjutkan / Perbaiki / Hentikan) |
| `tkl_catatan` | text | Catatan tambahan |

Update `$fillable` di `app/Models/Monev.php` dengan kolom `pmh_*` + `tkl_*`.

---

## B. Backend

Route tetap (`monev.create`, `monev.store`) — controller role-aware, tanpa route baru.

### `MonevController@create`
Pilih komponen berdasarkan role:
```php
$component = match (true) {
    $isAdmin       => "$this->view/Create",        // form lama, tidak diubah
    $isTkksdLokus  => "$this->view/Forms/Tkksd",
    default        => "$this->view/Forms/Pemohon",
};
```
Perbaiki prop: kirim `pemohon` (bukan `operator`).

### `MonevController@store`
Branch validasi per role:
```php
$rules = ['id_permohonan' => 'required|exists:permohonan,id',
          'tanggal_evaluasi' => 'required|date'];
if ($isAdmin)           $rules += [ /* aturan existing (kesesuaian_tujuan dst) */ ];
elseif ($isTkksdLokus)  $rules += [ /* tkl_* */ ];
else                    $rules += [ /* pmh_* */ ];
```
Logika lain (anti double-submit, otorisasi per role, file upload, set `submitter_role`/`tipe`, `notifyAdminMonevSubmitted`) **tetap**. Branch admin = perilaku sekarang persis.

File upload: admin pakai `file_bukti` (existing), pemohon pakai `pmh_file_laporan`.

---

## C. Frontend

Buat folder `resources/js/Pages/Backend/Monev/Forms/`:
- `Pemohon.vue` — field `pmh_*`.
- `Tkksd.vue` — field `tkl_*`.

**Admin tetap pakai `Create.vue` sekarang — tidak disentuh.**

Opsional (mengurangi duplikasi): ekstrak shell layout (header card, "Pilih Kerjasama" + "Tanggal Evaluasi", footer tombol) jadi `Forms/Partials/MonevFormShell.vue` dengan slot `#questions`, dipakai Pemohon.vue & Tkksd.vue. Pola dropdown+section direuse dari Create.vue.

### Index admin (detail modal)
Payload `monevs.map(...)` perlu tambah field `pmh_*` & `tkl_*`. Modal detail tampilkan blok kondisional sesuai `submitter_role`:
- `admin` → blok 11 pertanyaan existing.
- `pemohon` → blok `pmh_*`.
- `tkksd_lokus` → blok `tkl_*`.

---

## D. File yang disentuh

| File | Aksi |
|---|---|
| `database/migrations/2026_05_26_*_add_pemohon_tkksd_monev_columns.php` | Buat |
| `app/Models/Monev.php` | Tambah `pmh_*`,`tkl_*` ke `$fillable` |
| `app/Http/Controllers/MonevController.php` | `create()` pilih komponen; `store()` validasi per role |
| `resources/js/Pages/Backend/Monev/Forms/Pemohon.vue` | Buat |
| `resources/js/Pages/Backend/Monev/Forms/Tkksd.vue` | Buat |
| `resources/js/Pages/Backend/Monev/Forms/Partials/MonevFormShell.vue` | Buat (opsional) |
| `resources/js/Pages/Backend/Monev/Create.vue` | **Tidak diubah** (tetap form admin) |
| `resources/js/Pages/Backend/Monev/Index.vue` | Tampilkan field role-baru di detail |

---

## E. Verifikasi

1. `php artisan migrate` → kolom `pmh_*`,`tkl_*` muncul.
2. Login **admin** → `/monev/create` → form lama persis sama (tidak ada perubahan).
3. Login **pemohon** → form Pemohon (`pmh_*`) → submit → baris `submitter_role=pemohon`, kolom `pmh_*` terisi.
4. Login **tkksd_lokus** → form Tkksd (`tkl_*`) → submit kerjasama OPD-nya; 403 jika OPD tidak terlibat.
5. Index admin → 1 kerjasama tampil monev dari 3 role; modal detail render blok sesuai role.
6. Double-submit ditolak.
7. `npm run build` lolos.

---

## Perlu dikonfirmasi

1. **Daftar pertanyaan/field final** untuk Pemohon (`pmh_*`) & TKKSD Lokus (`tkl_*`) — tabel di section A masih draft.
2. Form Admin dikonfirmasi **tidak berubah** sama sekali (termasuk 11 pertanyaan & rating).

---

## F. Update — Split tabel monev per role (2026-05-26)

Setelah implementasi awal landed, `monevs` jadi gemuk (39 kolom: identitas + 11 admin + 6 pemohon + 6 tkksd + status + dll). Diputuskan **pisah jawaban detail ke 3 tabel 1:1** supaya:

- `monevs` ringan, index lebih cepat (cuma identitas + role + status + tanggal).
- Tiap baris detail tidak punya kolom kosong (sebelumnya 1 baris pemohon punya 17+ kolom NULL milik admin/tkksd).
- Schema lebih jelas per role; mudah tambah/ubah field tanpa nyentuh tabel inti.

### Skema baru

| Tabel | Isi |
|---|---|
| `monevs` (18 kolom) | id, uuid, id_permohonan, id_pemohon, kode_monev, tanggal_evaluasi, tipe, submitter_role, submitter_user_id, id_tkksd_lokus, tkksd_approved_at, tkksd_catatan, status, catatan_admin, reviewed_at, reviewed_by, timestamps |
| `monev_admin_details` | monev_id (UNIQUE FK) + 11 jawaban admin + rating + file_bukti + saran/kendala |
| `monev_pemohon_details` | monev_id (UNIQUE FK) + 6 field `pmh_*` |
| `monev_tkksd_details` | monev_id (UNIQUE FK) + 6 field `tkl_*` |

FK pakai `cascadeOnDelete()` — hapus monev otomatis hapus detail.

### Migration

`database/migrations/2026_05_26_010000_split_monev_details_per_role.php`:
1. Buat 3 tabel detail.
2. Backfill data existing (6 baris) berdasar `submitter_role`.
3. Drop kolom jawaban (admin + pmh_* + tkl_*) dari `monevs`.
4. `down()` re-add kolom dan re-fill balik dari detail tables.

### Model

| File | Aksi |
|---|---|
| `app/Models/MonevAdminDetail.php` | Buat |
| `app/Models/MonevPemohonDetail.php` | Buat |
| `app/Models/MonevTkksdDetail.php` | Buat |
| `app/Models/Monev.php` | Hapus jawaban dari `$fillable`; tambah `hasOne` ke 3 detail; magic `__get` accessor; override `toArray()` flatten ke root supaya **JSON shape ke frontend tidak berubah** (back-compat) |

Konstanta `Monev::ADMIN_DETAIL_KEYS`, `PEMOHON_DETAIL_KEYS`, `TKKSD_DETAIL_KEYS` jadi sumber kebenaran daftar field per role.

### Controller

`MonevController@store` & `@storeManual`:
- Pisah `$validated` → `$monevPayload` (kolom inti) + `$detailPayload` (jawaban) pakai `array_intersect_key`/`array_diff_key` dengan konstanta DETAIL_KEYS.
- Bungkus `Monev::create()` + `*Detail::create()` di `DB::transaction`.

`@index`, `@show`, `@notifyPemohon`, `@export`: tambah eager-load `adminDetail`, `pemohonDetail`, `tkksdDetail` untuk hindari N+1.

### Frontend

**Tidak diubah.** Berkat `toArray()` override yang flatten field detail ke root + magic `__get` accessor, semua akses `selectedMonev.kesesuaian_tujuan`, `m.pmh_*`, `m.tkl_*` di Vue tetap jalan persis seperti sebelumnya.

### Verifikasi (sudah dijalankan)

- `php artisan migrate` ✓ — backfill 2 admin + 2 pemohon + 2 tkksd dari 6 monev existing.
- `monevs` cuma 18 kolom (turun dari 39).
- Smoke test PHP: create monev pemohon, akses `$m->pmh_realisasi_kegiatan` via accessor → kembali "Terlaksana penuh" ✓.
- `toArray()` flatten benar; relasi `admin_detail`/`pemohon_detail`/`tkksd_detail` mentah disembunyikan dari payload ✓.
- `php artisan route:list --path=monev` ✓ — 9 route monev tetap ada.
