<!DOCTYPE html>
<html lang="ka">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>მომსახურების რეპორტი</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: Arial, 'DejaVu Sans', sans-serif;
      font-size: 11px;
      color: #000;
      background: #fff;
      padding: 24px;
    }

    .page { max-width: 830px; margin: 0 auto; }

    .title {
      text-align: center;
      font-size: 15px;
      font-weight: bold;
      margin-bottom: 12px;
    }

    .hdr {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .hdr td {
      border: 1px solid #aaa;
      padding: 7px 9px;
      vertical-align: top;
    }
    .hdr .logo-td {
      text-align: center;
      vertical-align: middle;
      width: 155px;
    }

    .logo-top {
      font-size: 28px;
      font-weight: 900;
      color: #cc0000;
      letter-spacing: -1px;
      line-height: 1;
    }
    .logo-bot {
      font-size: 11.5px;
      font-weight: bold;
      color: #333;
      letter-spacing: 5px;
      border-top: 2px solid #cc0000;
      padding-top: 3px;
      margin-top: 2px;
    }

    .date-block { padding: 0; }
    .date-item {
      display: flex;
      align-items: center;
      padding: 3px 0;
      border-bottom: 1px solid #ddd;
    }
    .date-item:last-child { border-bottom: none; }
    .date-lbl { min-width: 135px; }
    .date-val {
      flex: 1;
      border-bottom: 1px solid #888;
      height: 16px;
      margin-right: 5px;
      padding: 0 3px;
    }

    .svc-desc {
      padding: 6px 2px;
      margin-bottom: 10px;
    }

    .meta-row {
      display: flex;
      gap: 20px;
      padding: 4px 2px;
      margin-bottom: 10px;
      font-size: 11px;
    }
    .meta-row span { font-weight: bold; }

    .sec-title {
      font-weight: bold;
      padding: 5px 8px;
      border: 1px solid #aaa;
      border-bottom: none;
      background: #f0f0f0;
    }

    .rpt {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 14px;
      font-size: 10.5px;
    }
    .rpt th {
      background: #e0e0e0;
      border: 1px solid #aaa;
      padding: 5px 7px;
      text-align: center;
      font-weight: bold;
    }
    .rpt td {
      border: 1px solid #aaa;
      padding: 5px 7px;
    }
    .rpt tr:nth-child(even) td { background: #f7f7f7; }
    .center { text-align: center; }
    .empty-row td { color: #aaa; font-style: italic; text-align: center; }

    .notes-block {
      border: 1px solid #aaa;
      padding: 8px 10px;
      min-height: 40px;
      margin-bottom: 14px;
      font-size: 11px;
    }
  </style>
</head>
@php
    $logoPath = public_path('images/logo.jpeg');
    $logoSrc = file_exists($logoPath)
        ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath))
        : '';
@endphp
<body>
<div class="page">

  <div class="title">მომსახურების რეპორტი</div>

  <table class="hdr">
    <tr>
      <td style="width:35%">
        <div style="font-weight:bold; margin-bottom:3px">მომსახურების გამწევი</div>
        <div>შპს "დეზო სერვისი"</div>
        <div>ს/ნ 402307801</div>
        <div>თბილისი, მ. მამარდაშვილის 32</div>
      </td>
      <td class="logo-td">
        @if($logoSrc)
          <img src="{{ $logoSrc }}" alt="DEZO SERVICE" style="max-width:140px; max-height:80px; object-fit:contain;">
        @endif
      </td>
      <td style="width:35%">
        <div style="font-weight:bold; margin-bottom:3px">მომსახურების მიმღები:</div>
        <div>{{ $monitoring->organization->legal_form }} „{{ $monitoring->organization->name }}"</div>
        <div>ს/ბ {{ $monitoring->organization->identification }}</div>
        <div>{{ $monitoring->organization->address }}</div>
      </td>
    </tr>
    <tr>
      <td colspan="2" style="border-top:none; padding:0;"></td>
      <td style="border-top:none; padding:5px 9px;">
        <div class="date-block">
          <div class="date-item">
            <span class="date-lbl">თარიღი</span>
            <span class="date-val">{{ $monitoring->created_at->format('d.m.Y') }}</span>
          </div>
          <div class="date-item">
            <span class="date-lbl">დაწყების დრო</span>
            <span class="date-val">{{ $monitoring->started_at?->format('H:i') }}</span>
          </div>
          <div class="date-item">
            <span class="date-lbl">დასრულების დრო</span>
            <span class="date-val">{{ $monitoring->finished_at?->format('H:i') }}</span>
          </div>
        </div>
      </td>
    </tr>
  </table>

  <div class="meta-row">
    <div>ტექნიკოსი: <span>{{ $monitoring->technician ?? '—' }}</span></div>
  </div>

  <div class="svc-desc">
    <strong>მომსახურების აღწერა:</strong> დეზინსექცია, დერატიზაცია, ბუების კონტროლი
  </div>

  <div class="sec-title">ინსპექციის შეჯამება მოწყობილობების მიხედვით</div>
  <table class="rpt">
    <thead>
      <tr>
        <th style="width:38%; text-align:left">მოწყობილობის მიხედვით</th>
        <th style="width:15%">მოწყობილობის რ-ბა</th>
        <th style="width:15%">შემოწმებული</th>
        <th style="width:16%">აქტიური</th>
        <th style="width:16%">გამოცვლილი</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($deviceSummary as $row)
        <tr>
          <td>{{ $row['name'] }}</td>
          <td class="center">{{ $row['total'] }}</td>
          <td class="center">{{ $row['inspected'] }}</td>
          <td class="center">{{ $row['active'] ?: '—' }}</td>
          <td class="center">{{ $row['replaced'] ?: '—' }}</td>
        </tr>
      @empty
        <tr class="empty-row"><td colspan="5">ჩანაწერები არ არის</td></tr>
      @endforelse
    </tbody>
  </table>

  <div class="sec-title">ინსპექციის შეჯამება:</div>
  <table class="rpt">
    <thead>
      <tr>
        <th style="width:40%; text-align:left">მოწყობილობის მიხედვით</th>
        <th style="width:20%">მოწყობილობის ID</th>
        <th style="width:25%">მავნებლის ტიპი</th>
        <th style="width:15%">ჯამი</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($pestLogs as $log)
        <tr>
          <td>{{ $log->movementProductItem?->productSettlement?->name ?? '—' }}</td>
          <td class="center">{{ $log->unique_code ?? '—' }}</td>
          <td class="center">{{ $log->pest_type ?? '—' }}</td>
          <td class="center">{{ $log->pest_quantity ? rtrim(rtrim(number_format((float) $log->pest_quantity, 4, '.', ''), '0'), '.') : '—' }}</td>
        </tr>
      @empty
        <tr class="empty-row"><td colspan="4">მავნებლები არ დაფიქსირებულა</td></tr>
      @endforelse
    </tbody>
  </table>

  @if ($monitoring->notes)
    <div class="sec-title">შენიშვნა</div>
    <div class="notes-block">{{ $monitoring->notes }}</div>
  @endif

</div>
</body>
</html>
