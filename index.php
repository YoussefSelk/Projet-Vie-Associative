<?php
/**
 * Point d'entree principal de l'application
 * 
 * Ce fichier est le point d'entree unique pour toutes les requetes.
 * Il charge la configuration et initialise le routeur.
 * 
 * Workflow :
 * 1. Chargement du bootstrap (session, BDD, constantes)
 * 2. Initialisation du routeur
 * 3. Dispatch vers le controleur approprie
 * 
 * @package Core
 */

// Chargement de la configuration d'initialisation
require_once __DIR__ . '/config/bootstrap.php';

// Chargement de la classe Router
require_once __DIR__ . '/config/Router.php';

// Si le serveur web redirige une erreur HTTP vers index.php, afficher la page personnalisée.
$httpErrorCode = filter_input(INPUT_GET, '__http_error', FILTER_VALIDATE_INT);
if (in_array($httpErrorCode, [403, 404, 500, 503], true)) {
	ErrorHandler::renderHttpError($httpErrorCode);
}

// Fallback pour URL "jolie" inconnue (ex: /page-inexistante) sans paramètre ?page=
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
$baseDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
$normalizedPath = $requestPath;

if ($baseDir !== '' && $baseDir !== '/' && strpos($normalizedPath, $baseDir . '/') === 0) {
	$normalizedPath = substr($normalizedPath, strlen($baseDir));
}

if ($normalizedPath === '') {
	$normalizedPath = '/';
}

if (!isset($_GET['page']) && $normalizedPath !== '/' && $normalizedPath !== '/index.php') {
	ErrorHandler::renderHttpError(404, 'La ressource demandée est introuvable.');
}

// Initialisation et execution du routeur
$router = new Router($db);
$router->dispatch();
