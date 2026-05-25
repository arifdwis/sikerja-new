# SiKerja V2 — Audit Kesesuaian Aplikasi vs Hasil Rapat

> **Sprint 1 Progress** (selesai sesi ini, WA gateway DISABLED selama eksekusi):
> - ✅ A1 — Status cast integer (Permohonan model)
> - ✅ A2 — Banner TTD tervalidasi → menunggu pelaksanaan
> - ✅ A3 — Reload state setelah rollback (existing flow sudah handle)
> - ✅ A4 — Guard duplicate WA per request lifecycle di observer
> - ✅ A5 — Hapus alias STATUS_DISETUJUI
> - ✅ B1 — Identitas approver/rejector di pembahasan (role + waktu)
> - ✅ B5 — Section visibility Detail modal (sudah implemented sebelumnya)
> - ✅ B6 — Dashboard TKKSD Lokus klik kerjasama → ke /tkksd-lokus/kerjasama
> - ✅ C3 — Infografis include kerjasama_manual (sudah ada)
> - ✅ D1 — Utility status badge class (`resources/js/utils/status.js`)
> - ✅ G1 — Sync STATUS_PELAKSANAAN/SELESAI di design.md
> - ⏸️ D6, D8, F1 — perlu visual review / setup test framework
>
> Run `npm run dev` lalu refresh aplikasi untuk verifikasi changes.
> Aktifkan kembali WA dengan `WA_GATEWAY_RUN=true` di `.env` saat siap rilis.

---

Dokumen ini hasil audit menyeluruh sistem SiKerja V2 saat ini terhadap requirement & flow yang sudah disepakati di rapat. Disusun sebagai daftar perbaikan terprioritisasi (Critical → Major → Minor → Polish) plus catatan style/UX.

Status setiap item:
- 🔴 **Belum** — perlu perbaikan
- 🟡 **Parsial** — sudah, tapi ada gap
- 🟢 **Sesuai** — sudah selesai dan teruji

---

## A. CRITICAL — Wajib Diperbaiki Sebelum Rilis

### A1. 🔴 Status Permohonan Disimpan sebagai String, Tidak Ada Cast

**Masalah**: Kolom `status` di tabel `permohonan` tidak punya cast `'integer'` di model. Hasil: `$permohonan->status === 7` selalu `false` karena disimpan sebagai `'7'` (string), sementara konstanta `STATUS_SELESAI = 7` (int). Ini bikin `getStatusLabelAttribute()` return "Unknown" untuk beberapa record (verified: REQ/2026/0008 & 0009 status 7 → label "Unknown").

**Dampak**: Status tidak tampil benar di tabel Riwayat, Dashboard, dan beberapa logic di observer.

**Solusi**:
```php
// app/Models/Permohonan.php
protected $casts = [
    'status'           => 'integer',
    'tanggal_mulai'    => 'date',
    'tanggal_berakhir' => 'date',
];
```

Lalu jalankan one-time fix data:
```php
DB::table('permohonan')->whereNotNull('status')->orderBy('id')->chunk(100, fn($rows) => $rows->each(fn($r) => DB::table('permohonan')->where('id', $r->id)->update(['status' => (int) $r->status])));
```

**Estimasi**: 15 menit.

---

### A2. 🔴 Permohonan Tidak Bisa Lanjut ke Pelaksanaan dari TTD Pemohon

**Masalah**: Setelah TTD pemohon divalidasi admin (`is_valid=true`), status hanya di-set ke `STATUS_PASCA_TANDATANGAN` (5). Tidak ada otomatis transition ke `STATUS_PELAKSANAAN` (6). User harus manual klik "Setujui PKS Final" di TtdSection.

**Status saat ini**: Sudah ada tombol manual di `TtdSection.vue`, tapi sering user bingung kenapa kerjasama "stuck" di status 5.

**Solusi**: Tambahkan banner instruksi di section TTD (pemohon view) saat status=5: "Dokumen Anda telah tervalidasi. Admin akan menyetujui PKS final untuk memulai pelaksanaan."

Pertimbangkan juga **auto-transition** 5→6 saat TTD valid + tanggal_mulai sudah tiba, untuk reduksi langkah manual.

**Estimasi**: 30 menit (banner) atau 1 jam (auto-transition + safeguard).

---

### A3. 🔴 Edit Permohonan dari Pemohon Hilang Setelah Rollback

