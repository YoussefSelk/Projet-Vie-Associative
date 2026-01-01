# Documentation du Routage

## Vue d'Ensemble

Le système utilise un routeur personnalisé (`config/Router.php`) qui gère toutes les requêtes via un point d'entrée unique (`index.php`).

## Architecture du Routage

```
                    ┌────────────────────┐
                    │    REQUÊTE HTTP    │
                    │  ?page=club&id=5   │
                    └─────────┬──────────┘
                              │
                              ▼
                    ┌────────────────────┐
                    │     index.php      │
                    │  (Point d'entrée)  │
                    └─────────┬──────────┘
                              │
                              ▼
                    ┌────────────────────┐
                    │   bootstrap.php    │
                    │ (Initialisation)   │
                    └─────────┬──────────┘
                              │
                              ▼
                    ┌────────────────────┐
                    │   routes/web.php   │
                    │ (Définition routes)│
                    └─────────┬──────────┘
                              │
                              ▼
                    ┌────────────────────┐
                    │      Router        │
                    │   (Dispatching)    │
                    └─────────┬──────────┘
                              │
              ┌───────────────┼───────────────┐
              │               │               │
              ▼               ▼               ▼
       ┌──────────┐    ┌──────────┐    ┌──────────┐
       │Controller│    │Controller│    │Controller│
       │   Club   │    │  Event   │    │   User   │
       └──────────┘    └──────────┘    └──────────┘
```

## Définition des Routes

Les routes sont définies dans `routes/web.php` :

```php
<?php
return [
    // Routes publiques
    'home'     => ['controller' => 'HomeController',  'action' => 'index'],
    'login'    => ['controller' => 'AuthController',  'action' => 'login'],
    'register' => ['controller' => 'AuthController',  'action' => 'register'],
    'logout'   => ['controller' => 'AuthController',  'action' => 'logout'],

    // Routes clubs
    'clubs'       => ['controller' => 'ClubController', 'action' => 'list'],
    'club'        => ['controller' => 'ClubController', 'action' => 'view'],
    'club-create' => ['controller' => 'ClubController', 'action' => 'create', 'auth' => true],

    // Routes événements
    'events'       => ['controller' => 'EventController', 'action' => 'list'],
    'event'        => ['controller' => 'EventController', 'action' => 'view'],
    'event-create' => ['controller' => 'EventController', 'action' => 'create', 'auth' => true],

    // Routes admin
    'admin'          => ['controller' => 'AdminController', 'action' => 'dashboard', 'permission' => 5],
    'admin-users'    => ['controller' => 'AdminController', 'action' => 'users', 'permission' => 5],
    'admin-settings' => ['controller' => 'AdminController', 'action' => 'settings', 'permission' => 5],
];
```

## Structure d'une Route

```php
'nom-route' => [
    'controller' => 'NomController',    // Classe contrôleur (requis)
    'action'     => 'methode',          // Méthode à appeler (requis)
    'auth'       => true,               // Authentification requise (optionnel)
    'permission' => 2,                  // Permission minimale (optionnel)
    'methods'    => ['GET', 'POST'],    // Méthodes HTTP (optionnel)
]
```

## Liste Complète des Routes

### Routes Publiques

| Route      | Contrôleur      | Action   | URL                | Description      |
| ---------- | --------------- | -------- | ------------------ | ---------------- |
| `home`     | HomeController  | index    | `?page=home`       | Page d'accueil   |
| `login`    | AuthController  | login    | `?page=login`      | Connexion        |
| `register` | AuthController  | register | `?page=register`   | Inscription      |
| `logout`   | AuthController  | logout   | `?page=logout`     | Déconnexion      |
| `clubs`    | ClubController  | list     | `?page=clubs`      | Liste des clubs  |
| `club`     | ClubController  | view     | `?page=club&id=X`  | Détail club      |
| `events`   | EventController | list     | `?page=events`     | Liste événements |
| `event`    | EventController | view     | `?page=event&id=X` | Détail événement |

### Routes Authentifiées (Membre+)

