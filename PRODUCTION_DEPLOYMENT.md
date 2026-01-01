# Production Deployment Guide

## Language / Langue

- English: see the **English** section
- Français : voir la section **Français**

---

## English

## Overview

This guide covers deploying the Vie Étudiante EILCO platform to a production environment.

**Related Documentation:**
- [Docs/INSTALLATION.md](Docs/INSTALLATION.md) - Development setup
- [Docs/SECURITY.md](Docs/SECURITY.md) - Security configuration
- [Docs/DATABASE.md](Docs/DATABASE.md) - Database schema

## Pre-Deployment Checklist

### 1. Install Dependencies

1. **Install Composer dependencies:**

   ```bash
   composer install --no-dev --optimize-autoloader
   ```

   This installs `vlucas/phpdotenv` for environment variable management.

### 2. Environment Configuration

1. **Copy the environment file:**

   ```bash
   cp .env.example .env
   ```

   **Windows (PowerShell):**

   ```powershell
   Copy-Item .env.example .env
   ```

2. **Edit `.env` with your production values:**

   ```ini
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com

   DB_HOST=your_db_host
   DB_NAME=your_database_name
   DB_USER=your_database_user
   DB_PASS=your_secure_password

   MAIL_HOST=your_smtp_host
   MAIL_PORT=587
   MAIL_USERNAME=your_email@domain.com
   MAIL_PASSWORD=your_email_password
   MAIL_FROM=noreply@yourdomain.com
   MAIL_FROM_NAME="Your App Name"
   MAIL_ENCRYPTION=tls

   SESSION_LIFETIME=3600
   CSRF_LIFETIME=1800
   ```

### 3. Database Setup

1. Create your production database
2. Import the database schema
3. Ensure the database user has only necessary permissions (SELECT, INSERT, UPDATE, DELETE)

### 4. File Permissions

Set secure file permissions:

```bash
# Directories should be 755
find . -type d -exec chmod 755 {} \;

# Files should be 644
find . -type f -exec chmod 644 {} \;

# Make uploads directory writable
chmod 775 uploads/
chmod 775 logs/

# Protect sensitive files
chmod 600 .env
```

**Windows note:** file permissions are managed via NTFS ACLs (not `chmod`). At minimum, ensure the web server identity (e.g. IIS App Pool identity or Apache service user) can write to `uploads/` and `logs/`, and that `.env` is not readable by other users.

Example (PowerShell, adjust identity name):

```powershell
icacls .\uploads /grant "IIS_IUSRS:(OI)(CI)M" /T
icacls .\logs /grant "IIS_IUSRS:(OI)(CI)M" /T
icacls .\.env /inheritance:r
```

### 5. SSL Certificate

1. Install an SSL certificate (Let's Encrypt is free)
2. Update `.htaccess` to enable HTTPS redirect:
   - Uncomment the HTTPS redirect rules
   - Uncomment the HSTS header

### 6. Remove Debug Files

Delete or restrict access to these files in production:

- `health-check.php`
- `route-tester.php`

These are already blocked by `.htaccess`, but consider removing them entirely.

### 7. Apache/Nginx Configuration

#### Apache (mod_rewrite required)

Ensure these modules are enabled:

- `mod_rewrite`
- `mod_headers`
- `mod_deflate`
- `mod_expires`

**Windows (Apache):** ensure `mod_rewrite` is enabled in `httpd.conf` and that your VirtualHost allows overrides (e.g. `AllowOverride All`) so `.htaccess` rules are applied.

#### Nginx Configuration Example

```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    root /var/www/your-project;
    index index.php;

    # SSL configuration
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Block access to sensitive directories and files
    location ~ ^/(config|models|controllers|views|logs)/ {
        deny all;
        return 404;
    }

    location ~ /\. {
        deny all;
        return 404;
    }

    location ~ ^/(health-check|route-tester)\.php$ {
        deny all;
        return 404;
    }

    # Block PHP in uploads
    location ~ ^/uploads/.*\.php$ {
        deny all;
        return 404;
    }

    # Route all requests through index.php
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Cache static assets
    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$host$request_uri;
}
```

### 8. PHP Configuration (php.ini)

Recommended production settings (PHP 8.0+ required):

```ini
# Disable error display
display_errors = Off
log_errors = On
error_log = /path/to/your/project/logs/php-error.log

# Security settings
expose_php = Off
allow_url_fopen = Off
allow_url_include = Off

# Session security
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1
session.cookie_samesite = Strict

# File upload limits (adjust as needed)
upload_max_filesize = 10M
post_max_size = 12M
max_file_uploads = 5

# Memory and execution limits
memory_limit = 128M
max_execution_time = 30
```

## Post-Deployment Verification

1. **Test all routes** work correctly
2. **Verify HTTPS** is enforced
3. **Check error logging** is working (check `logs/error.log`)
4. **Test email functionality**
5. **Verify file uploads** work
6. **Check CSRF protection** on forms
7. **Run security headers test** at https://securityheaders.com

## Security Features Implemented

| Feature                                    | Status |
| ------------------------------------------ | ------ |
| Environment-based configuration            | ✅     |
| No hardcoded credentials                   | ✅     |
| HTTPS enforcement                          | ✅     |
| Security headers (XSS, Clickjacking, etc.) | ✅     |
| CSRF protection                            | ✅     |
| Session security                           | ✅     |
| Rate limiting support                      | ✅     |
| Proper error handling                      | ✅     |
| Error logging                              | ✅     |
| Directory protection                       | ✅     |
| PHP execution blocked in uploads           | ✅     |

## Maintenance

### Log Rotation

Set up log rotation to prevent disk space issues:

```bash
# Create /etc/logrotate.d/your-app
/path/to/your/project/logs/*.log {
    weekly
    rotate 4
    compress
    missingok
    notifempty
}
```

### Backup Strategy

1. Database: Daily automated backups
2. Uploads: Regular backups of user-uploaded files
3. Configuration: Keep `.env` backed up securely (not in git!)

### Monitoring

Consider setting up:

- Uptime monitoring
- Error rate alerts
- Performance monitoring

---

**Version:** 4.0  
**Last Updated:** January 2025

---

## Français

### Objectif

Ce document décrit les étapes recommandées pour déployer l’application en production (configuration d’environnement, droits, HTTPS, et vérifications).

### 1) Configuration d’environnement

- Copier `.env.example` vers `.env`

Linux/macOS :

```bash
cp .env.example .env
```

Windows (PowerShell) :

```powershell
Copy-Item .env.example .env
```

- Renseigner les variables (`APP_ENV`, `DB_*`, `SMTP_*`) avec des valeurs de production.

### 2) Droits fichiers

- Sous Linux, utiliser `chmod`/`chown`.
- Sous Windows, utiliser les ACL NTFS : le serveur web doit pouvoir écrire dans `uploads/` et `logs/`.
- Protéger `.env` (pas de lecture inutile, ne jamais le versionner).

### 3) HTTPS

- Installer un certificat SSL (Let’s Encrypt, ou certificat fourni)
- Activer la redirection HTTPS + HSTS si applicable

### 4) Vérifications après déploiement

- Tester les routes, l’envoi d’e-mails, les uploads, et la protection CSRF
