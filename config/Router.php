<?php
/**
 * =============================================================================
 * ROUTEUR DE L'APPLICATION
 * =============================================================================
 * 
 * Gère la logique de routage de l'application :
 * - Chargement des routes depuis le fichier de configuration
 * - Validation des tokens CSRF pour les requêtes POST
 * - Vérification des permissions et authentification
 * - Dispatch vers le bon contrôleur/méthode
 * - Rendu des vues avec injection des données
 * 
 * Format des routes (défini dans routes/web.php) :
 * 'nom-page' => [
 *     'controller' => 'NomController',
 *     'method' => 'nomMethode',
 *     'view' => 'dossier/fichier.php',
 *     'auth' => true/false,
 *     'permission' => null ou niveau (0-5)
 * ]
 * 
 * @author Équipe de développement EILCO
 * @version 2.0
 */

class Router
{
    /** @var array Routes chargées depuis le fichier de configuration */
    private array $routes = [];
    
    /** @var PDO Instance de connexion à la base de données */
    private $db;
    
    /**
     * Constructeur du routeur
     * Charge automatiquement les routes depuis routes/web.php
     * 
     * @param PDO $db Instance de connexion PDO
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->loadRoutes();
    }
    
    /**
     * Charge les routes depuis le fichier de configuration
     * Le fichier doit retourner un tableau associatif de routes
     */
    private function loadRoutes(): void
    {
        $routesFile = __DIR__ . '/../routes/web.php';
        if (file_exists($routesFile)) {
            $this->routes = require $routesFile;
        }
    }
    
    /**
     * Récupère et nettoie le paramètre 'page' de l'URL
     * Supprime tous les caractères non alphanumériques (sauf - et _)
     * 
     * @return string Nom de la page (défaut: 'home')
     */
    public function getPage(): string
    {
        return isset($_GET['page']) ? preg_replace('/[^a-zA-Z0-9\-_]/', '', $_GET['page']) : 'home';
    }
    
    /**
     * Valide le token CSRF pour les requêtes POST
     * Les routes publiques (login, register) sont exemptées
     * 
     * @return bool True si le token est valide ou non requis
     */
    public function validateCsrf(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true;
        }
        
        $page = $this->getPage();
        // Routes POST publiques exemptées de CSRF
        $publicPostRoutes = ['login', 'register'];
        
        if (in_array($page, $publicPostRoutes)) {
            return true;
        }
        
        $token = $_POST['csrf_token'] ?? '';
        return Security::validateCsrfToken($token);
    }
    
    /**
     * Dispatch la requête vers le contrôleur approprié
     * 
     * Étapes du dispatch :
     * 1. Validation du token CSRF
     * 2. Vérification de l'existence de la route
     * 3. Redirection si déjà connecté (pour login/register)
     * 4. Vérification de l'authentification
     * 5. Vérification des permissions
     * 6. Instanciation du contrôleur et appel de la méthode
     * 7. Rendu de la vue avec les données
     */
    public function dispatch(): void
    {
        $page = $this->getPage();
        
        // Validation CSRF
        if (!$this->validateCsrf()) {
            ErrorHandler::renderHttpError(403, 'Token de sécurité invalide.');
        }
        
        // Vérifier que la route existe
        if (!isset($this->routes[$page])) {
            ErrorHandler::renderHttpError(404, "La page '$page' n'existe pas.");
        }
        
        $route = $this->routes[$page];
        
        // Rediriger si déjà connecté (pour login/register)
        if (isset($route['redirect_if_logged']) && $route['redirect_if_logged'] && isset($_SESSION['id'])) {
            redirect('?page=home');
        }
        
        // Vérifier l'authentification
        if ($route['auth'] === true) {
            validateSession();
        }
        
        // Vérifier les permissions
        if ($route['permission'] !== null) {
            checkPermission($route['permission']);
        }
        
        // Instancier le contrôleur
        $controllerClass = $route['controller'];
        $controller = new $controllerClass($this->db);
        
        // Appeler la méthode
        $method = $route['method'];
        $data = $controller->$method();
        
        // Rendre la vue si spécifiée
        if ($route['view'] !== null) {
            if (is_array($data)) {
                extract($data);
            }
            
            include VIEWS_PATH . $route['view'];
        }
    }
    
    /**
     * Retourne toutes les routes enregistrées
     * 
     * @return array Tableau des routes
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
    
    /**
     * Vérifie si une route existe
     * 
     * @param string $page Nom de la page
     * @return bool True si la route existe
     */
    public function hasRoute(string $page): bool
    {
        return isset($this->routes[$page]);
    }
}
