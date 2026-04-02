<?php
/**
 * Fichier de configuration des routes
 * 
 * Ce fichier contient toutes les routes de l'application organisees par categorie.
 * Chaque route associe un parametre 'page' a une action de controleur et une vue.
 * 
 * Structure d'une route :
 * - 'permission' : niveau de permission minimum requis (null = public, 0-5 = niveau specifique)
 * - 'auth'       : necessite une authentification (true/false)
 * - 'controller' : nom de la classe controleur
 * - 'method'     : methode du controleur a appeler
 * - 'view'       : chemin vers le fichier vue (relatif a VIEWS_PATH)
 * 
 * @package Routes
 */

return [
    // ==========================================
    // ROUTES PUBLIQUES (Pas d'authentification requise)
    // ==========================================
    
    'home' => [
        'permission' => null,
        'auth' => false,
        'controller' => 'HomeController',
        'method' => 'index',
        'view' => '/home/index.php'
    ],

    'legal' => [
        'permission' => null,
        'auth' => false,
        'controller' => 'HomeController',
        'method' => 'legal',
        'view' => '/home/legal.php'
    ],
    
    'calendar-data' => [
        'permission' => null,
        'auth' => false,
        'controller' => 'HomeController',
        'method' => 'calendarData',
        'view' => null
    ],
    
    'subscribe-ajax' => [
        'permission' => null,
        'auth' => true,
        'methods' => ['POST'],
        'controller' => 'SubscriptionController',
        'method' => 'toggleSubscriptionAjax',
        'view' => null
    ],
    
    // ==========================================
    // ROUTES D'AUTHENTIFICATION
    // ==========================================
    
    'login' => [
        'permission' => null,
        'auth' => false,
        'controller' => 'AuthController',
        'method' => 'login',
        'view' => '/auth/login.php',
        'redirect_if_logged' => true
    ],
    
    'register' => [
        'permission' => null,
        'auth' => false,
        'controller' => 'AuthController',
        'method' => 'register',
        'view' => '/auth/register.php',
        'redirect_if_logged' => true
    ],
    
    'logout' => [
        'permission' => null,
        'auth' => false,
        'controller' => 'AuthController',
        'method' => 'logout',
        'view' => null
    ],
    
    // ==========================================
    // ROUTES UTILISATEUR (Protegees)
    // ==========================================
    
    'profile' => [
        'permission' => null,
        'auth' => true,
        'controller' => 'UserController',
        'method' => 'viewProfile',
        'view' => '/user/profile.php'
    ],
    
    'profile-edit' => [
        'permission' => null,
        'auth' => true,
        'controller' => 'UserController',
        'method' => 'editProfile',
        'view' => '/user/profile_edit.php'
    ],
    
    'dashboard' => [
        'permission' => null,
        'auth' => true,
        'controller' => 'UserController',
        'method' => 'dashboard',
        'view' => '/user/dashboard.php'
    ],
    
    'users-list' => [
        'permission' => 3,
        'auth' => true,
        'controller' => 'UserController',
        'method' => 'listAllUsers',
        'view' => '/user/list.php'
    ],
    
    // ==========================================
    // ROUTES EVENEMENTS
    // ==========================================
    
    'event-list' => [
        'permission' => null,
        'auth' => false,
        'controller' => 'EventController',
        'method' => 'listEvents',
        'view' => '/event/list.php'
    ],
    
    'event-view' => [
        'permission' => null,
        'auth' => false,
        'controller' => 'EventController',
        'method' => 'viewEvent',
        'view' => '/event/view.php'
    ],
    
    'event-create' => [
        'permission' => [1,3,4,5],
        'auth' => true,
        'controller' => 'EventController',
        'method' => 'createEvent',
        'view' => '/event/create.php'
    ],
    
    'event-report' => [
        'permission' => null,
        'auth' => true,
        'controller' => 'EventController',
        'method' => 'eventReport',
        'view' => '/event/report.php'
    ],
    
    'my-events' => [
        'permission' => null,
        'auth' => true,
        'controller' => 'EventController',
        'method' => 'myEvents',
        'view' => '/event/my_list.php'
    ],

    'update-event' => [
        'permission' => null,
        'auth' => true,
        'controller' => 'EventController',
        'method' => 'updateEvent',
        'view' => '/event/edit.php'
    ],
    
    // ==========================================
    // ROUTES INSCRIPTIONS (Protegees)
    // ==========================================
    
    'subscribe' => [
        'permission' => null,
        'auth' => true,
        'methods' => ['POST'],
        'controller' => 'SubscriptionController',
        'method' => 'subscribe',
        'view' => null
    ],
    
    'unsubscribe' => [
        'permission' => null,
        'auth' => true,
        'methods' => ['POST'],
        'controller' => 'SubscriptionController',
        'method' => 'unsubscribe',
        'view' => null
    ],
    
    'my-subscriptions' => [
        'permission' => null,
        'auth' => true,
        'controller' => 'SubscriptionController',
        'method' => 'getUserSubscriptions',
        'view' => '/subscription/list.php'
    ],
    
    // ==========================================
    // ROUTES CLUBS
    // ==========================================
    
    'club-list' => [
        'permission' => 1,
        'auth' => true,
        'controller' => 'ClubController',
        'method' => 'listClubs',
        'view' => '/club/list.php'
    ],

    'clubs-browse' => [
        'permission' => null,
        'auth' => false,
        'controller' => 'ClubController',
        'method' => 'browseClubs',
        'view' => '/club/browse.php'
    ],

    'my-clubs' => [
        'permission' => null,
        'auth' => true,
        'controller' => 'ClubController',
        'method' => 'myClubs',
        'view' => '/club/my_clubs.php'
    ],

    'club-edit' => [
        'permission' => null,
        'auth' => true,
        'controller' => 'ClubController',
        'method' => 'editClub',
        'view' => '/club/edit.php'
    ],
    
    'club-view' => [
        'permission' => null,
        'auth' => false,
        'controller' => 'ClubController',
        'method' => 'viewClub',
        'view' => '/club/view.php'
    ],
    
    'club-create' => [
        'permission' => null,
        'auth' => true,
        'controller' => 'ClubController',
        'method' => 'createClub',
        'view' => '/club/create.php'
    ],
    
    'export-members' => [
        'permission' => 2,
        'auth' => true,
        'controller' => 'ClubController',
        'method' => 'exportMembers',
        'view' => null
    ],
    
    // ==========================================
    // ROUTES VALIDATION (Admin/Tuteur)
    // ==========================================
    
    'pending-clubs' => [
        'permission' => 3,
        'auth' => true,
        'controller' => 'ValidationController',
        'method' => 'validateClub',
        'view' => '/validation/pending_clubs.php'
    ],
    
    'pending-events' => [
        'permission' => 3,
        'auth' => true,
        'controller' => 'ValidationController',
        'method' => 'validateEvent',
        'view' => '/validation/pending_events.php'
    ],
    
    'tutoring' => [
        'permission' => [2, 3, 4, 5],
        'auth' => true,
        'controller' => 'ValidationController',
        'method' => 'tutoring',
        'view' => '/validation/tutoring.php'
    ],

    
    
    // ==========================================
    // ROUTES ADMINISTRATION (BDE et plus)
    // ==========================================
    
    'admin' => [
        'permission' => 3,
        'auth' => true,
        'controller' => 'AdminController',
        'method' => 'dashboard',
        'view' => '/admin/dashboard.php'
    ],
    
    'event-analytics' => [
        'permission' => 3,
        'auth' => true,
        'controller' => 'AdminController',
        'method' => 'eventAnalytics',
        'view' => '/event/analytics.php'
    ],
    
    'admin-reports' => [
        'permission' => 3,
        'auth' => true,
        'controller' => 'AdminController',
        'method' => 'generateReport',
        'view' => '/admin/reports.php'
    ],
    
    'admin-event-reports' => [
        'permission' => 2,
        'auth' => true,
        'controller' => 'AdminController',
        'method' => 'eventReports',
        'view' => '/admin/event_reports.php'
    ],
    
    // ==========================================
    // ROUTES EXPORT CSV (Tuteur et supérieur)
    // ==========================================

    'export' => [
        'permission' => 2,
        'auth' => true,
        'controller' => 'ExportController',
        'method' => 'index',
        'view' => '/export/index.php'
    ],

    'export-clubs' => [
        'permission' => 2,
        'auth' => true,
        'controller' => 'ExportController',
        'method' => 'exportClubs',
        'view' => null
    ],

    'export-club-members' => [
        'permission' => 2,
        'auth' => true,
        'controller' => 'ExportController',
        'method' => 'exportClubMembers',
        'view' => null
    ],

    'export-soutenance-members' => [
        'permission' => 2,
        'auth' => true,
        'controller' => 'ExportController',
        'method' => 'exportSoutenanceMembers',
        'view' => null
    ],

    'export-club-events' => [
        'permission' => 2,
        'auth' => true,
        'controller' => 'ExportController',
        'method' => 'exportClubEvents',
        'view' => null
    ],

    'export-events-period' => [
        'permission' => 2,
        'auth' => true,
        'controller' => 'ExportController',
        'method' => 'exportEventsByPeriod',
        'view' => null
    ],

    'export-past-events' => [
        'permission' => 2,
        'auth' => true,
        'controller' => 'ExportController',
        'method' => 'exportPastEvents',
        'view' => null
    ],

    'export-upcoming-events' => [
        'permission' => 2,
        'auth' => true,
        'controller' => 'ExportController',
        'method' => 'exportUpcomingEvents',
        'view' => null
    ],

    // ==========================================
    // ROUTES SUPER ADMIN (Permission 5 uniquement)
    // ==========================================
    
    'admin-settings' => [
        'permission' => 5,
        'auth' => true,
        'controller' => 'AdminController',
        'method' => 'settings',
        'view' => '/admin/settings.php'
    ],
    
    'export-data' => [
        'permission' => 5,
        'auth' => true,
        'controller' => 'AdminController',
        'method' => 'exportData',
        'view' => null
    ],
    
    'admin-users' => [
        'permission' => 5,
        'auth' => true,
        'controller' => 'AdminController',
        'method' => 'listUsers',
        'view' => '/admin/users.php'
    ],
    
    'admin-user-view' => [
        'permission' => 5,
        'auth' => true,
        'controller' => 'AdminController',
        'method' => 'viewUser',
        'view' => '/admin/user_view.php'
    ],
    
    'admin-audit' => [
        'permission' => 5,
        'auth' => true,
        'controller' => 'AdminController',
        'method' => 'auditLog',
        'view' => '/admin/audit.php'
    ],
    
    'admin-database' => [
        'permission' => 5,
        'auth' => true,
        'controller' => 'AdminController',
        'method' => 'databaseTools',
        'view' => '/admin/database.php'
    ],
    
    'update-permission' => [
        'permission' => 5,
        'auth' => true,
        'methods' => ['POST'],
        'controller' => 'AdminController',
        'method' => 'updatePermission',
        'view' => null
    ],
    
    'delete-user' => [
        'permission' => 5,
        'auth' => true,
        'methods' => ['POST'],
        'controller' => 'AdminController',
        'method' => 'deleteUser',
        'view' => null
    ],
];