| Route           | Contrôleur             | Action    | URL                   | Permission | Description        |
| --------------- | ---------------------- | --------- | --------------------- | ---------- | ------------------ |
| `club-create`   | ClubController         | create    | `?page=club-create`   | 1          | Créer un club      |
| `event-create`  | EventController        | create    | `?page=event-create`  | 1          | Créer un événement |
| `profile`       | UserController         | profile   | `?page=profile`       | 1          | Mon profil         |
| `profile-edit`  | UserController         | edit      | `?page=profile-edit`  | 1          | Éditer profil      |
| `my-events`     | EventController        | myList    | `?page=my-events`     | 1          | Mes événements     |
| `my-clubs`      | ClubController         | myClubs   | `?page=my-clubs`      | 1          | Mes clubs          |
| `dashboard`     | UserController         | dashboard | `?page=dashboard`     | 1          | Tableau de bord    |
| `subscriptions` | SubscriptionController | list      | `?page=subscriptions` | 1          | Mes inscriptions   |

### Routes Tuteur (Permission 2+)

| Route               | Contrôleur           | Action        | URL                       | Description           |
| ------------------- | -------------------- | ------------- | ------------------------- | --------------------- |
| `validation-clubs`  | ValidationController | pendingClubs  | `?page=validation-clubs`  | Clubs en attente      |
| `validation-events` | ValidationController | pendingEvents | `?page=validation-events` | Événements en attente |
| `tutoring`          | ValidationController | tutoring      | `?page=tutoring`          | Dashboard tuteur      |

### Routes BDE (Permission 3+)

| Route       | Contrôleur      | Action    | URL               | Description  |
| ----------- | --------------- | --------- | ----------------- | ------------ |
| `reports`   | AdminController | reports   | `?page=reports`   | Rapports     |
| `analytics` | EventController | analytics | `?page=analytics` | Statistiques |

### Routes Admin (Permission 5)

| Route            | Contrôleur      | Action    | URL                     | Description          |
| ---------------- | --------------- | --------- | ----------------------- | -------------------- |
| `admin`          | AdminController | dashboard | `?page=admin`           | Dashboard admin      |
| `admin-users`    | AdminController | users     | `?page=admin-users`     | Gestion utilisateurs |
| `admin-user`     | AdminController | userView  | `?page=admin-user&id=X` | Détail utilisateur   |
| `admin-settings` | AdminController | settings  | `?page=admin-settings`  | Paramètres système   |
| `admin-database` | AdminController | database  | `?page=admin-database`  | Gestion BDD          |
| `admin-audit`    | AdminController | audit     | `?page=admin-audit`     | Logs d'audit         |

### Routes API/Actions

| Route               | Contrôleur             | Action        | Méthode | Description          |
| ------------------- | ---------------------- | ------------- | ------- | -------------------- |
| `join-club`         | ClubController         | join          | POST    | Rejoindre un club    |
| `leave-club`        | ClubController         | leave         | POST    | Quitter un club      |
| `subscribe-event`   | SubscriptionController | subscribe     | POST    | S'inscrire événement |
| `unsubscribe-event` | SubscriptionController | unsubscribe   | POST    | Se désinscrire       |
| `validate-club`     | ValidationController   | validateClub  | POST    | Valider club         |
| `reject-club`       | ValidationController   | rejectClub    | POST    | Rejeter club         |
| `validate-event`    | ValidationController   | validateEvent | POST    | Valider événement    |
| `reject-event`      | ValidationController   | rejectEvent   | POST    | Rejeter événement    |
| `export-members`    | ClubController         | exportMembers | GET     | Export CSV membres   |
| `upload-report`     | EventController        | uploadReport  | POST    | Upload rapport       |

## Classe Router

### Initialisation

```php
// Dans index.php
$router = new Router();
$routes = require 'routes/web.php';
$router->setRoutes($routes);
```

### Dispatch

```php
$router->dispatch($_GET['page'] ?? 'home');
```

### Méthodes Principales

```php
class Router {
    // Définir les routes
    public function setRoutes(array $routes): void;

    // Dispatcher la requête
    public function dispatch(string $page): void;

    // Vérifier si route existe
    public function routeExists(string $page): bool;

    // Générer URL pour une route
    public static function url(string $route, array $params = []): string;

    // Rediriger vers une route
    public static function redirect(string $route, array $params = []): void;
}
```

### Génération d'URLs

```php
// URL simple
Router::url('clubs');  // ?page=clubs

// URL avec paramètres
Router::url('club', ['id' => 5]);  // ?page=club&id=5

// URL avec action
Router::url('club', ['id' => 5, 'action' => 'edit']);  // ?page=club&id=5&action=edit
```

### Redirections

