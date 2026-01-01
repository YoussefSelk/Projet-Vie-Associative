# Vie Étudiante EILCO - Plateforme de Gestion Associative

## Language / Langue

This README is available in two languages.

- English: see the **English** section
- Français : voir la section **Français**

---

## English

## 📋 Overview

**Vie Étudiante EILCO** is a complete student life management platform for EILCO (École d'Ingénieurs du Littoral Côte d'Opale). It enables club creation and management, event organization, member subscriptions, and administrative validation workflows.

The project uses a clean **MVC (Model-View-Controller)** architecture with centralized routing, secure authentication, and role-based permissions.

**Key Features:**

- ✅ Clean MVC architecture with centralized routing
- ✅ Secure authentication with bcrypt password hashing
- ✅ Role-based authorization (5 permission levels)
- ✅ Club creation with tutor validation workflow
- ✅ Event management with subscription system
- ✅ CSV export for club members
- ✅ File upload (logos, reports)
- ✅ Email notifications via SMTP
- ✅ Responsive design
- ✅ PHP 8.1+ compatibility

## 📚 Documentation

Detailed documentation is available in the [`Docs/`](Docs/) folder:

| Document | Description |
|----------|-------------|
| [ARCHITECTURE.md](Docs/ARCHITECTURE.md) | System architecture and diagrams |
| [DATABASE.md](Docs/DATABASE.md) | Database schema and queries |
| [SECURITY.md](Docs/SECURITY.md) | Security implementation details |
| [ROUTING.md](Docs/ROUTING.md) | Complete routing documentation |
| [INSTALLATION.md](Docs/INSTALLATION.md) | Setup and installation guide |
| [CONTRIBUTING.md](Docs/CONTRIBUTING.md) | Contribution guidelines |

Additional documentation:
- [API_REFERENCE.md](API_REFERENCE.md) - Controller and Model API
- [MVC_STRUCTURE.md](MVC_STRUCTURE.md) - MVC architecture details
- [PRODUCTION_DEPLOYMENT.md](PRODUCTION_DEPLOYMENT.md) - Production deployment

## 🚀 Quick Start

### Prerequisites

- PHP 8.0+ (8.1+ recommended)
- Composer 2.x
- MySQL 5.7+ or MariaDB 10.3+
- Apache with mod_rewrite (or Nginx)
- PHP Extensions: PDO, mbstring, intl, fileinfo

### Setup

1. **Clone the project**
   ```bash
   git clone <repository-url> vie-etudiante
   cd vie-etudiante
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your settings
   ```

