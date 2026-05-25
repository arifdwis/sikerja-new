# Dokumentasi Notifikasi SIKERJA v2

Dokumen ini menjelaskan **semua jenis notifikasi** yang dikirim oleh sistem SIKERJA, beserta **penerima**, **kapan dipicu**, dan **contoh isi pesan**.

## 1. Arsitektur Notifikasi

Sistem mengirim notifikasi melalui dua kanal:

| Kanal | Tujuan | Storage |
|---|---|---|
| **Notifikasi Sistem** (in-app) | Inbox di aplikasi SIKERJA, badge bell di header | Tabel `notifikasi` |
| **WhatsApp** | Nomor pemohon, OPD, atau grup admin | Gateway WA (Fonnte/sejenis) |

Setiap event yang sudah disepakati selalu mengirim **dua-duanya** secara paralel — supaya pengguna yang tidak buka aplikasi tetap dapat info via WA, dan sebaliknya.

### File Pusat Format Pesan

Semua format pesan terkonsentrasi di **`app/Services/NotificationTemplate.php`**. Untuk mengubah wording, footer, atau struktur, cukup edit file itu — tidak perlu mengubah controller mana pun.

### Tiga Jenis Bentuk Pesan

1. **Formal ke Pemohon** — Surat resmi salam "Yth. Bpk/Ibu", header tahapan, identifikasi kerja sama, penjelasan, langkah berikutnya, footer "Hormat kami, Tim Kerja Sama Daerah, Pemerintah Kota Samarinda".
2. **Formal ke OPD / TKKSD Lokus** — Salam "Yth. {Nama OPD}", konteks kerja sama yang melibatkan instansi, ajakan koordinasi.
3. **Internal Group** — Format ringkas untuk grup admin / TKKSD pusat dengan prefix `[NOTIFIKASI INTERNAL]`, tabel ringkas Perihal/Nomor/Mitra, action item yang harus dilakukan.

### Aturan Pengiriman (Dev Mode)

Di mode development, untuk menghindari spam ke nomor admin asli:

| Setting | Default | Dampak |
|---|---|---|
| `WA_NOTIFY_ADMIN_ENABLED` | `false` | WA ke admin individu di-skip |
| `WA_GROUP_ENABLED` | `false` | WA ke grup di-redirect ke nomor dev `085121942579` |
| `WHATSAPP_ENABLED` | `false` | Semua WA dimatikan total (untuk testing isolasi) |

Notifikasi sistem (in-app) **selalu aktif** terlepas dari setting WA di atas.

---

## 2. Daftar Notifikasi per Tahapan Workflow

Workflow status SIKERJA: `0 Permohonan Baru → 1 Pembahasan → 2 Penjadwalan → 3 Upload PKS Admin → 4 Menunggu TTD → 5 Pasca TTD → 6 Pelaksanaan → 7 Selesai · 9 Ditolak`.

### A. Tahap 0 → 1: Permohonan Diterima

**Trigger**: Admin klik "Validasi" pada permohonan baru.

| # | Penerima | Kanal | Template | Penjelasan |
|---|---|---|---|---|
| A1 | Pemohon | Sistem + WA | `permohonanDiterima` | Konfirmasi formal bahwa permohonan diterima dan masuk pembahasan TKKSD. |
| A2 | Grup Admin | WA Internal | `internalGroup` (status update) | Rekap update status. |

**Contoh isi WA ke Pemohon:**
```
*PERMOHONAN DITERIMA — SIKERJA*

Yth. Bpk/Ibu *Budi Santoso*,

Dengan hormat, kami sampaikan informasi berikut terkait permohonan kerja sama Anda.

Perihal      : *Penyediaan Sarana TPST3R*
Nomor        : REQ/2026/0001
Mitra        : PT. Cipta Serra Utama
Tahapan      : *Permohonan Diterima*

Permohonan kerja sama Anda telah diterima dan saat ini memasuki tahap
pembahasan oleh Tim Koordinasi Kerja Sama Daerah (TKKSD). Mohon menunggu
hasil pembahasan untuk tahap berikutnya.

Mohon kiranya Bapak/Ibu dapat menindaklanjuti melalui aplikasi SIKERJA pada
laman https://sikerja-v2.samarindakota.go.id.

Atas perhatian dan kerja sama Bpk/Ibu, kami sampaikan terima kasih.

Hormat kami,
Tim Kerja Sama Daerah
Pemerintah Kota Samarinda
```

