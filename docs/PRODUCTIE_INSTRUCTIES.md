# Productie-instructies – Beveiligingsupdate

Na het deployen van de beveiligingsverbeteringen (feb 2026) gelden de volgende instructies.

---

## Wat is er gewijzigd?

1. **Editie-wissel** – Alleen gebruikers met `dashboard_view` mogen de editie wijzigen (selector alleen zichtbaar voor hen)
2. **Scan-overzicht** – `totalParticipants` toont nu correct het aantal van de geselecteerde editie
3. **QR-scanner** – Striktere validatie: alleen geldige `vierdaagsekesteren:...` QR-codes of (optioneel) numeriek ID; scoped op actieve editie
4. **Bedankt-pagina** – Beveiligd met signed URLs; link verloopt na 48 uur
5. **Rate limiting** – Inschrijfformulier, sponsorformulier en Mollie webhook beperkt in aantal requests per minuut
6. **IDOR-fix** – Sponsor, Registration, CostEntry, WalkRoute alleen bewerkbaar binnen de geselecteerde editie
7. **Sponsor webhook** – Invoice ID race condition opgelost met database lock

---

## Verplichte stappen na deployment

### 1. Cache legen

```bash
cd ~/vierdaagse  # of jouw projectpad
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

### 2. Geen .env-wijzigingen nodig

De wijzigingen werken direct. Optioneel kun je het volgende instellen:

---

## Optionele .env-instellingen

### QR-scanner: numeriek ID uitschakelen

Standaard accepteren oude scanners nog steeds een numeriek deelnemer-ID (bijv. `123`) naast de volledige QR-code (`vierdaagsekesteren:123:uuid`). Voor extra beveiliging kun je numeriek ID uitschakelen. **Let op:** Controleer of al je scanners de volledige `vierdaagsekesteren:id:uuid` QR-code lezen.

```env
# Zet op false om alleen volledige QR-codes toe te staan (standaard: true)
SCANNER_ALLOW_NUMERIC_ID_FALLBACK=false
```

---

## Gedragswijzigingen

| Onderdeel | Oud gedrag | Nieuw gedrag |
|-----------|------------|--------------|
| **Editie kiezen** | Elke ingelogde gebruiker | Alleen met `dashboard_view` |
| **Bedankt-pagina** | Altijd bereikbaar via /bedankt/123 | Alleen via signed link (48 uur geldig) |
| **Inschrijven** | Geen limiet | Max. 10 per minuut per IP |
| **Sponsorformulier** | Geen limiet | Max. 10 per minuut per IP |
| **Mollie webhook** | Geen limiet | Max. 60 per minuut |

---

## Rollback (als iets misgaat)

```bash
git revert HEAD
# of specifieke commits terugdraaien
composer install --no-dev
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

---

---

## Back-up en export

- **Database:** Zorg voor regelmatige back-ups van de database (frequentie afhankelijk van hosting; minimaal dagelijks aanbevolen).
- **Export per editie:** Via Intouch kun je inschrijvingen en evaluatieresultaten per editie exporteren (Inschrijvingen → Export; Evaluatie → Resultaten → Export CSV). Bewaar deze exports periodiek als archief.
- **View-cache na deploy:** Na een deploy met wijzigingen in Blade-templates: `php artisan view:clear`.

---

## Problemen?

- **"Editie wisselen lukt niet"** – Controleer of de gebruiker de permission `dashboard_view` heeft
- **"Bedankt-link werkt niet"** – De link is 48 uur geldig; bezoekers met een verlopen link worden doorgestuurd naar het inschrijfformulier met een melding
- **"Scanner accepteert QR niet"** – Zorg dat de scanner de volledige `vierdaagsekesteren:...` string leest, of zet `SCANNER_ALLOW_NUMERIC_ID_FALLBACK=true` (standaard)
