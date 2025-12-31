<?php
/**
 * Router Class
 * 
 * Handles routing logic for the application.
 * Loads routes from configuration file and dispatches to appropriate controller/action.
 */

class Router
{
    private array $routes = [];
    private $db;
    
    public function __construct($db)
    {
        $this->db = $db;
        $this->loadRoutes();
    }
    
    /**
     * Load routes from configuration file
     */
    private function loadRoutes(): void
    {
        $routesFile = __DIR__ . '/../routes/web.php';
        if (file_exists($routesFile)) {
            $this->routes = require $routesFile;
        }
    }
    
    /**
     * Get sanitized page parameter from URL
     */
    public function getPage(): string
    {
        return isset($_GET['page']) ? preg_replace('/[^a-zA-Z0-9\-_]/', '', $_GET['page']) : 'home';
    }
    
    /**
     * Validate CSRF token for POST requests
     */
    public function validateCsrf(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true;
        }
        
        $page = $this->getPage();
        $publicPostRoutes = ['login', 'register'];
        
        if (in_array($page, $publicPostRoutes)) {
            return true;
        }
        
        $token = $_POST['csrf_token'] ?? '';
        return Security::validateCsrfToken($token);
    }
    
    /**
     * Dispatch request to appropriate controller
     */
    public function dispatch(): void
    {
        $page = $this->getPage();
        
        // Validate CSRF
        if (!$this->validateCsrf()) {
            ErrorHandler::renderHttpError(403, 'Token de sécurité invalide.');
        }
        
        // Check if route exists
        if (!isset($this->routes[$page])) {
            ErrorHandler::renderHttpError(404, "La page '$page' n'existe pas.");
        }
        
        $route = $this->routes[$page];
        
        // Handle redirect if already logged in (for login/register pages)
        if (isset($route['redirect_if_logged']) && $route['redirect_if_logged'] && isset($_SESSION['id'])) {
            redirect('?page=home');
        }
        
        // Check authentication
        if ($route['auth'] === true) {
            validateSession();
        }
        
        // Check permission
        if ($route['permission'] !== null) {
            checkPermission($route['permission']);
        }
        
        // Instantiate controller
        $controllerClass = $route['controller'];
        $controller = new $controllerClass($this->db);
        
        // Call method
        $method = $route['method'];
        $data = $controller->$method();
        
        // Render view if specified
        if ($route['view'] !== null) {
            if (is_array($data)) {
                extract($data);
            }
            
            // Debug output for club-view
            if (isset($route['debug']) && $route['debug'] && $page === 'club-view') {
                echo "<!-- DEBUG From index: Received club ID = " . ($club['club_id'] ?? 'NULL') . " -->\n";
                echo "<!-- DEBUG From index: Received club Name = " . ($club['nom_club'] ?? 'NULL') . " -->\n";
            }
            
            include VIEWS_PATH . $route['view'];
        }
    }
    
    /**
     * Get all registered routes
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
    
    /**
     * Check if a route exists
     */
    public function hasRoute(string $page): bool
    {
        return isset($this->routes[$page]);
    }
}
