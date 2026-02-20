<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mahjong Ways Pro</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --gold: #facc15;
            --jade: #065f46;
            --dark-bg: #020617;
            --mahjong-white: #ffffff;
        }

        body { 
            background: var(--dark-bg); 
            font-family: 'Arial Black', sans-serif; 
            display: flex; justify-content: center; align-items: center; 
            min-height: 100vh; margin: 0;
            color: white;
        }

        .game-container {
            background: linear-gradient(145deg, #064e3b, #022c22);
            padding: 30px;
            border-radius: 25px;
            border: 4px solid var(--gold);
            box-shadow: 0 0 50px rgba(0,0,0,0.8);
            text-align: center;
            width: 100%;
            max-width: 450px;
        }

        .header-title {
            color: var(--gold);
            font-size: 2.2rem;
            margin-bottom: 5px;
            text-shadow: 0 0 15px rgba(250, 204, 21, 0.6);
        }

        .multiplier-box {
            color: var(--gold);
            font-size: 1.5rem;
            margin-bottom: 15px;
            font-weight: bold;
        }

        #multValue {
            transition: all 0.3s ease;
            display: inline-block;
        }

        .slot-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            background: rgba(0,0,0,0.4);
            padding: 15px;
            border-radius: 15px;
            margin-bottom: 25px;
        }

        .symbol {
            aspect-ratio: 3/4;
            background: var(--mahjong-white);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            color: #1e293b;
            box-shadow: inset 0 -4px 0 #cbd5e1, 0 4px 8px rgba(0,0,0,0.3);
            transition: transform 0.1s;
        }

        .symbol.gold {
            background: linear-gradient(180deg, #ffe58a, #d4af37);
            box-shadow: inset 0 -4px 0 #b8860b;
        }

        .spinning {
            animation: fastBlur 0.1s infinite linear;
        }

        @keyframes fastBlur {
            0% { transform: translateY(-5px); filter: blur(2px); }
            50% { transform: translateY(5px); filter: blur(4px); }
            100% { transform: translateY(-5px); filter: blur(2px); }
        }

        .win-glow {
            animation: pulseWin 0.5s infinite alternate;
            z-index: 5;
        }

        @keyframes pulseWin {
            from { transform: scale(1); box-shadow: 0 0 10px gold; }
            to { transform: scale(1.1); box-shadow: 0 0 25px gold; border: 2px solid white; }
        }

        .bottom-panel {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(0,0,0,0.5);
            padding: 15px 20px;
            border-radius: 50px;
        }

        .info-label { font-size: 0.7rem; color: #94a3b8; display: block; }
        .info-value { font-size: 1.1rem; font-weight: bold; color: white; }

        .spin-btn {
            width: 70px; height: 70px;
            border-radius: 50%;
            border: none;
            background: linear-gradient(180deg, #facc15, #a16207);
            font-weight: 900;
            cursor: pointer;
            box-shadow: 0 4px 0 #713f12;
            transition: 0.1s;
        }

        .spin-btn:active { transform: translateY(3px); box-shadow: 0 1px 0 #713f12; }
        .spin-btn:disabled { background: #475569; box-shadow: none; cursor: not-allowed; }

        #statusText {
            margin-top: 15px;
            height: 20px;
            font-size: 0.9rem;
            color: var(--gold);
            font-style: italic;
        }
    </style>
</head>
<body>

<div class="game-container">
    <div class="header-title">MAHJONG WAYS</div>
    <div class="multiplier-box">Multiplier: <span id="multValue">x1</span></div>

    <div class="slot-grid" id="mainGrid"></div>

    <div class="bottom-panel">
        <div style="text-align: left;">
            <span class="info-label">WIN</span>
            <span class="info-value" id="winAmount">0</span>
        </div>

        <button class="spin-btn" id="spinBtn" onclick="handleSpin()">SPIN</button>

        <div style="text-align: right;">
            <span class="info-label">BALANCE</span>
            <span class="info-value" id="balanceAmount">
                {{ number_format(DB::table('game_settings')->where('name', 'user_balance')->value('value') ?? 1000000, 0, ',', '.') }}
            </span>
        </div>
    </div>

    <div id="statusText"></div>
</div>

<script>
    let currentMultiplier = 1;
    const gridContainer = document.getElementById('mainGrid');

    // Inisialisasi Grid Awal
    for (let i = 0; i < 15; i++) {
        const div = document.createElement('div');
        div.className = 'symbol';
        div.innerText = '🀄';
        gridContainer.appendChild(div);
    }

    async function handleSpin() {
        const betAmount = 10000; 

        // 1. SweetAlert Konfirmasi
        const resultAlert = await Swal.fire({
            title: 'KONFIRMASI SPIN',
            text: `Pasang taruhan sebesar Rp ${betAmount.toLocaleString('id-ID')}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#facc15',
            cancelButtonColor: '#d33',
            confirmButtonText: 'GAS SPIN!',
            cancelButtonText: 'BATAL',
            background: '#064e3b',
            color: '#fff'
        });

        if (!resultAlert.isConfirmed) return;

        const btn = document.getElementById('spinBtn');
        const status = document.getElementById('statusText');
        const winDisplay = document.getElementById('winAmount');
        const balanceDisplay = document.getElementById('balanceAmount');
        const allSymbols = document.querySelectorAll('.symbol');
        
        // 2. Persiapan Animasi
        btn.disabled = true;
        status.innerText = "Memutar...";
        
        allSymbols.forEach(s => {
            s.classList.add('spinning');
            s.classList.remove('win-glow', 'gold');
        });

        try {
            // 3. Ambil Data dari Backend
            const response = await fetch('/spin-test'); 
            
            // Cek jika saldo tidak cukup (Error 400)
            if (!response.ok) {
                const errorData = await response.json();
                Swal.fire({
                    icon: 'error',
                    title: 'Waduh!',
                    text: errorData.pesan,
                    background: '#064e3b',
                    color: '#fff'
                });
                btn.disabled = false;
                allSymbols.forEach(s => s.classList.remove('spinning'));
                return;
            }

            const data = await response.json();

            // 4. Efek Berhenti Bertahap
            for (let i = 0; i < allSymbols.length; i++) {
                await new Promise(r => setTimeout(r, 50 + (i * 30)));
                allSymbols[i].classList.remove('spinning');
                allSymbols[i].innerText = data.hasil[i];

                if (data.gold_indices && data.gold_indices.includes(i)) {
                    allSymbols[i].classList.add('gold');
                }
            }

            // 5. Update Hasil Akhir
            setTimeout(() => {
                balanceDisplay.innerText = data.new_balance;

                if (data.is_win) {
                    updateMultiplier(true);
                    winDisplay.innerText = data.win_amount;
                    status.innerText = data.pesan;
                    status.style.color = "#4ade80";

                    // Glow simbol menang
                    const winSym = data.hasil[0];
                    allSymbols.forEach(s => {
                        if (s.innerText === winSym || s.innerText === '🔥') {
                            s.classList.add('win-glow');
                        }
                    });
                } else {
                    updateMultiplier(false);
                    winDisplay.innerText = "0";
                    status.innerText = data.pesan;
                    status.style.color = "#f87171";
                }
                btn.disabled = false;
            }, 200);

        } catch (error) {
            console.error("Error:", error);
            btn.disabled = false;
            allSymbols.forEach(s => s.classList.remove('spinning'));
        }
    }

    function updateMultiplier(isWin) {
        const multDisplay = document.getElementById('multValue');
        if (isWin) {
            if (currentMultiplier === 1) currentMultiplier = 2;
            else if (currentMultiplier === 2) currentMultiplier = 3;
            else if (currentMultiplier === 3) currentMultiplier = 5;
        } else {
            currentMultiplier = 1;
        }
        
        multDisplay.innerText = "x" + currentMultiplier;
        multDisplay.style.transform = "scale(1.4)";
        setTimeout(() => multDisplay.style.transform = "scale(1)", 200);
        multDisplay.style.color = (currentMultiplier === 5) ? "#facc15" : "white";
    }
</script>

</body>
</html>