# Testing Flow — SiKerja V2 Workflow Overhaul

Panduan testing untuk semua perubahan yang sudah diimplementasi. Ikuti urutan dari atas ke bawah, karena tahap selanjutnya bergantung pada tahap sebelumnya.

---

## 0. Persiapan Awal

### 0.1 Jalankan Server

```bash
# Terminal 1 — Laravel
php artisan serve

# Terminal 2 — Vite (hot reload Vue)
npm run dev
```

Akses: `http://localhost:8000`

### 0.2 Verifikasi Database

```bash
php artisan migrate:status | grep "2026_05_21"
# Semua harus "Ran"

php artisan tinker --execute="echo 'OPD: ' . DB::table('opd')->count();"
# Expected: OPD: 145

php artisan tinker --execute="echo 'Role tkksd_lokus: ' . DB::table('roles')->where('slug', 'tkksd_lokus')->count();"
# Expected: 1
```

### 0.3 Akun yang Perlu Disiapkan

| Role | Tugas Test | Cara Buat |
|------|------------|-----------|
| `superadmin` | Test semua | Sudah ada di seeder default |
| `administrator` | Test admin actions | Sudah ada / buat baru |
| `tkksd` | Test pembahasan | Sudah ada / buat baru |
| `pemohon` | Test alur pengajuan | Login SSO atau buat manual |
| `tkksd_lokus` | Test monev lokus | **Buat baru** (lihat Test 11) |

---

## TEST 1 — Bug Fix: Edit Permohonan Ditolak (Req 2)

**Prerequisite:** Login sebagai `pemohon`, ada minimal 1 permohonan.

### Langkah:
1. Login sebagai admin → buka permohonan apapun → tolak (status 9)
2. Logout, login sebagai pemohon pemilik permohonan tersebut
3. Buka detail permohonan yang ditolak → klik **Edit**
4. **Expected:** Form edit terbuka tanpa error
5. Ubah field provinsi/kota → klik **Update**
6. **Expected:**
   - ✅ Tidak ada error `Table 'sikerja.provinsi' doesn't exist`
   - ✅ Status permohonan otomatis kembali ke **0 (Menunggu Validasi)**
   - ✅ Histori mencatat "Permohonan direvisi dan diajukan kembali setelah ditolak"

---

## TEST 2 — Field OPD Multiple di Form Permohonan (Req 12)

### Test 2A — Pemohon Pemkot (User SSO dengan id_opd)
1. Login sebagai pemohon yang sudah punya `id_opd` (otomatis dari SSO atau set manual via DB)
2. Buka **Permohonan → Buat Baru**
3. Isi sampai Step 3 (Detail Kerjasama)
4. **Expected:**
   - ✅ Field OPD terisi otomatis dengan OPD dari profil
   - ✅ Field read-only/locked dengan badge "Terhubung otomatis dari profil Anda"

### Test 2B — Pemohon Eksternal (Tanpa id_opd)
1. Login sebagai pemohon tanpa `id_opd` (set NULL via DB jika perlu)
2. Buka **Permohonan → Buat Baru** → Step 3
3. **Expected:**
   - ✅ Tampil multi-select OPD dengan 145 pilihan
   - ✅ Bisa pilih lebih dari 1 OPD (tahan Ctrl/Cmd)

### Test 2C — Verifikasi Penyimpanan
1. Pilih 2-3 OPD → submit form
2. Buka detail permohonan → tab/section OPD
3. **Expected:** Semua OPD yang dipilih tersimpan dan tampil

---

## TEST 3 — Auto-link OPD dari Profile (Req 1)

**Prerequisite:** Sudah ada master OPD di database.

### Test 3A — Saat Pertama Login & Lengkapi Profil
1. Login sebagai user pemohon baru (belum lengkapi profil)
2. Akan otomatis di-redirect ke `/profile` (dipaksa middleware `EnsurePemohonProfileComplete`)
3. Isi tab **Biodata Pemohon** — field "Satuan Kerja"
4. **Expected:**
   - ✅ Field "Satuan Kerja" jadi dropdown OPD (bukan input text bebas)
5. Pilih OPD → simpan
6. **Expected:**
   - ✅ `users.id_opd` otomatis terisi
   - ✅ `pemohon.unit_kerja` otomatis terisi nama OPD

