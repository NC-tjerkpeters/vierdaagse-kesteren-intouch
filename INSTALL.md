# Installatie Vierdaagse Kesteren – SiteGround

Handleiding voor het installeren van de Vierdaagse Kesteren-app op SiteGround hosting.

## Vereisten

- **PHP** 8.2 of hoger (instelbaar via Site Tools → PHP Manager)
- **MySQL** database
- **Composer** (aanwezig op SiteGround)
- **SSH-toegang** (Site Tools → SSH Keys)

## Domeinen

De app gebruikt **domein-gebaseerde routing**. Configureer vier (sub)domeinen die allemaal naar dezelfde Laravel-installatie wijzen:

| Domein | Gebruik |
|--------|---------|
| `inschrijven.vierdaagsekesteren.nl` | Inschrijfformulier deelnemers + sponsorformulier (vrienden) |
| `intouch.vierdaagsekesteren.nl` | Beheerportaal |
| `scanner.vierdaagsekesteren.nl` | QR-scanner |
| `routes.vierdaagsekesteren.nl` | Publieke wandelroutes met PDF-download en afstrepen |

Eventueel: `vierdaagsekesteren.nl` of `www.vierdaagsekesteren.nl` voor de hoofdpagina.

---

## Stap 1: Bestanden uploaden

### Optie A: Via Git (aanbevolen)

1. SSH in op je SiteGround-account.
2. Ga naar de gewenste map (bijv. `~/`):

   ```bash
   cd ~
   ```

3. Clone het project:

   ```bash
   git clone <repository-url> vierdaagse
   cd vierdaagse
   ```

### Optie B: ZIP uploaden

1. Maak lokaal een ZIP van het project (zonder `vendor`, `.env`, `node_modules`).
2. Upload via FTP of Site Tools → File Manager.
3. Pak uit in bijv. `~/vierdaagse/`.

---

## Stap 2: Composer en dependencies

Via SSH:

```bash
cd ~/vierdaagse
composer install --no-dev --optimize-autoloader
```

---

## Stap 3: Database

1. Maak in **Site Tools → MySQL** een database en gebruiker.
2. Noteer: database naam, gebruikersnaam, wachtwoord.

---

## Stap 4: Document root

1. Ga naar **Site Tools → Domains**.
2. Voor elk domein (inschrijven, intouch, scanner):
   - Klik op het domein → **Manage**.
   - Stel **Document root** in op: `~/vierdaagse/public`  
     (of het pad waar `public` van je Laravel-installatie staat).

---

## Stap 5: .env configureren

1. Kopieer het voorbeeldbestand:

   ```bash
   cp .env.example .env
   ```

2. Genereer de app key:

   ```bash
   php artisan key:generate
   ```

3. Pas `.env` aan:

   ```env
   APP_NAME="Vierdaagse Kesteren"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://inschrijven.vierdaagsekesteren.nl

   INSCHRIJVEN_DOMAIN=inschrijven.vierdaagsekesteren.nl
   INTOUCH_DOMAIN=intouch.vierdaagsekesteren.nl
   SCANNER_DOMAIN=scanner.vierdaagsekesteren.nl
   ROUTES_DOMAIN=routes.vierdaagsekesteren.nl

   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=<jouw_database>
   DB_USERNAME=<jouw_gebruiker>
   DB_PASSWORD=<jouw_wachtwoord>

   SESSION_DRIVER=database
   SESSION_ENCRYPT=true

   MOLLIE_KEY=live_xxxxx
   MOLLIE_WEBHOOKS_ENABLED=true

   # Sponsors (vrienden)
   SPONSORS_DOELBEDRAG=1850
   SPONSORS_REDIRECT_URL=https://vierdaagsekesteren.nl/vrienden-van-de-vierdaagse-kesteren/aanmelden/bedankt-voor-uw-bijdrage/
   SPONSORS_RECEIPT_BCC=mail@vierdaagsekesteren.nl

   # Microsoft Graph (Intouch-login, tickets)
   MSGRAPH_TENANT_ID=...
   MSGRAPH_CLIENT_ID=...
   MSGRAPH_CLIENT_SECRET=...
   MSGRAPH_SENDER_ADDRESS=...

   # Optioneel: scanner, noodnummers (ook via Beheer → Instellingen)
   SCANNER_MIN_MINUTES_BETWEEN_SCANS=5
   APP_NOODNUMMERS="06 52 44 16 10, 06 40 89 37 40"

   MAIL_MAILER=log
   ```

