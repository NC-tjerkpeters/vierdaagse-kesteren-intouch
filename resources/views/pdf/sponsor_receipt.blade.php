<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Kwitantie {{ $filename }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 16px; }
        .header-img { width: 210mm; height: auto; display: block; }
        .content { padding-top: 20px; }
        .kwitantie-title { font-size: 26px; color: #2e74b5; font-weight: 500; margin: 30px 0 0 0; }
        .highlight { font-weight: 700; color: #ff0000; margin: 15px 0 0 0; }
        table { width: 100%; border-collapse: collapse; margin: 30px 0 0 0; border: 1px solid #000; }
        th, td { padding: 8px; text-align: left; border: 1px solid #000; }
        th { background: #f5f5f5; }
        tfoot th { border-top: 2px solid #000; text-align: right; }
        .footer { text-align: center; margin-top: 40px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
<div class="content">
    @if(!empty($topBannerBase64))
        <img src="data:image/png;base64,{{ $topBannerBase64 }}" class="header-img" alt="" style="width:210mm; height:auto;">
    @endif

    <p>{{ $sponsor->bedrijfsnaam ?: '' }}</p>
    <p>{{ $sponsor->voornaam }} {{ $sponsor->achternaam }}</p>
    <p>{{ $sponsor->postcode }} {{ $sponsor->huisnummer }}</p>
    <p>{{ $sponsor->email }}</p>

    <p class="kwitantie-title">Kwitantie</p>
    <p>Kwitantie datum: {{ $datum }}</p>
    <p>Kwitantie nummer: {{ $filename }}</p>
    <p class="highlight">
        Deze kwitantie is middels iDeal betaald met transactieID: {{ $sponsor->betaling_id }}
    </p>

    <table>
        <thead>
            <tr>
                <th style="width: 80%;">Omschrijving</th>
                <th style="width: 20%;">Bedrag</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Vriend van de vierdaagse Kesteren {{ date('Y') }}</td>
                <td>€ {{ $bedrag }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <th>Totaal</th>
                <th>€ {{ $bedrag }}</th>
            </tr>
        </tfoot>
    </table>

    <p style="margin: 30px 0 0 0;">
        De Vrienden van de Vierdaagse Kesteren zijn een groep enthousiaste en loyale sponsors die ons helpen om dit prachtige evenement jaarlijks te organiseren.
    </p>

    <p style="margin: 15px 0 0 0;">Met vriendelijke groet,</p>
    <p style="margin: 0;">Namens stichting De Hoenderik</p>
    <p style="margin: 15px 0 0 0;">Tjerk Peters</p>
    <p style="margin: 0;">0640893740</p>
    <p style="margin: 0;">mail@vierdaagsekesteren.nl</p>
</div>

<div class="footer">
    Stichting De Hoenderik | Schuilenburg 2, 4041BK Kesteren | KVK-nummer: 11059622
</div>
</body>
</html>
