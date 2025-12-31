<?php
/**
 * Application Entry Point
 * 
 * This is the main entry point for the application.
 * It initializes the bootstrap configuration and dispatches the request through the router.
 */

// Load bootstrap configuration
require_once __DIR__ . '/config/bootstrap.php';

// Load Router class
require_once __DIR__ . '/config/Router.php';

// Initialize and dispatch router
$router = new Router($db);
$router->dispatch();