---

### B. Permohonan Ditolak / Direvisi

**Trigger**: Admin tolak permohonan dengan alasan, atau dokumen TTD ditolak validasi.

| # | Penerima | Kanal | Template |
|---|---|---|---|
| B1 | Pemohon | Sistem + WA | `permohonanDitolak` |

**Contoh isi WA ke Pemohon:**
```
*PERMOHONAN MEMERLUKAN REVISI — SIKERJA*

Yth. Bpk/Ibu *Budi Santoso*,

...

Tahapan      : *Permohonan Memerlukan Revisi*

Permohonan kerja sama Anda memerlukan revisi sebelum dapat dilanjutkan
ke tahap pembahasan. Silakan lakukan perbaikan sesuai catatan di bawah,
kemudian ajukan kembali melalui aplikasi.

*Catatan:*
Lampiran KAK belum sesuai format. Mohon dilengkapi dengan tanda tangan
pimpinan instansi.
```

---

### C. Berkas Awal Diunggah Pemohon (Status 0/1)

**Trigger**: Pemohon upload berkas pendukung permohonan.

| # | Penerima | Kanal | Template |
|---|---|---|---|
| C1 | Grup Admin | WA Internal | `berkasAwalDiupload` |

**Contoh:**
```
*[NOTIFIKASI INTERNAL] Berkas Permohonan Diunggah*
*SIKERJA — Pemerintah Kota Samarinda*

Perihal : *Penyediaan Sarana TPST3R*
Nomor   : REQ/2026/0001
Mitra   : PT. Cipta Serra Utama
Oleh    : Budi Santoso
Waktu   : 22 May 2026 21:25

Pemohon telah mengunggah berkas pendukung permohonan kerja sama. Berkas
siap diperiksa pada tahap pembahasan TKKSD.

Mohon segera ditindaklanjuti pada dashboard SIKERJA.

_Sistem Kerja Sama Daerah Samarinda_
```

---

### D. Tahap 1 → 2: Pembahasan Selesai

**Trigger**: Admin klik "Selesai Pembahasan" → pemohon dapat ajukan jadwal.

| # | Penerima | Kanal | Template |
|---|---|---|---|
| D1 | Pemohon | Sistem + WA | `pembahasanSelesai` |
| D2 | Grup Admin | WA Internal | `internalGroup` (status update) |

**Contoh isi WA ke Pemohon:**
```
*PEMBAHASAN SELESAI — MOHON AJUKAN JADWAL PENANDATANGANAN — SIKERJA*

Yth. Bpk/Ibu *Budi Santoso*,
...
Pembahasan oleh TKKSD telah selesai. Selanjutnya, Bpk/Ibu dipersilakan
untuk mengajukan jadwal penandatanganan kerja sama melalui menu
Penjadwalan pada aplikasi SIKERJA.
```

---

### E. Pengajuan Jadwal Penandatanganan

**Trigger**: Pemohon submit jadwal usulan.

| # | Penerima | Kanal | Template |
|---|---|---|---|
| E1 | Admin (per user, sistem) | Sistem | "Pengajuan Jadwal Penandatanganan" |
| E2 | Grup Admin | WA Internal | `jadwalDiajukan` |

**Contoh WA Grup:**
```
*[NOTIFIKASI INTERNAL] Pengajuan Jadwal Penandatanganan*
*SIKERJA — Pemerintah Kota Samarinda*

Perihal : *Penyediaan Sarana TPST3R*
Nomor   : REQ/2026/0001
Mitra   : PT. Cipta Serra Utama
Oleh    : Budi Santoso
Waktu   : 22 May 2026 21:25

Pemohon telah mengajukan jadwal penandatanganan kerja sama pada
*Senin, 02 Juni 2026 pukul 10:00 WITA*. Mohon admin memeriksa dan
memberikan persetujuan jadwal pada dashboard SIKERJA.
```

---

### F. Jadwal Disetujui / Ditolak

