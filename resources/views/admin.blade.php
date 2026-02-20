<!DOCTYPE html>
<html>
<head>
    <title>Panel Admin Bandar</title>
    <style>
        body { font-family: sans-serif; background: #2c3e50; color: white; text-align: center; padding-top: 50px; }
        .card { background: #34495e; padding: 30px; display: inline-block; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); }
        .status { font-size: 24px; margin-bottom: 20px; }
        .btn { padding: 15px 30px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 16px; margin: 10px; }
        .btn-lose { background: #e74c3c; color: white; }
        .btn-normal { background: #2ecc71; color: white; }
    </style>
</head>
<body>
    <h1>Panel Kendali Bandar (Secret)</h1>
<div class="card">
    <p>Status Kontrol Saat Ini: 
        <strong>{{ $currentSetting->value == 0 ? 'USER PASTI KALAH' : 'USER PASTI MENANG' }}</strong>
    </p>
    
    <form action="/update-setting" method="POST">
        @csrf
        <button name="value" value="0" class="btn btn-lose">AKTIFKAN MODE KALAH</button>
        <button name="value" value="1" class="btn btn-normal">AKTIFKAN MODE MENANG</button>
    </form>
</div>
</body>
</html>