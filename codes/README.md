# GL Parade QR Codes Module (`/codes`)

## What this provides
- Admin-protected QR/link manager.
- Unique short slugs (`https://glparade.com/<slug>`).
- Interstitial tracking page + 301 redirect hop.
- Scan analytics storage in MySQL.
- QR render options similar to common online generators: size, margin, foreground/background colors, error correction, PNG/SVG.

## Requirements
- PHP 8.3+
- MySQL (PDO only)
- Composer

## Install
1. `cd codes && composer install`
2. `cp config.example.php config.php` and update DB credentials/base URL.
3. Import `sql/qr_module.sql`.
4. Add rewrite rules from `ROOT_HTACCESS_SNIPPET.md` at your web root.

## Create first admin
Generate password hash:

```bash
php -r "echo password_hash('your-password', PASSWORD_DEFAULT), PHP_EOL;"
```

Insert into `qr_admin_users` with `is_approved = 1`.

## Notes
- Uses your existing `biz_stpats` and `crawl_participants` tables.
- Slug uniqueness is enforced in both AJAX checks and DB unique key.
- Tracking stores hashed IP, user-agent, referer, language, query data, and source channel.