**Trigger**: Admin approve atau reject jadwal yang diajukan pemohon.

| # | Event | Penerima | Kanal | Template |
|---|---|---|---|---|
| F1 | Disetujui | Pemohon | Sistem + WA | `jadwalDisetujui` |
| F2 | Ditolak | Pemohon | Sistem + WA | `statusUpdate` ("Jadwal Penandatanganan Belum Disetujui") |

**Contoh disetujui:**
```
*JADWAL PENANDATANGANAN DISETUJUI — SIKERJA*

Yth. Bpk/Ibu *Budi Santoso*,
...
Jadwal penandatanganan kerja sama Bpk/Ibu telah disetujui dan ditetapkan
pada *Senin, 02 Juni 2026 pukul 10:00 WITA*. Mohon mempersiapkan seluruh
dokumen yang akan ditandatangani sesuai ketentuan. Dokumen PKS final akan
disiapkan oleh admin.
```

---

### G. Tahap 3 → 4: PKS Final Disiapkan Admin

**Trigger**: Admin upload PKS final → sistem auto-transition status ke MENUNGGU_TANDATANGAN.

| # | Penerima | Kanal | Template |
|---|---|---|---|
| G1 | Pemohon | Sistem + WA | `pksFinalSiap` |

**Contoh:**
```
*PKS FINAL TELAH DISIAPKAN — SIKERJA*
...
PKS final telah disiapkan oleh admin dan siap untuk ditandatangani.
Bpk/Ibu mohon mempersiapkan diri pada hari penandatanganan sesuai
jadwal yang telah ditentukan.
```

---

### H. Tahap 4 → 5: Pemohon Upload Dokumen Tertandatangani

**Trigger**: Pemohon upload PDF dokumen tertandatangani (paraf, materai, stempel).

| # | Penerima | Kanal | Template |
|---|---|---|---|
| H1 | Grup Admin | WA Internal | `dokumenTtdDiterima` |

**Contoh:**
```
*[NOTIFIKASI INTERNAL] Dokumen Tertandatangani Diterima*
*SIKERJA — Pemerintah Kota Samarinda*

...
Pemohon telah mengunggah dokumen tertandatangani. Mohon admin segera
melakukan validasi dokumen pada dashboard SIKERJA.
```

---

### I. Validasi TTD oleh Admin

**Trigger**: Admin klik "Valid" atau "Tidak Valid" pada dokumen tertandatangani.

| # | Hasil | Penerima | Kanal | Template |
|---|---|---|---|---|
| I1 | Valid | Pemohon | Sistem + WA | `statusUpdate` ("Dokumen Tertandatangani Tervalidasi") |
| I2 | Tidak Valid | Pemohon | Sistem + WA | `ttdDitolak` (dengan catatan alasan) |

**Contoh ditolak:**
```
*DOKUMEN TERTANDATANGANI MEMERLUKAN PERBAIKAN — SIKERJA*

Yth. Bpk/Ibu *Budi Santoso*,
...
Dokumen tertandatangani yang Bpk/Ibu unggah belum dapat divalidasi.
Mohon Bpk/Ibu mengunggah ulang dokumen yang telah diperbaiki sesuai
catatan di bawah.

*Catatan:*
Stempel basah pada halaman 5 belum lengkap.
```

---

### J. Tahap 5 → 6: Pelaksanaan Kerja Sama Dimulai

**Trigger**: Admin approve PKS final & kerja sama mulai berjalan.

| # | Penerima | Kanal | Template |
|---|---|---|---|
| J1 | Pemohon | Sistem + WA | `pelaksanaanDimulai` |
| J2 | Grup Admin | WA Internal | `internalGroup` (status update) |

**Contoh:**
```
*KERJA SAMA MEMASUKI TAHAP PELAKSANAAN — SIKERJA*
...
Seluruh dokumen kerja sama telah divalidasi dan disetujui. Mulai saat ini
kerja sama Bpk/Ibu memasuki tahap pelaksanaan. Mohon Bpk/Ibu menjalankan
kerja sama sesuai ruang lingkup yang disepakati. Tim akan mendampingi
monitoring & evaluasi sesuai jadwal.
```

---

### K. Perubahan Jangka Waktu Pasca-Pembahasan

