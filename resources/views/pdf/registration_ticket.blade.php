<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <title>{{ $registration->qr_code }}</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; }
    .serif { font-family: DejaVu Serif, serif; }
    .content-wrap { page-break-inside: avoid; }
  </style>
</head>
<body>
<div class="content-wrap">

  {{-- Banner + nummer in wit vlak --}}
  <div style="position:relative; width:100%;">

    @if(!empty($topBannerBase64))
      <img
        src="data:image/png;base64,{{ $topBannerBase64 }}"
        alt=""
        style="width:210mm; height:300px; display:block;"
      >
    @endif

    {{-- Nummer in het witte vlak --}}
    @if($registration->wants_medal && $registration->medal_number)
      <div style="
        position:absolute;
        top:180px;           /* DIT bepaalt positie in wit vlak */
        left:0;
        width:100%;
        text-align:center;
        font-size:110px;
        font-weight:bold;
        color:#ff0000;
        line-height:1;
      ">
        {{ $registration->medal_number }}
      </div>
    @endif

  </div>

  {{-- Ruimte onder banner --}}
  <div style="height:40px;"></div>

  {{-- Titel gecentreerd --}}
  <div class="serif" style="text-align:center; margin-bottom:50px; line-height:1.2;">
    <div style="font-size:48px;">
      {{ $registration->first_name }} {{ $registration->last_name }}
    </div>
    <div style="font-size:42px;">
      Afstand: {{ $registration->distance->name }}
    </div>
    <div style="font-size:42px;">
      Medaille: {{ $registration->wants_medal ? 'Ja' : 'Nee' }}
    </div>
  </div>

  {{-- Onderste layout --}}
  <table style="width:100%; border-collapse:collapse;">
    <tr>
      <td style="width:50%; vertical-align:top; padding: 0 20px 0 60px;">

        <div style="font-size:22px; font-weight:bold; margin-bottom:4px;">
          Deelnemer:
        </div>
        <div style="font-size:20px; margin-bottom:14px; word-break:break-all;">
          {{ $registration->qr_code }}
        </div>

        <div style="font-size:22px; font-weight:bold; margin-bottom:4px;">
          Deelname:
        </div>
        <div style="font-size:20px; margin-bottom:18px;">
          {{ $registration->medal_number ?? '—' }}
        </div>

        <div style="font-size:22px; font-weight:bold; margin-bottom:6px;">
          Noodnummers:
        </div>
        @foreach(explode(', ', \App\Services\AppSettings::appNoodnummers()) as $num)
          <div style="font-size:20px; line-height:1.2;">
            {{ trim($num) }}
          </div>
        @endforeach

      </td>

      <td style="width:50%; vertical-align:top; padding: 0 60px 0 20px; text-align:right;">

        @if(!empty($qrImageBase64))
          <img
            src="data:image/png;base64,{{ $qrImageBase64 }}"
            alt="QR"
            style="width:220px; height:220px; display:inline-block;"
          >
        @endif

        @if(!empty($birdBase64))
          <div style="margin-top:14px;">
            <img
              src="data:image/png;base64,{{ $birdBase64 }}"
              alt=""
              style="width:220px; display:inline-block;"
            >
          </div>
        @endif

      </td>
    </tr>
  </table>

  {{-- Footer --}}
  <div style="text-align:center; margin-top:50px;">
    <a href="https://www.vierdaagsekesteren.nl"
       style="font-size:18px; color:blue; text-decoration:none;">
      www.vierdaagsekesteren.nl
    </a>
  </div>

</div>
</body>
</html>