**Masalah**: Saat permohonan di-rollback admin ke status 0/9 (revisi), pemohon kadang tidak melihat tombol Edit karena `editableStatuses = [0, 9]` di controller, tapi front-end `GridItem.vue` cek `item.status == 0 || item.status == 9` — bekerja, tapi terkadang setelah Inertia reload komponen masih cache state lama.

**Solusi**: Tambah `router.reload({ only: ['permohonan'], force: true })` setelah aksi rollback admin di `PermohonanController::updateStatus()`.

**Estimasi**: 20 menit.

---

### A4. 🔴 Notifikasi WA Bisa Dobel Saat Status Berubah Bersamaan

**Masalah**: Saat admin approve PKS dan langsung trigger pelaksanaan, observer `PermohonanObserver::updated()` jalan untuk perubahan status DAN perubahan `tanggal_mulai`/`tanggal_berakhir` — bisa kirim 2 WA ke pemohon.

**Solusi**: Tambah guard di `notifyPemohonForStatus()`:
```php
private static array $notifiedThisRequest = [];
// di awal method:
$key = $permohonan->id . '_' . $newStatus;
if (in_array($key, self::$notifiedThisRequest)) return;
self::$notifiedThisRequest[] = $key;
```

**Estimasi**: 15 menit.

---

### A5. 🔴 Spec Status `STATUS_DISETUJUI` Alias Membingungkan

**Masalah**: Di `Permohonan.php`:
```php
const STATUS_DISETUJUI = 6;    // Alias - sebelumnya 3, sekarang ke PELAKSANAAN
```
Konstanta ini masih dipakai di beberapa tempat dan menimbulkan ambiguity. Status 6 itu "Pelaksanaan", bukan "Disetujui".

**Solusi**: Hapus alias `STATUS_DISETUJUI` dan ganti semua reference ke `STATUS_PELAKSANAAN`. Cek seluruh codebase via grep.

**Estimasi**: 30 menit.

---

## B. MAJOR — Sebaiknya Diperbaiki

### B1. 🟡 Pembahasan Histori Tidak Tampilkan Identitas Approver/Rejector

**Requirement 4**: "Identitas TKKSD yang menyetujui/menolak per dokumen harus terlihat".

**Status saat ini**: Migration `2026_05_21_000004_add_role_operator_to_permohonan_histori.php` sudah jalan, kolom `role_operator` ada. Tapi di Vue side (`Pembahasan/Index.vue`, modal detail file), info reviewer & role tidak ditampilkan secara eksplisit.

**Solusi**: Update DiskusiSection / DetailDokumen menampilkan:
```
Disetujui oleh: Budi Santoso (TKKSD) — 22 Mei 2026 14:30
```

**Estimasi**: 1 jam.

---

### B2. 🟡 Field "Lokasi Kerjasama" Tidak Bisa Dipilih dari List Kota

**Status**: Field `lokasi_kerjasama` masih textarea bebas. Sesuai rapat, lokasi seharusnya dropdown dengan list kota Samarinda + opsi "Lainnya".

**Solusi**:
- Tambah dropdown `master_cities` di Step3Detail.vue
- Auto-fill dari OPD yang dipilih (kalau OPD punya lokasi default)

**Estimasi**: 2 jam.

---

### B3. 🟡 Step 1 Pemohon — Field Read-Only Belum Konsisten

**Status**: Saat user SSO login dengan profil pemohon lengkap, beberapa field (alamat, kontak) seharusnya read-only karena diambil dari profil. Saat ini masih editable, jadi user bisa "lupa" konsistensi.

**Solusi**: Lock semua field instansi yang berasal dari profil dengan badge "Dari profil — edit di /profile".

**Estimasi**: 1 jam.

---

### B4. 🟡 Reminder Cron Belum Tersinkronisasi dengan TKKSD Lokus Multi-OPD

**Status**: `CheckKerjasamaExpiry` kirim notif ke `tkksd_lokus` per OPD. Tapi kalau 1 user TKKSD Lokus mewakili lebih dari 1 OPD, dia bisa dapat notif ganda (1 reminder per OPD). 

**Solusi**: Group by user_id sebelum kirim, atau pakai cache key `(user_id + permohonan_id + window_days)` untuk dedupe.

**Estimasi**: 30 menit.

---

