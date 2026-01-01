<?php

// Define root path first
define('ROOT_PATH', dirname(__DIR__));
define('MODELS_PATH', ROOT_PATH . '/models');
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('CONFIG_PATH', __DIR__);
define('LOGS_PATH', ROOT_PATH . '/logs');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');

// Load environment configuration
require_once CONFIG_PATH . '/Environment.php';
Environment::load();

// Load error handler based on environment
require_once CONFIG_PATH . '/ErrorHandler.php';

// Load security configuration
require_once CONFIG_PATH . '/Security.php';

// Apply security headers
Security::setHeaders();

// Enforce HTTPS in production
Security::enforceHttps();

// Get security configuration from Environment
$securityConfig = Environment::getSecurityConfig();

// Session Configuration (must be before session_start)
$isSecure = $securityConfig['cookie_secure'] || 
            (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
            Environment::isProduction();
$sessionLifetime = $securityConfig['session_lifetime'];

ini_set('session.cookie_httponly', $securityConfig['cookie_httponly'] ? 1 : 0);
ini_set('session.cookie_secure', $isSecure ? 1 : 0);
ini_set('session.cookie_samesite', $securityConfig['cookie_samesite']);
ini_set('session.use_strict_mode', 1);
ini_set('session.gc_maxlifetime', $sessionLifetime);

session_set_cookie_params([
    'lifetime' => $sessionLifetime,
    'path' => '/',
    'httponly' => $securityConfig['cookie_httponly'],
    'secure' => $isSecure,
    'samesite' => $securityConfig['cookie_samesite']
]);

session_start();

// Regenerate session ID periodically to prevent session fixation
if (!isset($_SESSION['_created'])) {
    $_SESSION['_created'] = time();
} else if (time() - $_SESSION['_created'] > 1800) {
    // Regenerate session ID every 30 minutes
    session_regenerate_id(true);
    $_SESSION['_created'] = time();
}

// Database connection
require_once CONFIG_PATH . '/Database.php';
require_once CONFIG_PATH . '/Email.php';
$database = new Database();
$db = $database->connect();

// Include all models
foreach (glob(MODELS_PATH . '/*.php') as $model) {
    require_once $model;
}

// Include all controllers
foreach (glob(CONTROLLERS_PATH . '/*.php') as $controller) {
    require_once $controller;
}

// Helper function for redirection
function redirect($path) {
    header('Location: ' . $path);
    exit;
}

// Helper function for session validation
function validateSession() {
    if (!isset($_SESSION['id'])) {
        // Store the requested URL for redirect after login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect('/index.php?page=login');
    }
}

// Helper function for permission check - raises 403 Forbidden if insufficient permissions
function checkPermission($required_level) {
    validateSession();
    $userPermission = $_SESSION['permission'] ?? 0;
    if ($userPermission < $required_level) {
        // Log the unauthorized access attempt
        $userId = $_SESSION['id'] ?? 'unknown';
        $requestedPage = $_GET['page'] ?? 'unknown';
        error_log("[SECURITY] Unauthorized access attempt: User ID $userId (permission: $userPermission) tried to access '$requestedPage' (required: $required_level)");
        
        // Raise 403 Forbidden error
        ErrorHandler::renderHttpError(403, "Vous n'avez pas les permissions nécessaires pour accéder à cette page. Niveau requis: $required_level, votre niveau: $userPermission");
    }
}

// CSRF validation helper for POST requests
function validateCsrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!Security::validateCsrfToken($token)) {
            http_response_code(403);
            die('Invalid security token. Please refresh the page and try again.');
        }
    }
}

