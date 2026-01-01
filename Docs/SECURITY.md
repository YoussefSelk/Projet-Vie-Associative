# Documentation Sécurité

## Vue d'Ensemble

Le système implémente plusieurs couches de sécurité pour protéger les données et les utilisateurs.

## Architecture de Sécurité

```
┌─────────────────────────────────────────────────────────────────┐
│                        REQUÊTE HTTP                              │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    HEADERS DE SÉCURITÉ                           │
│  (X-Frame-Options, X-XSS-Protection, CSP, HSTS)                 │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    GESTION SESSION                               │
│  (Cookie secure, HttpOnly, SameSite, régénération ID)           │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    VALIDATION CSRF                               │
│  (Token unique par session, vérification sur POST)              │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    AUTHENTIFICATION                              │
│  (Vérification session, permissions, accès route)               │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    VALIDATION ENTRÉES                            │
│  (Sanitisation, validation types, échappement)                  │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    REQUÊTES PRÉPARÉES                            │
│  (PDO prepared statements, binding paramètres)                  │
└─────────────────────────────────────────────────────────────────┘
```

## Classe Security

Située dans `config/Security.php`, cette classe centralise toutes les fonctions de sécurité.

### Headers de Sécurité

```php
Security::setSecurityHeaders();
```

**Headers Appliqués :**

| Header | Valeur | Description |
|--------|--------|-------------|
| `X-Frame-Options` | `DENY` | Empêche l'inclusion en iframe (clickjacking) |
| `X-Content-Type-Options` | `nosniff` | Empêche le MIME sniffing |
| `X-XSS-Protection` | `1; mode=block` | Active la protection XSS du navigateur |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | Limite les infos du referrer |
| `Content-Security-Policy` | Configurable | Politique de contenu |
| `Strict-Transport-Security` | `max-age=31536000` | Force HTTPS (production) |

### Gestion des Sessions

```php
Security::initSession();
```

**Configuration Session :**

```php
[
    'name'            => 'VIEASSO_SESSION',
    'cookie_httponly' => true,      // Pas accessible par JavaScript
    'cookie_secure'   => true,      // HTTPS uniquement (production)
    'cookie_samesite' => 'Lax',     // Protection CSRF
    'use_strict_mode' => true,      // Refuse IDs invalides
    'use_only_cookies'=> true,      // Pas d'ID en URL
    'gc_maxlifetime'  => 3600       // Expiration 1h
]
```

**Régénération d'ID :**
```php
Security::regenerateSession();
```
Régénère l'ID de session après authentification pour prévenir le session fixation.

### Protection CSRF

#### Génération du Token

```php
$token = Security::generateCSRFToken();
```

Génère un token cryptographique unique stocké en session :
- Utilise `random_bytes(32)` pour l'aléatoire
- Encode en Base64
- Stocké dans `$_SESSION['csrf_token']`
- Expiration configurable (défaut: 30 min)

#### Inclusion dans les Formulaires

```php
<form method="POST">
    <?= Security::csrfField() ?>
    <!-- Champs du formulaire -->
</form>
```

Génère :
```html
<input type="hidden" name="csrf_token" value="abc123...">
```

#### Vérification du Token

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::validateCSRF();  // Lance exception si invalide
}
```

### Authentification

#### Vérification Connexion

```php
if (Security::isLoggedIn()) {
    $userId = Security::getCurrentUserId();
    $user = Security::getCurrentUser();
}
```

#### Vérification Permissions

```php
// Permission minimale requise
Security::requirePermission(2);  // Minimum tuteur

// Permission exacte
if (Security::hasPermission(5)) {
    // Actions admin uniquement
}

// Accès à une ressource spécifique
Security::requireClubAccess($clubId);  // Vérifie propriété ou rôle
```

#### Déconnexion Sécurisée

```php
Security::logout();  // Détruit session et cookies
```

### Validation et Sanitisation

#### Sanitisation des Entrées

```php
$email = Security::sanitizeInput($_POST['email'], 'email');
$text = Security::sanitizeInput($_POST['description'], 'text');
$html = Security::sanitizeInput($_POST['content'], 'html');
$int = Security::sanitizeInput($_POST['age'], 'int');
```

**Types de Sanitisation :**

| Type | Description | Fonction PHP |
|------|-------------|--------------|
| `email` | Valide et nettoie email | `FILTER_SANITIZE_EMAIL` |
| `text` | Échappe HTML | `htmlspecialchars()` |
| `html` | Nettoie HTML (garde certaines balises) | `strip_tags()` avec whitelist |
| `int` | Force entier | `intval()` |
| `float` | Force décimal | `floatval()` |
| `url` | Valide URL | `FILTER_SANITIZE_URL` |

#### Validation

```php
// Email valide
if (!Security::isValidEmail($email)) {
    throw new Exception("Email invalide");
}

// Mot de passe fort
if (!Security::isStrongPassword($password)) {
    // Minimum 8 caractères, majuscule, minuscule, chiffre
}
```

### Hashage des Mots de Passe

```php
// Création hash
$hash = Security::hashPassword($plainPassword);
// Utilise bcrypt avec coût adaptatif

// Vérification
if (Security::verifyPassword($plainPassword, $hash)) {
    // Mot de passe correct
}
```

**Algorithme :** `PASSWORD_BCRYPT` avec coût de 12.

## Protection SQL Injection

### Requêtes Préparées

Toutes les requêtes utilisent PDO avec requêtes préparées :

```php
// ❌ MAUVAIS - Vulnérable
$sql = "SELECT * FROM users WHERE email = '$email'";

// ✅ BON - Sécurisé
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);