4. **Create database**
   ```sql
   CREATE DATABASE vieasso CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

5. **Set permissions**
   ```bash
   chmod 755 uploads/ logs/
   chmod 600 .env
   ```

For detailed installation instructions, see [Docs/INSTALLATION.md](Docs/INSTALLATION.md).

### First Use

- Visit `index.php?page=home` for home page
- Visit `index.php?page=login` to log in
- Visit `index.php?page=register` to create an account

## 📁 Project Structure

```
project/
├── .env.example        # Environment variables template
├── composer.json        # PHP dependencies (phpdotenv)
├── vendor/              # Composer dependencies
│
├── config/              # Configuration & bootstrap
│   ├── bootstrap.php    # Main initialization
│   ├── Database.php     # DB connection class
│   ├── DatabaseUtil.php # DB utilities
│   ├── Email.php        # Email functions
│   ├── Environment.php  # Dotenv loader + helpers
│   ├── ErrorHandler.php # Custom error/exception handling
│   ├── Router.php       # Centralized routing class
│   └── Security.php     # Security headers + CSRF
│
├── routes/              # Route definitions
│   └── web.php          # All application routes
│
├── models/              # Data models
│   ├── User.php
│   ├── Club.php
│   ├── Event.php
│   ├── EventReport.php
│   ├── ClubMember.php
│   ├── EventSubscription.php
│   └── Validation.php
│
├── controllers/         # Business logic
│   ├── AdminController.php
│   ├── AuthController.php
│   ├── UserController.php
│   ├── ClubController.php
│   ├── EventController.php
│   ├── ValidationController.php
│   ├── SubscriptionController.php
│   └── HomeController.php
│
├── views/              # HTML templates
│   ├── includes/       # Shared templates (head, header, footer)
│   ├── auth/           # Login, register views
│   ├── admin/          # Admin dashboard views
│   ├── errors/         # Custom error pages (403, 404, 500, 503)
│   └── [other views]
│
├── uploads/            # User uploads
├── images/             # Static images
├── logs/               # App/PHP logs
├── index.php           # Entry point (uses Router)
├── css/                # Stylesheets (professional tables, dropdowns)
└── .htaccess          # URL rewriting
```

## 🔐 Authentication & Authorization

### Permission Levels

| Level | Role | Description |
|-------|------|-------------|
| 0 | Visiteur | Unverified account |
| 1 | Membre | Standard student member |
| 2 | Tuteur | Teacher/tutor (can validate) |
| 3 | BDE | Student union (extended access) |
| 5 | Admin | Full system administration |

### Session Management

Sessions are managed in `config/bootstrap.php` with security settings:

- HttpOnly cookies (not accessible via JavaScript)
- Secure flag for HTTPS
- SameSite=Lax protection
- Session regeneration on login
- CSRF protection for all POST routes

### Helper Functions

```php
validateSession();        // Check user is logged in
checkPermission($level);  // Check user permission level
redirect($path);          // Redirect with exit
```

## 🛣️ Routing Guide

### Core Routes

```
?page=home              Home page
?page=login             Login/password reset
?page=register          Register
?page=logout            Logout
```

### User Routes

```
?page=profile           View profile
?page=profile-edit      Edit profile
?page=users-list        List users (admin only)
```

### Club Routes

```
?page=club-view&id=X    View club details (public)
?page=club-list         List/manage clubs (admin)
?page=club-create       Create club (logged-in users)
?page=export-members    Export members (admin)
```

### Event Routes

```
?page=event-list        List events
?page=event-view&id=X   View event details
?page=event-create      Create event (permission level 2+)
?page=event-report      Event report
?page=my-events         My events
```

### Admin Routes

```
?page=admin             Admin dashboard
?page=pending-clubs     Pending club validations
?page=pending-events    Pending event validations
?page=tutoring          Tutoring/validation view
```

### Subscription Routes

```
?page=subscribe         Subscribe to event (POST)
?page=unsubscribe       Unsubscribe from event (POST)
?page=my-subscriptions  View my subscriptions
```

## 📊 Database Models

### User Model

```php
$user = new User($db);
$user->getUserById($id);
$user->getUserByEmail($email);
$user->authenticate($email, $password);
$user->updateUser($id, $data);
```

### Club Model

```php
$club = new Club($db);
$club->getAllValidatedClubs();
$club->getClubById($id);
$club->createClub($data);
$club->updateClub($id, $data);
```

### Event Model

```php
$event = new Event($db);
$event->getAllValidatedEvents();
$event->getEventById($id);
$event->createEvent($data);
$event->updateEvent($id, $data);
```

See `API_REFERENCE.md` for complete model documentation.

## 🎮 Controllers

Controllers handle business logic and coordinate between models and views:

```php
// Example: UserController
$controller = new UserController($db);
$data = $controller->viewProfile();
extract($data);  // Makes variables available to view
include VIEWS_PATH . '/user_profile.php';
```

### Controller Actions

Each controller provides specific actions:

- `AuthController::login()` - Handle authentication
- `UserController::viewProfile()` - Display user profile
- `ClubController::listClubs()` - List all clubs
- `EventController::createEvent()` - Create new event
- `ValidationController::pendingClubs()` - Manage validations

## 🎨 Views

Views are HTML templates that receive data from controllers:

```php
<!-- Display user data -->
<h1><?= htmlspecialchars($user['nom']) ?></h1>

<!-- Display lists -->
<?php foreach ($clubs as $club): ?>
    <p><?= htmlspecialchars($club['nom_club']) ?></p>
<?php endforeach; ?>

<!-- Display conditional content -->
<?php if(!empty($error_msg)): ?>
    <div class="error"><?= $error_msg ?></div>
