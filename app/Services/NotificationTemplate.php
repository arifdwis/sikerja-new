<?php

namespace App\Services;

use App\Models\Permohonan;

/**
 * Pusat format pesan notifikasi formal untuk SIKERJA.
 *
 * Setiap method mengembalikan array dengan dua kunci:
 *   - 'system' : pesan singkat untuk inbox aplikasi (Notifikasi DB)
 *               dipecah lagi jadi ['title' => ..., 'message' => ...]
 *   - 'wa'     : pesan WhatsApp lengkap dengan format formal Pemerintah Kota Samarinda
 *
 * Format umum WhatsApp:
 *   *JUDUL TAHAPAN — SIKERJA*
 *
 *   Yth. Bpk/Ibu {Nama},
 *
 *   Dengan hormat, kami sampaikan informasi berikut.
 *
 *   Perihal      : ...
 *   Nomor        : ...
 *   Tahapan      : ...
 *   Status       : ...
 *
 *   {Penjelasan tahapan & langkah selanjutnya}
 *
 *   Atas perhatian dan kerja sama Bpk/Ibu, kami sampaikan terima kasih.
 *
 *   Hormat kami,
 *   Tim Kerja Sama Daerah
 *   Pemerintah Kota Samarinda
 *
 * Pesan grup admin / TKKSD Lokus pakai format ringkas tapi tetap formal.
 */
class NotificationTemplate
{
    private const FOOTER_FORMAL = "Hormat kami,\nTim Kerja Sama Daerah\nPemerintah Kota Samarinda";
    private const FOOTER_INTERNAL = "_Sistem Kerja Sama Daerah Samarinda_";

    // ----------------------------------------------------------------------
    // PRIMITIVE BUILDERS
    // ----------------------------------------------------------------------

    /**
     * Build pesan WA formal untuk pemohon (perorangan) — pakai header + identitas + isi + penutup.
     */
    public static function formalToPemohon(
        string $tahapan,
        string $namaPemohon,
        Permohonan $permohonan,
        string $penjelasan,
        ?string $catatan = null
    ): string {
        $lines = [];
        $lines[] = "*" . strtoupper($tahapan) . " — SIKERJA*";
        $lines[] = "";
        $lines[] = "Yth. Bpk/Ibu *{$namaPemohon}*,";
        $lines[] = "";
        $lines[] = "Dengan hormat, kami sampaikan informasi berikut terkait permohonan kerja sama Anda.";
        $lines[] = "";
        $lines[] = "Perihal      : *" . ($permohonan->label ?? '-') . "*";
        $lines[] = "Nomor        : " . ($permohonan->nomor_permohonan ?? '-');
        $lines[] = "Mitra        : " . ($permohonan->nama_instansi ?? '-');
        $lines[] = "Tahapan      : *{$tahapan}*";
        $lines[] = "";
        $lines[] = $penjelasan;
        if (!empty($catatan)) {
            $lines[] = "";
            $lines[] = "*Catatan:*";
            $lines[] = $catatan;
        }
        $lines[] = "";
        $lines[] = "Mohon kiranya Bapak/Ibu dapat menindaklanjuti melalui aplikasi SIKERJA pada laman " . config('app.url', 'https://sikerja-v2.samarindakota.go.id') . ".";
        $lines[] = "";
        $lines[] = "Atas perhatian dan kerja sama Bpk/Ibu, kami sampaikan terima kasih.";
        $lines[] = "";
        $lines[] = self::FOOTER_FORMAL;

        return implode("\n", $lines);
    }

