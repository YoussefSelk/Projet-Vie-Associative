# MVC Project - Quick Reference Card

## 🚀 Getting Started

```bash
# 1. Configure database
Edit: config/Database.php
Set: host, db_name, user, pass

# 2. Configure email (optional)
Edit: config/Email.php
Set: SMTP credentials

# 3. Set permissions
chmod 755 uploads/
chmod 755 uploads/logos/
chmod 755 uploads/rapports/

# 4. Access the application
URL: http://yoursite.com/index.php
Or: http://yoursite.com/?page=home
```

---

## 📂 File Structure Quick Map

```
├── config/         → Database, Email, Bootstrap
├── models/         → User, Club, Event, etc.
├── controllers/    → AuthController, UserController, etc.
├── views/          → HTML templates
├── index.php       → Main router/entry point
└── .htaccess       → URL rewriting
```

---

## 🔗 Common Routes

| Action        | Route                   |
| ------------- | ----------------------- |
| Home          | `?page=home`            |
| Login         | `?page=login`           |
| Logout        | `?page=logout`          |
| My Profile    | `?page=profile`         |
| Edit Profile  | `?page=profile-edit`    |
| Clubs List    | `?page=club-list`       |
| Create Club   | `?page=club-create`     |
| Events        | `?page=event-list`      |
| Event Details | `?page=event-view&id=1` |
| Create Event  | `?page=event-create`    |
| Admin Panel   | `?page=admin`           |

---

## 💻 Using Models

```php
// In a controller:
$user = new User($db);
$data = $user->getUserById(1);

// Available on all models:
getById($id)
getAll()
create($data)
update($id, $data)
delete($id)
```

---

## 🎮 Creating a Controller

```php
class MyController {
    private $model;
    private $db;

    public function __construct($database) {
        $this->db = $database;
        $this->model = new MyModel($database);
    }

    public function action() {
        $data = $this->model->getData();
        return ['key' => $data];
    }
}
```

---

## 🎨 Creating a View

```php
<?php include VIEWS_PATH . '/includes/head.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <?php include VIEWS_PATH . '/includes/head.php'; ?>
</head>
<body>
    <header><?php include VIEWS_PATH . "/includes/header.php"; ?></header>
    <main>
        <!-- Your content here -->
        <?= htmlspecialchars($data['field']) ?>
    </main>
    <footer><?php include VIEWS_PATH . '/includes/footer.php'; ?></footer>
</body>
</html>
```

---

## 🛣️ Adding a Route

In `index.php`:

```php
case 'my-page':
    $controller = new MyController($db);
    $data = $controller->action();
    extract($data);  // Makes variables available
    include VIEWS_PATH . '/my_page.php';
    break;
```

---

## 🔐 Permission Levels

```
0 = Guest
1 = Member
2 = Club Manager
3 = Admin/Tutor
4 = Super Admin
```

Use in controllers:

```php
checkPermission(3);  // Require level 3+
validateSession();   // Check logged in
```

---

## 📧 Sending Email

```php
sendEmail(
    'recipient@example.com',
    'Subject',
    'Message content'
);
```

---

## 🐛 Debugging

```php
// Log errors
error_log("Debug message");

// Check database connection
$test = $db->query("SELECT 1");

// Dump variable
var_dump($variable);

// Check what's in session
var_dump($_SESSION);
```

---

## 🔍 Useful Constants

```php
ROOT_PATH      # Project root directory
CONFIG_PATH    # config/ directory
MODELS_PATH    # models/ directory
CONTROLLERS_PATH # controllers/ directory
VIEWS_PATH     # views/ directory
```

---

## ✅ Database Operations

```php
// Prepared statement
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

// Insert
$stmt = $db->prepare("INSERT INTO users (name) VALUES (?)");
$stmt->execute([$name]);

// Update
$stmt = $db->prepare("UPDATE users SET name = ? WHERE id = ?");
$stmt->execute([$name, $id]);

// Delete
$stmt = $db->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$id]);
```

---

## 🔐 Security Reminders

```php
// ✅ DO: Use prepared statements
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);

// ❌ DON'T: String concatenation
$sql = "SELECT * FROM users WHERE id = " . $id;

// ✅ DO: Sanitize output
<?= htmlspecialchars($user['name']) ?>

// ❌ DON'T: Echo user input directly
<?= $user['name'] ?>

// ✅ DO: Check permissions
checkPermission(3);

// ❌ DON'T: Skip permission checks
// Just assume user has access
```

---

## 📱 Form Handling

```php
// POST data in controller
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? null;
    $email = $_POST['email'] ?? null;

    if (!$name || !$email) {
        $error_msg = "All fields required";
    } else {
        // Process form
    }
}

// In view
<form method="POST">
    <input type="text" name="name" required>
    <input type="email" name="email" required>
    <button type="submit">Submit</button>
</form>
```

---

## 🔄 Redirects

```php
// In controller
redirect('index.php');
redirect('index.php?page=home');
redirect('index.php?page=profile');

// Redirect with exit (automatic)
```

---

## 📚 Documentation Files

| File                     | Purpose               |
| ------------------------ | --------------------- |
| README.md                | Project overview      |
| MVC_STRUCTURE.md         | Architecture guide    |
| SETUP_GUIDE.md           | Installation & config |
| API_REFERENCE.md         | API documentation     |
| RESTRUCTURING_SUMMARY.md | Changes made          |

---

## 🚨 Common Issues & Solutions

| Issue               | Solution                                     |
| ------------------- | -------------------------------------------- |
| "Page not found"    | Check mod_rewrite enabled, .htaccess present |
| "Database error"    | Verify credentials in config/Database.php    |
| "Session lost"      | Check cookie settings, session.save_path     |
| "Permission denied" | Verify file permissions on uploads/          |
| "Email not sending" | Check SMTP credentials in config/Email.php   |

---

## 📞 Quick Help

```php
// Get current logged-in user
$_SESSION['id']        // User ID
$_SESSION['nom']       // Last name
$_SESSION['prenom']    // First name
$_SESSION['permission'] // Permission level

// Make variable available to view
extract($data);  // $data['key'] becomes $key

// Escape output to prevent XSS
htmlspecialchars($user_input)

// Check if user owns resource
if ($resource['user_id'] == $_SESSION['id'])

// Validate permission before action
checkPermission(3);  // Redirects if insufficient

// Send JSON response (for AJAX)
header('Content-Type: application/json');
echo json_encode($data);
```

---

## 🎯 Next Steps

1. Configure database credentials
2. Test login functionality
3. Verify all routes work
4. Check permissions are enforced
5. Test email sending
6. Perform security audit
7. Deploy to production

---

**For detailed help, see the documentation files!**

- Questions about structure? → MVC_STRUCTURE.md
- Installation issues? → SETUP_GUIDE.md
- API questions? → API_REFERENCE.md
- Setup problems? → SETUP_GUIDE.md
