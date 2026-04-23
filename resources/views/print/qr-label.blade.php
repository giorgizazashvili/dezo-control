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
            margin: 0;
        }

        html, body {
            width: 56mm;
            height: 40mm;
            max-height: 40mm;
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
            gap: 1.5mm;
            overflow: hidden;
        }

        .qr svg {
            display: block;
            width: 24mm !important;
            height: 24mm !important;
        }

        .name {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 6pt;
            font-weight: 700;
            text-align: center;
            line-height: 1.2;
            width: 54mm;
            word-break: break-word;
            color: #000;
        }

        .qty {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 5.5pt;
            font-weight: 400;
            text-align: center;
            color: #000;
        }

        @media print {
            @page {
                size: 56mm 40mm;
                margin: 0;
            }

            html, body {
                width: 56mm !important;
                height: 40mm !important;
                max-height: 40mm !important;
                overflow: hidden !important;
            }
        }
    </style>
</head>
<body>
    <div class="label">
        <div class="qr">{!! $qrCode !!}</div>
        @if($uniqueCode)
            <div class="name">{{ $uniqueCode }}</div>
        @endif
    </div>
</body>
</html>
