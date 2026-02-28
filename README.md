# Vierdaagse Kesteren – Intouch

Beheersysteem voor de Avondvierdaagse Kesteren. Inschrijvingen, sponsors, loopoverzicht, financiën, QR-scanner en gebruikersbeheer.

## Overzicht

De app bestaat uit vier domeinen:

| Domein | Functie |
|--------|---------|
| **Inschrijven** | Inschrijfformulier deelnemers, sponsorformulier (vrienden), Mollie-betaling, PDF-ticket per e-mail |
| **Intouch** | Beheerportaal: dashboard, sponsors, inschrijvingen, loopoverzicht, financiën, gebruikers, rollen, edities, instellingen |
| **Scanner** | QR-code scanner voor start/post/finish tijdens het evenement |
| **Routes** | Publieke wandelroutes met PDF-download en controlepunten afstrepen |

### Intouch-modules

- **Dashboard** – Overzicht inschrijvingen, medailles, eindsaldo, sponsors-voortgang
- **Sponsors** – Beheer vrienden van de vierdaagse (betalingen via Mollie)
- **Routes** – Wandelroutes per editie; routebibliotheek voor hergebruik over edities
- **Inschrijvingen** – Overzicht, medaille-bestelling, export, medaille-informatie achteraf wijzigen
- **Loopoverzicht** – Wie heeft gestart/post/finish gescand
- **Financiën** – Startsaldo, opbrengsten, kosten, Mollie-schatting, bank/kas
- **Beheer** – Afstanden, gebruikers, rollen, edities, systeeminstellingen
- **Mijn profiel** – Naam, e-mail, wachtwoord wijzigen

### Systeeminstellingen (Beheer → Instellingen)

Aanpasbaar via het beheerportaal (geen .env nodig):

- Sponsors doelbedrag
- Mollie transactiekosten per betaalmethode
- Scanner: min. minuten tussen scans, puntnamen
- Noodnummers op tickets

## Technisch

- **Laravel 12** met PHP 8.2+
- **MySQL** of SQLite
- **Mollie** voor betalingen (inschrijvingen + sponsors)
- **Microsoft Graph** voor Intouch-e-mail (tickets)
- **DomPDF** voor PDF-tickets

## Installatie

Zie [INSTALL.md](INSTALL.md) voor de volledige installatiehandleiding op SiteGround of andere hosting.

Kort overzicht:

```bash
composer install --no-dev
cp .env.example .env
php artisan key:generate
# .env invullen: database, domeinen, Mollie, MS Graph
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
```

Standaard inlog na seed: `admin@vierdaagsekesteren.nl` / `wijzig-dit-wachtwoord`

## Lokaal development

```bash
git clone <repository-url>
cd vierdaagse-kesteren-intouch
composer install
cp .env.example .env
php artisan key:generate
# .env: DB_CONNECTION=sqlite, DB_DATABASE=database/database.sqlite
php artisan migrate
php artisan db:seed
php artisan storage:link
```

Voor multi-domein lokaal: `INSCHRIJVEN_DOMAIN`, `INTOUCH_DOMAIN`, `SCANNER_DOMAIN` en `ROUTES_DOMAIN` op `localhost` of een hosts-entry.

## Documentatie

- **[INSTALL.md](INSTALL.md)** – Installatie op SiteGround of andere hosting
- **[DEPLOY.md](DEPLOY.md)** – Deployment-checklist
- **[docs/PRODUCTIE_INSTRUCTIES.md](docs/PRODUCTIE_INSTRUCTIES.md)** – Na beveiligingsupdates of wijzigingen
- **[docs/ROUTEBIBLIOTHEEK.md](docs/ROUTEBIBLIOTHEEK.md)** – Routebibliotheek en hergebruik over edities
- **[docs/COMMUNICATIE.md](docs/COMMUNICATIE.md)** – E-mail naar deelnemers (templates, plaatshouders)
- **[docs/VRIJWILLIGERS.md](docs/VRIJWILLIGERS.md)** – Vrijwilligersrooster
- **[docs/ARCHITECTUUR.md](docs/ARCHITECTUUR.md)** – Architectuur en begrippen voor ontwikkelaars
- **[docs/SPONSOREN_FORMULIER.md](docs/SPONSOREN_FORMULIER.md)** – Vrienden-formulier integreren

## Licentie

MIT