---

## Stap 6: Migraties en seed

```bash
php artisan migrate --force
php artisan db:seed --force
```

Na de seed kun je inloggen op Intouch met:

- **E-mail:** `admin@vierdaagsekesteren.nl`
- **Wachtwoord:** `wijzig-dit-wachtwoord`

**Let op:** Bij nieuwe releases met extra permissies (bijv. `inschrijvingen_edit`):

```bash
php artisan db:seed --class=PermissionSeeder --force
```

---

## Stap 7: Storage-link, rechten en cache

> **Na beveiligingsupdates:** Zie [docs/PRODUCTIE_INSTRUCTIES.md](docs/PRODUCTIE_INSTRUCTIES.md) voor instructies.

```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache

# Cache legen (belangrijk na deployment om 500-fouten te voorkomen)
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

## Stap 8: Mollie-webhooks

Er zijn **twee webhook-routes**:

### Inschrijvingen (deelnemers)

1. De ticket-mail wordt verzonden als de deelnemer na betaling naar de bedankpagina gaat.
2. Voor extra zekerheid (als iemand de browser sluit vóór de bedankpagina):
   - Stel `MOLLIE_WEBHOOKS_ENABLED=true` in `.env`.
   - Registreer in het Mollie-dashboard de webhook-URL:  
     `https://inschrijven.vierdaagsekesteren.nl/webhooks/mollie`
   - Eventueel `MOLLIE_WEBHOOK_SIGNING_SECRETS` instellen.

### Sponsors (vrienden)

- De webhook-URL wordt **per betaling** meegegeven; je hoeft geen extra webhook in het Mollie-dashboard te registreren.
- URL: `https://inschrijven.vierdaagsekesteren.nl/webhooks/mollie/sponsors`
- Zie [docs/SPONSOREN_FORMULIER.md](docs/SPONSOREN_FORMULIER.md) voor integratie van het sponsorformulier.

---

## Stap 9: Systeeminstellingen (optioneel)

Veel instellingen kun je aanpassen via **Intouch → Beheer → Instellingen**:

- Sponsors doelbedrag
- Mollie transactiekosten per betaalmethode
- Scanner: min. minuten tussen scans, namen scanpunten
- Noodnummers op tickets

Die overschrijven de standaardwaarden uit `.env` en config.

---

## Stap 10: Afbeeldingen PDF-ticket

Controleer of deze bestanden aanwezig zijn:

- `public/images/pdf/top-banner.png`
- `public/images/pdf/bird.png`

Zo niet, plaats ze handmatig.

---

## Controlelijst

- [ ] PHP 8.2+
- [ ] Composer-dependencies geïnstalleerd
- [ ] `.env` correct ingevuld
- [ ] Database migraties uitgevoerd
- [ ] `php artisan db:seed` uitgevoerd
- [ ] Document root van alle domeinen op `public`
- [ ] Storage-link aangemaakt
- [ ] Mollie live key en webhook (inschrijvingen) actief
- [ ] Microsoft Graph (Intouch) geconfigureerd

---

## Problemen

**500 Internal Server Error**

- Voer de cache-clear commando's uit:
  ```bash
  php artisan config:clear
  php artisan cache:clear
  php artisan route:clear
  php artisan view:clear
  ```
- Controleer `storage/logs/laravel.log` voor de exacte foutmelding
- Controleer rechten op `storage/` en `bootstrap/cache/`

**"No application encryption key"**

- Voer `php artisan key:generate` uit

**Sessies werken niet over subdomeinen**

- Stel eventueel `SESSION_DOMAIN=.vierdaagsekesteren.nl` in (met punt)

**routes.vierdaagsekesteren.nl geeft 500**

- Controleer of `ROUTES_DOMAIN=routes.vierdaagsekesteren.nl` in `.env` staat (zonder https://)
- Controleer of het subdomein correct naar de Laravel-installatie wijst (zelfde document root als de andere domeinen)
- Voer `php artisan config:clear` en `php artisan route:clear` uit na wijzigingen in `.env`