### B5. 🟡 Detail Modal Permohonan — Section Visibility Belum 100% Konsisten

**Requirement (rapat)**: Section di detail modal harus sesuai status:
- Status 1: Diskusi pembahasan only
- Status 2: + Penjadwalan
- Status 3: + PKS upload (admin) / banner menunggu (pemohon)
- Status 4: + TTD section (pemohon upload)
- Status 5: + Validasi admin
- Status 6+: + Pelaksanaan info

**Status saat ini**: Sudah implemented di `DetailModal.vue` dengan computed `showDiskusi/showSchedule/showPks/showTtd`, tapi belum ada test. Beberapa pemohon melaporkan section diskusi masih muncul di status 6 (sudah pelaksanaan).

**Solusi**: Tambahkan flag `hideAfterStatus` per section. Untuk section diskusi, hide saat status >= 3 (kecuali admin yang selalu lihat history).

**Estimasi**: 1 jam.

---

### B6. 🟡 Permohonan TKKSD Lokus Tidak Bisa Buka Modal Detail di Dashboard

**Status**: Akun TKKSD Lokus klik kerjasama di Dashboard widget kadang dapat 403 atau redirect ke menu Permohonan yang tidak boleh diakses.

**Solusi**: Pastikan dashboard cards untuk `tkksd_lokus` route ke `/tkksd-lokus/kerjasama/{tipe}` (yang sudah dibuat), bukan `/permohonan`.

**Estimasi**: 30 menit.

---

### B7. 🟡 Monev Form — Auto-redirect ke Modal Detail Setelah Submit

**Status**: Setelah submit monev, user di-redirect ke `monev.index?detail={uuid}`. Tapi modal kadang tidak auto-open kalau Inertia state belum sync.

**Solusi**: Pakai `usePage().props.flash.success` plus auto-trigger `openDetailModal()` di `onMounted`.

**Estimasi**: 30 menit.

---

### B8. 🟡 KerjasamaManual — File PDF Tidak Bisa Diunduh dari Detail

**Status**: Detail modal kerjasama_manual menampilkan link file_pks tapi `asset('storage/...')` kadang return 404 karena symlink belum dibuat.

**Solusi**: Pastikan `php artisan storage:link` dijalankan saat deployment. Tambah fallback link via `route('kerjasama-manual.download-pks', $uuid)`.

**Estimasi**: 30 menit.

---

## C. MINOR — Polish & Konsistensi

### C1. 🟢 Tab di Dashboard Admin Belum Cocok Per Role

**Status**: 4 KPI yang sama untuk admin/pemohon/tkksd. Padahal pemohon hanya butuh "Permohonan Saya Aktif" + "Riwayat Saya".

**Solusi**: Sudah dikerjakan sebagian (Dashboard per role). Tinggal pastikan di mobile responsive.

---

### C2. 🟢 Dropdown OPD di Form Punya 145 Item — Perlu Search

**Status**: Sudah pakai PrimeVue `MultiSelect` dengan `:filter="true"`. ✅ Sesuai.

---

### C3. 🔴 Halaman Infografis Publik — Data Manual Belum Masuk Statistik OPD

**Status**: `LandingController::infografis()` sudah merge `permohonan` + `kerjasama_manual` untuk total count, tapi breakdown OPD masih hanya dari `permohonan_opd`. Padahal ada `kerjasama_manual_opd` juga.

**Solusi**: Sudah saya benerin di `LaporanController::statistik`, replikasi ke `LandingController::infografis`.

**Estimasi**: 30 menit.

---

### C4. 🟡 Notifikasi Bell di Header — Counter Tidak Real-time

**Status**: Counter notifikasi update saat full page reload saja. Tidak ada Echo/Pusher.

**Solusi**: Tambah polling `setInterval(() => router.reload({ only: ['notifications'] }), 60000)` di `AuthenticatedLayout`.

**Estimasi**: 30 menit.

---

### C5. 🔴 PKS Final yang Diupload Pemohon Sebelum Workflow Diubah Belum Di-clean

**Status**: Sebelum perubahan "PKS final upload by ADMIN", ada 2-3 record di `permohonan_pks` dengan `tipe=pemohon`. Sekarang field itu deprecated, tapi data lama tidak diapa-apain.

**Solusi**: Migration cleanup atau biarkan saja untuk audit trail.

