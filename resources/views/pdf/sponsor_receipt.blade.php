<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<title>Kwitantie {{ $filename }}</title>

<style>
@page { margin: 0; }

body{
  margin:0; padding:0;
  font-family: DejaVu Sans, Arial, sans-serif;
  font-size:16px; color:#000;
}

p{ margin: 0 0 6px 0; }
.header-img{ width:100%; display:block; }

/* BELANGRIJK: reserveer ruimte voor footer */
.page{
  padding: 28px 44px 110px 44px; /* bottom padding omhoog */
}

.footer{
  position: fixed;
  left: 0;
  right: 0;

  /* Zet ’m iets omhoog zodat hij niet in de “niet-printbare” zone valt */
  bottom: 12mm;

  padding: 6px 0;
  text-align: center;
  font-size: 12px;
  color: #666;

  /* Zorgt dat tekst altijd leesbaar is */
  background: #fff;
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