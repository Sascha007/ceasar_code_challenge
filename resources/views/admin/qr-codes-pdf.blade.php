<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Team QR-Codes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .page-break {
            page-break-after: always;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        .team-card {
            border: 1px solid #ccc;
            padding: 15px;
            text-align: center;
        }
        .team-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .qr-code {
            width: 200px;
            height: 200px;
            margin: 0 auto;
        }
        .team-url {
            font-size: 12px;
            word-break: break-all;
            margin-top: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="grid">
        @foreach($teams ?? $qrCodes ?? [] as $qrCode)
            <div class="team-card">
                <div class="team-name">{{ $qrCode['name'] }}</div>
                <div class="qr-code">
                    {!! $qrCode['qr'] !!}
                </div>
                <div class="team-url">{{ $qrCode['url'] }}</div>
            </div>
            @if($loop->iteration % 6 == 0 && !$loop->last)
                </div>
                <div class="page-break"></div>
                <div class="grid">
            @endif
        @endforeach
    </div>
</body>
</html>