### Test 3B — Verifikasi via Database
```bash
php artisan tinker --execute="
\$u = DB::table('users')->where('email', 'EMAIL_PEMOHON')->first();
echo 'id_opd: ' . (\$u->id_opd ?: 'NULL') . PHP_EOL;
\$opd = DB::table('opd')->find(\$u->id_opd);
echo 'OPD: ' . (\$opd->nama ?? 'tidak terhubung');
"
```

---

## TEST 4 — Workflow Permohonan Lengkap (Req 5-10)

Test paling lengkap — alur dari Pemohon Buat → Pelaksanaan Kerjasama.

### Persiapan
- 1 user pemohon
- 1 user admin
- 1 user tkksd

### Step-by-Step

#### 4.1 Pemohon Buat Permohonan
1. Login pemohon → **Permohonan → Buat Baru**
2. Isi 3 step (instansi, detail, OPD)
3. Submit → upload dokumen Surat Permohonan (PDF), KAK (PDF), MOU (DOCX)
4. **Expected (Req 3):**
   - ✅ Validasi MIME ketat: Surat Permohonan WAJIB PDF (DOC/DOCX ditolak)
   - ✅ KAK WAJIB PDF
   - ✅ MOU WAJIB DOC/DOCX (PDF ditolak)
5. **Expected status:** `0 — Menunggu Validasi`

#### 4.2 Admin Validasi
1. Login admin → **Validasi**
2. Klik permohonan → klik **Setujui untuk Pembahasan**
3. **Expected status:** `1 — Dalam Pembahasan`

#### 4.3 TKKSD Pembahasan + Identitas Approver (Req 4)
1. Login tkksd → **Pembahasan**
2. Klik permohonan → review file → klik **Setujui** pada salah satu file
3. **Expected (Req 4):**
   - ✅ Histori mencatat: "Dokumen X telah Disetujui oleh [Nama TKKSD] (TKKSD)"
   - ✅ Field `role_operator` di histori = "TKKSD"
   - ✅ Identitas approver tampil di sisi pemohon dan admin

#### 4.4 Admin/TKKSD Selesaikan Pembahasan
1. Login admin → buka permohonan dengan status `Dalam Pembahasan`
2. Klik **Selesaikan Pembahasan**
3. **Expected:**
   - ✅ Status berubah ke `2 — Menunggu Jadwal Penandatanganan`
   - ✅ WA notif ke pemohon: *"Pembahasan selesai. Silakan ajukan jadwal penandatanganan."* (Req 5.2)

#### 4.5 Pemohon Ajukan Jadwal Penandatanganan (Req 6)
1. Login pemohon → buka permohonan
2. Klik tombol **Ajukan Jadwal**
3. **Expected (Req 6):**
   - ✅ Form judul: "Pengajuan Jadwal Penandatanganan"
   - ✅ Pilihan metode: **Desk to Desk**, **Seremonial**, **Hybrid** (3 kartu)
   - ✅ Tidak ada lagi opsi `calendar` / `langsung`
4. Pilih metode, isi tanggal, waktu, lokasi → submit
5. **Expected:** Jadwal masuk dengan tipe sesuai pilihan

#### 4.6 Admin Setujui Jadwal (Req 8)
1. Login admin → **Penjadwalan**
2. Setujui jadwal yang baru saja diajukan
3. **Expected (Req 8):**
   - ✅ Status permohonan: `3 — Upload PKS Final`
   - ✅ Notif ke pemohon: *"Menunggu waktu penandatanganan, harap mempersiapkan seluruh dokumen yang akan ditandatangani"*

#### 4.7 Pemohon Upload PKS Final (Req 7)
1. Login pemohon → buka detail permohonan
2. **Expected:** Section "Perjanjian Kerjasama (PKS) Final" muncul dengan form upload
3. Coba upload file `.docx` → **Expected:** ditolak ("File harus berformat PDF")
4. Upload file PDF → submit
5. **Expected:**
   - ✅ Status: `4 — Menunggu Penandatanganan`
   - ✅ File ter-list di section PKS

#### 4.8 Lock Berkas Pembahasan (Req 7.4)
1. Login pemohon → coba upload/edit berkas tahap pembahasan via tombol upload lama
2. **Expected:** HTTP 403 dengan pesan "Berkas tidak dapat diubah karena permohonan sudah melewati tahap pembahasan"

#### 4.9 Pemohon Upload Dokumen Tertandatangani + Checklist (Req 9)
1. Login pemohon → buka permohonan
2. **Expected:** Section "Dokumen Tertandatangani" muncul
3. Coba submit tanpa centang checklist → **Expected:** ditolak
4. Centang **paraf basah**, **materai**, **stempel** → upload PDF → submit
5. **Expected:**
   - ✅ Status: `5 — Validasi Dokumen Pasca-Tandatangan`
   - ✅ File ter-list dengan badge checklist hijau semua

