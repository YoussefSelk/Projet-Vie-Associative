<?php
/**
 * =============================================================================
 * CHARGEUR DE CONFIGURATION D'ENVIRONNEMENT
 * =============================================================================
 * 
 * Gère le chargement des variables d'environnement :
 * - Utilise vlucas/phpdotenv pour une gestion robuste
 * - Parser personnalisé en fallback si phpdotenv n'est pas disponible
 * - Valeurs par défaut pour le développement
 * 
 * Variables d'environnement supportées :
 * - APP_ENV : 'development' ou 'production'
 * - APP_DEBUG : 'true' ou 'false'
 * - DB_HOST, DB_NAME, DB_USER, DB_PASS : Configuration base de données
 * - MAIL_* : Configuration SMTP
 * - SESSION_*, COOKIE_* : Configuration sécurité
 * 
 * Détection automatique du serveur :
 * - Apache, Nginx, IIS, LiteSpeed
 * - Mode CLI
 * 
 * @author Équipe de développement EILCO
 * @version 2.0
 */

// Charger l'autoloader Composer pour phpdotenv
$autoloadPath = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

use Dotenv\Dotenv;

class Environment {
    /** @var bool Indicateur de chargement (évite les doubles chargements) */
    private static bool $loaded = false;
    
    /** @var array Cache des variables d'environnement */
    private static array $variables = [];

    /**
     * Charge les variables d'environnement depuis le fichier .env
     * Utilise phpdotenv si disponible, sinon un parser personnalisé
     * 
     * @param string|null $path Chemin du dossier contenant .env (optionnel)
     */
    public static function load(?string $path = null): void
    {
        if (self::$loaded) {
            return;
        }

        $envDir = $path ?? dirname(__DIR__);
        $envFile = $envDir . '/.env';
        
        // Utiliser phpdotenv si disponible et si .env existe
        if (class_exists('Dotenv\Dotenv') && file_exists($envFile)) {
            try {
                $dotenv = Dotenv::createImmutable($envDir);
                $dotenv->load();
                
                // Définir les variables requises
                $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER']);
                
                // Copier dans notre tableau interne
                self::$variables = $_ENV;
                self::$loaded = true;
                return;
            } catch (\Exception $e) {
                // Fallback vers le loader personnalisé en cas d'erreur
                error_log("Erreur Dotenv: " . $e->getMessage());
            }
        }
        
        // Fallback : Parser .env personnalisé
        if (file_exists($envFile)) {
            self::parseEnvFile($envFile);
        } else {
            // En développement sans .env, utiliser les valeurs par défaut
            self::loadDefaults();
        }
        
        self::$loaded = true;
    }

