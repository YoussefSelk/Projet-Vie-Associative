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
// DÉMARRAGE DU BUFFER DE SORTIE
// =============================================================================
// Permet de nettoyer tout l'output en cas d'erreur pour afficher
// la page d'erreur complète sans contenu parasite
ob_start();

// =============================================================================
// CHARGEMENT DE L'ENVIRONNEMENT
// =============================================================================
require_once CONFIG_PATH . '/Environment.php';
Environment::load();
date_default_timezone_set(Environment::getTimezone());

// URL de base fiable (priorité APP_URL, fallback auto-détection durcie)
define('BASE_URL', Environment::getBaseUrl());

// Base sans host/schéma pour les ressources statiques (CSS/JS/images).
// On ne garde que le composant chemin de APP_URL : les assets sont ainsi
// TOUJOURS chargés sur la même origine que la page, donc jamais bloqués par la
// CSP, quel que soit le host visité (www / non-www) ou la config Apache.
// Les balises SEO (canonical, og:image, JSON-LD) continuent d'utiliser BASE_URL.
define('ASSET_BASE', rtrim((string) (parse_url(BASE_URL, PHP_URL_PATH) ?? ''), '/'));

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

// Expiration de session sur inactivite
if (isset($_SESSION['_last_activity']) && (time() - (int)$_SESSION['_last_activity']) > $sessionLifetime) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
    session_start();
}
$_SESSION['_last_activity'] = time();

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
    $_SESSION['_last_activity'] = time();
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

    // Supporte un tableau de permissions autorisées
    if (is_array($required_level)) {
        if (!in_array((int)$userPermission, $required_level, true)) {
            $userId = $_SESSION['id'] ?? 'unknown';
            $requestedPage = $_GET['page'] ?? 'unknown';
            ErrorHandler::logSecurity(
                "Tentative d'accès non autorisé: Utilisateur ID $userId a tenté d'accéder à '$requestedPage'",
                'WARN',
                ['permission' => $userPermission, 'required' => $required_level, 'page' => $requestedPage]
            );
            ErrorHandler::renderHttpError(403, "Vous n'avez pas les permissions nécessaires pour accéder à cette page.");
        }
        return;
    }

    if ($userPermission < $required_level) {
        // Journalisation de la tentative d'accès non autorisé
        $userId = $_SESSION['id'] ?? 'unknown';
        $requestedPage = $_GET['page'] ?? 'unknown';
        ErrorHandler::logSecurity(
            "Tentative d'accès non autorisé: Utilisateur ID $userId a tenté d'accéder à '$requestedPage'",
            'WARN',
            ['permission' => $userPermission, 'required' => $required_level, 'page' => $requestedPage]
        );
        
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

