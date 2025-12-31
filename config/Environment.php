<?php
/**
 * Environment Configuration Loader
 * Uses vlucas/phpdotenv for production-ready environment variable management
 */

// Load composer autoloader for phpdotenv
$autoloadPath = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

use Dotenv\Dotenv;

class Environment {
    private static bool $loaded = false;
    private static array $variables = [];

    /**
     * Load environment variables from .env file
     */
    public static function load(?string $path = null): void
    {
        if (self::$loaded) {
            return;
        }

        $envDir = $path ?? dirname(__DIR__);
        $envFile = $envDir . '/.env';
        
        // Use phpdotenv if available and .env exists
        if (class_exists('Dotenv\Dotenv') && file_exists($envFile)) {
            try {
                $dotenv = Dotenv::createImmutable($envDir);
                $dotenv->load();
                
                // Define required variables
                $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER']);
                
                // Copy to our internal array
                self::$variables = $_ENV;
                self::$loaded = true;
                return;
            } catch (\Exception $e) {
                // Fall back to custom loader on error
                error_log("Dotenv Error: " . $e->getMessage());
            }
        }
        
        // Fallback: Custom .env parser
        if (file_exists($envFile)) {
            self::parseEnvFile($envFile);
        } else {
            // In development without .env, use defaults
            self::loadDefaults();
        }
        
        self::$loaded = true;
    }

    /**
     * Parse .env file manually (fallback if phpdotenv not available)
     */
    private static function parseEnvFile(string $envFile): void
    {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            if (strpos($line, '=') !== false) {
                [$name, $value] = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                // Remove quotes if present
                $value = trim($value, '"\'');
                
                self::$variables[$name] = $value;
                $_ENV[$name] = $value;
                putenv("$name=$value");
            }
        }
    }

    /**
     * Load default development values
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
     * Get an environment variable
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (!self::$loaded) {
            self::load();
        }

        return self::$variables[$key] ?? $_ENV[$key] ?? getenv($key) ?: $default;
    }

    /**
     * Check if we're in production
     */
    public static function isProduction(): bool
    {
        return self::get('APP_ENV', 'production') === 'production';
    }

    /**
     * Check if debug mode is enabled
     */
    public static function isDebug(): bool
    {
        return filter_var(self::get('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN);
    }
    
    /**
     * Get database configuration
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
     * Get mail configuration
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
}