// ✅ BON - Avec paramètres nommés
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id AND email = :email");
$stmt->execute([':id' => $id, ':email' => $email]);
```

### Classe Database

```php
$db = Database::getInstance();

// Requête sécurisée
$user = $db->query(
    "SELECT * FROM users WHERE email = ? AND permission >= ?",
    [$email, $minPermission]
)->fetch();
```

## Protection XSS

### Échappement Automatique

Dans les vues, toujours échapper les données utilisateur :

```php
<!-- ✅ BON - Échappe le HTML -->
<?= htmlspecialchars($user['pseudo'], ENT_QUOTES, 'UTF-8') ?>

<!-- ✅ BON - Helper court -->
<?= e($user['pseudo']) ?>

<!-- ❌ MAUVAIS - Vulnérable XSS -->
<?= $user['pseudo'] ?>
```

### Helper d'Échappement

```php
// Défini dans bootstrap.php
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
```

### Content Security Policy

En production, une CSP stricte est appliquée :

```php
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'");
```

## Protection Upload de Fichiers

### Validation Type MIME

```php
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $_FILES['file']['tmp_name']);

if (!in_array($mimeType, $allowedTypes)) {
    throw new Exception("Type de fichier non autorisé");
}
```

### Validation Extension

```php
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExtensions)) {
    throw new Exception("Extension non autorisée");
}
```

### Renommage Sécurisé

```php
// Nom unique pour éviter les collisions et injections
$newFilename = uniqid('logo_', true) . '.' . $ext;
$destination = 'uploads/logos/' . $newFilename;
```

### Limite de Taille

```php
$maxSize = 5 * 1024 * 1024; // 5 MB
if ($_FILES['file']['size'] > $maxSize) {
    throw new Exception("Fichier trop volumineux");
}
```

## Niveaux de Permission

### Hiérarchie

```
┌─────────────────────────────────────────┐
│             ADMIN (5)                    │
│  - Accès total au système               │
│  - Gestion utilisateurs                 │
│  - Configuration système                │
├─────────────────────────────────────────┤
│             BDE (3)                      │
│  - Gestion clubs/événements             │
│  - Validation sans tuteur               │
│  - Rapports et statistiques             │
├─────────────────────────────────────────┤
│             TUTEUR (2)                   │
│  - Validation clubs/événements          │
│  - Supervision étudiante                │
├─────────────────────────────────────────┤
│             MEMBRE (1)                   │
│  - Création clubs (soumis validation)   │
│  - Inscription événements               │
│  - Profil personnel                     │
├─────────────────────────────────────────┤
│             VISITEUR (0)                 │
│  - Consultation publique                │
│  - Inscription/Connexion                │
└─────────────────────────────────────────┘
```

### Vérification dans le Code

```php
// Dans les contrôleurs
public function adminDashboard() {
    Security::requirePermission(5);  // Admin requis
    // ...
}

public function validateClub($id) {
    Security::requirePermission(2);  // Tuteur ou plus
    // ...
}

// Dans les vues
<?php if (Security::hasPermission(3)): ?>
    <a href="?page=admin">Administration</a>
<?php endif; ?>
```

## Gestion des Erreurs Sécurisée

### Environnement Development

```php
// APP_DEBUG=true
// Affiche erreurs détaillées (stack trace, etc.)
// NE JAMAIS UTILISER EN PRODUCTION
```

### Environnement Production

```php
// APP_DEBUG=false
// Messages génériques à l'utilisateur
// Erreurs détaillées dans logs/error.log
// Pas d'exposition de chemins/code
```

### Logging Sécurisé

```php
// Les mots de passe ne sont JAMAIS loggés
ErrorHandler::log("Login attempt", [
    'email' => $email,
    // 'password' => $password  ← JAMAIS !
    'ip' => $_SERVER['REMOTE_ADDR']
]);
```

## Checklist Sécurité

### Avant Déploiement

- [ ] `APP_ENV=production` dans `.env`
- [ ] `APP_DEBUG=false` dans `.env`
- [ ] Mots de passe forts pour DB
- [ ] `.env` non accessible publiquement
- [ ] Permissions fichiers correctes
- [ ] HTTPS configuré
- [ ] Sauvegardes automatisées

### Revue de Code

- [ ] Toutes les requêtes SQL préparées
- [ ] Toutes les sorties échappées
- [ ] CSRF sur tous les formulaires POST
- [ ] Vérification permissions sur chaque action
- [ ] Validation des uploads
- [ ] Pas de secrets dans le code source

### Maintenance Régulière

- [ ] Mise à jour PHP et dépendances
- [ ] Revue des logs d'erreur
- [ ] Audit des permissions utilisateurs
- [ ] Test des sauvegardes
- [ ] Scan de vulnérabilités

## Vulnérabilités Connues et Mitigations

| Vulnérabilité | Mitigation Implémentée |
|---------------|------------------------|
| SQL Injection | Requêtes préparées PDO |
| XSS | Échappement HTML, CSP |
| CSRF | Tokens par session |
| Session Fixation | Régénération ID après login |
| Clickjacking | X-Frame-Options: DENY |
| MIME Sniffing | X-Content-Type-Options: nosniff |
| Man-in-the-Middle | HTTPS + HSTS |
| Brute Force | (À implémenter: rate limiting) |
| Path Traversal | Validation chemins fichiers |

## Améliorations Futures

1. **Rate Limiting** - Limiter les tentatives de connexion
2. **2FA** - Authentification à deux facteurs
3. **Audit Log** - Journal des actions sensibles
4. **Password Policy** - Politique de complexité renforcée
5. **Session Timeout** - Déconnexion automatique après inactivité
6. **IP Whitelisting** - Restriction accès admin par IP