**Trigger**: Admin edit `tanggal_mulai`, `tanggal_berakhir`, atau `jangka_waktu` setelah pembahasan selesai (status ≥ 2).

| # | Penerima | Kanal | Template |
|---|---|---|---|
| K1 | Pemohon | Sistem + WA | `statusUpdate` ("Perubahan Jangka Waktu") + detail perubahan |

---

## 3. Notifikasi Monitoring & Evaluasi (Monev)

### M1. Form Monev Disubmit

**Trigger**: Pemohon, TKKSD Lokus, atau Admin submit form Monev. Tiga pihak ini submit monev secara **paralel**, masing-masing notif ke Admin saja (mereka tidak saling notif).

| # | Penerima | Kanal | Template |
|---|---|---|---|
| M1.1 | Admin (per user, sistem) | Sistem | "Monev Diterima dari {Role}" |
| M1.2 | Admin individu | WA | `monevDariPemohonKeAdmin` |

**Contoh WA ke Admin:**
```
*[NOTIFIKASI INTERNAL] Monev Diterima*
*SIKERJA — Pemerintah Kota Samarinda*

Perihal : *Penyediaan Sarana TPST3R*
Nomor   : REQ/2026/0001
Mitra   : PT. Cipta Serra Utama
Oleh    : Budi Santoso
Waktu   : 22 May 2026 21:25

Telah diterima form Monitoring & Evaluasi *MON/2026/0006* dari *Pemohon*
(Budi Santoso). Mohon admin meninjau hasil monev pada dashboard untuk
konsolidasi laporan.
```

---

### M2. Konfirmasi ke Pemohon Setelah Monev Submit

**Trigger**: Pemohon (operator yang mengajukan kerja sama) submit monev.

| # | Penerima | Kanal | Template |
|---|---|---|---|
| M2.1 | Pemohon | Sistem + WA | `statusUpdate` ("Form Monev Terkirim") |

---

### M3. Permintaan Review ke TKKSD Lokus

**Trigger**: Pemohon submit monev → notif minta review ke TKKSD Lokus dari OPD yang terlibat (multi-OPD didukung).

| # | Penerima | Kanal | Template |
|---|---|---|---|
| M3.1 | TKKSD Lokus per OPD | Sistem + WA | `reviewRequestKeTkksdLokus` |

**Contoh:**
```
*PERMOHONAN REVIEW HASIL MONITORING & EVALUASI — SIKERJA*

Yth. Dinas Komunikasi & Informatika,

Dengan hormat, kami sampaikan informasi terkait kerja sama yang
melibatkan instansi Bpk/Ibu.

Perihal      : *Penyediaan Sarana TPST3R*
Nomor        : REQ/2026/0001
Mitra        : PT. Cipta Serra Utama
Tahapan      : *Permohonan Review Hasil Monitoring & Evaluasi*

Form Monev *MON/2026/0006* untuk kerja sama yang melibatkan instansi
Bpk/Ibu memerlukan persetujuan TKKSD Lokus. Mohon Bpk/Ibu meninjau dan
memberikan persetujuan melalui aplikasi SIKERJA.
```

---

### M4. Hasil Monev Dikirim ke Pemohon

**Trigger**: Admin klik "Kirim Notifikasi" di modal grouped Monev (admin only).

| # | Penerima | Kanal | Template |
|---|---|---|---|
| M4.1 | Pemohon | Sistem + WA | `monevHasilKePemohon` |

**Contoh:**
```
*HASIL MONITORING & EVALUASI — SIKERJA*

...
Hasil Monitoring & Evaluasi untuk kerja sama Bpk/Ibu telah tersedia.
Status tindak lanjut: *Layak dilanjutkan*. Detail lengkap dapat diakses
melalui menu Monev pada aplikasi SIKERJA.
```

Status tindak lanjut diturunkan dari `rekomendasi_lanjutan`:
- `Dilanjutkan` → "Layak dilanjutkan"
- `Diperluas` → "Layak diperluas"
- `Dihentikan` → "Tidak direkomendasikan dilanjutkan"

---

## 4. Reminder Otomatis (Cron)