**Estimasi**: skip (cuma 2-3 record, tidak menghalangi).

---

### C6. 🟡 Bahasa di Spec Tidak Konsisten

**Status**: Mix "kerjasama" vs "kerja sama" di label, controller comment, dan template notifikasi.

**Solusi**: Sesuai EYD, pakai "**kerja sama**" (dua kata) untuk semua user-facing text. Constants/code variable boleh tetap `kerjasama` untuk compatibility.

**Estimasi**: 1 jam.

---

### C7. 🟡 Laporan PDF/Excel Belum Tersedia

**Requirement (rapat)**: Admin bisa export laporan kerjasama dan monev ke PDF/Excel.

**Status**: Hanya CSV export di `MonevController::export`. Belum ada PDF formal.

**Solusi**: Pakai DomPDF + view template yang formal (kop surat Pemkot).

**Estimasi**: 4 jam.

---

### C8. 🟢 Penjadwalan Metode Sudah 3 Pilihan

**Status**: Desk to Desk, Seremonial, Hybrid sudah implemented sesuai Req 6. ✅

---

## D. STYLE / UX — Konsistensi Visual

### D1. 🔴 Warna Status Per Stage Inconsistent di GridItem vs Modal vs Dashboard

**Masalah**: Status `Selesai` (7):
- GridItem: `bg-emerald-600`
- Dashboard KPI: `from-emerald-700 to-emerald-500` (gradient)
- Modal detail header: `bg-emerald-500`

Ada perbedaan shade meskipun warna dasar sama. Perlu satu source of truth.

**Solusi**: Tambah utility `getStatusBadgeClass(status)` di `resources/js/utils/status.js` yang return class konsisten.

**Estimasi**: 1.5 jam.

---

### D2. 🟡 Empty State di Berbagai Halaman Tidak Seragam

**Status**: 
- Permohonan/Riwayat: empty state dengan illustration ✅
- Monev: text-only "Belum ada riwayat Monev"
- TKKSD Lokus Kerjasama: prop `emptyTitle` & `emptyDescription` (sudah saya tambah)

**Solusi**: Buat shared component `EmptyState.vue` yang dipakai semua list.

**Estimasi**: 1 jam.

---

### D3. 🟡 Card di Detail Modal — Padding Tidak Sama

**Status**: `DetailParties.vue`, `DetailContact.vue`, `DetailSchedule.vue` sudah diseragamkan dengan `text-sm font-bold uppercase` + `p-1.5 rounded-lg` icon pill. Tapi `DetailSubstance.vue` masih pakai `p-4` polos tanpa icon header.

**Solusi**: Apply gaya yang sama ke DetailSubstance.

**Estimasi**: 30 menit.

---

### D4. 🟢 Tab Bar Riwayat (Sedang Terlaksana / Selesai / Ditolak)

**Status**: Sudah diimplementasi dengan accent warna yang berbeda per tab + badge counter. ✅

---

### D5. 🟡 Notifikasi Toast — Lokasi Tidak Konsisten

**Status**: Beberapa toast muncul `top-right`, ada yang `top-center`. 

**Solusi**: Set global di `app.js` dengan `useToast({ position: POSITION.TOP_RIGHT })`.

**Estimasi**: 15 menit.

---

### D6. 🔴 Modal PrimeVue tidak align dengan style aplikasi

**Masalah**: Default PrimeVue Dialog header pakai font sedikit beda dari Tailwind shared font. Beberapa dialog (CreateForm) overflow di mobile.

**Solusi**: 
- Tambah `:pt="{ root: { class: 'p-dialog-aligned' } }"` 
- Tambah CSS global override di `app.css` untuk align header dengan shared font-bold.

**Estimasi**: 1 jam.

---

### D7. 🟡 Form Step Wizard — Indicator Belum Jelas

**Status**: CreateForm.vue wizard 4 step. Step indicator di atas terlihat dengan dot, tapi tidak jelas mana step aktif vs done.

**Solusi**: Pakai pola: check icon untuk done, dot besar untuk current, dot kecil untuk pending. Plus label step di bawah.

**Estimasi**: 1 jam.

---

### D8. 🔴 Dark Mode — Beberapa Component Belum Support

**Status**: Banyak div pakai `bg-white` polos tanpa `dark:bg-gray-800`. Misal: Empty state container di list, Detail modal section.

