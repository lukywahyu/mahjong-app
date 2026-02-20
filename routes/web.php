<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController; 
use Illuminate\Support\Facades\DB; // Tambahkan ini agar perintah DB:: bisa jalan

// Route untuk API hasil spin (JSON)
Route::get('/spin-test', [GameController::class, 'spin']);

// Route untuk tampilan utama Game (Frontend)
Route::get('/game', function () {
    return view('game');
});

// Route untuk Panel Admin (Remote Kontrol Bandar)
Route::get('/admin-panel', function () {
    $currentSetting = DB::table('game_settings')->where('name', 'mode_bandar')->first();
    return view('admin', compact('currentSetting'));
});

// Route untuk memproses perubahan settingan dari Admin
Route::post('/update-setting', [GameController::class, 'updateSetting']);