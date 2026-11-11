# OwnTV Custom management panel

Laravel 13 panel and public, read-only Android configuration API. The first version uses one admin account and SQLite; friends do not have panel accounts.

## Local setup

Requires PHP 8.3+, Composer 2, and PDO SQLite.

```bash
cd server
composer install
cp .env.example .env
php artisan key:generate
mkdir -p database
touch database/database.sqlite
php artisan migrate --force
php artisan panel:admin:create
php artisan serve
```

Visit `http://127.0.0.1:8000/panel/`. The local bootstrap endpoint is `http://127.0.0.1:8000/panel/api/v1/bootstrap`.

For local testing, `SESSION_SECURE_COOKIE=true` in `.env.example` expects HTTPS. Set it to `false` only in your local development `.env`; never do this in production.

## Ubuntu 24.04 deployment outline

1. Use a real domain; `paneldomain.com` is only the agreed placeholder. Point its DNS A/AAAA record to the VPS.
2. Copy this `server/` directory to `/var/www/owntv-custom/server`.
3. Run `sudo bash deploy/ubuntu-24.04-setup.sh`.
4. Create the SQLite file and configure production environment:

   ```bash
   cd /var/www/owntv-custom/server
   sudo -u www-data touch database/database.sqlite
   sudo cp .env.example .env
   sudo nano .env
   ```

   Set `APP_URL=https://YOUR_REAL_DOMAIN`, `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, `APP_TIMEZONE=Europe/London`, and `DB_DATABASE=/var/www/owntv-custom/server/database/database.sqlite`.

5. Install dependencies and initialise the app:

   ```bash
   sudo composer install --no-dev --prefer-dist --optimize-autoloader
   sudo php artisan key:generate
   sudo php artisan migrate --force
   sudo php artisan panel:admin:create
   sudo chown -R www-data:www-data storage bootstrap/cache database
   sudo chmod 600 .env database/database.sqlite
   sudo chmod -R u=rwX,g=rX,o= storage bootstrap/cache
   ```

6. Install `deploy/nginx-paneldomain.com.conf` as `/etc/nginx/sites-available/owntv-custom`, replacing the placeholder domain and root path. Enable it, test Nginx, and reload:

   ```bash
   sudo ln -s /etc/nginx/sites-available/owntv-custom /etc/nginx/sites-enabled/owntv-custom
   sudo nginx -t && sudo systemctl reload nginx
   ```

7. Obtain and configure TLS after DNS points to the VPS:

   ```bash
   sudo certbot --nginx --redirect -d YOUR_REAL_DOMAIN
   ```

8. Set up the daily database backup:

   ```bash
   sudo chmod 750 deploy/backup-sqlite.sh
   sudo crontab -e
   ```

   Add `15 3 * * * /var/www/owntv-custom/server/deploy/backup-sqlite.sh`.

9. Configure a firewall to allow SSH, HTTP (for certificate renewal/redirect) and HTTPS only. Do not expose PHP-FPM or SQLite.

**Important:** these are deployment instructions, not a claim that the VPS has been configured. No domain or server has been accessed or changed.

## Admin account

Create the one administrator from the VPS shell:

```bash
php artisan panel:admin:create
```

There is no registration form. To reset the admin password:

```bash
php artisan panel:admin:reset-password
```

The password is hashed by Laravel. Admin login is rate-limited to five attempts per minute per email/IP combination, and the session is regenerated after successful login.

## Panel features

- Add/edit/enable/disable/delete IPTV server records.
- Store the complete provider URL including protocol and optional port.
- Edit an existing server record when its domain changes; this preserves its UUID so Android credentials remain associated with the same provider.
- Create general or server-specific maintenance notices, with information/warning/urgent severity and optional start/end times.
- Public bootstrap API returns all server records (including enabled state) and currently active maintenance messages.

## Encryption and recovery

`IptvServer.base_url` uses Laravel's encrypted Eloquent cast. Ciphertext is stored in SQLite. The Laravel `APP_KEY` is therefore essential: **back up the `.env` encryption key separately and securely**. Do not rotate it casually; existing encrypted provider URLs would no longer decrypt unless the previous key is configured and supported by the app.

A normal SQLite restore must restore the `panel_metadata` row and therefore the original `installation_id`. A database that is genuinely recreated from scratch generates a new `installation_id` on the next bootstrap request. That is how Android clients detect a wiped/reinstalled panel.

The included backup script keeps seven SQLite snapshots. Copy backups off the VPS as well. Store the matching `.env`/APP_KEY backup in a separate encrypted location; never put it in Git or alongside a publicly served directory.

## API security boundary

`GET /panel/api/v1/bootstrap` is intentionally unauthenticated so installed apps can retrieve server configuration before a user logs in. It contains no Xtream credentials. HTTPS is mandatory in production. Provider base URLs are encrypted at rest, but the endpoint must reveal them to the app so it can connect. Basic protection cannot hide a domain from a determined user/device owner.

The endpoint returns `Cache-Control: no-store`. Android must keep its own last-valid encrypted cache and compare `installation_id` and `checksum`. Checksum is the final content comparator; timestamp is diagnostic and may move backwards after restoring an older backup.

## Current implementation status

This is an initial source scaffold. It has not yet been installed with Composer dependencies or executed against a real Laravel runtime in this environment. Run `composer install`, `php artisan migrate`, and the feature tests before deploying. Android integration remains a separate milestone.
