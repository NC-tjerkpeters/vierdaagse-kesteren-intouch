# Communicatie naar deelnemers

Module voor het versturen van e-mails naar ingeschreven deelnemers van een editie.

## Locatie

**Inschrijvingen → Communicatie**

## Functionaliteit

- **Doelgroep filteren** – Op afstand (5 km, 10 km, …) of betaalstatus (betaald / nog niet betaald)
- **Templates** – Voorbereiding, herinnering, routes bekend, of eigen tekst
- **Plaatshouders** – `{{voornaam}}`, `{{achternaam}}`, `{{afstand}}`, `{{edition_name}}`, `{{start_datum}}`, `{{eind_datum}}`, `{{inschrijf_url}}`, `{{routes_url}}`
- **Voorvertoning** – Bekijk hoe het bericht eruitziet (op basis van eerste deelnemer uit de gefilterde lijst)
- **Verzendgeschiedenis** – Overzicht van verstuurde berichten met aantallen

## E-mailverzending

E-mails worden verstuurd via **Microsoft Graph** (zelfde integratie als tickets en sponsor-receipts). Zorg dat `MSGRAPH_*` in `.env` correct is geconfigureerd.

## Permissies

| Permissie        | Omschrijving              |
|------------------|---------------------------|
| `communicatie_view` | Pagina bekijken, voorvertoning |
| `communicatie_send` | E-mails versturen         |

## Templates aanpassen

Bewerk `config/participant_communication.php` om templates aan te passen of nieuwe toe te voegen.
