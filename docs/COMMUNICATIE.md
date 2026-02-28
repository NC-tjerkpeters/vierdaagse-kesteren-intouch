# Communicatie naar deelnemers

Module voor het versturen van e-mails naar ingeschreven deelnemers van een editie.

## Locatie

**Inschrijvingen → Communicatie**

## Functionaliteit

- **Doelgroep filteren** – Op afstand (5 km, 10 km, …) of betaalstatus (betaald / nog niet betaald)
- **Templates** – Beheer templates via **Templates beheren**; HTML toegestaan
- **Algemeen bericht** – Eigen tekst zonder template
- **Bijlage** – Optioneel bestand toevoegen (PDF, afbeelding, Word, max 10 MB)
- **Plaatshouders** – `{{voornaam}}`, `{{achternaam}}`, `{{afstand}}`, `{{edition_name}}`, `{{start_datum}}`, `{{eind_datum}}`, `{{inschrijf_url}}`, `{{routes_url}}`
- **Voorvertoning** – Bekijk hoe het bericht eruitziet
- **Verzendgeschiedenis** – Overzicht van verstuurde berichten

## Templates beheren

Via **Inschrijvingen → Communicatie → Templates beheren** kun je:

- Nieuwe templates aanmaken
- Bestaande bewerken (naam, onderwerp, bericht)
- Templates verwijderen

Het berichtveld ondersteunt **HTML** (`<p>`, `<strong>`, `<a>`, `<ul>`, `<li>`, etc.). Gebruik de plaatshouders voor persoonlijke inhoud.

## E-mailverzending

E-mails worden verstuurd via **Microsoft Graph** (zelfde integratie als tickets en sponsor-receipts). Zorg dat `MSGRAPH_*` in `.env` correct is geconfigureerd.

**Queue**: E-mails worden op de achtergrond verstuurd via Laravel's queue. Voorkomt timeouts bij veel ontvangers.
- Zet `QUEUE_CONNECTION=database` in `.env`
- Run `php artisan queue:work` (of gebruik Supervisor in productie)
- Voortgang is zichtbaar in de verzendgeschiedenis

## Permissies

| Permissie             | Omschrijving                    |
|-----------------------|---------------------------------|
| `communicatie_view`   | Pagina bekijken, voorvertoning  |
| `communicatie_send`   | E-mails versturen               |
| `communicatie_templates` | Templates beheren, aanmaken, bewerken |
