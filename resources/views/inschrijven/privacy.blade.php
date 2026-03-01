<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Privacyverklaring – Vierdaagse Kesteren</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="mb-4">Privacyverklaring</h1>
                    <p class="text-muted small">Laatst bijgewerkt: {{ now()->format('d-m-Y') }}</p>

                    <h2 class="h5 mt-4">1. Wie zijn wij?</h2>
                    <p>Vierdaagse Kesteren verwerkt persoonsgegevens van deelnemers en sponsors in het kader van de organisatie van het evenement. Voor vragen over deze privacyverklaring kunt u contact opnemen via de website.</p>

                    <h2 class="h5 mt-4">2. Welke gegevens verwerken we?</h2>
                    <p>Bij inschrijving als deelnemer of sponsor vragen we om:</p>
                    <ul>
                        <li>Naam en contactgegevens (e-mailadres, telefoonnummer, adres)</li>
                        <li>Inschrijvingsgegevens (afstand, medaille-informatie)</li>
                        <li>Betalingsgegevens (worden verwerkt door Mollie, wij ontvangen alleen betalingsstatus)</li>
                    </ul>

                    <h2 class="h5 mt-4">3. Waarvoor gebruiken we deze gegevens?</h2>
                    <ul>
                        <li>Uitvoering van de inschrijving en betaling</li>
                        <li>Versturen van het ticket en eventuele communicatie over het evenement</li>
                        <li>Registratie van aanwezigheid tijdens de wandelavonden (QR-code)</li>
                        <li>Administratie en verantwoording (sponsors: kwitanties)</li>
                    </ul>

                    <h2 class="h5 mt-4">4. Bewaartermijn</h2>
                    <p>We bewaren uw gegevens niet langer dan noodzakelijk voor het bovenstaande doel en eventuele wettelijke bewaarverplichtingen.</p>

                    <h2 class="h5 mt-4">5. Delen met derden</h2>
                    <p>Gegevens worden gedeeld met:</p>
                    <ul>
                        <li><strong>Mollie</strong> – voor betalingsverwerking</li>
                        <li><strong>Microsoft</strong> – voor het versturen van e-mails (tickets, kwitanties)</li>
                    </ul>
                    <p>Wij sluiten verwerkersovereenkomsten af met deze partijen.</p>

                    <h2 class="h5 mt-4">6. Uw rechten</h2>
                    <p>U hebt het recht om uw gegevens in te zien, te laten wijzigen of te laten verwijderen. Neem daarvoor contact met ons op.</p>

                    <h2 class="h5 mt-4">7. Klacht</h2>
                    <p>Heeft u een klacht over de verwerking van uw gegevens? U kunt een klacht indienen bij de Autoriteit Persoonsgegevens.</p>

                    <p class="mt-4">
                        <a href="{{ url()->previous() ?? route('inschrijven.create') }}" class="btn btn-outline-secondary">Terug</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
