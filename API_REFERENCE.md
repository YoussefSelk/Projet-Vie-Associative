# API Reference - Controllers & Models

## Table of Contents

1. [User Model & Controller](#user)
2. [Club Model & Controller](#club)
3. [Event Model & Controller](#event)
4. [Authentication Controller](#auth)
5. [Validation Model & Controller](#validation)
6. [Event Subscription](#subscription)

---

## User Model & Controller {#user}

### User Model

#### `getUserById($id)`

Retrieves a single user by ID.

```php
$user = new User($db);
$data = $user->getUserById(1);
// Returns: ['id' => 1, 'nom' => 'Dupont', 'prenom' => 'Jean', ...]
```

#### `getUserByEmail($email)`

Retrieves a user by email address.

```php
$data = $user->getUserByEmail('user@example.com');
```

#### `getAllUsers()`

Retrieves all users.

```php
$users = $user->getAllUsers();
// Returns: Array of user records
```

#### `authenticate($email, $password)`

Authenticates user with email and password.

```php
$authenticatedUser = $user->authenticate('user@example.com', 'password123');
// Returns user data if successful, null otherwise
```

#### `updatePassword($email, $password)`

Updates user password (auto-hashes with BCRYPT).

```php
$success = $user->updatePassword('user@example.com', 'newpassword123');
```

#### `updateUser($id, $data)`

Updates user information.

```php
$success = $user->updateUser(1, [
    'nom' => 'New Name',
    'prenom' => 'New First Name',
    'mail' => 'newemail@example.com'
]);
```

### UserController

#### Route: `?page=profile`

**Method:** GET
**Description:** Display current user profile
**Permissions:** Requires login
**Returns:** User profile data

#### Route: `?page=profile-edit`

**Method:** GET/POST
**Description:** Edit user profile
**Permissions:** User or admin
**POST Parameters:**

```php
[
    'nom' => 'string',
    'prenom' => 'string',
    'mail' => 'email'
]
```

#### Route: `?page=users-list`

**Method:** GET
**Description:** List all users
**Permissions:** Admin (level 3+)

---

## Club Model & Controller {#club}

### Club Model

#### `getAllValidatedClubs()`

Get all clubs with final validation.

```php
$clubs = new Club($db);
$data = $clubs->getAllValidatedClubs();
```

#### `getClubById($id)`

Get specific club by ID.

```php
$club = $clubs->getClubById(5);
```

#### `getClubByName($name)`

Get club by name.

```php
$club = $clubs->getClubByName('Football Club');
```

#### `getAllClubs()`

Get all clubs (including pending).

```php
$allClubs = $clubs->getAllClubs();
```

#### `createClub($data)`

Create new club.

```php
$success = $clubs->createClub([
    'nom_club' => 'Chess Club',
    'type_club' => 'Strategy',
    'description' => 'For chess enthusiasts',
    'campus' => 'Calais'
]);
```

#### `updateClub($id, $data)`

Update club information.

```php
$success = $clubs->updateClub(1, [
    'nom_club' => 'Updated Name',
    'description' => 'New description'
]);
```

#### `deleteClub($id)`

Delete a club.

```php
$success = $clubs->deleteClub(1);
```

### ClubController

#### Route: `?page=club-list`

**Method:** GET/POST
**Description:** List clubs and edit
**Permissions:** Admin (level 4+)
**POST Parameters:**

```php
[
    'club' => 'club_name',
    'nom_club' => 'string',
    'type_club' => 'string',
    'description' => 'text',
    'campus' => 'Calais|Longuenesse|Dunkerque|Boulogne'
]
```

#### Route: `?page=club-create`

**Method:** GET/POST
**Description:** Create new club
**Permissions:** Tutor+ (level 3+)

---

## Event Model & Controller {#event}

### Event Model

#### `getAllValidatedEvents()`

Get all validated events.

```php
$events = new Event($db);
$data = $events->getAllValidatedEvents();
```

#### `getEventById($id)`

Get specific event.

```php
$event = $events->getEventById(10);
```

#### `getEventsByUser($user_id)`

Get events created by user.

```php
$userEvents = $events->getEventsByUser(1);
```

#### `getAllEvents()`

Get all events (including pending).

```php
$all = $events->getAllEvents();
```

#### `createEvent($data)`

Create new event.

```php
$success = $events->createEvent([
    'nom_event' => 'Weekly Meeting',
    'description' => 'Team discussion',
    'date_event' => '2024-01-15 10:00:00',
    'user_id' => 1,
    'campus' => 'Calais'
]);
```

#### `updateEvent($id, $data)`

Update event.

```php
$success = $events->updateEvent(1, [
    'nom_event' => 'Monthly Meeting',
    'date_event' => '2024-02-15 10:00:00'
]);
```

#### `deleteEvent($id)`

Delete event.

```php
$success = $events->deleteEvent(1);
```

### EventController

#### Route: `?page=event-list`

**Method:** GET
**Description:** List all events
**Permissions:** User+ (level 1+)

#### Route: `?page=event-view&id=X`

**Method:** GET
**Description:** View event details
**Permissions:** User+

#### Route: `?page=event-create`

**Method:** GET/POST
**Description:** Create event
**Permissions:** Manager+ (level 2+)

---

## Authentication Controller {#auth}

### Routes

#### Route: `?page=login`

**Method:** GET/POST
**Description:** User login/password reset
**POST Parameters:**

```php
// Login
['mail' => 'email', 'password' => 'pass']

// Password reset flow
['check-email' => true]
['send_reset_code' => true, 'mail' => 'email']
['verify_reset_code' => true, 'reset_code' => '123456']
['reset_password' => true, 'password' => 'new_pass', 'cpassword' => 'confirm']
```

#### Route: `?page=logout`

**Method:** GET
**Description:** Logout and destroy session
**Redirects to:** Login page

---

## Validation Model & Controller {#validation}

### Validation Model

#### `getPendingClubs()`

Get clubs awaiting validation.

```php
$validation = new Validation($db);
$pending = $validation->getPendingClubs();
```

#### `validateClub($id, $admin, $tuteur, $final)`

Validate club at different levels.

```php
$validation->validateClub(1, 1, 1, 1);  // All approved
```

#### `getPendingEvents()`

Get events awaiting validation.

```php
$pending = $validation->getPendingEvents();
```

#### `validateEvent($id, $bde, $tuteur, $final)`

Validate event.

```php
$validation->validateEvent(1, 1, 1, 1);
```

### ValidationController

#### Route: `?page=pending-clubs`

**Method:** GET/POST
**Description:** Manage pending clubs
**Permissions:** Admin (level 3+)

#### Route: `?page=pending-events`

**Method:** GET/POST
**Description:** Manage pending events
**Permissions:** Admin (level 3+)

---

## Event Subscription {#subscription}

### EventSubscription Model

#### `getEventSubscribers($event_id)`

Get all subscribers for event.

```php
$subscription = new EventSubscription($db);
$subscribers = $subscription->getEventSubscribers(1);
```

#### `getUserSubscriptions($user_id)`

Get all events user is subscribed to.

```php
$userEvents = $subscription->getUserSubscriptions($_SESSION['id']);
```

#### `subscribeToEvent($event_id, $user_id)`

Subscribe user to event.

```php
$success = $subscription->subscribeToEvent(1, 5);
```

#### `unsubscribeFromEvent($event_id, $user_id)`

Unsubscribe user from event.

```php
$success = $subscription->unsubscribeFromEvent(1, 5);
```

#### `isSubscribed($event_id, $user_id)`

Check if user is subscribed.

```php
$subscribed = $subscription->isSubscribed(1, 5);  // Returns: true/false
```

### SubscriptionController

#### Route: `?page=subscribe`

**Method:** POST
**Description:** Subscribe to event
**POST Parameters:** `['event_id' => int]`
**Permissions:** User+

#### Route: `?page=unsubscribe`

**Method:** POST
**Description:** Unsubscribe from event
**POST Parameters:** `['event_id' => int]`
**Permissions:** User+

#### Route: `?page=my-subscriptions`

**Method:** GET
**Description:** View my subscribed events
**Permissions:** User+

---

## Permission Levels

```
0 = Guest
1 = Member
2 = Club Manager
3 = Admin/Tutor
4 = Super Admin
```

## Helper Functions

### `validateSession()`

Ensures user is logged in. Redirects to login if not.

```php
validateSession();  // Throws redirect if not logged in
```

### `checkPermission($level)`

Ensures user has required permission. Redirects home if insufficient.

```php
checkPermission(3);  // Requires level 3+
```

### `redirect($path)`

Redirect to new URL and exit.

```php
redirect('index.php?page=home');
```

### `sendEmail($to, $subject, $message)`

Send email via SMTP.

```php
sendEmail('user@example.com', 'Welcome!', 'Hello there!');
```

---

## Error Handling

Controllers set error and success messages:

```php
$error_msg = "An error occurred";
$success_msg = "Operation completed";
```

These are passed to views and can be displayed:

```php
<?php if(!empty($error_msg)): ?>
    <div class="error"><?= $error_msg ?></div>
<?php endif; ?>
```

---

## Data Types

### User

```php
[
    'id' => int,
    'nom' => string,
    'prenom' => string,
    'mail' => string,
    'permission' => int,
    'password' => string (hashed)
]
```

### Club

```php
[
    'club_id' => int,
    'nom_club' => string,
    'type_club' => string,
    'description' => string,
    'campus' => string,
    'validation_final' => bool,
    'tuteur' => int (user_id)
]
```

### Event

```php
[
    'event_id' => int,
    'nom_event' => string,
    'description' => string,
    'date_event' => string (datetime),
    'campus' => string,
    'user_id' => int,
    'validation_finale' => bool
]
```
