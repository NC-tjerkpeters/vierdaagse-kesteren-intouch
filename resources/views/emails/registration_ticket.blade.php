<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Ticket Vierdaagse Kesteren</title>
</head>
<body>
    <p>Beste {{ $registration->first_name }},</p>

    <p>
        Bedankt voor je inschrijving voor de Vierdaagse Kesteren.
        In de bijlage vind je jouw ticket als PDF, inclusief QR-code.
    </p>

    <p>
        De belangrijkste gegevens op een rij:
    </p>

    <ul>
        <li><strong>Naam</strong>: {{ $registration->first_name }} {{ $registration->last_name }}</li>
        <li><strong>Afstand</strong>: {{ $registration->distance->name }}</li>
        <li><strong>Medaille</strong>: {{ $registration->wants_medal ? 'Ja' : 'Nee' }}</li>
        @if($registration->wants_medal && $registration->medal_number)
            <li><strong>Medaillenummer</strong>: {{ $registration->medal_number }}</li>
        @endif
    </ul>

    <p>
        Neem dit ticket (digitaal of geprint) mee naar de start. De QR-code op het ticket wordt gebruikt
        om je aanwezigheid te registreren.
    </p>

    <p>Met sportieve groet,<br>
        Vierdaagse Kesteren</p>
</body>
</html>

