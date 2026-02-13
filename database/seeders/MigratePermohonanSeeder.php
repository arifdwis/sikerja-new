<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MigratePermohonanSeeder extends Seeder
{
    /**
     * Migrate permohonan data from sikerja-v2 database to current sikerja database.
     * Safe to run multiple times — skips existing records by ID.
     */
    public function run(): void
    {
        $source = DB::connection('sikerja_v2');
        $target = DB::connection('mysql');

        $this->command->info('=== Migrasi Data dari sikerja-v2 ===');

        // 1. Migrate pemohon
        $this->migratePemohon($source, $target);

        // 2. Migrate permohonan
        $this->migratePermohonan($source, $target);

        // 3. Migrate permohonan_file
        $this->migratePermohonanFile($source, $target);

        // 4. Migrate permohonan_histori
        $this->migratePermohonanHistori($source, $target);

        // 5. Migrate permohonan_histori_pembahasan
        $this->migratePermohonanPembahasan($source, $target);

        $this->command->info('=== Migrasi Selesai ===');
    }

    private function migratePemohon($source, $target): void
    {
        $this->command->info('Migrasi pemohon...');

        $existingIds = $target->table('pemohon')->pluck('id')->toArray();
        $rows = $source->table('pemohon')->get();

        $inserted = 0;
        foreach ($rows as $row) {
            if (in_array($row->id, $existingIds)) {
                continue;
            }

            $target->table('pemohon')->insert([
                'id' => $row->id,
                'id_operator' => $row->id_operator,
                'uuid' => $row->uuid,
                'name' => $row->name,
                'foto' => $row->foto,
                'jabatan' => $row->jabatan,
                'unit_kerja' => $row->unit_kerja,
                'nip' => $row->nip,
                'nik' => $row->nik,
                'nik_file' => $row->nik_file,
                'slug' => $row->slug,
                'email' => $row->email,
                'phone' => $row->phone,
                'nickname' => $row->nickname,
                'address' => $row->address,
                'kota' => $row->kota,
                'kota_id' => $row->kota_id,
                'gender' => $row->gender,
                'date_birth' => $row->date_birth,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
            $inserted++;
        }

        $this->command->info("  → Pemohon: {$inserted} inserted, " . (count($rows) - $inserted) . " skipped (already exist)");
    }

    private function migratePermohonan($source, $target): void
    {
        $this->command->info('Migrasi permohonan...');

        $existingIds = $target->table('permohonan')->pluck('id')->toArray();
        $rows = $source->table('permohonan')->get();

        $inserted = 0;
        foreach ($rows as $row) {
            if (in_array($row->id, $existingIds)) {
                continue;
            }

            $target->table('permohonan')->insert([
                'id' => $row->id,
                'uuid' => $row->uuid,
                'id_pemohon_0' => $row->id_pemohon_0,
                'id_pemohon_1' => $row->id_pemohon_1,
                'id_provinsi' => $row->id_provinsi,
                'id_kota' => $row->id_kota,
                'id_kategori' => $row->id_kategori,
                'kode' => $row->kode,
                'nama_instansi' => $row->nama_instansi,
                'nomor_permohonan' => $row->nomor_permohonan,
                'alamat' => $row->alamat,
                'lokasi' => $row->lokasi,
                'kode_pos' => $row->kode_pos,
                'slug' => $row->slug,
                'email' => $row->email,
                'telepon' => $row->telepon,
                'website' => $row->website,
                'status' => $row->status,
                'file_final' => $row->file_final,
                'label' => $row->label,
                'latar_belakang' => $row->latar_belakang,
                'alasan_tolak' => $row->alasan_tolak,
                'maksud_tujuan' => $row->maksud_tujuan,
                'lokasi_kerjasama' => $row->lokasi_kerjasama,
                'pembiayaan' => $row->pembiayaan,
                'analisis_dampak' => $row->analisis_dampak,
                'manfaat' => $row->manfaat,
                'jangka_waktu' => $row->jangka_waktu,
                'ruang_lingkup' => $row->ruang_lingkup,
                'tanggal_mulai' => null, // column doesn't exist in source
                'tanggal_berakhir' => null, // column doesn't exist in source
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
            $inserted++;
        }

        $this->command->info("  → Permohonan: {$inserted} inserted, " . (count($rows) - $inserted) . " skipped");
    }

    private function migratePermohonanFile($source, $target): void
    {
        $this->command->info('Migrasi permohonan_file...');

        $existingIds = $target->table('permohonan_file')->pluck('id')->toArray();
        $rows = $source->table('permohonan_file')->get();

        $inserted = 0;
        foreach ($rows as $row) {
            if (in_array($row->id, $existingIds)) {
                continue;
            }

            $target->table('permohonan_file')->insert([
                'id' => $row->id,
                'id_permohonan' => $row->id_permohonan,
                'uuid' => $row->uuid,
                'label' => $row->label,
                'deskripsi' => $row->deskripsi,
                'file' => $row->file,
                'file_path' => null, // column doesn't exist in source
                'file_name' => null, // column doesn't exist in source
                'status' => $row->status,
                'komentar' => null, // column doesn't exist in source
                'reviewer_id' => null, // column doesn't exist in source
                'reviewed_at' => null, // column doesn't exist in source
                'slug' => $row->slug,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'deleted_at' => $row->deleted_at,
            ]);
            $inserted++;
        }

        $this->command->info("  → Permohonan File: {$inserted} inserted, " . (count($rows) - $inserted) . " skipped");
    }

    private function migratePermohonanHistori($source, $target): void
    {
        $this->command->info('Migrasi permohonan_histori...');

        $existingIds = $target->table('permohonan_histori')->pluck('id')->toArray();
        $rows = $source->table('permohonan_histori')->get();

        $inserted = 0;
        foreach ($rows as $row) {
            if (in_array($row->id, $existingIds)) {
                continue;
            }

            $target->table('permohonan_histori')->insert([
                'id' => $row->id,
                'id_permohonan' => $row->id_permohonan,
                'id_operator' => $row->id_operator,
                'id_file' => $row->id_file,
                'uuid' => $row->uuid,
                'deskripsi' => $row->deskripsi,
                'komentar' => null, // column doesn't exist in source
                'file' => $row->file,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
            $inserted++;
        }

        $this->command->info("  → Permohonan Histori: {$inserted} inserted, " . (count($rows) - $inserted) . " skipped");
    }

    private function migratePermohonanPembahasan($source, $target): void
    {
        $this->command->info('Migrasi permohonan_histori_pembahasan...');

        $existingIds = $target->table('permohonan_histori_pembahasan')->pluck('id')->toArray();
        $rows = $source->table('permohonan_histori_pembahasan')->get();

        $inserted = 0;
        foreach ($rows as $row) {
            if (in_array($row->id, $existingIds)) {
                continue;
            }

            $target->table('permohonan_histori_pembahasan')->insert([
                'id' => $row->id,
                'id_permohonan' => $row->id_permohonan,
                'id_operator' => $row->id_operator,
                'id_file' => $row->id_file,
                'id_histori' => $row->id_histori,
                'uuid' => $row->uuid,
                'komentar' => $row->komentar,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'deleted_at' => $row->deleted_at,
            ]);
            $inserted++;
        }

        $this->command->info("  → Pembahasan: {$inserted} inserted, " . (count($rows) - $inserted) . " skipped");
    }
}
