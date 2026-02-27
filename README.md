# Vierdaagse Kesteren – Intouch

Beheersysteem voor de Avondvierdaagse Kesteren. Inschrijvingen, sponsors, loopoverzicht, financiën, QR-scanner en gebruikersbeheer.

## Overzicht

De app bestaat uit drie domeinen:

| Domein | Functie |
|--------|---------|
| **Inschrijven** | Inschrijfformulier deelnemers, sponsorformulier (vrienden), Mollie-betaling, PDF-ticket per e-mail |
| **Intouch** | Beheerportaal: dashboard, sponsors, inschrijvingen, loopoverzicht, financiën, gebruikers, rollen, edities, instellingen |
| **Scanner** | QR-code scanner voor start/post/finish tijdens het evenement |

### Intouch-modules

- **Dashboard** – Overzicht inschrijvingen, medailles, eindsaldo, sponsors-voortgang
- **Sponsors** – Beheer vrienden van de vierdaagse (betalingen via Mollie)
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

## Documentatie

- **[INSTALL.md](INSTALL.md)** – Installatie op SiteGround
- **[DEPLOY.md](DEPLOY.md)** – Deployment-checklist
- **[docs/SPONSOREN_FORMULIER.md](docs/SPONSOREN_FORMULIER.md)** – Vrienden-formulier integreren

## Licentie

MIT
