# Vie Étudiante - MVC Project Structure

## Language / Langue

- English: see the **English** section
- Français : voir la section **Français**

## English

## Project Overview

This is a restructured PHP project using the MVC (Model-View-Controller) architecture pattern. The project maintains backward compatibility with the existing database while providing a cleaner, more maintainable code structure.

## Directory Structure

```
project-root/
├── .env.example               # Environment variables template
├── config/
│   ├── bootstrap.php          # Main configuration file
│   ├── Database.php           # Database connection class
│   ├── Email.php              # Email utility functions
│   ├── Environment.php        # Loads .env and environment flags
│   ├── ErrorHandler.php       # Environment-based error handling
│   └── Security.php           # Security headers + CSRF helpers
├── models/
│   ├── User.php               # User data model
│   ├── Club.php               # Club data model
│   ├── Event.php              # Event data model
│   └── EventReport.php        # Event report data model
├── controllers/
│   ├── AuthController.php     # Authentication logic
│   ├── UserController.php     # User management logic
│   ├── ClubController.php     # Club management logic
│   ├── EventController.php    # Event management logic
│   └── HomeController.php     # Home/dashboard logic
├── views/
│   ├── includes/              # Shared view templates
│   │   ├── head.php
│   │   ├── header.php
│   │   ├── footer.php
│   │   └── ... other includes
│   ├── home_index.php         # Home page view
│   ├── auth_login.php         # Login view
│   ├── user_profile.php       # User profile view
│   ├── user_profile_edit.php  # Edit profile view
│   ├── club_list.php          # Club listing
│   ├── club_create.php        # Create club view
│   ├── event_list.php         # Event listing
│   ├── event_view.php         # View event details
│   └── event_create.php       # Create event view
├── images/                    # Images and media
├── uploads/                   # User uploaded files
├── logs/                      # App/PHP logs (if enabled)
├── css/                       # Stylesheets
├── PHPMailer-master/          # Email library
├── .htaccess                  # URL rewriting rules
├── index.php                  # Main router/entry point
└── README.md                  # Project overview
```

## MVC Pattern Explanation

### Models

Models contain all database interaction logic. They handle queries and data retrieval/storage.

**Example:**

```php
$user = new User($db);
$userData = $user->getUserById(1);
```

### Views

Views contain all HTML and presentation logic. They receive data from controllers through variable extraction.

**Example:**

```php
<?= htmlspecialchars($user['nom']) ?>
```

### Controllers

Controllers handle business logic and orchestrate between models and views. They receive HTTP requests, process them, and pass data to views.

**Example:**

```php
$controller = new UserController($db);
$data = $controller->viewProfile();
extract($data); // Makes variables available to view
include VIEWS_PATH . '/user_profile.php';
```

## Routing

Routing is handled by the `Router` class in `config/Router.php`. Routes are defined in `routes/web.php` as a configuration array:

```php
// routes/web.php
return [
    'home' => [
        'controller' => 'HomeController',
        'method' => 'index',
        'view' => '/home_index.php',
        'auth' => false,
        'permission' => null
    ],
    'profile' => [
        'controller' => 'UserController',
        'method' => 'viewProfile',
        'view' => '/user_profile.php',
        'auth' => true,
        'permission' => null
    ],
    // ... more routes
];
```

### Entry Point (index.php)

The `index.php` file is now minimal (~17 lines):

```php
<?php
require_once 'config/bootstrap.php';
$router = new Router($db);
$router->dispatch();
```

### CSRF protection

The Router validates CSRF tokens for **all POST requests**, except `login` and `register` which handle their own flow.

### Error Handling

Invalid routes trigger `ErrorHandler::renderHttpError(404)` which displays a professional custom error page.

## Authentication & Authorization

- **Session Management**: Handled in `config/bootstrap.php`
- **Permission Levels**:
  - 0: Guest
  - 1: Member
  - 2: Club Manager
  - 3: Admin/Tutor
  - 4: Super Admin

**Helper Functions:**

