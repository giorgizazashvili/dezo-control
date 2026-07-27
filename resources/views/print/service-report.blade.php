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

    .date-block { padding: 0; width: 100%; border-collapse: collapse; }
    .date-item {
      padding: 3px 0;
      border-bottom: 1px solid #ddd;
    }
    .date-item:last-child { border-bottom: none; }
    .date-lbl { display: inline-block; min-width: 135px; vertical-align: bottom; }
    .date-val {
      display: inline-block;
      width: 120px;
      border-bottom: 1px solid #888;
      height: 16px;
      padding: 0 3px;
      vertical-align: bottom;
    }

    .contact-block { padding: 0; width: 100%; border-collapse: collapse; }
    .contact-item {
      padding: 3px 0;
      font-size: 12px;
      border-bottom: 1px solid #ddd;
    }
    .contact-item:last-child { border-bottom: none; }
    .contact-lbl { display: inline-block; min-width: 75px; font-weight: bold; }

    .svc-desc {
      padding: 6px 2px;
      margin-bottom: 10px;
    }

    .meta-row {
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

    .photo-grid {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 14px;
    }

    .photo-cell {
      width: 50%;
      padding: 4px;
      vertical-align: top;
      text-align: center;
    }

    .photo-img {
      max-width: 100%;
      max-height: 220px;
      object-fit: contain;
      border: 1px solid #ccc;
    }

    .photo-label {
      font-size: 10px;
      color: #555;
      margin-top: 3px;
    }

    .signature-row {
      width: 100%;
      border-collapse: collapse;
      margin-top: 40px;
    }
    .signature-col {
      width: 50%;
      padding-right: 20px;
      vertical-align: top;
    }
    .signature-label {
      font-weight: bold;
      margin-bottom: 28px;
      font-size: 11px;
    }
    .signature-line {
      margin-bottom: 10px;
    }
    .signature-line-lbl {
      display: inline-block;
      white-space: nowrap;
      width: 80px;
      font-size: 11px;
      vertical-align: bottom;
    }
    .signature-line-underline {
      display: inline-block;
      width: 200px;
      border-bottom: 1px solid #000;
      height: 16px;
      vertical-align: bottom;
    }
    .signature-name-value {
      display: inline-block;
      width: 200px;
      border-bottom: 1px solid #000;
      height: 16px;
      vertical-align: bottom;
      font-size: 11px;
      padding: 0 3px 1px;
    }
    .signature-img-wrap {
      display: inline-block;
      width: 200px;
      border-bottom: 1px solid #000;
      vertical-align: bottom;
      text-align: center;
      line-height: 0;
    }
    .signature-img {
      max-width: 190px;
      max-height: 72px;
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
        <div>{{ $session->organization->legal_form }} „{{ $session->organization->name }}"</div>
        <div>ს/ბ {{ $session->organization->identification }}</div>
        <div>{{ $session->organization->address }}</div>
      </td>
    </tr>
    <tr>
      <td colspan="2" style="border-top:none; padding:5px 9px;">
        <div class="contact-block">
          <div class="contact-item"><span class="contact-lbl">ტელ:</span> 555400635</div>
          <div class="contact-item"><span class="contact-lbl">ელ. ფოსტა:</span> tako@dezo.ge</div>
          <div class="contact-item"><span class="contact-lbl">საიტი:</span> www.dezo.ge</div>
        </div>
      </td>
      <td style="border-top:none; padding:5px 9px;">
        <div class="date-block">
          <div class="date-item">
            <span class="date-lbl">თარიღი</span>
            <span class="date-val">{{ $session->started_at?->format('d.m.Y') }}</span>
          </div>
          <div class="date-item">
            <span class="date-lbl">დაწყების დრო</span>
            <span class="date-val">{{ $session->started_at?->format('H:i') }}</span>
          </div>
          <div class="date-item">
            <span class="date-lbl">დასრულების დრო</span>
            <span class="date-val">{{ $session->finished_at?->format('H:i') }}</span>
          </div>
        </div>
      </td>
    </tr>
  </table>

  <div class="meta-row">
    <div>ტექნიკოსი: <span>{{ $session->technician ?? '—' }}</span></div>
  </div>

  <div class="svc-desc">
    <strong>მომსახურების აღწერა:</strong> დეზინსექცია, დერატიზაცია
  </div>

  <div class="sec-title">ინსპექციის შეჯამება მოწყობილობების მიხედვით</div>
  <table class="rpt">
    <thead>
      <tr>
        <th style="width:36%; text-align:left">მოწყობილობის მიხედვით</th>
        <th style="width:16%">მოწყობილობის რ-ბა</th>
        <th style="width:16%">შემოწმებული %</th>
        <th style="width:16%">მავნებლის რაოდენობა</th>
        <th style="width:16%">გამოტოვებული %</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($deviceSummary as $row)
        <tr>
          <td>{{ $row['name'] }}</td>
          <td class="center">{{ $row['total'] }}</td>
          <td class="center">{{ $row['inspected_pct'] }}%</td>
          <td class="center">{{ $row['pest_quantity'] > 0 ? rtrim(rtrim(number_format($row['pest_quantity'], 4, '.', ''), '0'), '.') : '—' }}</td>
          <td class="center">{{ $row['missed_pct'] }}%</td>
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
        <th style="width:36%; text-align:left">მოწყობილობის მიხედვით</th>
        <th style="width:20%">მოწყობილობის ID</th>
        <th style="width:28%">მავნებლის ტიპი</th>
        <th style="width:16%">ჯამი</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($pestSummary as $row)
        <tr>
          <td>{{ $row['device_name'] }}</td>
          <td class="center">{{ $row['unique_code'] }}</td>
          <td class="center">{{ $row['pest_type'] }}</td>
          <td class="center">{{ $row['pest_quantity'] > 0 ? rtrim(rtrim(number_format($row['pest_quantity'], 4, '.', ''), '0'), '.') : '—' }}</td>
        </tr>
      @empty
        <tr class="empty-row"><td colspan="4">ჩანაწერები არ არის</td></tr>
      @endforelse
    </tbody>
  </table>

  <div class="sec-title">ინსპექტირება დეტალურად</div>
  <table class="rpt" style="font-size:9.5px;">
    <thead>
      <tr>
        <th style="width:8%">მოწყობილობის ID</th>
        <th style="width:15%">ადგილმდებარეობა</th>
        <th style="width:13%">მოწყობილობა</th>
        <th style="width:13%">სატყუარას მდგომარეობა</th>
        <th style="width:7%">სკანირების დრო</th>
        <th style="width:10%">სკანირების სტატუსი</th>
        <th style="width:14%">მოწყობილობის მდგომარეობა</th>
        <th style="width:7%">რაოდენობა</th>
        <th style="width:13%">რისკის დონე</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($inspectionDetails as $row)
        <tr>
          <td class="center">{{ $row['unique_code'] }}</td>
          <td>{{ $row['location'] ?: '—' }}</td>
          <td>{{ $row['device_name'] }}</td>
          <td class="center">{{ $row['bait_status'] }}</td>
          <td class="center">{{ $row['scan_time'] }}</td>
          <td class="center">{{ $row['scan_status'] }}</td>
          <td class="center">{{ $row['device_condition'] }}</td>
          <td class="center">{{ $row['pest_quantity'] }}</td>
          <td class="center">{{ $row['risk_level'] }}</td>
        </tr>
      @empty
        <tr class="empty-row"><td colspan="9">ჩანაწერები არ არის</td></tr>
      @endforelse
    </tbody>
  </table>

  @if($componentSummary->isNotEmpty())
    <div class="sec-title">გამოყენებული კომპონენტები</div>
    <table class="rpt">
      <thead>
        <tr>
          <th style="width:60%; text-align:left">კომპონენტი</th>
          <th style="width:20%">განზომილება</th>
          <th style="width:20%">ჯამური რაოდენობა</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($componentSummary as $comp)
          <tr>
            <td>{{ $comp['name'] }}</td>
            <td class="center">{{ $comp['dimension'] ?: '—' }}</td>
            <td class="center">{{ rtrim(rtrim(number_format((float) $comp['quantity'], 4, '.', ''), '0'), '.') }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @endif


  @if ($session->notes)
    <div class="sec-title">შენიშვნა</div>
    <div class="notes-block">{{ $session->notes }}</div>
  @endif

  @php
      $encodePhoto = function ($photo) {
          $path = \Illuminate\Support\Facades\Storage::disk('public')->path($photo);
          if (! is_file($path)) {
              return null;
          }
          $mime = function_exists('mime_content_type') ? mime_content_type($path) : 'image/jpeg';
          $contents = file_get_contents($path);

          // iPhone photos store rotation as EXIF metadata instead of rotating the
          // pixel data, and dompdf ignores it — correct it here before rendering.
          if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
              $orientation = @exif_read_data($path)['Orientation'] ?? 1;

              if (in_array($orientation, [3, 6, 8], true)) {
                  $image = @imagecreatefromstring($contents);

                  if ($image !== false) {
                      $angle = match ($orientation) {
                          3 => 180,
                          6 => -90,
                          8 => 90,
                          default => 0,
                      };
                      $rotated = imagerotate($image, $angle, 0);
                      imagedestroy($image);

                      ob_start();
                      imagejpeg($rotated, null, 90);
                      $contents = ob_get_clean();
                      imagedestroy($rotated);
                  }
              }
          }

          return 'data:'.$mime.';base64,'.base64_encode($contents);
      };

      $photoItems = collect($session->photos ?? [])
          ->map(fn ($photo) => ['src' => $encodePhoto($photo), 'label' => null]);

      $monitoringPhotoItems = $session->monitorings
          ->flatMap(function ($m) use ($encodePhoto) {
              $code = $m->logs->first()?->unique_code;
              $name = $m->movementProductItem?->productSettlement?->name;
              $label = trim(implode(' — ', array_filter([$code, $name]))) ?: null;

              return collect($m->inspection_photos ?? [])
                  ->map(fn ($photo) => ['src' => $encodePhoto($photo), 'label' => $label]);
          });

      $photoItems = $photoItems
          ->concat($monitoringPhotoItems)
          ->filter(fn ($item) => $item['src'])
          ->values();
  @endphp

  @if ($photoItems->isNotEmpty())
    <div class="sec-title">ფოტოები</div>
    <table class="photo-grid">
      @foreach ($photoItems->chunk(2) as $row)
        <tr>
          @foreach ($row as $item)
            <td class="photo-cell">
              <img class="photo-img" src="{{ $item['src'] }}">
              @if ($item['label'])
                <div class="photo-label">{{ $item['label'] }}</div>
              @endif
            </td>
          @endforeach
          @if ($row->count() < 2)
            <td class="photo-cell"></td>
          @endif
        </tr>
      @endforeach
    </table>
  @endif

  <table class="signature-row">
    <tr>
      <td class="signature-col">
        <div class="signature-label">მომსახურების გამწევი:</div>
        <div class="signature-line">
          <span class="signature-line-lbl">სახელი/გვარი</span>
          @if($user?->name)
            <span class="signature-name-value">{{ $user->name }}</span>
          @else
            <span class="signature-line-underline"></span>
          @endif
        </div>
        <div class="signature-line">
          <span class="signature-line-lbl">ხელმოწერა</span>
          @if($user?->signature)
            <span class="signature-img-wrap"><img class="signature-img" src="{{ $user->signature }}"></span>
          @else
            <span class="signature-line-underline"></span>
          @endif
        </div>
      </td>
      <td class="signature-col">
        <div class="signature-label">მომსახურების მიმღები:</div>
        <div class="signature-line">
          <span class="signature-line-lbl">სახელი/გვარი</span>
          <span class="signature-line-underline"></span>
        </div>
        <div class="signature-line">
          <span class="signature-line-lbl">ხელმოწერა</span>
          <span class="signature-line-underline"></span>
        </div>
      </td>
    </tr>
  </table>

</div>
</body>
</html>
