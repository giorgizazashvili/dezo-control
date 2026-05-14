<!DOCTYPE html>
<html lang="ka">
<head>
    <meta charset="UTF-8">
    <title>QR კოდები</title>
    <script>window.onload = function () { window.print(); };</script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page { size: A4; margin: 8mm; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .grid {
            display: flex;
            flex-wrap: wrap;
            gap: 4mm;
        }

        .label {
            width: 56mm;
            height: 40mm;
            border: 1px solid #ccc;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1.5mm;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .label .qr svg {
            display: block;
            width: 22mm !important;
            height: 22mm !important;
        }

        .label .code {
            font-size: 7pt;
            font-weight: 700;
            text-align: center;
            color: #000;
        }

        .label .sub {
            font-size: 5.5pt;
            color: #444;
            text-align: center;
            line-height: 1.3;
        }
    </style>
</head>
<body>
    <div class="grid">
        @foreach ($items as $item)
            <div class="label">
                <div class="qr">{!! $item['qrCode'] !!}</div>
                <div class="code">{{ $item['uniqueCode'] }}</div>
                @if($item['name'])
                    <div class="sub">
                        {{ $item['name'] }}
                        @if($item['zone'] || $item['location'])
                            <br>{{ implode(' / ', array_filter([$item['zone'], $item['location']])) }}
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</body>
</html>
