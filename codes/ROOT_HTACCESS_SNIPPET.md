Add this to your domain root `.htaccess` so short URLs like `https://glparade.com/my-business-name` resolve to `/codes/public/index.php` while preserving existing files and folders.

```apache
RewriteEngine On

# Keep existing files/directories working
RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]

# Allow /codes public app paths
RewriteRule ^codes(/.*)?$ /codes/public/index.php [QSA,L]

# Short URL slugs -> QR app router
RewriteRule ^([a-z0-9-]+)/?$ /codes/public/index.php [QSA,L]

# 301 redirect hop endpoint
RewriteRule ^go/([a-z0-9-]+)/?$ /codes/public/index.php [QSA,L]
```
