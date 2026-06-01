<?php
declare(strict_types=1);
/**
 * =============================================================================
 * CLASSE DE SÉCURITÉ
 * =============================================================================
 * 
 * Gère tous les aspects de sécurité de l'application :
 * - En-têtes HTTP de sécurité
 * - Protection CSRF (Cross-Site Request Forgery)
 * - Validation et assainissement des entrées
 * - Limitation du taux de requêtes (Rate Limiting)
 * - Forcement HTTPS
 * 
 * @author Équipe de développement EILCO
 * @version 2.0
 */

class Security {

    /**
     * Normalise l'URI demandée pour éviter l'injection d'en-tête dans les redirections.
     */
    private static function normalizeRequestUri(string $uri): string {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';
        $query = parse_url($uri, PHP_URL_QUERY);
        if (!is_string($path) || $path === '') {
            $path = '/';
        }
        return $query !== null && $query !== '' ? $path . '?' . $query : $path;
    }

    /**
     * Construit le suffixe de redirection en évitant de dupliquer le chemin de base.
     */
    private static function buildRedirectSuffix(string $requestUri, string $basePath): string {
        $path = parse_url($requestUri, PHP_URL_PATH) ?? '/';
        $query = parse_url($requestUri, PHP_URL_QUERY);

        if (!is_string($path) || $path === '') {
            $path = '/';
        }

        $basePath = '/' . trim($basePath, '/');
        if ($basePath === '/') {
            $basePath = '';
        }

        if ($basePath !== '') {
            if ($path === $basePath) {
                $path = '/';
            } elseif (str_starts_with($path, $basePath . '/')) {
                $path = substr($path, strlen($basePath));
                if ($path === false || $path === '') {
                    $path = '/';
                }
            }
        }

        return $query !== null && $query !== '' ? $path . '?' . $query : $path;
    }
    
    /**
     * Définit les en-têtes de sécurité HTTP
     * Ces en-têtes protègent contre diverses attaques (XSS, clickjacking, etc.)
     * 
     * @return void
     */
    public static function setHeaders() {
        // Protection contre le clickjacking
        header('X-Frame-Options: SAMEORIGIN');
        
        // Empêche le sniffing MIME
        header('X-Content-Type-Options: nosniff');
        
        // Active la protection XSS du navigateur
        header('X-XSS-Protection: 1; mode=block');
        
        // Politique de référent
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Politique de sécurité du contenu (CSP)
        // Autorise Chart.js depuis cdn.jsdelivr.net et les fonts Google
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
               "img-src 'self' data: blob:; " .
               "font-src 'self' https://fonts.gstatic.com; " .
               "connect-src 'self' https://cdn.jsdelivr.net; " .
               "frame-ancestors 'self';";
        header("Content-Security-Policy: " . $csp);
        
        // Politique des permissions
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        
        // Suppression de l'en-tête de version PHP
        header_remove('X-Powered-By');
    }

    /**
     * Detecte si la connexion utilise HTTPS
     * Supporte les proxies inverses (load balancers, CloudFlare, etc.)
     * 
     * @return bool True si connexion HTTPS
     */
    public static function isHttps(): bool {
        // Direct HTTPS
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            return true;
        }
        
