<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $nip = '199703262025211007';

        // 1. Cari user berdasarkan email / uid / number_id
        $user = DB::table('users')
            ->where('email', $nip)
            ->orWhere('uid', $nip)
            ->orWhere('number_id', $nip)
            ->first();

        if ($user) {
            // Ambil role kabag_kerjasama dan administrator
            $kabagRole = DB::table('roles')->where('slug', 'kabag_kerjasama')->first();
            $adminRole = DB::table('roles')->where('slug', 'administrator')->first();

            $rolesToAssign = array_filter([$kabagRole?->id, $adminRole?->id]);

            foreach ($rolesToAssign as $roleId) {
                $exists = DB::table('role_users')
                    ->where('user_id', $user->id)
                    ->where('role_id', $roleId)
                    ->exists();

                if (!$exists) {
                    DB::table('role_users')->insert([
                        'user_id' => $user->id,
                        'role_id' => $roleId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Pastikan notification_email terisi agar lolos notifikasi dan middleware EnsureEmailIsFilled
            if (empty($user->notification_email)) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'notification_email' => 'arifdwi286@gmail.com',
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $nip = '199703262025211007';
        $user = DB::table('users')
            ->where('email', $nip)
            ->orWhere('uid', $nip)
            ->orWhere('number_id', $nip)
            ->first();

        if ($user) {
            $kabagRole = DB::table('roles')->where('slug', 'kabag_kerjasama')->first();
            if ($kabagRole) {
                DB::table('role_users')
                    ->where('user_id', $user->id)
                    ->where('role_id', $kabagRole->id)
                    ->delete();
            }
        }
    }
};