Cron `kerjasama:check-expiry` jalan **setiap hari pukul 07:00 WITA** (`routes/console.php`). Memantau kerja sama yang akan / sudah berakhir dengan tingkatan reminder:

| Hari sebelum berakhir | Label | Level |
|---|---|---|
| 90 | 3 bulan | Info |
| 60 | 2 bulan | Info |
| 30 | 1 bulan | Warning |
| 7 | 1 minggu | Warning |
| 0 | hari ini | Critical |

Toleransi ± 1 hari (kalau scheduler agak telat tetap ter-cover). Anti-duplikat: notifikasi yang sama (user + permohonan + title) tidak dikirim ulang dalam 24 jam.

### R1. Reminder ke Pemohon

| Hari | Penerima | Kanal | Template |
|---|---|---|---|
| 90 / 60 / 30 / 7 | Pemohon | Sistem + WA | `reminderMonevPemohon` (sisa hari) |
| 0 / lewat | Pemohon | Sistem + WA | `reminderMonevPemohon` (mode "telah berakhir") |

**Contoh 90 hari:**
```
*KERJA SAMA AKAN SEGERA BERAKHIR — SIKERJA*

Yth. Bpk/Ibu *Budi Santoso*,
...
Kerja sama Bpk/Ibu akan berakhir dalam *90 hari*. Mohon Bpk/Ibu
mempersiapkan pengisian form Monitoring & Evaluasi (Monev) yang menjadi
laporan akhir kerja sama.
```

**Contoh hari berakhir:**
```
*KERJA SAMA TELAH BERAKHIR — MOHON LAKUKAN MONEV — SIKERJA*

Yth. Bpk/Ibu *Budi Santoso*,
...
Kerja sama Bpk/Ibu telah berakhir. Sesuai ketentuan, mohon Bpk/Ibu
segera mengisi form Monitoring & Evaluasi (Monev) sebagai laporan
akhir kerja sama melalui menu Monev pada aplikasi SIKERJA.
```

---

### R2. Reminder ke TKKSD Lokus (OPD Terkait)

| Hari | Penerima | Kanal | Template |
|---|---|---|---|
| 90 / 60 / 30 / 7 / 0 | TKKSD Lokus per OPD | Sistem + WA | `formalToOpd` |

**Contoh:**
```
*KERJA SAMA AKAN BERAKHIR DALAM 1 BULAN — SIKERJA*

Yth. Dinas Komunikasi & Informatika,

Dengan hormat, kami sampaikan informasi terkait kerja sama yang
melibatkan instansi Bpk/Ibu.

...
Kerja sama yang melibatkan instansi Bpk/Ibu akan berakhir pada
05 Juli 2026 (1 bulan lagi). Mohon Bpk/Ibu mendampingi proses Monev
kerja sama tersebut.
```

---

### R3. Reminder Internal Grup Admin

| Hari | Penerima | Kanal | Template |
|---|---|---|---|
| 90 / 60 / 30 / 7 / 0 | Admin (sistem per user + WA grup) | Sistem + WA Grup | `reminderMonevAdmin` |

**Contoh:**
```
*[NOTIFIKASI INTERNAL] Kerja Sama Akan Berakhir*
*SIKERJA — Pemerintah Kota Samarinda*

Perihal : *Penyediaan Sarana TPST3R*
Nomor   : REQ/2026/0001
Mitra   : PT. Cipta Serra Utama
Waktu   : 22 May 2026 21:25

Kerja sama akan berakhir dalam *90 hari*. Mohon admin memastikan pemohon
dan TKKSD Lokus siap mengisi Monev sesuai jadwal.
```

---

### R4. Reminder Kerjasama Sistem Lama (Manual)

Tabel `kerjasama_manual` (data legacy) juga ikut dipantau dengan template internal yang sama, hanya prefix-nya beda: "Kerja Sama (Sistem Lama) Akan Berakhir...". OPD terkait dan grup admin sama-sama dapat reminder.

---

## 5. Auto-Transition Status

Cron yang sama (`kerjasama:check-expiry`) juga menjalankan **auto-close**:

> Status `Pelaksanaan (6)` → `Selesai (7)` otomatis ketika `tanggal_berakhir <= hari ini`.

