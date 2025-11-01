# Caesar-Cipher Wettbewerb

Ein Laravel-basiertes System für interaktive Caesar-Verschlüsselungs-Wettbewerbe in Workshop-Umgebungen. Teams erhalten verschlüsselte Nachrichten und wetteifern darum, sie als Erste zu entschlüsseln.

## Systemanforderungen

- PHP 8.2+ (8.3 empfohlen)
- Erforderliche PHP-Erweiterungen:
  - ext-pdo
  - ext-pdo_sqlite (oder mysql/pgsql)
  - ext-mbstring
  - ext-openssl
  - ext-json
  - ext-tokenizer
  - ext-ctype
  - ext-fileinfo
  - ext-xml
  - ext-gd (für QR-Codes)

## Installation auf Shared Hosting

1. Dateien hochladen
   ```bash
   # Lokal ausführen:
   composer install --no-dev
   touch storage/database.sqlite
   php artisan key:generate
   php artisan migrate
   php artisan db:seed
   ```
   Dann alle Dateien auf den Webspace hochladen.

2. Verzeichnisrechte setzen
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

3. DocumentRoot konfigurieren
   - Idealerweise auf `/public` zeigen lassen
   - Alternative: `.htaccess` im Root-Verzeichnis:
     ```apache
     RewriteEngine On
     RewriteRule ^(.*)$ public/$1 [L]
     ```

4. `.env` Konfiguration
   ```env
   APP_ENV=production
   APP_DEBUG=false
   DB_CONNECTION=sqlite
   DB_DATABASE=/absoluter/pfad/zu/storage/database.sqlite
   REALTIME_STRATEGY=sse  # oder 'poll' für Polling-Fallback
   ASSETS_MODE=cdn        # oder 'static' für vorkompiliertes CSS
   ```

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
