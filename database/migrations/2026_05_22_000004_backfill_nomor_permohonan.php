<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('permohonan') || !Schema::hasColumn('permohonan', 'nomor_permohonan')) {
            return;
        }

        $years = DB::table('permohonan')
            ->where(function ($query) {
                $query->whereNull('nomor_permohonan')
                    ->orWhere('nomor_permohonan', '');
            })
            ->selectRaw('YEAR(created_at) as year')
            ->whereNotNull('created_at')
            ->distinct()
            ->orderBy('year')
            ->pluck('year');

        foreach ($years as $year) {
            $sequence = DB::table('permohonan')
                ->whereYear('created_at', $year)
                ->whereNotNull('nomor_permohonan')
                ->where('nomor_permohonan', 'like', "REQ/{$year}/%")
                ->pluck('nomor_permohonan')
                ->map(fn ($number) => (int) substr($number, -4))
                ->max() ?? 0;

            DB::table('permohonan')
                ->whereYear('created_at', $year)
                ->where(function ($query) {
                    $query->whereNull('nomor_permohonan')
                        ->orWhere('nomor_permohonan', '');
                })
                ->orderBy('created_at')
                ->orderBy('id')
                ->get(['id'])
                ->each(function ($permohonan) use ($year, &$sequence) {
                    $sequence++;

                    DB::table('permohonan')
                        ->where('id', $permohonan->id)
                        ->update([
                            'nomor_permohonan' => sprintf('REQ/%d/%04d', $year, $sequence),
                        ]);
                });
        }
    }

    public function down(): void
    {
        // Backfilled request numbers are intentionally kept for audit consistency.
    }
};
