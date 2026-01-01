<?php
/**
 * =============================================================================
 * FICHIER DE DÉMARRAGE (BOOTSTRAP) DE L'APPLICATION
 * =============================================================================
 * 
 * Ce fichier initialise tous les composants essentiels de l'application :
 * - Définition des chemins d'accès (ROOT_PATH, MODELS_PATH, etc.)
 * - Chargement de l'environnement (.env)
 * - Gestion des erreurs
 * - Configuration de la sécurité (headers HTTP, CSRF)
 * - Configuration des sessions
 * - Connexion à la base de données
 * - Chargement automatique des modèles et contrôleurs
 * 
 * @author Équipe de développement EILCO
 * @version 2.0
 * @since 2025
 */

// =============================================================================
// DÉFINITION DES CHEMINS D'ACCÈS
// =============================================================================
define('ROOT_PATH', dirname(__DIR__));
define('MODELS_PATH', ROOT_PATH . '/models');
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('CONFIG_PATH', __DIR__);
define('LOGS_PATH', ROOT_PATH . '/logs');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');

// =============================================================================
// CHARGEMENT DE L'ENVIRONNEMENT
// =============================================================================
require_once CONFIG_PATH . '/Environment.php';
Environment::load();

// =============================================================================
// GESTIONNAIRE D'ERREURS
// =============================================================================
require_once CONFIG_PATH . '/ErrorHandler.php';

// =============================================================================
// CONFIGURATION DE LA SÉCURITÉ
// =============================================================================
require_once CONFIG_PATH . '/Security.php';

// Application des en-têtes de sécurité HTTP
Security::setHeaders();

// Forcer HTTPS en production
Security::enforceHttps();

// Récupération de la configuration de sécurité
$securityConfig = Environment::getSecurityConfig();

// =============================================================================
// CONFIGURATION DES SESSIONS PHP
// =============================================================================
// Note: Ces paramètres doivent être définis AVANT session_start()
// Utilise Security::isHttps() pour detecter HTTPS meme derriere un proxy
$isSecure = $securityConfig['cookie_secure'] || 
            Security::isHttps() || 
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

// =============================================================================
// PROTECTION CONTRE LA FIXATION DE SESSION
// =============================================================================
// Régénère l'ID de session périodiquement pour prévenir les attaques
if (!isset($_SESSION['_created'])) {
    $_SESSION['_created'] = time();
} else if (time() - $_SESSION['_created'] > 1800) {
    // Régénération toutes les 30 minutes
    session_regenerate_id(true);
    $_SESSION['_created'] = time();
}

// =============================================================================
// CONNEXION À LA BASE DE DONNÉES
// =============================================================================
require_once CONFIG_PATH . '/Database.php';
require_once CONFIG_PATH . '/Email.php';
$database = new Database();
$db = $database->connect();

// =============================================================================
// CHARGEMENT AUTOMATIQUE DES MODÈLES ET CONTRÔLEURS
// =============================================================================
foreach (glob(MODELS_PATH . '/*.php') as $model) {
    require_once $model;
}

foreach (glob(CONTROLLERS_PATH . '/*.php') as $controller) {
    require_once $controller;
}

// =============================================================================
// FONCTIONS UTILITAIRES (HELPERS)
// =============================================================================

/**
 * Redirige l'utilisateur vers une URL spécifiée
 * 
 * @param string $path Chemin de redirection
 * @return void
 */
function redirect($path) {
    header('Location: ' . $path);
    exit;
}

/**
 * Valide que l'utilisateur est connecté
 * Redirige vers la page de connexion si non authentifié
 * 
 * @return void
 */
function validateSession() {
    if (!isset($_SESSION['id'])) {
        // Stocke l'URL demandée pour redirection après connexion
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect('/index.php?page=login');
    }
}

/**
 * Vérifie que l'utilisateur possède le niveau de permission requis
 * Lève une erreur 403 (Accès refusé) si permissions insuffisantes
 * 
 * @param int $required_level Niveau de permission minimum requis
 * @return void
 */
function checkPermission($required_level) {
    validateSession();
    $userPermission = $_SESSION['permission'] ?? 0;
    if ($userPermission < $required_level) {
        // Journalisation de la tentative d'accès non autorisé
        $userId = $_SESSION['id'] ?? 'unknown';
        $requestedPage = $_GET['page'] ?? 'unknown';
        error_log("[SECURITY] Tentative d'accès non autorisé: Utilisateur ID $userId (permission: $userPermission) a tenté d'accéder à '$requestedPage' (requis: $required_level)");
        
        // Lever une erreur 403 Forbidden
        ErrorHandler::renderHttpError(403, "Vous n'avez pas les permissions nécessaires pour accéder à cette page. Niveau requis: $required_level, votre niveau: $userPermission");
    }
}

/**
 * Valide le jeton CSRF pour les requêtes POST
 * Termine l'exécution avec une erreur si le jeton est invalide
 * 
 * @return void
 */
function validateCsrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!Security::validateCsrfToken($token)) {
            http_response_code(403);
            die('Jeton de sécurité invalide. Veuillez rafraîchir la page et réessayer.');
        }
    }
}

