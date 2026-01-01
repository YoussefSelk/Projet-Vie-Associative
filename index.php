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

// Initialisation et execution du routeur
$router = new Router($db);
$router->dispatch();