#### 4.10 Admin Validasi + Upload Versi Admin (Req 9.5, 9.6)
1. Login admin → buka permohonan
2. Section "Dokumen Tertandatangani" muncul form **Validasi + Upload Versi Admin**
3. Pilih "Valid" + upload PDF versi admin → submit
4. **Expected:**
   - ✅ Dokumen pemohon ter-validasi
   - ✅ Dokumen versi admin tampil untuk diunduh pemohon
   - ✅ Notif ke pemohon: dokumen versi admin tersedia

#### 4.11 Admin Setujui PKS Final (Req 10)
1. Admin → klik **Setujui PKS Final & Mulai Pelaksanaan Kerjasama**
2. **Expected:**
   - ✅ Status: `6 — Pelaksanaan Kerjasama`
   - ✅ Notif ke pemohon: *"Anda memasuki pelaksanaan kerjasama"*

---

## TEST 5 — Edit Jangka Waktu Pasca-Pembahasan + Notif Detail (Req 5.5, 5.6)

**Prerequisite:** Permohonan sudah lewat status `STATUS_PENJADWALAN` (status >= 2).

1. Login admin → buka permohonan yang sudah di tahap penjadwalan/penandatanganan
2. Edit `tanggal_mulai`, `tanggal_berakhir`, atau `jangka_waktu`
3. Save
4. **Expected:**
   - ✅ Histori mencatat: "Perubahan jangka waktu kerjasama oleh admin"
   - ✅ Field `komentar` berisi detail: "Tanggal Mulai: 2025-01-01 → 2025-02-01; Tanggal Berakhir: ..."
   - ✅ Notif ke pemohon mencantumkan detail perubahan (sebelum & sesudah)
   - ✅ WA notif ke pemohon

---

## TEST 6 — Reminder 3 Bulan Sebelum Berakhir (Req 14)

### Manual Trigger
```bash
php artisan kerjasama:check-expiry
```

### Persiapan Data Test
```bash
php artisan tinker --execute="
DB::table('permohonan')->where('id', 1)->update([
    'tanggal_berakhir' => now()->addDays(90)->format('Y-m-d'),
    'status' => 6 // PELAKSANAAN
]);
"
```

Jalankan command. **Expected:**
- ✅ Notifikasi muncul di tabel `notifikasi` untuk: pemohon, TKKSD Lokus dari OPD terkait, semua admin
- ✅ WA terkirim ke pemohon, TKKSD Lokus, admin (cek log Laravel)
- ✅ WA group admin juga terkirim
- ✅ Output command: "Total reminder dikirim: N"

### Verifikasi Schedule Terdaftar
```bash
php artisan schedule:list 2>&1 | grep kerjasama
# Expected: kerjasama:check-expiry .... 0 7 * * *
```

---

## TEST 7 — Infografis Publik (Req 15)

1. Logout dari semua akun
2. Akses `http://localhost:8000/infografis`
3. **Expected:**
   - ✅ Halaman terbuka tanpa login
   - ✅ Tampil 4 stat card: Total, Aktif, Dalam Proses, Selesai
   - ✅ Distribusi per Kategori (bar chart)
   - ✅ Top OPD Pelaksana (max 9)
   - ✅ Trend per Tahun (5 tahun terakhir)
   - ✅ Top 5 Instansi Mitra
   - ✅ Data juga termasuk dari `kerjasama_manual` (Req 17.5)

---

## TEST 8 — Master OPD CRUD (Req 12.4)

**Prerequisite:** Login sebagai administrator/superadmin.

1. Akses `/master/opd`
2. **Expected:** Tampil 145 OPD dengan pagination
3. Klik **Tambah OPD** → isi nama "Test OPD", singkatan "TST" → simpan
4. **Expected:** OPD baru muncul di list
5. Klik edit pada OPD baru → ubah singkatan → simpan
6. Klik delete pada OPD baru
7. **Expected:** OPD terhapus
8. Coba delete OPD yang masih digunakan (misal: yang ada di `users.id_opd`)
9. **Expected:** Error "OPD masih digunakan, tidak dapat dihapus"

---

## TEST 9 — Kerjasama Manual (Req 17)

**Prerequisite:** Login sebagai administrator/superadmin.

