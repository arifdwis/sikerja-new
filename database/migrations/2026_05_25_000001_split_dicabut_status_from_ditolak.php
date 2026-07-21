<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Backfill data lama: pencabutan saat pelaksanaan sebelumnya disimpan di status 9.
        DB::table('permohonan')
            ->where('status', 9)
            ->where('alasan_tolak', 'like', 'Pencabutan saat pelaksanaan:%')
            ->update([
                'status' => 8,
                'alasan_tolak' => DB::raw("TRIM(REPLACE(alasan_tolak, 'Pencabutan saat pelaksanaan:', ''))"),
            ]);
    }

    public function down(): void
    {
        DB::table('permohonan')
            ->where('status', 8)
            ->update([
                'status' => 9,
                'alasan_tolak' => DB::raw("CONCAT('Pencabutan saat pelaksanaan: ', alasan_tolak)"),
            ]);
    }
};
