# Vierdaagse Kesteren – Intouch

Beheersysteem voor de Avondvierdaagse Kesteren. Inschrijvingen, loopoverzicht, QR-scanner en gebruikersbeheer.

## Overzicht

De app bestaat uit drie domeinen:

| Domein | Functie |
|--------|---------|
| **Inschrijven** | Inschrijfformulier met Mollie-betaling, PDF-ticket per e-mail |
| **Intouch** | Beheerportaal: dashboard, afstanden, inschrijvingen, loopoverzicht, gebruikers & rollen |
| **Scanner** | QR-code scanner voor start/post/finish tijdens het evenement |

## Technisch

- **Laravel 12** met PHP 8.2+
- **MySQL** of SQLite
- **Mollie** voor betalingen
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

## Licentie

MIT