1. Akses `/kerjasama-manual`
2. Klik **Tambah Kerjasama Manual**
3. Isi form:
   - Nomor PKS: `MANUAL/001/2025`
   - Nama Instansi: `PT XYZ`
   - Perihal: `Kerjasama Pelatihan SDM`
   - Tanggal mulai/berakhir
   - Pilih 2-3 OPD
   - **Upload PDF PKS bertandatangan** (wajib)
4. Submit
5. **Expected:**
   - ✅ Tersimpan di tabel `kerjasama_manual`
   - ✅ OPD tersimpan di pivot `kerjasama_manual_opd`
   - ✅ File PDF tersimpan di `storage/app/public/uploads/kerjasama_manual/`
6. Buka detail → verifikasi data lengkap, file bisa diunduh

### Verifikasi Tampil di Infografis
1. Logout, akses `/infografis`
2. **Expected:** Total kerjasama bertambah, OPD yang dipilih muncul di Top OPD

---

## TEST 10 — Monev Manual (Req 16)

**Prerequisite:** Login sebagai administrator/superadmin.

1. Akses `/monev`
2. **Expected:** Tampil tab atau button untuk monev manual (admin only)
3. Klik **Buat Monev Manual** (atau panggil endpoint `POST /monev/manual` via tools)
4. Isi semua field termasuk **rating 1-5**
5. Submit
6. **Expected:**
   - ✅ Tersimpan dengan `tipe = 'manual'`
   - ✅ Status = `STATUS_REVIEWED` (langsung final, tidak pakai TKKSD review)
   - ✅ `id_pemohon` boleh NULL
7. Verifikasi di list monev: muncul badge "Monev Manual"

---

## TEST 11 — Akun TKKSD Lokus + Scope per OPD (Req 11)

### 11.1 Buat Akun TKKSD Lokus
1. Login admin → **Settings → Users → Tambah**
2. Isi nama, email, password
3. Pilih role **TKKSD Lokus Kerjasama**
4. **Expected:** Field "OPD yang Diwakili" muncul (kondisional)
5. Pilih salah satu OPD (misal "Dinas Pendidikan dan Kebudayaan")
6. Coba submit tanpa pilih OPD → **Expected:** Error "OPD wajib dipilih untuk role TKKSD Lokus Kerjasama"
7. Pilih OPD → submit
8. **Expected:** User baru ter-create dengan `id_opd` terisi

### 11.2 Login sebagai TKKSD Lokus
1. Logout, login dengan akun tkksd_lokus baru
2. **Expected sidebar:** Hanya 2 menu — **Dashboard** dan **Monitoring & Evaluasi**
3. Coba akses `/permohonan` langsung di URL → **Expected:** HTTP 403

### 11.3 Scope Monev per OPD
**Prerequisite:** Ada permohonan yang sudah pelaksanaan, salah satunya melibatkan OPD si tkksd_lokus.

1. Login tkksd_lokus → **Monev**
2. **Expected:** Hanya tampil monev kerjasama yang OPD-nya = `id_opd` user

### 11.4 Approve Monev Pemohon
1. Login pemohon → buat monev untuk kerjasama yang melibatkan OPD si tkksd_lokus
2. **Expected:** Notif terkirim ke tkksd_lokus
3. Login tkksd_lokus → buka monev tersebut
4. Klik **Setujui** + isi catatan
5. **Expected:**
   - ✅ Field `id_tkksd_lokus`, `tkksd_approved_at`, `tkksd_catatan` terisi di tabel `monevs`
   - ✅ Status berubah ke `STATUS_REVIEWED`
   - ✅ Notif ke admin: "Monev Disetujui TKKSD Lokus"
6. Login admin → buka monev → tampil section "Persetujuan TKKSD Lokus" dengan info reviewer

---

## TEST 12 — Rating Monev Final (Req 13)

1. Login pemohon → **Monev → Buat**
2. Isi semua field, di section terakhir ada **Rating** (1-5 bintang)
3. Klik 4 bintang → submit
4. **Expected:** Tersimpan `rating = 4`
5. Buka **Detail Monev** → **Expected:** Tampil 4 bintang kuning + "4 dari 5"

---

## TEST 13 — Template Dokumen Tetap (Req 3)

### 13.1 Upload Template ke Storage
```bash
# Letakkan file template di storage/app/templates/
cp ~/Downloads/surat_permohonan.pdf storage/app/templates/surat_permohonan_kerjasama.pdf
cp ~/Downloads/kak.pdf storage/app/templates/kak.pdf
cp ~/Downloads/mou.docx storage/app/templates/mou.docx
```

