<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Pisah jawaban monev ke 3 tabel detail per role supaya `monevs` ringan.
     * - monev_admin_details   : 11 pertanyaan + rating + file_bukti + saran/kendala
     * - monev_pemohon_details : pmh_*
     * - monev_tkksd_details   : tkl_*
     *
     * `monevs` jadi tabel inti: identitas + role + status + tanggal_evaluasi.
     */
    public function up(): void
    {
        // 1) Buat tabel detail
        Schema::create('monev_admin_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monev_id')->unique()->constrained('monevs')->cascadeOnDelete();
            // Evaluasi pelaksanaan
            $table->string('kesesuaian_tujuan')->nullable();
            $table->string('ketepatan_waktu')->nullable();
            $table->string('kontribusi_mitra')->nullable();
            $table->string('tingkat_koordinasi')->nullable();
            // Capaian & dampak
            $table->string('capaian_indikator')->nullable();
            $table->string('dampak_pelaksanaan')->nullable();
            $table->string('inovasi_manfaat')->nullable();
            // Administrasi
            $table->string('kelengkapan_dokumen')->nullable();
            $table->string('pelaporan_berkala')->nullable();
            $table->text('kendala_administrasi')->nullable();
            // Rekomendasi
            $table->string('relevansi_kebutuhan')->nullable();
            $table->string('rekomendasi_lanjutan')->nullable();
            $table->text('saran_rekomendasi')->nullable();
            // Bukti & rating
            $table->string('file_bukti')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->timestamps();
        });

        Schema::create('monev_pemohon_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monev_id')->unique()->constrained('monevs')->cascadeOnDelete();
            $table->string('pmh_realisasi_kegiatan')->nullable();
            $table->string('pmh_kesesuaian_output')->nullable();
            $table->text('pmh_pemanfaatan_hasil')->nullable();
            $table->text('pmh_kendala_lapangan')->nullable();
            $table->string('pmh_keberlanjutan')->nullable();
            $table->string('pmh_file_laporan')->nullable();
            $table->timestamps();
        });

        Schema::create('monev_tkksd_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monev_id')->unique()->constrained('monevs')->cascadeOnDelete();
            $table->string('tkl_kepatuhan_pks')->nullable();
            $table->string('tkl_koordinasi_mitra')->nullable();
            $table->string('tkl_kesesuaian_anggaran')->nullable();
            $table->text('tkl_temuan_pengawasan')->nullable();
            $table->string('tkl_rekomendasi_lokus')->nullable();
            $table->text('tkl_catatan')->nullable();
            $table->timestamps();
        });

        // 2) Backfill data dari monevs ke detail tables
        $now = now();
        $rows = DB::table('monevs')->get();

        foreach ($rows as $r) {
            // Admin: rule lama, semua row pre-split mungkin punya jawaban admin (form asal).
            // Setelah split, hanya simpan ke admin_details kalau ada jawaban admin yang terisi.
            $hasAdminAnswer = $r->kesesuaian_tujuan || $r->ketepatan_waktu || $r->kontribusi_mitra
                || $r->tingkat_koordinasi || $r->capaian_indikator || $r->dampak_pelaksanaan
                || $r->inovasi_manfaat || $r->kelengkapan_dokumen || $r->pelaporan_berkala
                || $r->relevansi_kebutuhan || $r->rekomendasi_lanjutan || $r->saran_rekomendasi
                || $r->kendala_administrasi || $r->file_bukti || $r->rating;

            // Tentukan role: pakai submitter_role kalau ada, kalau null fallback berdasar isi data
            $role = $r->submitter_role;
            if (!$role) {
                if ($hasAdminAnswer) $role = 'admin';
                elseif (($r->pmh_realisasi_kegiatan ?? null) || ($r->pmh_kesesuaian_output ?? null)) $role = 'pemohon';
                elseif (($r->tkl_kepatuhan_pks ?? null) || ($r->tkl_rekomendasi_lokus ?? null)) $role = 'tkksd_lokus';
                else $role = 'admin';
                DB::table('monevs')->where('id', $r->id)->update(['submitter_role' => $role]);
            }

            if ($role === 'admin' && $hasAdminAnswer) {
                DB::table('monev_admin_details')->insert([
                    'monev_id' => $r->id,
                    'kesesuaian_tujuan' => $r->kesesuaian_tujuan,
                    'ketepatan_waktu' => $r->ketepatan_waktu,
                    'kontribusi_mitra' => $r->kontribusi_mitra,
                    'tingkat_koordinasi' => $r->tingkat_koordinasi,
                    'capaian_indikator' => $r->capaian_indikator,
                    'dampak_pelaksanaan' => $r->dampak_pelaksanaan,
                    'inovasi_manfaat' => $r->inovasi_manfaat,
                    'kelengkapan_dokumen' => $r->kelengkapan_dokumen,
                    'pelaporan_berkala' => $r->pelaporan_berkala,
                    'kendala_administrasi' => $r->kendala_administrasi,
                    'relevansi_kebutuhan' => $r->relevansi_kebutuhan,
                    'rekomendasi_lanjutan' => $r->rekomendasi_lanjutan,
                    'saran_rekomendasi' => $r->saran_rekomendasi,
                    'file_bukti' => $r->file_bukti,
                    'rating' => $r->rating,
                    'created_at' => $r->created_at ?? $now,
                    'updated_at' => $r->updated_at ?? $now,
                ]);
            } elseif ($role === 'pemohon') {
                DB::table('monev_pemohon_details')->insert([
                    'monev_id' => $r->id,
                    'pmh_realisasi_kegiatan' => $r->pmh_realisasi_kegiatan ?? null,
                    'pmh_kesesuaian_output' => $r->pmh_kesesuaian_output ?? null,
                    'pmh_pemanfaatan_hasil' => $r->pmh_pemanfaatan_hasil ?? null,
                    'pmh_kendala_lapangan' => $r->pmh_kendala_lapangan ?? null,
                    'pmh_keberlanjutan' => $r->pmh_keberlanjutan ?? null,
                    'pmh_file_laporan' => $r->pmh_file_laporan ?? null,
                    'created_at' => $r->created_at ?? $now,
                    'updated_at' => $r->updated_at ?? $now,
                ]);
            } elseif ($role === 'tkksd_lokus') {
                DB::table('monev_tkksd_details')->insert([
                    'monev_id' => $r->id,
                    'tkl_kepatuhan_pks' => $r->tkl_kepatuhan_pks ?? null,
                    'tkl_koordinasi_mitra' => $r->tkl_koordinasi_mitra ?? null,
                    'tkl_kesesuaian_anggaran' => $r->tkl_kesesuaian_anggaran ?? null,
                    'tkl_temuan_pengawasan' => $r->tkl_temuan_pengawasan ?? null,
                    'tkl_rekomendasi_lokus' => $r->tkl_rekomendasi_lokus ?? null,
                    'tkl_catatan' => $r->tkl_catatan ?? null,
                    'created_at' => $r->created_at ?? $now,
                    'updated_at' => $r->updated_at ?? $now,
                ]);
            }
        }

        // 3) Drop kolom jawaban dari monevs (dipisah jadi 2 step supaya foreign key/index aman)
        Schema::table('monevs', function (Blueprint $table) {
            // Admin columns
            $table->dropColumn([
                'kesesuaian_tujuan',
                'ketepatan_waktu',
                'kontribusi_mitra',
                'tingkat_koordinasi',
                'capaian_indikator',
                'dampak_pelaksanaan',
                'inovasi_manfaat',
                'kelengkapan_dokumen',
                'pelaporan_berkala',
                'kendala_administrasi',
                'relevansi_kebutuhan',
                'rekomendasi_lanjutan',
                'saran_rekomendasi',
                'file_bukti',
                'rating',
            ]);
        });

        Schema::table('monevs', function (Blueprint $table) {
            // Pemohon & TKKSD columns
            $table->dropColumn([
                'pmh_realisasi_kegiatan',
                'pmh_kesesuaian_output',
                'pmh_pemanfaatan_hasil',
                'pmh_kendala_lapangan',
                'pmh_keberlanjutan',
                'pmh_file_laporan',
                'tkl_kepatuhan_pks',
                'tkl_koordinasi_mitra',
                'tkl_kesesuaian_anggaran',
                'tkl_temuan_pengawasan',
                'tkl_rekomendasi_lokus',
                'tkl_catatan',
            ]);
        });
    }

    public function down(): void
    {
        // Re-add kolom jawaban di monevs
        Schema::table('monevs', function (Blueprint $table) {
            $table->string('kesesuaian_tujuan')->nullable();
            $table->string('ketepatan_waktu')->nullable();
            $table->string('kontribusi_mitra')->nullable();
            $table->string('tingkat_koordinasi')->nullable();
            $table->string('capaian_indikator')->nullable();
            $table->string('dampak_pelaksanaan')->nullable();
            $table->string('inovasi_manfaat')->nullable();
            $table->string('kelengkapan_dokumen')->nullable();
            $table->string('pelaporan_berkala')->nullable();
            $table->text('kendala_administrasi')->nullable();
            $table->string('relevansi_kebutuhan')->nullable();
            $table->string('rekomendasi_lanjutan')->nullable();
            $table->text('saran_rekomendasi')->nullable();
            $table->string('file_bukti')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();

            $table->string('pmh_realisasi_kegiatan')->nullable();
            $table->string('pmh_kesesuaian_output')->nullable();
            $table->text('pmh_pemanfaatan_hasil')->nullable();
            $table->text('pmh_kendala_lapangan')->nullable();
            $table->string('pmh_keberlanjutan')->nullable();
            $table->string('pmh_file_laporan')->nullable();

            $table->string('tkl_kepatuhan_pks')->nullable();
            $table->string('tkl_koordinasi_mitra')->nullable();
            $table->string('tkl_kesesuaian_anggaran')->nullable();
            $table->text('tkl_temuan_pengawasan')->nullable();
            $table->string('tkl_rekomendasi_lokus')->nullable();
            $table->text('tkl_catatan')->nullable();
        });

        // Re-fill back dari detail tables ke monevs (best effort)
        $admins = DB::table('monev_admin_details')->get();
        foreach ($admins as $d) {
            DB::table('monevs')->where('id', $d->monev_id)->update([
                'kesesuaian_tujuan' => $d->kesesuaian_tujuan,
                'ketepatan_waktu' => $d->ketepatan_waktu,
                'kontribusi_mitra' => $d->kontribusi_mitra,
                'tingkat_koordinasi' => $d->tingkat_koordinasi,
                'capaian_indikator' => $d->capaian_indikator,
                'dampak_pelaksanaan' => $d->dampak_pelaksanaan,
                'inovasi_manfaat' => $d->inovasi_manfaat,
                'kelengkapan_dokumen' => $d->kelengkapan_dokumen,
                'pelaporan_berkala' => $d->pelaporan_berkala,
                'kendala_administrasi' => $d->kendala_administrasi,
                'relevansi_kebutuhan' => $d->relevansi_kebutuhan,
                'rekomendasi_lanjutan' => $d->rekomendasi_lanjutan,
                'saran_rekomendasi' => $d->saran_rekomendasi,
                'file_bukti' => $d->file_bukti,
                'rating' => $d->rating,
            ]);
        }
        foreach (DB::table('monev_pemohon_details')->get() as $d) {
            DB::table('monevs')->where('id', $d->monev_id)->update([
                'pmh_realisasi_kegiatan' => $d->pmh_realisasi_kegiatan,
                'pmh_kesesuaian_output' => $d->pmh_kesesuaian_output,
                'pmh_pemanfaatan_hasil' => $d->pmh_pemanfaatan_hasil,
                'pmh_kendala_lapangan' => $d->pmh_kendala_lapangan,
                'pmh_keberlanjutan' => $d->pmh_keberlanjutan,
                'pmh_file_laporan' => $d->pmh_file_laporan,
            ]);
        }
        foreach (DB::table('monev_tkksd_details')->get() as $d) {
            DB::table('monevs')->where('id', $d->monev_id)->update([
                'tkl_kepatuhan_pks' => $d->tkl_kepatuhan_pks,
                'tkl_koordinasi_mitra' => $d->tkl_koordinasi_mitra,
                'tkl_kesesuaian_anggaran' => $d->tkl_kesesuaian_anggaran,
                'tkl_temuan_pengawasan' => $d->tkl_temuan_pengawasan,
                'tkl_rekomendasi_lokus' => $d->tkl_rekomendasi_lokus,
                'tkl_catatan' => $d->tkl_catatan,
            ]);
        }

        Schema::dropIfExists('monev_tkksd_details');
        Schema::dropIfExists('monev_pemohon_details');
        Schema::dropIfExists('monev_admin_details');
    }
};