    /**
     * Build pesan WA singkat untuk grup admin / TKKSD Lokus — informatif tapi rapi.
     */
    public static function internalGroup(
        string $tahapan,
        Permohonan $permohonan,
        string $penjelasan,
        ?string $oleh = null,
        ?string $catatan = null
    ): string {
        $lines = [];
        $lines[] = "*[NOTIFIKASI INTERNAL] {$tahapan}*";
        $lines[] = "*SIKERJA — Pemerintah Kota Samarinda*";
        $lines[] = "";
        $lines[] = "Perihal : *" . ($permohonan->label ?? '-') . "*";
        $lines[] = "Nomor   : " . ($permohonan->nomor_permohonan ?? '-');
        $lines[] = "Mitra   : " . ($permohonan->nama_instansi ?? '-');
        if ($oleh) {
            $lines[] = "Oleh    : {$oleh}";
        }
        $lines[] = "Waktu   : " . now()->format('d M Y H:i');
        $lines[] = "";
        $lines[] = $penjelasan;
        if (!empty($catatan)) {
            $lines[] = "";
            $lines[] = "Catatan : {$catatan}";
        }
        $lines[] = "";
        $lines[] = "Mohon segera ditindaklanjuti pada dashboard SIKERJA.";
        $lines[] = "";
        $lines[] = self::FOOTER_INTERNAL;

        return implode("\n", $lines);
    }

    /**
     * Build pesan WA formal untuk TKKSD Lokus / instansi pendamping (OPD).
     */
    public static function formalToOpd(
        string $tahapan,
        string $namaOpd,
        Permohonan $permohonan,
        string $penjelasan,
        ?string $catatan = null
    ): string {
        $lines = [];
        $lines[] = "*" . strtoupper($tahapan) . " — SIKERJA*";
        $lines[] = "";
        $lines[] = "Yth. " . ($namaOpd ?: 'TKKSD Lokus'). ",";
        $lines[] = "";
        $lines[] = "Dengan hormat, kami sampaikan informasi terkait kerja sama yang melibatkan instansi Bpk/Ibu.";
        $lines[] = "";
        $lines[] = "Perihal      : *" . ($permohonan->label ?? '-') . "*";
        $lines[] = "Nomor        : " . ($permohonan->nomor_permohonan ?? '-');
        $lines[] = "Mitra        : " . ($permohonan->nama_instansi ?? '-');
        $lines[] = "Tahapan      : *{$tahapan}*";
        $lines[] = "";
        $lines[] = $penjelasan;
        if (!empty($catatan)) {
            $lines[] = "";
            $lines[] = "*Catatan:*";
            $lines[] = $catatan;
        }
        $lines[] = "";
        $lines[] = "Mohon kiranya Bpk/Ibu dapat berkoordinasi melalui aplikasi SIKERJA pada laman " . config('app.url', 'https://sikerja-v2.samarindakota.go.id') . ".";
        $lines[] = "";
        $lines[] = "Atas perhatian dan kerja sama Bpk/Ibu, kami sampaikan terima kasih.";
        $lines[] = "";
        $lines[] = self::FOOTER_FORMAL;

        return implode("\n", $lines);
    }

    // ----------------------------------------------------------------------
    // EVENT TEMPLATES — per tahapan workflow
    // ----------------------------------------------------------------------

    /** Tahap 0 → 1: Permohonan baru divalidasi admin. */
    public static function permohonanDiterima(string $namaPemohon, Permohonan $p): array
    {
        $title = 'Permohonan Diterima';
        $msg = "Permohonan kerja sama Anda telah diterima dan saat ini memasuki tahap pembahasan oleh Tim Koordinasi Kerja Sama Daerah (TKKSD). Mohon menunggu hasil pembahasan untuk tahap berikutnya.";

        return [
            'system' => ['title' => $title, 'message' => 'Permohonan kerja sama "' . $p->label . '" telah diterima dan masuk tahap pembahasan TKKSD.'],
            'wa'     => self::formalToPemohon($title, $namaPemohon, $p, $msg),
        ];
    }

    public static function permohonanDitolak(string $namaPemohon, Permohonan $p, string $alasan): array
    {
        $title = 'Permohonan Memerlukan Revisi';
        $msg = "Permohonan kerja sama Anda memerlukan revisi sebelum dapat dilanjutkan ke tahap pembahasan. Silakan lakukan perbaikan sesuai catatan di bawah, kemudian ajukan kembali melalui aplikasi.";

        return [
            'system' => ['title' => $title, 'message' => 'Permohonan "' . $p->label . '" memerlukan revisi. ' . $alasan],
            'wa'     => self::formalToPemohon($title, $namaPemohon, $p, $msg, $alasan),
        ];
    }

