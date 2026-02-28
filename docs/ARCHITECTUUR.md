# Architectuur – ontwikkelaars

Korte uitleg voor developers die aan de codebase werken.

## Edities: actief vs. huidige

| Methode | Gebruik |
|---------|---------|
| **`Edition::active()`** | De “live” editie (waar inschrijvingen bij komen). Gebruik bij publieke flows: inschrijven, sponsorformulier, scanner, routes-pagina. |
| **`Edition::current()`** | De editie die de beheerder in Intouch heeft geselecteerd (session). Gebruik in Intouch: lijsten, bewerken, rapporten. Valt terug op actieve editie als geen session. |

De scope `forActiveEdition()` op modellen filtert op `Edition::current()` (de geselecteerde editie in Intouch, of de actieve editie als fallback). Gebruik deze scope dus in Intouch-context, niet voor publieke flows.

## Domeinen

- **inschrijven** – Inschrijvingen, sponsors, Mollie webhooks
- **intouch** – Beheerportaal (auth verplicht)
- **scanner** – QR-scanner (auth verplicht)
- **routes** – Publieke wandelroutes (geen auth)

Alle domeinen wijzen naar dezelfde Laravel-installatie en `public` map.

## CSRF-vrijstellingen

- `webhooks/mollie/sponsors` – Mollie webhook (geen CSRF-token)
- `vrienden/aanmelden` – Sponsorformulier op ander domein (vierdaagsekesteren.nl) → formulier post naar inschrijven-domein

## Storage

- **`storage/app/public`** – Uploads (route-PDFs, Word-documenten) via `php artisan storage:link`
- PDFs en Word worden via Laravel-routes aangeboden (niet via symlink) voor betrouwbare werking op multi-domein