### 13.2 Test Download
1. Login user manapun → akses `/templates/surat_permohonan`
2. **Expected:** File terdownload dengan nama "Template Surat Permohonan Kerjasama.pdf"
3. Test juga `/templates/kak` dan `/templates/mou`

### 13.3 Test List API
```bash
curl http://localhost:8000/templates -H "Cookie: laravel_session=..." 
# Expected: JSON dengan 3 template, status available true/false
```

### 13.4 Test 404
1. Akses `/templates/tidak_ada`
2. **Expected:** HTTP 404 "Template tidak ditemukan"

---

## TEST 14 — SSO Login (Req 1)

**Prerequisite:** SSO Samarinda dapat diakses dari environment.

1. Buka `/login`
2. **Expected:** Tombol "Login dengan SSO Samarinda" tersedia
3. Klik → redirect ke `sso.samarindakota.go.id`
4. Login dengan akun SSO yang valid
5. **Expected:**
   - ✅ Redirect kembali ke aplikasi sebagai user yang ter-login
   - ✅ Jika `unit_id` user cocok dengan OPD di tabel, `users.id_opd` otomatis terisi
   - ✅ Bisa langsung buat permohonan dengan OPD ter-fill

---

## Checklist Final Sebelum Git Push

Ceklis ini wajib semua hijau sebelum boleh push ke git:

```
[ ] TEST 1  — Bug fix edit permohonan ditolak — PASS
[ ] TEST 2  — Field OPD multiple di form permohonan — PASS
[ ] TEST 3  — Auto-link OPD dari profile — PASS
[ ] TEST 4  — Workflow permohonan lengkap (10 step) — PASS
[ ] TEST 5  — Edit jangka waktu + notif detail — PASS
[ ] TEST 6  — Reminder 3 bulan — PASS
[ ] TEST 7  — Infografis publik — PASS
[ ] TEST 8  — Master OPD CRUD — PASS
[ ] TEST 9  — Kerjasama manual — PASS
[ ] TEST 10 — Monev manual — PASS
[ ] TEST 11 — TKKSD Lokus + scope per OPD — PASS
[ ] TEST 12 — Rating monev — PASS
[ ] TEST 13 — Template dokumen tetap — PASS
[ ] TEST 14 — SSO login + auto-link — PASS
```

---

## Troubleshooting Umum

### Error: "OPD masih digunakan, tidak dapat dihapus"
Hapus dulu permohonan/user/kerjasama_manual yang masih merefer ke OPD tersebut.

### WA notif tidak terkirim
- Cek `config('services.whatsapp.group_id')` dan `WA_GATEWAY_*` di `.env`
- Cek log `storage/logs/laravel.log` untuk error WhatsappService

### Session SSO tidak terbentuk
- Cek `config/sso.php` — `broker_name`, `broker_secret`, `server_url` harus benar
- Cek `SESSION_DOMAIN` di `.env` (harus `.samarindakota.go.id` jika di production)

### Migration gagal di tabel kerjasama_manual_opd
Jalankan ulang dengan urutan benar:
```bash
php artisan migrate --path=database/migrations/2026_05_21_000001_create_opd_table.php
php artisan migrate
```

### TKKSD Lokus tidak lihat monev apapun
Cek `users.id_opd` user tersebut sudah terisi:
```bash
php artisan tinker --execute="
\$u = DB::table('users')->where('email', 'EMAIL_TKKSD_LOKUS')->first();
echo 'id_opd: ' . (\$u->id_opd ?: 'NULL');
"
```

### Vue page tidak update setelah edit file
Pastikan `npm run dev` jalan, atau jalankan `npm run build` untuk production.

---

## Setelah Semua Test Lulus

Baru lakukan git push:

```bash
git add .
git status
git commit -m "feat: SiKerja V2 workflow overhaul — 23 fitur dari rapat client

- Fix bug validasi tabel master_provinces/master_cities
- Workflow penandatanganan PKS lengkap (10 status)
- Role TKKSD Lokus dengan scope per OPD
- Field OPD multiple + auto-fill via SSO
- Rating monev + monev manual admin
- Reminder 3 bulan otomatis ke 3 pihak
- Infografis publik
- Menu kerjasama manual + PKS final
- Master data OPD (145 records dari Pemkot Samarinda)
- Template dokumen tetap (Surat Permohonan PDF, KAK PDF, MOU DOCX)
- Identitas approver/rejector di histori dengan role"

git push origin main
```
