<?php

// Load bootstrap configuration
require_once __DIR__ . '/config/bootstrap.php';

// Get page parameter from URL - sanitize input
$page = isset($_GET['page']) ? preg_replace('/[^a-zA-Z0-9\-_]/', '', $_GET['page']) : 'home';

// CSRF validation for all POST requests (except login/register which handle it internally)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $publicPostRoutes = ['login', 'register'];
    if (!in_array($page, $publicPostRoutes)) {
        $token = $_POST['csrf_token'] ?? '';
        if (!Security::validateCsrfToken($token)) {
            http_response_code(403);
            die('<div style="text-align:center;padding:50px;"><h1>403 - Accès refusé</h1><p>Token de sécurité invalide. <a href="javascript:history.back()">Retour</a></p></div>');
        }
    }
}

// Route to appropriate controller and action
switch ($page) {
    // Authentication Routes (Public)
    case 'login':
        // Redirect if already logged in
        if (isset($_SESSION['id'])) {
            redirect('?page=home');
        }
        $controller = new AuthController($db);
        $data = $controller->login();
        extract($data);
        include VIEWS_PATH . '/auth_login.php';
        break;

    case 'register':
        // Redirect if already logged in
        if (isset($_SESSION['id'])) {
            redirect('?page=home');
        }
        $controller = new AuthController($db);
        $data = $controller->register();
        extract($data);
        include VIEWS_PATH . '/auth_register.php';
        break;

    case 'logout':
        $controller = new AuthController($db);
        $controller->logout();
        break;

    // User Routes (Protected)
    case 'profile':
        validateSession();
        $controller = new UserController($db);
        $data = $controller->viewProfile();
        extract($data);
        include VIEWS_PATH . '/user_profile.php';
        break;

    case 'profile-edit':
        validateSession();
        $controller = new UserController($db);
        $data = $controller->editProfile();
        extract($data);
        include VIEWS_PATH . '/user_profile_edit.php';
        break;

    case 'users-list':
        checkPermission(3); // Admin only
        $controller = new UserController($db);
        $data = $controller->listAllUsers();
        extract($data);
        include VIEWS_PATH . '/user_list.php';
        break;

    // Event Routes (Protected)
    case 'event-list':
        validateSession();
        $controller = new EventController($db);
        $data = $controller->listEvents();
        extract($data);
        include VIEWS_PATH . '/event_list.php';
        break;

    case 'event-view':
        validateSession();
        $controller = new EventController($db);
        $data = $controller->viewEvent();
        extract($data);
        include VIEWS_PATH . '/event_view.php';
        break;

    case 'event-create':
        checkPermission(2); // Club members and above
        $controller = new EventController($db);
        $data = $controller->createEvent();
        extract($data);
        include VIEWS_PATH . '/event_create.php';
        break;

    case 'event-report':
        validateSession();
        $controller = new EventController($db);
        $data = $controller->eventReport();
        extract($data);
        include VIEWS_PATH . '/event_report.php';
        break;

    case 'my-events':
        validateSession();
        $controller = new EventController($db);
        $data = $controller->myEvents();
        extract($data);
        include VIEWS_PATH . '/event_my_list.php';
        break;

    // Validation Routes (Admin only)
    case 'pending-clubs':
        checkPermission(3);
        $controller = new ValidationController($db);
        $data = $controller->validateClub();
        extract($data);
        include VIEWS_PATH . '/validation_pending_clubs.php';
        break;

    case 'pending-events':
        checkPermission(3);
        $controller = new ValidationController($db);
        $data = $controller->validateEvent();
        extract($data);
        include VIEWS_PATH . '/validation_pending_events.php';
        break;

    // Subscription Routes (Protected)
    case 'subscribe':
        validateSession();
        $controller = new SubscriptionController($db);
        $controller->subscribe();
        break;

    case 'unsubscribe':
        validateSession();
        $controller = new SubscriptionController($db);
        $controller->unsubscribe();
        break;

    case 'my-subscriptions':
        validateSession();
        $controller = new SubscriptionController($db);
        $data = $controller->getUserSubscriptions();
        extract($data);
        include VIEWS_PATH . '/subscription_list.php';
        break;

    // Club Routes (Mixed permissions)
    case 'club-list':
        checkPermission(3); // Admin only
        $controller = new ClubController($db);
        $data = $controller->listClubs();
        extract($data);
        include VIEWS_PATH . '/club_list.php';
        break;

    case 'club-view':
        // Public route - anyone can view clubs
        $controller = new ClubController($db);
        $data = $controller->viewClub();
        extract($data);
        echo "<!-- DEBUG From index: Received club ID = " . ($club['club_id'] ?? 'NULL') . " -->\n";
        echo "<!-- DEBUG From index: Received club Name = " . ($club['nom_club'] ?? 'NULL') . " -->\n";
        include VIEWS_PATH . '/club_view.php';
        break;

    case 'club-create':
        validateSession();
        $controller = new ClubController($db);
        $data = $controller->createClub();
        extract($data);
        include VIEWS_PATH . '/club_create.php';
        break;
    
    case 'export-members':
        checkPermission(3);
        $controller = new ClubController($db);
        $controller->exportMembers();
        break;

    // Admin Routes
    case 'admin':
        checkPermission(3);
        $controller = new HomeController($db);
        $data = $controller->admin();
        extract($data);
        include VIEWS_PATH . '/home_admin.php';
        break;

    // Tutor Routes
    case 'tutoring':
        validateSession();
        $controller = new ValidationController($db);
        $data = $controller->tutoring();
        extract($data);
        include VIEWS_PATH . '/validation_tutoring.php';
        break;

    // Default Home Route (Public)
    case 'home':
    default:
        $controller = new HomeController($db);
        $data = $controller->index();
        extract($data);
        include VIEWS_PATH . '/home_index.php';
        break;
}
