<!DOCTYPE html>
<html lang="ka">
<head>
    <meta charset="UTF-8">
    <title>QR კოდები - A4</title>
    <script>window.onload = function () { window.print(); };</script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page { size: A4 portrait; margin: 8mm; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .grid {
            display: flex;
            flex-wrap: wrap;
            flex-direction: row;
            gap: 3mm;
        }

        .label {
            width: 56mm;
            height: 40mm;
            border: 0.3mm solid #ccc;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1mm;
            gap: 0.8mm;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .label .qr svg {
            display: block;
            width: 26mm !important;
            height: 26mm !important;
        }

        .label .code {
            font-family: 'Courier New', Courier, monospace;
            font-size: 6pt;
            font-weight: 700;
            text-align: center;
            color: #000;
            letter-spacing: 0.3px;
            word-break: break-all;
        }

        .label .sub {
            font-size: 5pt;
            color: #000;
            text-align: center;
            line-height: 1.3;
            word-break: break-word;
        }

        @media print {
            @page { size: A4 portrait; margin: 8mm; }
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
