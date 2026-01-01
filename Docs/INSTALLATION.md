# Guide d'Installation

## Prérequis

### Logiciels Requis

| Logiciel        | Version Minimum | Recommandé |
| --------------- | --------------- | ---------- |
| PHP             | 8.0             | 8.1+       |
| MySQL / MariaDB | 5.7 / 10.3      | 8.0 / 10.6 |
| Composer        | 2.0             | 2.5+       |
| Apache / Nginx  | 2.4 / 1.18      | Dernière   |

### Extensions PHP Requises

```ini
extension=pdo
extension=pdo_mysql
extension=mbstring
extension=openssl
extension=intl
extension=fileinfo
extension=curl
```

## Installation Pas à Pas

### 1. Cloner le Projet

```bash
git clone <repository-url> vie-etudiante
cd vie-etudiante
```

### 2. Installer les Dépendances

```bash
composer install
```

Cela installe :

- `vlucas/phpdotenv` - Gestion des variables d'environnement
- `phpmailer/phpmailer` - Envoi d'emails SMTP

### 3. Configuration de l'Environnement

Copier le fichier d'exemple :

**Linux/macOS :**

```bash
cp .env.example .env
```

**Windows (PowerShell) :**

```powershell
Copy-Item .env.example .env
```

Éditer `.env` avec vos paramètres :

```ini
# Application
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000

# Base de données
DB_HOST=localhost
DB_NAME=vieasso
DB_USER=root
DB_PASS=

# Email SMTP
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=your-password
MAIL_FROM=noreply@example.com
MAIL_FROM_NAME="Vie Étudiante EILCO"
MAIL_ENCRYPTION=tls

# Sécurité
SESSION_LIFETIME=3600
CSRF_LIFETIME=1800
```

### 4. Créer la Base de Données

Connectez-vous à MySQL :

```bash
mysql -u root -p
```

Créez la base :

```sql
CREATE DATABASE vieasso CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Importez le schéma (si fourni) :

```bash
mysql -u root -p vieasso < database/schema.sql
```

### 5. Permissions des Dossiers

**Linux/macOS :**

```bash
chmod 755 uploads/
chmod 755 uploads/logos/
chmod 755 uploads/rapports/
chmod 755 logs/
chmod 600 .env
```

**Windows :** S'assurer que le serveur web peut écrire dans `uploads/` et `logs/`.

### 6. Configuration du Serveur Web

#### Option A : Serveur PHP intégré (Développement)

```bash
php -S localhost:8000
```

Accédez à `http://localhost:8000`

#### Option B : Apache

Créer un VirtualHost :

```apache
<VirtualHost *:80>
    ServerName vie-etudiante.local
    DocumentRoot /chemin/vers/vie-etudiante

    <Directory /chemin/vers/vie-etudiante>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/vie-etudiante-error.log
    CustomLog ${APACHE_LOG_DIR}/vie-etudiante-access.log combined
</VirtualHost>
```

Activer mod_rewrite :

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Option C : Nginx

```nginx
server {
    listen 80;
    server_name vie-etudiante.local;
    root /chemin/vers/vie-etudiante;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(env|git|htaccess) {
        deny all;
    }
}
```

### 7. Vérification de l'Installation

1. Accédez à `http://localhost:8000` (ou votre URL)
2. La page d'accueil devrait s'afficher
3. Testez la connexion : `?page=login`
4. Vérifiez les logs dans `logs/error.log`

## Configuration Avancée

### Variables d'Environnement

| Variable          | Description        | Valeurs                     |
| ----------------- | ------------------ | --------------------------- |
| `APP_ENV`         | Environnement      | `development`, `production` |
| `APP_DEBUG`       | Mode debug         | `true`, `false`             |
| `DB_HOST`         | Hôte MySQL         | hostname ou IP              |
| `DB_NAME`         | Nom de la base     | string                      |
| `DB_USER`         | Utilisateur MySQL  | string                      |
| `DB_PASS`         | Mot de passe MySQL | string                      |
| `MAIL_HOST`       | Serveur SMTP       | hostname                    |
| `MAIL_PORT`       | Port SMTP          | `25`, `465`, `587`          |
| `MAIL_ENCRYPTION` | Chiffrement        | `tls`, `ssl`, `null`        |

### Configuration Email

Pour OVH :

```ini
MAIL_HOST=ssl0.ovh.net
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
```

Pour Gmail :

```ini
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

**Note :** Gmail nécessite un "App Password" si 2FA est activé.

### Configuration de Sécurité

Pour la production, modifiez :

```ini
APP_ENV=production
APP_DEBUG=false
```

Cela active :

- Masquage des erreurs détaillées
- Headers de sécurité stricts
- Forçage HTTPS

## Dépannage

### Erreur "Database connection failed"

1. Vérifiez les credentials dans `.env`
2. Assurez-vous que MySQL est démarré
3. Testez la connexion manuellement :
   ```bash
   mysql -h localhost -u root -p vieasso
   ```

### Erreur "Class not found"

1. Vérifiez que Composer est installé
2. Exécutez `composer install`
3. Vérifiez les autoloads : `composer dump-autoload`

### Page blanche

1. Activez l'affichage des erreurs dans `.env` :
   ```ini
   APP_DEBUG=true
   ```
2. Consultez `logs/error.log`
3. Vérifiez les logs PHP : `/var/log/php/error.log`

### Erreur 500 Internal Server Error

1. Vérifiez `.htaccess` et mod_rewrite
2. Consultez les logs Apache/Nginx
3. Vérifiez les permissions des fichiers

### Emails non envoyés

1. Vérifiez les paramètres SMTP dans `.env`
2. Testez la connexion SMTP :
   ```php
   // Créez un fichier test-mail.php temporaire
   require 'vendor/autoload.php';
   // Testez avec PHPMailer
   ```
3. Consultez `logs/error.log` pour les erreurs SMTP

## Mise à Jour

Pour mettre à jour le projet :

```bash
git pull origin main
composer install
```

Vérifiez les changements dans `.env.example` et mettez à jour votre `.env` si nécessaire.
