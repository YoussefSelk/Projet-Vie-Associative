# Vie Étudiante - MVC Restructured Project

## 📋 Overview

This is a complete restructuring of the "Vie Étudiante" project into a clean MVC (Model-View-Controller) architecture. The project maintains full backward compatibility with the existing database while providing a maintainable, scalable codebase structure.

**Key Features:**

- ✅ Clean MVC architecture
- ✅ Secure authentication & authorization
- ✅ Database abstraction through models
- ✅ Reusable controllers and views
- ✅ Centralized routing
- ✅ Session management
- ✅ Error handling
- ✅ Email integration

## 🚀 Quick Start

### Prerequisites

- PHP 7.4+
- MySQL 5.7+ or MariaDB
- Apache with mod_rewrite
- PHPMailer (included)

### Setup

1. **Configure Database** - Edit `config/Database.php` with your credentials
2. **Configure Email** - Edit `config/Email.php` with your SMTP settings
3. **Set Permissions** - Ensure `uploads/` is writable
4. **Test Connection** - Navigate to the project in your browser

### First Use

- Visit `index.php?page=login` to access login page
- Or simply visit root URL for home page

## 📁 Project Structure

```
project/
├── config/              # Configuration & bootstrap
│   ├── bootstrap.php    # Main initialization
│   ├── Database.php     # DB connection class
│   ├── DatabaseUtil.php # DB utilities
│   └── Email.php        # Email functions
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
│   ├── AuthController.php
│   ├── UserController.php
│   ├── ClubController.php
│   ├── EventController.php
│   ├── ValidationController.php
│   ├── SubscriptionController.php
│   └── HomeController.php
│
├── views/              # HTML templates
│   ├── includes/       # Shared templates
│   ├── home_index.php
│   ├── auth_login.php
│   ├── user_profile.php
│   ├── club_list.php
│   ├── event_list.php
│   └── [other views]
│
├── uploads/            # User uploads
├── images/             # Static images
├── index.php           # Main router
├── style.css           # Styling
└── .htaccess          # URL rewriting
```

## 🔐 Authentication & Authorization

### Permission Levels

- **0**: Guest (default)
- **1**: Member (can view content)
- **2**: Club Manager (can manage events)
- **3**: Admin/Tutor (full administrative access)
- **4**: Super Admin (system administration)

### Session Management

Sessions are managed in `config/bootstrap.php` with security settings:

- HttpOnly cookies
- Secure flag for HTTPS
- SameSite=Strict protection

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
?page=club-list         List/manage clubs (admin)
?page=club-create       Create club (tutor+)
```

### Event Routes

```
?page=event-list        List events
?page=event-view&id=X   View event details
?page=event-create      Create event (manager+)
```

### Admin Routes

```
?page=admin             Admin dashboard
?page=pending-clubs     Pending club validations
?page=pending-events    Pending event validations
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

- **MVC_STRUCTURE.md** - Architecture overview and detailed structure
- **API_REFERENCE.md** - Complete model and controller API
- **SETUP_GUIDE.md** - Installation and configuration guide
- **README.md** - This file

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

See `SETUP_GUIDE.md` for more troubleshooting.

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

**Version:** 2.0 (MVC Restructured)
**Last Updated:** December 30, 2024
**PHP Version:** 7.4+
**Database:** MySQL 5.7+