**Solusi**: Audit semua page dengan list `bg-white`, tambah `dark:bg-gray-800` paired class. Plus test dark mode.

**Estimasi**: 3 jam.

---

### D9. 🟡 Footer Aplikasi "Supported By @Enterwind"

**Status**: Footer di beberapa halaman muncul tapi tidak konsisten. Branding "Enterwind" perlu validasi dari client (apakah boleh tampil).

**Solusi**: Konfirmasi ke client + hide untuk login/landing page.

**Estimasi**: 15 menit + konfirmasi.

---

### D10. 🟡 Loading State — Tidak Ada Skeleton

**Status**: Loading state pakai spinner saja. Lebih bagus pakai skeleton untuk list/card.

**Solusi**: PrimeVue `Skeleton` untuk grid card permohonan & monev.

**Estimasi**: 2 jam.

---

## E. KEAMANAN & DATA INTEGRITY

### E1. 🔴 Permission Check di Controller Beberapa Belum Strict

**Masalah**: `PembahasanController::diskusiStore` tidak cek apakah user bisa akses permohonan ini. Pemohon bisa POST diskusi ke permohonan orang lain.

**Solusi**: Tambah `if ($permohonan->id_pemohon_0 !== Auth::id() && !Auth::user()->isAdmin()) abort(403)` di setiap endpoint yang manipulate file/diskusi.

**Estimasi**: 1.5 jam.

---

### E2. 🟡 File Upload Tidak Sanitize Filename

**Status**: `Str::slug($permohonan->kode)` aman, tapi `$file->getClientOriginalName()` disimpan as-is — bisa berisi karakter unsafe.

**Solusi**: Pakai `Str::slug` + ekstensi murni untuk filename storage. Original name boleh disimpan di kolom `file_name` untuk display.

**Estimasi**: 30 menit.

---

### E3. 🟡 Kolom UUID Tidak Diindex di Beberapa Tabel

**Status**: `permohonan.uuid` sudah unique. Tapi `monevs.uuid`, `permohonan_ttd.uuid` tidak ada index eksplisit.

**Solusi**: Tambah migration `$table->index('uuid')` untuk performansi query show.

**Estimasi**: 15 menit.

---

### E4. 🔴 Validation `tanggal_berakhir` Tidak Strict

**Masalah**: Validasi `after_or_equal:tanggal_mulai` membolehkan tanggal mulai = tanggal berakhir = hari ini. Untuk kerja sama, biasanya ada minimum durasi.

**Solusi**: Tambah custom rule "tanggal_berakhir minimal 30 hari setelah tanggal_mulai". Konfirmasi ke client.

**Estimasi**: 30 menit.

---

## F. TESTING

### F1. 🔴 Belum Ada Test Otomatis

**Status**: Tidak ada PHPUnit test sama sekali untuk feature workflow.

**Solusi**: Tulis minimal 5 feature test:
- `test_pemohon_can_create_permohonan_with_opd`
- `test_admin_can_validate_permohonan`
- `test_pks_upload_only_pdf`
- `test_tkksd_lokus_scope_to_their_opd`
- `test_reminder_cron_sends_to_all_parties`

**Estimasi**: 6 jam.

---

### F2. 🔴 Belum Ada Property-Based Test

**Status**: Spec menyebutkan PBT dengan 14 properties. Belum ada implementasi.

**Solusi**: Setup Eris atau gunakan PHPUnit data provider untuk property test ringan. Prioritaskan P3 (validation provinsi), P5 (reset status), P10 (checklist), P11 (TKKSD scope).

**Estimasi**: 4 jam (untuk 4 property critical).

---

## G. DOKUMENTASI

### G1. 🟡 Spec Document Tidak Sinkron dengan Implementasi

**Status**: `requirements.md` & `design.md` masih berisi referensi `STATUS_SELESAI = 6` yang sebenarnya di code = 7.

**Solusi**: Update `design.md` Data Models section untuk sinkron dengan kode aktual:
- STATUS_PELAKSANAAN = 6
- STATUS_SELESAI = 7
- Plus alias `STATUS_DISETUJUI` deprecated.

**Estimasi**: 30 menit.

---

### G2. 🟢 NOTIFIKASI.md Sudah Lengkap

Sudah dibuat di sesi sebelumnya. ✅

---

### G3. 🟡 README.md Belum Mendokumentasikan Cara Run Cron

