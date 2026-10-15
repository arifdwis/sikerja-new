<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom PIC, Pimpinan, Draft, & Tipe Dokumen di tabel permohonan
        Schema::table('permohonan', function (Blueprint $table) {
            if (!Schema::hasColumn('permohonan', 'nama_pimpinan')) {
                $table->string('nama_pimpinan')->nullable()->after('nama_instansi');
            }
            if (!Schema::hasColumn('permohonan', 'jabatan_pimpinan')) {
                $table->string('jabatan_pimpinan')->nullable()->after('nama_pimpinan');
            }
            if (!Schema::hasColumn('permohonan', 'nip_pimpinan')) {
                $table->string('nip_pimpinan')->nullable()->after('jabatan_pimpinan');
            }

            // PIC 1
            if (!Schema::hasColumn('permohonan', 'pic1_nama')) {
                $table->string('pic1_nama')->nullable()->after('website');
            }
            if (!Schema::hasColumn('permohonan', 'pic1_jabatan')) {
                $table->string('pic1_jabatan')->nullable()->after('pic1_nama');
            }
            if (!Schema::hasColumn('permohonan', 'pic1_phone')) {
                $table->string('pic1_phone')->nullable()->after('pic1_jabatan');
            }
            if (!Schema::hasColumn('permohonan', 'pic1_email')) {
                $table->string('pic1_email')->nullable()->after('pic1_phone');
            }

            // PIC 2
            if (!Schema::hasColumn('permohonan', 'pic2_nama')) {
                $table->string('pic2_nama')->nullable()->after('pic1_email');
            }
            if (!Schema::hasColumn('permohonan', 'pic2_jabatan')) {
                $table->string('pic2_jabatan')->nullable()->after('pic2_nama');
            }
            if (!Schema::hasColumn('permohonan', 'pic2_phone')) {
                $table->string('pic2_phone')->nullable()->after('pic2_jabatan');
            }
            if (!Schema::hasColumn('permohonan', 'pic2_email')) {
                $table->string('pic2_email')->nullable()->after('pic2_phone');
            }

            // Relasi KB Induk & Tipe Dokumen untuk siklus PKS turunan
            if (!Schema::hasColumn('permohonan', 'tipe_dokumen')) {
                $table->string('tipe_dokumen', 20)->default('KB')->after('pic2_email'); // 'KB' atau 'PKS'
            }
            if (!Schema::hasColumn('permohonan', 'id_kb_induk')) {
                // Table permohonan uses int(10) unsigned for id
                $table->unsignedInteger('id_kb_induk')->nullable()->index()->after('tipe_dokumen');
            }
            if (!Schema::hasColumn('permohonan', 'is_draft')) {
                $table->boolean('is_draft')->default(false)->after('id_kb_induk');
            }
        });

        // 2. Tambah kategori dokumen di permohonan_file
        Schema::table('permohonan_file', function (Blueprint $table) {
            if (!Schema::hasColumn('permohonan_file', 'kategori_dokumen')) {
                $table->string('kategori_dokumen', 50)->nullable()->after('label');
            }
        });

        // 3. Tambah field berkas berkala per 6 bulan di monev_pemohon_details
        Schema::table('monev_pemohon_details', function (Blueprint $table) {
            if (!Schema::hasColumn('monev_pemohon_details', 'file_berita_acara')) {
                $table->string('file_berita_acara')->nullable()->after('pmh_file_laporan');
            }
            if (!Schema::hasColumn('monev_pemohon_details', 'file_dokumentasi')) {
                $table->text('file_dokumentasi')->nullable()->after('file_berita_acara');
            }
            if (!Schema::hasColumn('monev_pemohon_details', 'progress_persen')) {
                $table->unsignedTinyInteger('progress_persen')->default(0)->after('file_dokumentasi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monev_pemohon_details', function (Blueprint $table) {
            $table->dropColumn(['file_berita_acara', 'file_dokumentasi', 'progress_persen']);
        });

        Schema::table('permohonan_file', function (Blueprint $table) {
            $table->dropColumn(['kategori_dokumen']);
        });

        Schema::table('permohonan', function (Blueprint $table) {
            $table->dropColumn([
                'nama_pimpinan', 'jabatan_pimpinan', 'nip_pimpinan',
                'pic1_nama', 'pic1_jabatan', 'pic1_phone', 'pic1_email',
                'pic2_nama', 'pic2_jabatan', 'pic2_phone', 'pic2_email',
                'tipe_dokumen', 'id_kb_induk', 'is_draft'
            ]);
        });
    }
};