    public static function kerjasamaDicabut(string $namaPemohon, Permohonan $p, string $alasan): array
    {
        $title = 'Kerjasama Dicabut';
        $msg = "Kerja sama yang sedang berjalan dinyatakan dicabut dan tidak dapat dilanjutkan. Mohon menghentikan pelaksanaan kegiatan serta berkoordinasi dengan pihak terkait sesuai ketentuan yang berlaku.";

        return [
            'system' => ['title' => $title, 'message' => 'Kerja sama "' . $p->label . '" dicabut. ' . $alasan],
            'wa'     => self::formalToPemohon($title, $namaPemohon, $p, $msg, $alasan),
        ];
    }

    /** Tahap 1 → 2: Pembahasan selesai, pemohon ajukan jadwal. */
    public static function pembahasanSelesai(string $namaPemohon, Permohonan $p): array
    {
        $title = 'Pembahasan Selesai — Mohon Ajukan Jadwal Penandatanganan';
        $msg = "Pembahasan oleh TKKSD telah selesai. Selanjutnya, Bpk/Ibu dipersilakan untuk mengajukan jadwal penandatanganan kerja sama melalui menu Penjadwalan pada aplikasi SIKERJA.";

        return [
            'system' => ['title' => 'Pembahasan Selesai', 'message' => 'Permohonan "' . $p->label . '" telah selesai dibahas. Silakan ajukan jadwal penandatanganan.'],
            'wa'     => self::formalToPemohon($title, $namaPemohon, $p, $msg),
        ];
    }

    /** Tahap 2 → 3: Jadwal penandatanganan disetujui admin. */
    public static function jadwalDisetujui(string $namaPemohon, Permohonan $p, string $tanggalJadwal): array
    {
        $title = 'Jadwal Penandatanganan Disetujui';
        $msg = "Jadwal penandatanganan kerja sama Bpk/Ibu telah disetujui dan ditetapkan pada *{$tanggalJadwal}*. Mohon mempersiapkan seluruh dokumen yang akan ditandatangani sesuai ketentuan. Dokumen PKS final akan disiapkan oleh admin.";

        return [
            'system' => ['title' => $title, 'message' => 'Jadwal penandatanganan untuk "' . $p->label . '" disetujui pada ' . $tanggalJadwal . '.'],
            'wa'     => self::formalToPemohon($title, $namaPemohon, $p, $msg),
        ];
    }

    public static function jadwalDiajukan(Permohonan $p, string $tanggalJadwal, string $oleh): string
    {
        $msg = "Pemohon telah mengajukan jadwal penandatanganan kerja sama pada *{$tanggalJadwal}*. Mohon admin memeriksa dan memberikan persetujuan jadwal pada dashboard SIKERJA.";
        return self::internalGroup('Pengajuan Jadwal Penandatanganan', $p, $msg, $oleh);
    }

    /** Tahap 3 → 4: Admin upload PKS final. Pemohon notif untuk siap-siap TTD. */
    public static function pksFinalSiap(string $namaPemohon, Permohonan $p): array
    {
        $title = 'PKS Final Telah Disiapkan';
        $msg = "PKS final telah disiapkan oleh admin dan siap untuk ditandatangani. Bpk/Ibu mohon mempersiapkan diri pada hari penandatanganan sesuai jadwal yang telah ditentukan.";

        return [
            'system' => ['title' => $title, 'message' => 'PKS final untuk "' . $p->label . '" telah disiapkan admin. Mohon siapkan dokumen tanda tangan.'],
            'wa'     => self::formalToPemohon($title, $namaPemohon, $p, $msg),
        ];
    }

    /** Tahap 4 → 5: Pemohon upload dokumen tertandatangani. */
    public static function dokumenTtdDiterima(Permohonan $p, string $oleh): string
    {
        $msg = "Pemohon telah mengunggah dokumen tertandatangani. Mohon admin segera melakukan validasi dokumen pada dashboard SIKERJA.";
        return self::internalGroup('Dokumen Tertandatangani Diterima', $p, $msg, $oleh);
    }