- `validateSession()` - Ensures user is logged in
- `checkPermission($level)` - Ensures user has required permission level
- `redirect($path)` - Redirects to new URL

## Database Connection

Database credentials are defined in `config/Database.php`. Update these values for your environment:

```php
private $host = 'localhost';
private $db_name = 'test_projet_tech';
private $user = 'root';
private $pass = '';
```

## Available Routes

### Authentication

- `?page=login` - Login form
- `?page=logout` - Logout

### User Routes

- `?page=profile` - View current user profile
- `?page=profile-edit` - Edit profile
- `?page=users-list` - List all users (admin only)

### Club Routes

- `?page=club-list` - List all clubs (admin only)
- `?page=club-create` - Create new club (tutor/admin only)

### Event Routes

- `?page=event-list` - List all events
- `?page=event-view&id=X` - View event details
- `?page=event-create` - Create new event

### Admin Routes

- `?page=admin` - Admin dashboard

### Home Routes

- `?page=home` or no parameters - Home page

## Email Configuration

Email settings are configured in `config/Email.php` (and/or via `.env`, depending on your configuration):

```php
$smtp_host = "ssl0.ovh.net";
$smtp_username = "your-email@example.com";
$smtp_password = "your-password";
$smtp_port = 465;
```

## Migration Notes

This structure maintains the same database while reorganizing code. Legacy files can be retired as the new MVC routes replace them:

- ✓ Replaced: `index.php`, `profil.php`, `liste-clubs.php`, form files
- ✓ Legacy files still available in root for reference during transition
- ⚠ Update navigation links to use new routing

## Adding New Features

To add a new feature:

1. **Create Model** (`models/FeatureName.php`) - Database operations
2. **Create Controller** (`controllers/FeatureController.php`) - Business logic
3. **Create Views** (`views/feature_*.php`) - HTML templates
4. **Add Routes** in `index.php` - Wire up the controller/view

Example for new "Announcements" feature:

```php
// 1. Model
class Announcement {
    public function getAll() { ... }
}

// 2. Controller
class AnnouncementController {
    public function list() { ... }
}

// 3. View
// views/announcement_list.php

// 4. Route
case 'announcements':
    $controller = new AnnouncementController($db);
    $data = $controller->list();
    extract($data);
    include VIEWS_PATH . '/announcement_list.php';
```

## Performance Optimization

- Use prepared statements (already implemented)
- Cache frequently accessed data
- Minimize database queries
- Use lazy loading for related data

## Security

- ✓ Prepared statements prevent SQL injection
- ✓ Password hashing with BCRYPT
- ✓ Session security with httponly/secure flags
- ✓ Input sanitization with htmlspecialchars()
- ✓ CSRF protection ready (can be enhanced)

## Troubleshooting

### Pages not loading

- Check that `config/bootstrap.php` is being included
- Verify database connection in `config/Database.php`
- Check file permissions

### Database errors

- Confirm database credentials
- Verify tables exist in database
- Check prepared statements

### Routing issues

- Ensure `.htaccess` is enabled (Apache mod_rewrite)
- Check URL parameters match case in routing
- Verify views paths are correct

## Future Improvements

- Add API endpoints for AJAX requests
- Implement template engine (Twig, Blade)
- Add middleware system
- Implement ORM (Doctrine, Eloquent)
- Add comprehensive logging
- Implement caching layer

---

## Français

### Vue d’ensemble

Ce projet utilise une architecture **MVC** :

- **Models** : requêtes SQL et accès aux données
- **Controllers** : logique métier / orchestration
- **Views** : affichage (HTML) et rendu

### Routage

Le fichier `index.php` sert de routeur via `?page=...`.

### CSRF

Les requêtes **POST** exigent un `csrf_token` valide (sauf `login` et `register`).

### Sécurité

Les en-têtes de sécurité et la configuration des sessions sont centralisés dans `config/Security.php` et `config/bootstrap.php`.

### E-mail

Ne stockez jamais de vrais identifiants SMTP dans Git. Utilisez des placeholders dans la documentation et placez les secrets dans `.env`.

---

**Last Updated:** December 31, 2025
