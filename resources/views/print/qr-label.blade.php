<!DOCTYPE html>
<html lang="ka">
<head>
    <meta charset="UTF-8">
    <title>QR Label</title>
    <script>window.onload = function () { window.print(); };</script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: 56mm 40mm;
            margin: 0mm;
        }

        html, body {
            width: 56mm;
            height: 40mm;
            overflow: hidden;
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
            padding: 1mm;
            gap: 1mm;
            overflow: hidden;
        }

        .qr {
            flex-shrink: 0;
        }

        .qr svg {
            display: block;
            width: 32mm !important;
            height: 32mm !important;
        }

        .unique-code {
            font-family: 'Courier New', Courier, monospace;
            font-size: 7pt;
            font-weight: 700;
            color: #000;
            text-align: center;
            letter-spacing: 0.5px;
            word-break: break-all;
            line-height: 1.2;
            width: 54mm;
        }

        @media print {
            @page {
                size: 56mm 40mm;
                margin: 0mm;
            }

            html, body {
                width: 56mm !important;
                height: 40mm !important;
                overflow: hidden !important;
            }
        }
    </style>
</head>
<body>
    <div class="label">
        <div class="qr">{!! $qrCode !!}</div>
        @if($uniqueCode)
            <div class="unique-code">{{ $uniqueCode }}</div>
        @endif
    </div>
</body>
</html>