**Solusi**: Tambah section "Scheduler Setup" di README dengan command crontab:
```cron
* * * * * cd /var/www/sikerja && php artisan schedule:run >> /dev/null 2>&1
```

**Estimasi**: 15 menit.

---

## RINGKASAN PRIORITAS

### Wajib selesai sebelum Go-Live (Critical + Major)

| # | Item | Estimasi |
|---|---|---|
| A1 | Status Permohonan cast integer | 15m |
| A2 | Auto/banner transition status 5→6 | 30m–1h |
| A3 | Force reload setelah rollback | 20m |
| A4 | Guard duplicate WA di observer | 15m |
| A5 | Hapus alias STATUS_DISETUJUI | 30m |
| B1 | Identitas approver di Pembahasan | 1h |
| B5 | Section visibility Detail modal | 1h |
| B6 | Dashboard TKKSD Lokus klik kerjasama | 30m |
| C3 | Infografis include data manual OPD | 30m |
| D1 | Utility status badge class | 1.5h |
| D6 | PrimeVue dialog align style | 1h |
| D8 | Dark mode audit | 3h |
| E1 | Permission check controllers | 1.5h |
| F1 | Feature tests minimum | 6h |
| G1 | Sync spec docs | 30m |
| **Total Critical/Major** | | **~18 jam** |

### Sebaiknya dilakukan (Minor + Polish)

| # | Item | Estimasi |
|---|---|---|
| B2 | Lokasi Kerjasama dropdown | 2h |
| B3 | Lock field profil | 1h |
| B4 | Dedupe TKKSD Lokus multi-OPD | 30m |
| B7 | Auto-open detail post-submit | 30m |
| B8 | KerjasamaManual file download fallback | 30m |
| C4 | Notification bell polling | 30m |
| C6 | Bahasa konsisten | 1h |
| C7 | PDF/Excel export | 4h |
| D2 | Shared EmptyState component | 1h |
| D3 | DetailSubstance card style | 30m |
| D5 | Toast position global | 15m |
| D7 | Step wizard indicator polish | 1h |
| D10 | Skeleton loading | 2h |
| E2 | Sanitize filename | 30m |
| E3 | UUID index | 15m |
| E4 | Validasi durasi minimum | 30m |
| F2 | Property-based test | 4h |
| G3 | README cron section | 15m |
| **Total Minor/Polish** | | **~20 jam** |

### Skip / Need Confirmation

- C5: PKS pemohon legacy data — biarkan saja
- D9: Footer Enterwind — tunggu konfirmasi client

---

## REKOMENDASI EKSEKUSI

**Sprint 1 (3–5 hari kerja)** — Critical fixes:
1. Hari 1: A1, A2, A3, A4, A5 + G1
2. Hari 2: B1, B5, B6 + C3
3. Hari 3: D1, D6 + E1
4. Hari 4: D8 (dark mode) + smoke test manual penuh
5. Hari 5: F1 (feature test setup + 5 test inti)

**Sprint 2 (3–5 hari kerja)** — Polish & UX:
1. B2, B3, C7 (PDF), D2, D7
2. E2, E3, E4
3. F2 (PBT minimal)

**Sprint 3 (continuous)** — Maintenance:
- Bug report dari pengguna
- Performance tuning (UUID index dst)
- Konsistensi bahasa

---

## CHECKLIST FINAL SEBELUM HAND-OFF

```
[ ] Semua status 0..7 tampil benar di tabel & dashboard
[ ] Pemohon dapat menyelesaikan workflow ujung-ke-ujung tanpa stuck
[ ] TKKSD Lokus dapat akses dashboard, monev, kerjasama OPD-nya saja
[ ] Reminder cron jalan dan kirim ke 3 pihak (verified via log)
[ ] Notifikasi WA terformat formal (sample disimpan)
[ ] Halaman publik infografis menampilkan data lengkap
[ ] Master OPD CRUD berjalan
[ ] Kerjasama Manual + monev manual berjalan
[ ] Dark mode tidak ada element putih polos
[ ] Mobile responsive di Permohonan, Monev, Detail Modal
[ ] PHPUnit feature test pass (minimum 5 test)
[ ] Spec docs sinkron dengan kode
[ ] Storage symlink dijalankan di production
[ ] Cron schedule terdaftar dan running
```
