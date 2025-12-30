# Production Deployment Guide

## Pre-Deployment Checklist

### 1. Environment Configuration

1. **Copy the environment file:**

   ```bash
   cp .env.example .env
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

   SMTP_HOST=your_smtp_host
   SMTP_PORT=465
   SMTP_USER=your_email@domain.com
   SMTP_PASS=your_email_password
   SMTP_FROM_NAME=Your App Name
   ```

### 2. Database Setup

1. Create your production database
2. Import the database schema
3. Ensure the database user has only necessary permissions (SELECT, INSERT, UPDATE, DELETE)

### 3. File Permissions

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

### 4. SSL Certificate

1. Install an SSL certificate (Let's Encrypt is free)
2. Update `.htaccess` to enable HTTPS redirect:
   - Uncomment the HTTPS redirect rules
   - Uncomment the HSTS header

### 5. Remove Debug Files

Delete or restrict access to these files in production:

- `health-check.php`
- `route-tester.php`

These are already blocked by `.htaccess`, but consider removing them entirely.

### 6. Apache/Nginx Configuration

#### Apache (mod_rewrite required)

Ensure these modules are enabled:

- `mod_rewrite`
- `mod_headers`
- `mod_deflate`
- `mod_expires`

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

### 7. PHP Configuration (php.ini)

Recommended production settings:

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
