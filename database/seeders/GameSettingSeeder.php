<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GameSettingSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus data lama dulu agar tidak double
        DB::table('game_settings')->truncate();

        // Masukkan data settingan bandar
        DB::table('game_settings')->insert([
            'name' => 'mode_bandar',
            'value' => 0, // 0 artinya settingan PASTI KALAH
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}