    /** Tahap 5: Admin validasi TTD = Tidak Valid → pemohon perbaiki. */
    public static function ttdDitolak(string $namaPemohon, Permohonan $p, string $alasan): array
    {
        $title = 'Dokumen Tertandatangani Memerlukan Perbaikan';
        $msg = "Dokumen tertandatangani yang Bpk/Ibu unggah belum dapat divalidasi. Mohon Bpk/Ibu mengunggah ulang dokumen yang telah diperbaiki sesuai catatan di bawah.";

        return [
            'system' => ['title' => $title, 'message' => 'Dokumen tertandatangani untuk "' . $p->label . '" ditolak. ' . $alasan],
            'wa'     => self::formalToPemohon($title, $namaPemohon, $p, $msg, $alasan),
        ];
    }

    /** Tahap 5 → 6: Admin validasi & approve → kerjasama mulai pelaksanaan. */
    public static function pelaksanaanDimulai(string $namaPemohon, Permohonan $p): array
    {
        $title = 'Kerja Sama Memasuki Tahap Pelaksanaan';
        $msg = "Seluruh dokumen kerja sama telah divalidasi dan disetujui. Mulai saat ini kerja sama Bpk/Ibu memasuki tahap pelaksanaan. Mohon Bpk/Ibu menjalankan kerja sama sesuai ruang lingkup yang disepakati. Tim akan mendampingi monitoring & evaluasi sesuai jadwal.";

        return [
            'system' => ['title' => $title, 'message' => 'Kerja sama "' . $p->label . '" mulai memasuki tahap pelaksanaan.'],
            'wa'     => self::formalToPemohon($title, $namaPemohon, $p, $msg),
        ];
    }

    /** Update status manual (legacy generic update). */
    public static function statusUpdate(string $namaPemohon, Permohonan $p, string $statusBaru, ?string $keterangan = null): array
    {
        $title = 'Pembaruan Status Permohonan';
        $msg = "Permohonan kerja sama Bpk/Ibu telah diperbarui statusnya menjadi *{$statusBaru}*.";

        return [
            'system' => ['title' => $title, 'message' => 'Status permohonan "' . $p->label . '" diperbarui menjadi ' . $statusBaru . '.'],
            'wa'     => self::formalToPemohon($title, $namaPemohon, $p, $msg, $keterangan),
        ];
    }

    /** Berkas kerja sama awal diupload pemohon (status 0/1). */
    public static function berkasAwalDiupload(Permohonan $p, string $oleh): string
    {
        $msg = "Pemohon telah mengunggah berkas pendukung permohonan kerja sama. Berkas siap diperiksa pada tahap pembahasan TKKSD.";
        return self::internalGroup('Berkas Permohonan Diunggah', $p, $msg, $oleh);
    }

    /** Permohonan baru masuk dari pemohon (notif ke admin). */
    public static function permohonanBaruDariPemohon(Permohonan $p, string $oleh): string
    {
        $msg = "Permohonan kerja sama baru telah masuk dan menunggu tahap validasi awal oleh admin pada dashboard SIKERJA.";
        return self::internalGroup('Permohonan Baru Masuk', $p, $msg, $oleh);
    }

    // ----------------------------------------------------------------------
    // MONEV
    // ----------------------------------------------------------------------

    public static function monevDariPemohonKeAdmin(Permohonan $p, string $kodeMonev, string $submitterRoleLabel, string $submitterName): string
    {
        $msg = "Telah diterima form Monitoring & Evaluasi *{$kodeMonev}* dari *{$submitterRoleLabel}* ({$submitterName}). Mohon admin meninjau hasil monev pada dashboard untuk konsolidasi laporan.";
        return self::internalGroup('Monev Diterima', $p, $msg, $submitterName);
    }

    public static function monevHasilKePemohon(string $namaPemohon, Permohonan $p, string $followUpStatus): array
    {
        $title = 'Hasil Monitoring & Evaluasi';
        $msg = "Hasil Monitoring & Evaluasi untuk kerja sama Bpk/Ibu telah tersedia. Status tindak lanjut: *{$followUpStatus}*. Detail lengkap dapat diakses melalui menu Monev pada aplikasi SIKERJA.";

        return [
            'system' => ['title' => $title, 'message' => 'Hasil Monev untuk "' . $p->label . '" telah tersedia. Status: ' . $followUpStatus . '.'],
            'wa'     => self::formalToPemohon($title, $namaPemohon, $p, $msg),
        ];
    }

