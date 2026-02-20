<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameController extends Controller
{
    public function spin()
    {
        // 1. Ambil Saldo & Setting dari Database
        $userSetting = DB::table('game_settings')->where('name', 'user_balance')->first();
        $bandarSetting = DB::table('game_settings')->where('name', 'mode_bandar')->first();

        $currentBalance = $userSetting ? (int)$userSetting->value : 1000000;
        $mode = $bandarSetting ? (int)$bandarSetting->value : 2; // Default Normal

        // 2. Cek Saldo & Potong Biaya Spin (Bet)
        $betAmount = 10000;
        if ($currentBalance < $betAmount) {
            return response()->json([
                'is_win' => false,
                'pesan' => "Saldo tidak cukup! Silakan isi ulang saldo Anda.",
                'error_balance' => true
            ], 400);
        }

        // KURANGI SALDO UNTUK TARUHAN
        $currentBalance -= $betAmount;

        // 3. Inisialisasi Simbol & Logika Game
        $symbols = ['🀄', '🐉', '⬜', '🧧', '🏮', '🪙', '🎋', '🌸'];
        $wildSymbol = '🔥';
        $result = [];
        $message = "";
        $totalWin = 0;

        if ($mode === 1) {
            // --- MODE GACOR ---
            $winSymbol = $symbols[array_rand($symbols)];
            for ($i = 0; $i < 15; $i++) {
                $result[] = (rand(1, 10) <= 8) ? $winSymbol : $symbols[array_rand($symbols)];
            }
            $result[5] = $result[6] = $result[7] = $winSymbol; 
            $result[2] = $wildSymbol;
            $message = "🀄 BIG WIN MAHJONG! 🀄";
            $totalWin = rand(500000, 2000000);

        } elseif ($mode === 0) {
            // --- MODE ZONK ---
            $tempSymbols = $symbols;
            for ($i = 0; $i < 15; $i++) {
                $pick = array_rand($tempSymbols);
                $result[] = $tempSymbols[$pick];
                unset($tempSymbols[$pick]);
                if (count($tempSymbols) == 0) $tempSymbols = $symbols;
            }
            $message = "Zonk! Belum beruntung.";
            $totalWin = 0;

        } else {
            // --- MODE NORMAL ---
            for ($i = 0; $i < 15; $i++) {
                $result[] = $symbols[array_rand($symbols)];
            }
            if ($result[0] == $result[1] && $result[1] == $result[2]) {
                $message = "KEBERUNTUNGAN MURNI!";
                $totalWin = rand(10000, 50000);
            } else {
                $message = "Coba lagi!";
                $totalWin = 0;
            }
        }

        // 4. Metadata Visual
        $goldIndices = [];
        foreach ($result as $index => $sym) {
            if (rand(1, 10) > 8 && $sym !== $wildSymbol) {
                $goldIndices[] = $index;
            }
        }

        // 5. Update Saldo Akhir (Saldo Sisa + Kemenangan)
        $currentBalance += $totalWin; 
        
        DB::table('game_settings')
            ->where('name', 'user_balance')
            ->updateOrInsert(
                ['name' => 'user_balance'],
                ['value' => $currentBalance]
            );

        // 6. Return Data ke Frontend
        return response()->json([
            'hasil' => $result,
            'gold_indices' => $goldIndices,
            'is_win' => $totalWin > 0,
            'pesan' => $message,
            'win_amount' => number_format($totalWin, 0, ',', '.'),
            'new_balance' => number_format($currentBalance, 0, ',', '.'),
            'debug_mode' => $this->getModeName($mode)
        ]);
    }

    private function getModeName($value) {
        return match($value) {
            0 => 'Settingan Zonk (0%)',
            1 => 'Settingan Gacor (100%)',
            default => 'Normal RNG'
        };
    }

    public function updateSetting(Request $request)
    {
        $request->validate(['value' => 'required|integer|in:0,1,2']);
        DB::table('game_settings')->updateOrInsert(
            ['name' => 'mode_bandar'],
            ['value' => $request->value, 'updated_at' => now()]
        );
        return back()->with('success', 'Mode server berhasil diperbarui!');
    }
}