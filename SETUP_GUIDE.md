# MVC Project - Setup Guide

## Prerequisites

- PHP 7.4 or higher
- MySQL/MariaDB
- Apache with mod_rewrite enabled (for .htaccess routing)
- PHPMailer library (included in project)

## Installation Steps

### 1. Database Setup

- Create a new database named `test_projet_tech`
- Import your existing database schema
- Verify tables include: users, fiche_club, fiche_event, subscribe_event, membres_club, rapport_event

### 2. Configuration

#### Update Database Credentials

Edit `config/Database.php`:

```php
private $host = 'localhost';        // Your host
private $db_name = 'test_projet_tech';
private $user = 'root';             // Your DB user
private $pass = '';                 // Your DB password
```

#### Update Email Configuration

Edit `config/Email.php`:

```php
$smtp_host = "ssl0.ovh.net";
$smtp_username = "your-email@example.com";
$smtp_password = "your-password";
$smtp_port = 465;
```

### 3. File Permissions

Ensure write permissions for upload directories:

```bash
chmod 755 uploads/
chmod 755 uploads/logos/
chmod 755 uploads/rapports/
```

### 4. Apache Configuration

Enable mod_rewrite:

```bash
a2enmod rewrite
```

Create a virtual host or update .htaccess. The .htaccess file is already included in the project root.

### 5. Testing the Installation

1. Navigate to your project URL
2. You should see the home page
3. Try logging in with existing credentials
4. Check that navigation works with new routing (?page=profile, etc.)

## Directory Ownership

Ensure proper permissions:

```bash
chown -R www-data:www-data /path/to/project
chmod -R 755 /path/to/project
```

## Troubleshooting

### "Database Connection Error"

- Check credentials in `config/Database.php`
- Ensure MySQL service is running
- Verify database exists and tables are created

### "Page not found"

- Verify .htaccess is in project root
- Check that Apache mod_rewrite is enabled
- Clear browser cache

### "Email not sending"

- Verify SMTP credentials in `config/Email.php`
- Check firewall allows port 465
- Test SMTP connection

### "Session errors"

- Check session storage permissions
- Ensure cookies are enabled in browser
- Verify session timeout settings

## Project Structure Quick Reference

```
Models/        → Database interaction (CRUD operations)
Controllers/   → Business logic & request handling
Views/         → HTML templates & presentation
Config/        → Configuration & bootstrap
index.php      → Router/entry point
```

## Common Tasks

### Adding a New Page

1. Create Model in `models/`
2. Create Controller in `controllers/`
3. Create View(s) in `views/`
4. Add routes in `index.php`

### Modifying an Existing Page

1. Update the Model if database queries change
2. Update the Controller if logic changes
3. Update the View if layout changes

### Creating a New Database Table

1. Create table in MySQL
2. Create corresponding Model class
3. Add model methods for CRUD operations
4. Create Controller and Views as needed

## Security Considerations

1. **SQL Injection**: Always use prepared statements (already implemented)
2. **XSS**: Use htmlspecialchars() when outputting user data (already implemented)
3. **CSRF**: Consider adding CSRF tokens for forms
4. **Authentication**: Store sessions securely (configured in bootstrap.php)
5. **Authorization**: Use permission levels (0-4) consistently

## Performance Tips

1. Index frequently queried columns
2. Cache database results for static data
3. Use lazy loading for related entities
4. Minimize database round trips
5. Optimize SQL queries

## Maintenance

### Regular Tasks

- Monitor error logs
- Test all forms and validations
- Backup database regularly
- Update dependencies
- Review user permissions

### Logging

Add logging to controllers for debugging:

```php
error_log("User " . $_SESSION['id'] . " accessed club list");
```

## Future Enhancements

- [ ] Add logging system
- [ ] Implement caching
- [ ] Create API endpoints
- [ ] Add form validation middleware
- [ ] Implement template engine
- [ ] Add automated tests
- [ ] Create admin dashboard
- [ ] Add user roles system

## Support

For issues or questions:

1. Check MVC_STRUCTURE.md for architecture overview
2. Review controller implementations for examples
3. Check database schema for table structures
4. Test individual components in isolation

## Deployment

### Production Checklist

- [ ] Update database credentials
- [ ] Update email credentials
- [ ] Set appropriate file permissions
- [ ] Enable error logging but hide errors from users
- [ ] Set secure session cookie parameters
- [ ] Test all critical functionality
- [ ] Set up automated backups
- [ ] Monitor error logs
- [ ] Document any customizations

### Production Configuration Tips

```php
// In config/bootstrap.php for production:
ini_set('display_errors', 0);  // Don't show errors to users
ini_set('log_errors', 1);      // Log errors instead
error_reporting(E_ALL);

// Secure session settings for HTTPS
session_set_cookie_params([
    'httponly' => true,
    'secure' => true,     // Only over HTTPS
    'samesite' => 'Strict'
]);
```