    /**
     * Parse le fichier .env manuellement
     * Utilisé en fallback si phpdotenv n'est pas disponible
     * 
     * @param string $envFile Chemin complet du fichier .env
     */
    private static function parseEnvFile(string $envFile): void
    {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Ignorer les commentaires
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            if (strpos($line, '=') !== false) {
                [$name, $value] = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                // Supprimer les guillemets si présents
                $value = trim($value, '"\'');
                
                self::$variables[$name] = $value;
                $_ENV[$name] = $value;
                putenv("$name=$value");
            }
        }
    }

    /**
     * Charge les valeurs par défaut pour le développement
     * Utilisé quand aucun fichier .env n'est trouvé
     */
    private static function loadDefaults(): void
    {
        $defaults = [
            'APP_ENV' => 'development',
            'APP_DEBUG' => 'true',
            'DB_HOST' => 'localhost',
            'DB_NAME' => 'test_projet_tech',
            'DB_USER' => 'root',
            'DB_PASS' => '',
            'MAIL_HOST' => 'smtp.gmail.com',
            'MAIL_PORT' => '587',
            'MAIL_USERNAME' => '',
            'MAIL_PASSWORD' => '',
            'MAIL_FROM' => 'noreply@eilco.fr',
            'MAIL_FROM_NAME' => 'EILCO Events',
        ];

        foreach ($defaults as $name => $value) {
            self::$variables[$name] = $value;
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }

    /**
     * Récupère une variable d'environnement
     * 
     * @param string $key Nom de la variable
     * @param mixed $default Valeur par défaut si non trouvée
     * @return mixed Valeur de la variable ou défaut
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (!self::$loaded) {
            self::load();
        }

        return self::$variables[$key] ?? $_ENV[$key] ?? getenv($key) ?: $default;
    }

    /**
     * Vérifie si on est en production
     * 
     * @return bool True si APP_ENV = 'production'
     */
    public static function isProduction(): bool
    {
        return self::get('APP_ENV', 'production') === 'production';
    }

    /**
     * Vérifie si le mode debug est activé
     * 
     * @return bool True si APP_DEBUG = 'true'
     */
    public static function isDebug(): bool
    {
        return filter_var(self::get('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN);
    }
    
    /**
     * Récupère la configuration de la base de données
     * 
     * @return array Configuration [host, name, user, pass]
     */
    public static function getDbConfig(): array
    {
        return [
            'host' => self::get('DB_HOST', 'localhost'),
            'name' => self::get('DB_NAME', 'test_projet_tech'),
            'user' => self::get('DB_USER', 'root'),
            'pass' => self::get('DB_PASS', ''),
        ];
    }
    
    /**
     * Récupère la configuration email SMTP
     * 
     * @return array Configuration [host, port, username, password, from, from_name]
     */
    public static function getMailConfig(): array
    {
        return [
            'host' => self::get('MAIL_HOST', 'smtp.gmail.com'),
            'port' => (int) self::get('MAIL_PORT', 587),
            'username' => self::get('MAIL_USERNAME', ''),
            'password' => self::get('MAIL_PASSWORD', ''),
            'from' => self::get('MAIL_FROM', 'noreply@eilco.fr'),
            'from_name' => self::get('MAIL_FROM_NAME', 'EILCO Events'),
        ];
    }

    /**
     * Détecte le type de serveur web actuel
     * 
     * @return string 'apache', 'nginx', 'iis', 'litespeed', 'cli', ou 'unknown'
     */
    public static function getServerType(): string
    {
        // Vérifier si le type est explicitement configuré
        $configuredType = self::get('SERVER_TYPE', 'auto');
        if ($configuredType !== 'auto') {
            return strtolower($configuredType);
        }

        // Mode CLI
        if (php_sapi_name() === 'cli') {
            return 'cli';
        }

        $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? '';
        
        if (stripos($serverSoftware, 'Apache') !== false) {
            return 'apache';
        }
        
        if (stripos($serverSoftware, 'nginx') !== false) {
            return 'nginx';
        }
        
        if (stripos($serverSoftware, 'Microsoft-IIS') !== false) {
            return 'iis';
        }
        
        if (stripos($serverSoftware, 'LiteSpeed') !== false) {
            return 'litespeed';
        }
        
        // Vérifier nginx via fastcgi
        if (isset($_SERVER['FCGI_ROLE']) || isset($_SERVER['PHP_SELF'])) {
            if (!empty($_SERVER['DOCUMENT_ROOT'])) {
                return 'nginx'; // Configuration nginx + PHP-FPM courante
            }
        }

        return 'unknown';
    }

    /**
     * Vérifie si on tourne sur Apache
     * Inclut LiteSpeed (compatible Apache)
     * 
     * @return bool True si Apache ou LiteSpeed
     */
    public static function isApache(): bool
    {
        $type = self::getServerType();
        return $type === 'apache' || $type === 'litespeed';
    }

    /**
     * Vérifie si on tourne sur Nginx
     * 
     * @return bool True si Nginx
     */
    public static function isNginx(): bool
    {
        return self::getServerType() === 'nginx';
    }

    /**
     * Vérifie si on tourne sur IIS (Windows)
     * 
     * @return bool True si IIS
     */
    public static function isIIS(): bool
    {
        return self::getServerType() === 'iis';
    }

    /**
     * Vérifie si on est en mode CLI (ligne de commande)
     * 
     * @return bool True si CLI
     */
    public static function isCLI(): bool
    {
        return self::getServerType() === 'cli' || php_sapi_name() === 'cli';
    }

    /**
     * Récupère l'URL de base de l'application
     * Auto-détection si APP_URL n'est pas configuré
     * 
     * @return string URL de base (sans slash final)
     */
    public static function getBaseUrl(): string
    {
        $baseUrl = self::get('APP_URL', '');
        
        if (!empty($baseUrl)) {
            return rtrim($baseUrl, '/');
        }
        
        // Auto-détection de l'URL
        if (self::isCLI()) {
            return 'http://localhost';
        }
        
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        return $protocol . '://' . $host;
    }

    /**
     * Récupère la configuration de sécurité
     * 
     * @return array Configuration [session_lifetime, csrf_lifetime, cookie_*]
     */
    public static function getSecurityConfig(): array
    {
        return [
            'session_lifetime' => (int) self::get('SESSION_LIFETIME', 3600),
            'csrf_lifetime' => (int) self::get('CSRF_TOKEN_LIFETIME', 7200),
            'cookie_secure' => filter_var(self::get('COOKIE_SECURE', self::isProduction()), FILTER_VALIDATE_BOOLEAN),
            'cookie_httponly' => filter_var(self::get('COOKIE_HTTPONLY', true), FILTER_VALIDATE_BOOLEAN),
            'cookie_samesite' => self::get('COOKIE_SAMESITE', 'Strict'),
        ];
    }
}
