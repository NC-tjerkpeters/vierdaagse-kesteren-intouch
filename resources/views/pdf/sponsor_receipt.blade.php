<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<title>Kwitantie {{ $filename }}</title>

<style>
@page { margin: 0; }

body{
    margin:0;
    padding:0;
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size:16px;
    color:#000;
}

/* reset p ruimte (Dompdf geeft anders vaak te veel witruimte) */
p{ margin: 0 0 6px 0; }

/* banner */
.header-img{ width:100%; display:block; }

/* content: compacter zodat alles op 1 pagina past */
.page{
    padding: 28px 44px 78px 44px; /* was groter */
}

.kwitantie-title{
    font-size:26px;
    color:#2e74b5;
    font-weight:500;
    margin: 18px 0 10px 0;
}

.highlight{
    font-weight:700;
    color:#ff0000;
    margin: 10px 0 10px 0;
    line-height: 1.15;
}

table{
    width:100%;
    border-collapse:collapse;
    margin: 12px 0 12px 0;
    border:1px solid #000;
}

th,td{ padding:7px; border:1px solid #000; }
th{ background:#f5f5f5; text-align:left; }
tfoot th{ border-top:2px solid #000; text-align:right; }

/* footer vast onderaan */
.footer{
    position:fixed;
    bottom:0;
    left:0;
    right:0;
    height:40px;
    line-height:40px;
    text-align:center;
    font-size:12px;
    color:#666;
}
</style>
</head>

<body>

@if(!empty($topBannerBase64))
<img src="data:image/png;base64,{{ $topBannerBase64 }}" class="header-img" alt="">
@endif

<div class="page">

    <p>{{ $sponsor->bedrijfsnaam ?? '' }}</p>
    <p>{{ $sponsor->voornaam }} {{ $sponsor->achternaam }}</p>
    <p>{{ $sponsor->postcode }} {{ $sponsor->huisnummer }}</p>
    <p>{{ $sponsor->email }}</p>

    <div class="kwitantie-title">Kwitantie</div>

    <p>Kwitantie datum: {{ $datum }}</p>
    <p>Kwitantie nummer: {{ $filename }}</p>

    <div class="highlight">
        Deze kwitantie is middels iDeal betaald met transactieID:
        {{ $sponsor->betaling_id }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:80%;">Omschrijving</th>
                <th style="width:20%;">Bedrag</th>
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

    <p style="margin-top:10px;">
        De Vrienden van de Vierdaagse Kesteren zijn een groep enthousiaste en loyale sponsors die ons helpen om dit prachtige evenement jaarlijks te organiseren.
    </p>

    <p style="margin-top:10px;">Met vriendelijke groet,</p>
    <p>Namens stichting De Hoenderik</p>

    <p style="margin-top:10px;">
        Tjerk Peters<br>
        0640893740<br>
        mail@vierdaagsekesteren.nl
    </p>

</div>

<div class="footer">
    Stichting De Hoenderik | Schuilenburg 2, 4041BK Kesteren | KVK-nummer: 11059622
</div>

</body>
</html>