    public static function reviewRequestKeTkksdLokus(string $namaOpd, Permohonan $p, string $kodeMonev): array
    {
        $title = 'Permohonan Review Hasil Monitoring & Evaluasi';
        $msg = "Form Monev *{$kodeMonev}* untuk kerja sama yang melibatkan instansi Bpk/Ibu memerlukan persetujuan TKKSD Lokus. Mohon Bpk/Ibu meninjau dan memberikan persetujuan melalui aplikasi SIKERJA.";

        return [
            'system' => ['title' => 'Permintaan Review Monev', 'message' => 'Hasil monev ' . $kodeMonev . ' untuk "' . $p->label . '" menunggu persetujuan Anda.'],
            'wa'     => self::formalToOpd($title, $namaOpd, $p, $msg),
        ];
    }

    // ----------------------------------------------------------------------
    // REMINDER (CRON)
    // ----------------------------------------------------------------------

    /** Reminder ke pemohon: kerjasama akan/baru berakhir, perlu monev. */
    public static function reminderMonevPemohon(string $namaPemohon, Permohonan $p, int $sisaHari): array
    {
        $title = $sisaHari < 0
            ? 'Kerja Sama Telah Berakhir — Mohon Lakukan Monev'
            : 'Kerja Sama Akan Segera Berakhir';

        $msg = $sisaHari < 0
            ? "Kerja sama Bpk/Ibu telah berakhir. Sesuai ketentuan, mohon Bpk/Ibu segera mengisi form Monitoring & Evaluasi (Monev) sebagai laporan akhir kerja sama melalui menu Monev pada aplikasi SIKERJA."
            : "Kerja sama Bpk/Ibu akan berakhir dalam *{$sisaHari} hari*. Mohon Bpk/Ibu mempersiapkan pengisian form Monitoring & Evaluasi (Monev) yang menjadi laporan akhir kerja sama.";

        return [
            'system' => ['title' => $title, 'message' => 'Kerja sama "' . $p->label . '" ' . ($sisaHari < 0 ? 'telah berakhir' : 'akan berakhir dalam ' . $sisaHari . ' hari') . '. Silakan isi form Monev.'],
            'wa'     => self::formalToPemohon($title, $namaPemohon, $p, $msg),
        ];
    }

    /** Reminder grup admin tentang kerjasama yang akan/baru berakhir. */
    public static function reminderMonevAdmin(Permohonan $p, int $sisaHari): string
    {
        $tahapan = $sisaHari < 0 ? 'Kerja Sama Berakhir — Pantau Monev' : 'Kerja Sama Akan Berakhir';
        $msg = $sisaHari < 0
            ? "Kerja sama telah berakhir. Mohon admin memantau pengisian Monev oleh pemohon dan TKKSD Lokus untuk konsolidasi laporan akhir."
            : "Kerja sama akan berakhir dalam *{$sisaHari} hari*. Mohon admin memastikan pemohon dan TKKSD Lokus siap mengisi Monev sesuai jadwal.";

        return self::internalGroup($tahapan, $p, $msg);
    }

    // ----------------------------------------------------------------------
    // PEMBAHASAN INTERAKTIF
    // ----------------------------------------------------------------------

    public static function diskusiBaruKeAdmin(Permohonan $p, string $oleh): string
    {
        $msg = "Pemohon mengirim pesan baru pada ruang pembahasan permohonan kerja sama. Mohon admin meninjau dan memberikan tanggapan pada dashboard SIKERJA.";
        return self::internalGroup('Pesan Pembahasan Baru', $p, $msg, $oleh);
    }

    public static function diskusiBaruKePemohon(string $namaPemohon, Permohonan $p): array
    {
        $title = 'Tanggapan Baru pada Pembahasan';
        $msg = "Admin telah memberikan tanggapan baru pada ruang pembahasan permohonan kerja sama Bpk/Ibu. Mohon Bpk/Ibu meninjau pesan tersebut melalui aplikasi SIKERJA.";

        return [
            'system' => ['title' => $title, 'message' => 'Admin mengirim pesan baru pada pembahasan "' . $p->label . '".'],
            'wa'     => self::formalToPemohon($title, $namaPemohon, $p, $msg),
        ];
    }
}
