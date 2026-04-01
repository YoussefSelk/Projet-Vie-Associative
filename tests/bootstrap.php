<?php
declare(strict_types=1);

/**
 * PHPUnit Bootstrap File
 * 
 * Sets up the test environment by loading required classes and
 * providing mock/stub infrastructure for testing without a real database.
 */

// Define paths used by application code
define('ROOT_PATH', dirname(__DIR__));
define('MODELS_PATH', ROOT_PATH . '/models');
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('LOGS_PATH', ROOT_PATH . '/logs');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('BASE_URL', 'http://localhost');

// Start session for tests that need it
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load Environment first (needed by Security and ErrorHandler)
require_once CONFIG_PATH . '/Environment.php';

// Load ErrorHandler (used by some models)
require_once CONFIG_PATH . '/ErrorHandler.php';

// Load the Validator class (standalone, no dependencies)
require_once CONFIG_PATH . '/Validator.php';

// Load model classes
require_once MODELS_PATH . '/Club.php';
require_once MODELS_PATH . '/User.php';
require_once MODELS_PATH . '/Event.php';
require_once MODELS_PATH . '/Validation.php';
require_once MODELS_PATH . '/ClubMember.php';
require_once MODELS_PATH . '/EventSubscription.php';
require_once MODELS_PATH . '/EventReport.php';

// Load Security class
require_once CONFIG_PATH . '/Security.php';

// Load controller classes
require_once CONTROLLERS_PATH . '/ExportController.php';

// ─── Stubs des fonctions globales de l'application ───────────────────────────
// Ces fonctions sont définies dans config/bootstrap.php de l'application ;
// les stubs ci-dessous permettent de tester les contrôleurs sans charger
// l'intégralité du bootstrap (qui nécessite une connexion DB réelle).
if (!function_exists('checkPermission')) {
    function checkPermission(int $level): void {}
}
if (!function_exists('validateSession')) {
    function validateSession(): void {}
}
if (!function_exists('redirect')) {
    function redirect(string $url): void {}
}

// Autoloader for test helpers
spl_autoload_register(function (string $class): void {
    $prefix = 'Tests\\';
    $baseDir = __DIR__ . '/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