```php
// Redirection simple
Router::redirect('home');

// Avec paramètres
Router::redirect('club', ['id' => $clubId]);

// Avec message flash
$_SESSION['flash'] = ['success' => 'Club créé !'];
Router::redirect('club', ['id' => $newClubId]);
```

## Paramètres d'URL

### Paramètres Standards

| Paramètre | Type   | Description                      |
| --------- | ------ | -------------------------------- |
| `page`    | string | Nom de la route                  |
| `id`      | int    | Identifiant de ressource         |
| `action`  | string | Sous-action (edit, delete, etc.) |

### Récupération dans les Contrôleurs

```php
class ClubController {
    public function view() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            Router::redirect('clubs');
            return;
        }

        $club = Club::find($id);
        // ...
    }

    public function list() {
        $page = $_GET['p'] ?? 1;        // Pagination
        $search = $_GET['q'] ?? '';      // Recherche
        $filter = $_GET['filter'] ?? ''; // Filtres
        // ...
    }
}
```

## Gestion des Erreurs de Routage

### Route Non Trouvée (404)

```php
public function dispatch(string $page): void {
    if (!$this->routeExists($page)) {
        http_response_code(404);
        require 'views/errors/404.php';
        return;
    }
    // ...
}
```

### Accès Non Autorisé (403)

```php
if ($route['permission'] > $userPermission) {
    http_response_code(403);
    require 'views/errors/403.php';
    return;
}
```

### Authentification Requise

```php
if ($route['auth'] && !Security::isLoggedIn()) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    Router::redirect('login');
    return;
}
```

## Middleware de Route

### Vérification Avant Dispatch

```php
// Ordre d'exécution
1. Vérification route existe
2. Vérification authentification (si 'auth' => true)
3. Vérification permission (si 'permission' => X)
4. Appel du contrôleur
```

### Exemple Complet

```php
public function dispatch(string $page): void {
    // 1. Route existe ?
    if (!isset($this->routes[$page])) {
        $this->error404();
        return;
    }

    $route = $this->routes[$page];

    // 2. Authentification requise ?
    if (isset($route['auth']) && $route['auth']) {
        if (!Security::isLoggedIn()) {
            $this->redirectToLogin();
            return;
        }
    }

    // 3. Permission suffisante ?
    if (isset($route['permission'])) {
        if (!Security::hasPermission($route['permission'])) {
            $this->error403();
            return;
        }
    }

    // 4. Méthode HTTP autorisée ?
    if (isset($route['methods'])) {
        if (!in_array($_SERVER['REQUEST_METHOD'], $route['methods'])) {
            $this->error405();
            return;
        }
    }

    // 5. Appeler le contrôleur
    $controller = new $route['controller']();
    $action = $route['action'];
    $controller->$action();
}
```

## URLs Propres (URL Rewriting)

### Configuration Apache (.htaccess)

```apache
RewriteEngine On
RewriteBase /

# Ne pas réécrire les fichiers/dossiers existants
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# Réécrire vers index.php
RewriteRule ^(.*)$ index.php?page=$1 [QSA,L]
```

**URLs résultantes :**

- `/clubs` → `index.php?page=clubs`
- `/club/5` → `index.php?page=club&id=5`
- `/admin/users` → `index.php?page=admin-users`

### Configuration Nginx

```nginx
location / {
    try_files $uri $uri/ /index.php?page=$uri&$args;
}
```

## Bonnes Pratiques

### Nommage des Routes

```php
// ✅ Bon - Clair et cohérent
'clubs'        => [...],  // Liste
'club'         => [...],  // Détail
'club-create'  => [...],  // Création
'club-edit'    => [...],  // Édition

// ❌ Mauvais - Incohérent
'listeClubs'   => [...],
'voirClub'     => [...],
'newClub'      => [...],
```

### Organisation des Contrôleurs

```php
// Un contrôleur par ressource
ClubController     → clubs, club, club-create, club-edit
EventController    → events, event, event-create
UserController     → profile, profile-edit, dashboard
AdminController    → admin, admin-users, admin-settings
```

### Gestion des Actions Multiples

```php
// Dans le contrôleur, utiliser l'action en paramètre
public function view() {
    $action = $_GET['action'] ?? 'view';

    switch ($action) {
        case 'edit':
            return $this->edit();
        case 'delete':
            return $this->delete();
        default:
            return $this->show();
    }
}
```