        // Behind load balancer or proxy
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return true;
        }
        
        // CloudFlare
        if (isset($_SERVER['HTTP_CF_VISITOR'])) {
            $cfVisitor = json_decode($_SERVER['HTTP_CF_VISITOR'], true);
            if (isset($cfVisitor['scheme']) && $cfVisitor['scheme'] === 'https') {
                return true;
            }
        }
        
        // AWS ELB
        if (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') {
            return true;
        }
        
        // Port 443
        if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
            return true;
        }
        
        return false;
    }

    /**
     * Force l'utilisation de HTTPS en production
     * Redirige automatiquement vers HTTPS et active HSTS
     * Supporte les environnements derriere un proxy/load balancer
     * 
     * @return void
     */
    public static function enforceHttps() {
        if (Environment::isProduction()) {
            // Only ensure HTTPS is used; preserve the original host so both
            // vieassociative.eilco-ulco.fr and www.vieassociative.eilco-ulco.fr work.
            if (!self::isHttps()) {
                $requestUri = self::normalizeRequestUri((string)($_SERVER['REQUEST_URI'] ?? '/'));
                $host = (string) ($_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost'));
                $host = preg_replace('/:\d+$/', '', $host);
                $redirectUrl = 'https://' . $host . $requestUri;
                header('Location: ' . $redirectUrl, true, 301);
                exit;
            }

            // HSTS header is still useful when HTTPS is present
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
    }

    /**
     * Génère un jeton CSRF (Cross-Site Request Forgery)
     * Le jeton est stocké en session et régénéré périodiquement
     * 
     * @return string Jeton CSRF
     */
    public static function generateCsrfToken() {
        if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        
        // Régénère le jeton s'il a plus de 2 heures
        $tokenLifetime = (int) Environment::get('CSRF_TOKEN_LIFETIME', 7200);
        if (time() - $_SESSION['csrf_token_time'] > $tokenLifetime) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        
        return $_SESSION['csrf_token'];
    }

    /**
     * Valide un jeton CSRF
     * Utilise hash_equals pour éviter les attaques timing
     * 
     * @param string $token Jeton à valider
     * @return bool True si le jeton est valide
     */
    public static function validateCsrfToken($token) {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Génère un champ HTML caché contenant le jeton CSRF
     * À inclure dans tous les formulaires POST
     * 
     * @return string Code HTML du champ caché
     */
    public static function csrfField() {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Assainit une chaîne d'entrée utilisateur
     * Supprime les espaces et encode les caractères spéciaux HTML
     * 
     * @param string|array $input Entrée à assainir
     * @return string|array Entrée assainie
     */
    public static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Assainit une adresse email
     * 
     * @param string $email Email à assainir
     * @return string Email assaini
     */
    public static function sanitizeEmail($email) {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }

    /**
     * Valide une adresse email
     * 
     * @param string $email Email à valider
     * @return bool True si l'email est valide
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Recupere l'IP client en tenant compte des proxies courants.
     */
    public static function getClientIp(): string {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (empty($_SERVER[$header])) {
                continue;
            }

            $value = (string) $_SERVER[$header];
            $candidate = trim(explode(',', $value)[0]);
            if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                return $candidate;
            }
        }

        return '0.0.0.0';
    }

    /**
     * Verifie si la cle est deja verrouillee sans incrementer le compteur.
     */
    public static function isRateLimited($key, $maxAttempts = 5, $decayMinutes = 5): bool {
        $sessionKey = 'rate_limit_' . $key;
        $timeKey = 'rate_limit_time_' . $key;

        if (!isset($_SESSION[$sessionKey], $_SESSION[$timeKey])) {
            return false;
        }

        if (time() - $_SESSION[$timeKey] > ($decayMinutes * 60)) {
            $_SESSION[$sessionKey] = 0;
            $_SESSION[$timeKey] = time();
            return false;
        }

        return $_SESSION[$sessionKey] >= $maxAttempts;
    }

    /**
     * Enregistre une tentative et retourne si l'action reste autorisee.
     */
    public static function hitRateLimit($key, $maxAttempts = 5, $decayMinutes = 5): bool {
        $sessionKey = 'rate_limit_' . $key;
        $timeKey = 'rate_limit_time_' . $key;

        if (!isset($_SESSION[$sessionKey])) {
            $_SESSION[$sessionKey] = 0;
            $_SESSION[$timeKey] = time();
        }

        if (time() - $_SESSION[$timeKey] > ($decayMinutes * 60)) {
            $_SESSION[$sessionKey] = 0;
            $_SESSION[$timeKey] = time();
        }

        $_SESSION[$sessionKey]++;
        return $_SESSION[$sessionKey] <= $maxAttempts;
    }

    /**
     * Vérifie la limitation de taux de requêtes
     * Protège contre les attaques par force brute
     * 
     * @param string $key Identifiant de l'action à limiter
     * @param int $maxAttempts Nombre maximum de tentatives autorisées
     * @param int $decayMinutes Durée en minutes avant réinitialisation
     * @return bool True si l'action est autorisée, False si limite atteinte
     */
    public static function checkRateLimit($key, $maxAttempts = 5, $decayMinutes = 5) {
        return self::hitRateLimit($key, $maxAttempts, $decayMinutes);
    }

    /**
     * Reset rate limit
     */
    public static function resetRateLimit($key) {
        unset($_SESSION['rate_limit_' . $key], $_SESSION['rate_limit_time_' . $key]);
    }
}
