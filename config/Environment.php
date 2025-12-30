<?php
/**
 * Environment Configuration Loader
 * Loads configuration from .env file for production deployments
 */

class Environment {
    private static $loaded = false;
    private static $variables = [];

    /**
     * Load environment variables from .env file
     */
    public static function load($path = null) {
        if (self::$loaded) {
            return;
        }

        $envFile = $path ?? dirname(__DIR__) . '/.env';
        
        if (!file_exists($envFile)) {
            // In production, .env should exist. In development, use defaults
            self::loadDefaults();
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                // Remove quotes if present
                $value = trim($value, '"\'');
                
                self::$variables[$name] = $value;
                
                // Also set in $_ENV for global access
                $_ENV[$name] = $value;
                putenv("$name=$value");
            }
        }

        self::$loaded = true;
    }

    /**
     * Load default development values
     */
    private static function loadDefaults() {
        $defaults = [
            'APP_ENV' => 'development',
            'APP_DEBUG' => 'true',
            'DB_HOST' => 'localhost',
            'DB_NAME' => 'test_projet_tech',
            'DB_USER' => 'root',
            'DB_PASS' => '',
        ];

        foreach ($defaults as $name => $value) {
            self::$variables[$name] = $value;
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }

        self::$loaded = true;
    }

    /**
     * Get an environment variable
     */
    public static function get($key, $default = null) {
        if (!self::$loaded) {
            self::load();
        }

        return self::$variables[$key] ?? getenv($key) ?: $default;
    }

    /**
     * Check if we're in production
     */
    public static function isProduction() {
        return self::get('APP_ENV', 'production') === 'production';
    }

    /**
     * Check if debug mode is enabled
     */
    public static function isDebug() {
        return filter_var(self::get('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN);
    }
}
