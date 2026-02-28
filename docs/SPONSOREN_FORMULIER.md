# Vrienden/sponsoren formulier

Het vrienden-formulier op vierdaagsekesteren.nl kan naar de Laravel-app wijzen in plaats van de oude PHP-scripts.

## Form action aanpassen

Wijzig de form action van:
```
https://api.vierdaagsekesteren.nl/vrienden-van-de-vierdaagse-kesteren/payment_processing.php
```

naar:
```
https://inschrijven.vierdaagsekesteren.nl/vrienden/aanmelden
```

*(Vervang `inschrijven.vierdaagsekesteren.nl` door je daadwerkelijke inschrijf-domein als dat anders is.)*

De route staat op het **inschrijven-domein**; CSRF is uitgeschakeld voor dit endpoint omdat het formulier op een ander domein (vierdaagsekesteren.nl) staat.

## Form HTML (voorbeeld)

```html
<form action="https://inschrijven.vierdaagsekesteren.nl/vrienden/aanmelden" method="POST">
  <div class="form-floating mb-3">
    <input class="form-control" name="bedrijfsnaam" id="bedrijfsnaam" type="text" placeholder="Bedrijfsnaam">
    <label for="bedrijfsnaam">Bedrijfsnaam</label>
  </div>

  <div class="form-floating mb-3">
    <input class="form-control" name="voornaam" id="voornaam" type="text" placeholder="Voornaam" required>
    <label for="voornaam">Voornaam</label>
  </div>

  <div class="form-floating mb-3">
    <input class="form-control" name="achternaam" id="achternaam" type="text" placeholder="Achternaam" required>
    <label for="achternaam">Achternaam</label>
  </div>

  <div class="form-floating mb-3">
    <input class="form-control" name="postcode" id="postcode"
           type="text" placeholder="1234 AB"
           pattern="^[1-9][0-9]{3}\s?[A-Za-z]{2}$"
           style="text-transform:uppercase" required>
    <label for="postcode">Postcode</label>
  </div>

  <div class="form-floating mb-3">
    <input class="form-control" name="huisnummer" id="huisnummer" type="text" placeholder="Huisnummer" required>
    <label for="huisnummer">Huisnummer</label>
  </div>

  <div class="form-floating mb-3">
    <input class="form-control" name="telefoonnummer" id="telefoonnummer"
           type="tel" placeholder="Telefoonnummer"
           pattern="^[0-9+\-\s]{6,20}$" required>
    <label for="telefoonnummer">Telefoonnummer</label>
  </div>

  <div class="form-floating mb-3">
    <input class="form-control" name="email" id="email" type="email" placeholder="E-mail" required>
    <label for="email">E-mail</label>
  </div>

  <div class="form-floating mb-3">
    <input class="form-control" name="bedrag" id="bedrag"
           type="number" step="0.01" min="1"
           inputmode="decimal" placeholder="Bedrag" required>
    <label for="bedrag">Bedrag (€)</label>
  </div>

  <div class="d-grid">
    <button class="btn btn-primary btn-lg" type="submit"
            onclick="this.disabled=true; this.form.submit();">
      Aanmelden en betalen
    </button>
  </div>
</form>
```

**Let op:** `bedrijfsnaam` is optioneel (geen `required`). De overige velden zijn verplicht.

## Configuratie (.env)

| Variabele | Beschrijving |
|-----------|--------------|
| `SPONSORS_REDIRECT_URL` | Waar de gebruiker na succesvolle betaling naartoe gaat (standaard: bedankpagina op vierdaagsekesteren.nl) |
| `SPONSORS_WEBHOOK_URL` | Optioneel: volledige webhook-URL (anders: APP_URL + `/webhooks/mollie/sponsors`) |
| `SPONSORS_RECEIPT_BCC` | BCC-adres voor ontvangstbevestiging per e-mail |
| `SPONSORS_DOELBEDRAG` | Doelbedrag voor de voortgangsbalk in Intouch (ook aanpasbaar via Beheer → Instellingen) |
| `MOLLIE_KEY` | Dezelfde key als voor inschrijvingen (live key in productie) |

## Mollie webhook

De webhook wordt **per betaling** ingesteld; je hoeft geen extra webhook in het Mollie-dashboard te registreren. De URL wordt automatisch meegegeven bij het aanmaken van de betaling.

De webhook-URL is: `https://inschrijven.vierdaagsekesteren.nl/webhooks/mollie/sponsors`  
Standaard: `APP_URL` + `/webhooks/mollie/sponsors`. Zet `SPONSORS_WEBHOOK_URL` alleen als de webhook op een ander domein moet uitkomen.

## Intouch

In **Intouch → Sponsors** beheer je de betalingen en ontvangstbevestigingen. Het doelbedrag en andere instellingen kun je aanpassen via **Beheer → Instellingen**.

## Oude PHP-scripts

De bestanden `payment_processing.php` en `payment_webhook.php` kunnen worden uitgeschakeld of verwijderd zodra het formulier naar Laravel wijst en de eerste betalingen succesvol verlopen.
