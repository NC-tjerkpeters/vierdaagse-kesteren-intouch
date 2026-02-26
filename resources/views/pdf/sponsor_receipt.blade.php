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

.header-img{ width:100%; display:block; }

/* Content (ruimte voor footer inbegrepen) */
.page{
  padding: 26px 44px 92px 44px;
}

.small-gap{ margin-top: 10px; }
.mid-gap{ margin-top: 16px; }
.big-gap{ margin-top: 22px; }

.addr p{ margin: 0 0 5px 0; }

.kwitantie-title{
  font-size: 26px;
  color: #2e74b5;
  font-weight: 500;
  margin: 0;
}

.meta p{ margin: 0 0 6px 0; }

.highlight{
  margin-top: 10px;
  font-weight: 700;
  color: #ff0000;
  line-height: 1.15;
}

table{
  width:100%;
  border-collapse: collapse;
  margin-top: 12px;
  border: 1px solid #000;
}
th, td{ padding: 7px; border: 1px solid #000; }
th{ background:#f5f5f5; text-align:left; }
tfoot th{ border-top: 2px solid #000; text-align:right; }

.paragraph{
  margin-top: 14px;
  line-height: 1.25;
}

.signoff p{ margin: 0 0 5px 0; }

/* Footer */
.footer{
  position: fixed;
  left: 0; right: 0; bottom: 0;
  height: 45px;
  line-height: 45px;
  text-align: center;
  font-size: 12px;
  color: #777;
  background: #fff;
}
</style>
</head>

<body>

@if(!empty($topBannerBase64))
  <img src="data:image/png;base64,{{ $topBannerBase64 }}" class="header-img" alt="">
@endif

<div class="page">

  <!-- Adresblok -->
  <div class="addr">
    <p>{{ $sponsor->bedrijfsnaam ?? '' }}</p>
    <p>{{ $sponsor->voornaam }} {{ $sponsor->achternaam }}</p>
    <p>{{ $sponsor->postcode }} {{ $sponsor->huisnummer }}</p>
    <p>{{ $sponsor->email }}</p>
  </div>

  <!-- Titel + meta -->
  <div class="big-gap">
    <p class="kwitantie-title">Kwitantie</p>
  </div>

  <div class="meta small-gap">
    <p>Kwitantie datum: {{ $datum }}</p>
    <p>Kwitantie nummer: {{ $filename }}</p>
  </div>

  <!-- Highlight -->
  <div class="highlight">
    Deze kwitantie is middels iDeal betaald met transactieID: {{ $sponsor->betaling_id }}
  </div>

  <!-- Tabel -->
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

  <!-- Tekst -->
  <p class="paragraph">
    De Vrienden van de Vierdaagse Kesteren zijn een groep enthousiaste en loyale sponsors die ons helpen om dit prachtige evenement jaarlijks te organiseren.
  </p>

  <!-- Afsluiting -->
  <div class="signoff mid-gap">
    <p>Met vriendelijke groet,</p>
    <p>Namens stichting De Hoenderik</p>
  </div>

  <div class="signoff small-gap">
    <p>Tjerk Peters</p>
    <p>0640893740</p>
    <p>mail@vierdaagsekesteren.nl</p>
  </div>

</div>

<div class="footer">
  Stichting De Hoenderik | Schuilenburg 2, 4041BK Kesteren | KVK-nummer: 11059622
</div>

</body>
</html>