Perubahan status ini **memicu** observer `PermohonanObserver` yang **TIDAK** mengirim notifikasi tambahan ke pemohon (untuk hindari spam — pemohon sudah dapat reminder R1 sebelumnya). Hanya histori dicatat.

---

## 6. Tabel Ringkas Per Penerima

### Untuk Pemohon

| Tahapan | Channel | Template |
|---|---|---|
| Permohonan diterima validasi | Sistem + WA | `permohonanDiterima` |
| Permohonan ditolak / direvisi | Sistem + WA | `permohonanDitolak` |
| Pembahasan selesai | Sistem + WA | `pembahasanSelesai` |
| Jadwal disetujui | Sistem + WA | `jadwalDisetujui` |
| Jadwal ditolak | Sistem + WA | `statusUpdate` |
| PKS final disiapkan admin | Sistem + WA | `pksFinalSiap` |
| Dokumen TTD tervalidasi | Sistem + WA | `statusUpdate` |
| Dokumen TTD ditolak | Sistem + WA | `ttdDitolak` |
| Pelaksanaan dimulai | Sistem + WA | `pelaksanaanDimulai` |
| Perubahan jangka waktu | Sistem + WA | `statusUpdate` |
| Form Monev terkirim (konfirmasi) | Sistem + WA | `statusUpdate` |
| Hasil Monev | Sistem + WA | `monevHasilKePemohon` |
| Reminder kerjasama akan/sudah berakhir | Sistem + WA | `reminderMonevPemohon` |

### Untuk TKKSD Lokus (OPD Terkait)

| Tahapan | Channel | Template |
|---|---|---|
| Permintaan review monev | Sistem + WA | `reviewRequestKeTkksdLokus` |
| Reminder kerjasama akan/sudah berakhir | Sistem + WA | `formalToOpd` |

### Untuk Admin (TKKSD Pusat)

| Tahapan | Channel | Template |
|---|---|---|
| Permohonan baru masuk | Sistem (per user) + WA Grup | `permohonanBaruDariPemohon` |
| Berkas awal diunggah | WA Grup | `berkasAwalDiupload` |
| Pengajuan jadwal | Sistem (per user) + WA Grup | `jadwalDiajukan` |
| Dokumen tertandatangani diterima | WA Grup | `dokumenTtdDiterima` |
| Update status (semua transition) | WA Grup | `internalGroup` |
| Monev disubmit (oleh siapa pun) | Sistem (per user) + WA admin | `monevDariPemohonKeAdmin` |
| Reminder kerjasama akan/sudah berakhir | Sistem (per user) + WA Grup | `reminderMonevAdmin` |

---

## 7. Kebijakan & Catatan Tambahan

- **TKKSD Lokus tidak menerima** notifikasi permohonan baru / pembahasan / jadwal / TTD — hanya monev review request dan reminder. Sesuai requirement: "TKKSD Lokus hanya monev dan permintaan monev".
- **Pemohon dan TKKSD Lokus tidak saling kirim** notifikasi monev. Mereka monev secara mandiri-paralel; hanya admin yang melihat hasil agregat ketiga submitter.
- **Anti-spam**: cron reminder cek `created_at >= now() - 24h` untuk title yang sama → tidak duplicate.
- **Footer formal vs internal** sengaja dibedakan agar pesan ke pemohon terasa profesional, sementara pesan grup admin tetap ringkas dan to-the-point.
- **Variabel template** seperti `{Tahapan}`, `{Nama}`, `{Perihal}`, `{Nomor}`, `{Mitra}`, `{Tanggal Jadwal}`, `{Sisa Hari}`, `{Catatan}` di-inject otomatis dari data Permohonan/Monev/Jadwal yang relevan.

---

## 8. Mengubah Format Pesan

1. Buka `app/Services/NotificationTemplate.php`.
2. Edit konstanta footer di bagian atas, atau edit method per-event di bagian "EVENT TEMPLATES".
3. Hot-reload langsung berlaku — tidak perlu migration atau cache clear khusus.
4. Untuk preview, jalankan di tinker:
   ```php
   echo App\Services\NotificationTemplate::permohonanDiterima(
       'Budi Santoso',
       App\Models\Permohonan::first()
   )['wa'];
   ```