<?php endif; ?>
```

## 📧 Email System

Send emails using the `sendEmail()` function:

```php
sendEmail(
    'user@example.com',
    'Welcome!',
    'Hello, welcome to our platform!'
);
```

Configuration in `config/Email.php`:

- SMTP Host: ssl0.ovh.net
- Port: 465
- Encryption: SMTPS

## 🔧 Adding New Features

### 1. Create a Model

```php
// models/Feature.php
class Feature {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function getData() { ... }
}
```

### 2. Create a Controller

```php
// controllers/FeatureController.php
class FeatureController {
    private $model;

    public function __construct($database) {
        $this->model = new Feature($database);
    }

    public function list() {
        $data = $this->model->getData();
        return ['data' => $data];
    }
}
```

### 3. Create a View

```php
// views/feature_list.php
<?php include VIEWS_PATH . '/includes/head.php'; ?>
<!-- HTML content -->
<?php include VIEWS_PATH . '/includes/footer.php'; ?>
```

### 4. Add Routes

```php
// In index.php
case 'feature-list':
    $controller = new FeatureController($db);
    $data = $controller->list();
    extract($data);
    include VIEWS_PATH . '/feature_list.php';
    break;
```

## 🛡️ Security Features

- **Prepared Statements**: Prevent SQL injection
- **Password Hashing**: BCRYPT with cost 12
- **Session Security**: HttpOnly, Secure, SameSite cookies
- **Input Sanitization**: htmlspecialchars() on output
- **Authorization**: Permission-based access control
- **CSRF Protection**: Ready to implement

## ⚡ Performance Tips

1. **Database**

   - Use indexes on frequently queried columns
   - Minimize queries per page load
   - Use lazy loading for related data

2. **Caching**

   - Cache static data (clubs, campuses)
   - Store session data efficiently
   - Implement page caching where applicable

3. **Optimization**
   - Minify CSS/JavaScript
   - Optimize images
   - Use content delivery network (CDN)
   - Implement gzip compression

## 📚 Documentation

- **[Docs/ARCHITECTURE.md](Docs/ARCHITECTURE.md)** - System architecture overview
- **[Docs/DATABASE.md](Docs/DATABASE.md)** - Database schema and queries
- **[Docs/SECURITY.md](Docs/SECURITY.md)** - Security implementation
- **[Docs/ROUTING.md](Docs/ROUTING.md)** - Complete route documentation
- **[Docs/INSTALLATION.md](Docs/INSTALLATION.md)** - Setup guide
- **[Docs/CONTRIBUTING.md](Docs/CONTRIBUTING.md)** - Contribution guidelines
- **[MVC_STRUCTURE.md](MVC_STRUCTURE.md)** - MVC architecture details
- **[API_REFERENCE.md](API_REFERENCE.md)** - Complete API documentation
- **[PRODUCTION_DEPLOYMENT.md](PRODUCTION_DEPLOYMENT.md)** - Production deployment guide

## 🐛 Troubleshooting

### Database Connection Error

```
Check config/Database.php credentials
Verify MySQL service is running
Test connection with: $db->query("SELECT 1");
```

### Page Not Found

```
Enable mod_rewrite: a2enmod rewrite
Verify .htaccess in project root
Clear browser cache
```

### Session Issues

```
Check session storage permissions
Verify cookie settings
Look at error logs for details
```

See `PRODUCTION_DEPLOYMENT.md` for production troubleshooting tips.

## 📋 Migration Checklist

When migrating from old to new structure:

- [ ] Update all navigation links to use new routing
- [ ] Test all authentication flows
- [ ] Verify club and event management
- [ ] Test email sending
- [ ] Check permission levels
- [ ] Validate database backups
- [ ] Test with different user roles
- [ ] Performance testing
- [ ] Security audit
- [ ] Update user documentation

## 🔄 Backward Compatibility

Legacy files are still present in the root directory:

- `profil.php` → now: `?page=profile`
- `liste-clubs.php` → now: `?page=club-list`
- `formulaireConnexion.php` → now: `?page=login`

You can gradually update links and retire old files.

## 📈 Next Steps

### Immediate

1. Configure database and email
2. Test login and basic navigation
3. Verify all pages load correctly

### Short Term

1. Update all links in templates
2. Test all functionality
3. Perform security audit
4. Deploy to production

### Long Term

1. Implement caching system
2. Add comprehensive logging
3. Create API endpoints
4. Add form validation middleware
5. Implement automated testing

## 🤝 Contributing

When adding new features:

1. Follow MVC pattern
2. Use prepared statements
3. Sanitize output
4. Check permissions
5. Add error handling
6. Document your code

## 📞 Support

For issues or questions:

1. Check documentation files
2. Review similar controller implementations
3. Check error logs
4. Test components in isolation
5. Verify database structure

## 📝 License

[Your License Here]

## 👥 Team

Project restructured to MVC architecture - 2024

---

**Version:** 3.0 (Router + Error Handling + Dotenv)
**Last Updated:** December 31, 2025
**PHP Version:** 8.0+
**Database:** MySQL 5.7+

---

## Français

### 📋 Présentation

Ce projet est une restructuration de **Vie Étudiante** vers une architecture **MVC (Model-View-Controller)**. L’objectif est de conserver la compatibilité avec la base existante tout en rendant le code plus lisible, maintenable et évolutif.

**Fonctionnalités clés :**

- Architecture MVC claire
- Authentification et autorisations (permissions)
- Modèles pour l’accès base de données
- Contrôleurs réutilisables + vues séparées
- Routage centralisé via `index.php?page=...`
- Sessions sécurisées
- Gestion d’erreurs et configuration par environnement
- Envoi d’e-mails via PHPMailer

### 🚀 Démarrage rapide

**Prérequis :**

- PHP 8.0+ (8.1+ recommandé)
- Composer 2.x
- MySQL 5.7+ ou MariaDB 10.3+
- Apache avec `mod_rewrite` (ou Nginx)

**Installation :**

1. `composer install`
2. Copier `.env.example` vers `.env` et configurer
3. Créer la base de données `vieasso`
4. Configurer les permissions sur `uploads/` et `logs/`

Voir [Docs/INSTALLATION.md](Docs/INSTALLATION.md) pour les instructions détaillées.

### 🔐 Authentification & Autorisations

**Niveaux de permissions :**

| Niveau | Rôle |
|--------|------|
| 0 | Visiteur |
| 1 | Membre |
| 2 | Tuteur |
| 3 | BDE |
| 5 | Admin |

### 📚 Documentation

Voir le dossier [`Docs/`](Docs/) pour la documentation détaillée.

### 🔐 Authentification & Autorisations

**Niveaux de permissions :**

- 0 : Invité
- 1 : Membre
- 2 : Responsable/gestionnaire de club
- 3 : Admin / Tuteur
- 4 : Super Admin

**Sécurité des sessions :** cookies HttpOnly, SameSite=Strict, et option `Secure` selon HTTPS.

**CSRF :** toutes les routes en **POST** exigent un `csrf_token` valide, sauf `login` et `register`.

### 🛣️ Routes principales

**Cœur :**

- `?page=home` : accueil
- `?page=login` : connexion / reset
- `?page=register` : inscription
- `?page=logout` : déconnexion

**Utilisateur :**

- `?page=profile` : profil
- `?page=profile-edit` : édition profil
- `?page=users-list` : liste utilisateurs (admin)

**Clubs :**

- `?page=club-view&id=X` : voir un club (public)
- `?page=club-list` : administration clubs (admin)
- `?page=club-create` : créer un club (utilisateur connecté)
- `?page=export-members` : export membres (admin)

**Événements :**

- `?page=event-list` : liste
- `?page=event-view&id=X` : détail
- `?page=event-create` : création (permission 2+)
- `?page=event-report` : rapport
- `?page=my-events` : mes événements

**Admin / Validation :**

- `?page=admin`
- `?page=pending-clubs`
- `?page=pending-events`
- `?page=tutoring`

### 📚 Documentation

- Voir [API_REFERENCE.md](API_REFERENCE.md) pour les contrôleurs / modèles
- Voir [MVC_STRUCTURE.md](MVC_STRUCTURE.md) pour l’architecture
- Voir [PRODUCTION_DEPLOYMENT.md](PRODUCTION_DEPLOYMENT.md) pour la mise en production
