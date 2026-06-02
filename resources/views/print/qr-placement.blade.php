<!DOCTYPE html>
<html lang="ka">
<head>
    <meta charset="UTF-8">
    <title>QR კოდები</title>
    <script>window.onload = function () { window.print(); };</script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page {
            size: 56mm 40mm;
            margin: 0;
        }

        html, body {
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .label {
            width: 56mm;
            height: 40mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1mm 1mm 1mm 5mm;
            gap: 0.8mm;
            overflow: hidden;
            page-break-after: always;
        }

        .label:last-child {
            page-break-after: auto;
        }

        .label .qr svg {
            display: block;
            width: 28mm !important;
            height: 28mm !important;
        }

        .label .code {
            font-family: 'Courier New', Courier, monospace;
            font-size: 6.5pt;
            font-weight: 700;
            text-align: center;
            color: #000;
            letter-spacing: 0.3px;
            word-break: break-all;
            width: 54mm;
        }

        .label .sub {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 5.5pt;
            color: #000;
            text-align: center;
            line-height: 1.3;
            width: 54mm;
            word-break: break-word;
        }
    </style>
</head>
<body>
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
</body>
</html>
