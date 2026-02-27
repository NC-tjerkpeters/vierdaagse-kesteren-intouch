# Deployment-checklist – Vierdaagse Kesteren

Korte checklist voor het deployen naar SiteGround (of andere shared hosting).

## Voor het uploaden

1. **ZIP maken** (zonder vendor, .env, node_modules):
   ```bash
   # Exclusief: vendor, .env, node_modules, .git, storage/logs/*, etc.
   ```

2. Of **Git** gebruiken en lokaal `composer install` op de server doen.

## Na het uploaden (SSH)

```bash
cd ~/vierdaagse   # of jouw map
composer install --no-dev --optimize-autoloader
cp .env.example .env   # alleen bij eerste deploy
php artisan key:generate
# .env bewerken met database, domeinen, Mollie, MS Graph
php artisan migrate --force
php artisan db:seed --force
# Bij nieuwe permissies (na code-update):
php artisan db:seed --class=PermissionSeeder --force
php artisan storage:link
chmod -R 775 storage bootstrap/cache

# Cache legen (belangrijk na deployment)
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

## Bij updates (pull / nieuwe release)

```bash
cd ~/vierdaagse
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed --class=PermissionSeeder --force
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

## Document root

Voor **inschrijven**, **intouch** en **scanner**: document root = `~/vierdaagse/public` (of jouw pad + `/public`).

## Eerste inlog

- **Intouch:** `intouch.vierdaagsekesteren.nl` → admin@vierdaagsekesteren.nl / wijzig-dit-wachtwoord
- **Scanner:** `scanner.vierdaagsekesteren.nl` → dezelfde inlog (users-tabel gedeeld)

Zie **INSTALL.md** voor de volledige installatiehandleiding.
