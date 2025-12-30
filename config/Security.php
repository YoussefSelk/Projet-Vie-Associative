<?php
/**
 * Security Configuration and Helpers
 * Provides CSRF protection, security headers, and input sanitization
 */

class Security {
    
    /**
     * Set security headers for production
     */
    public static function setHeaders() {
        // Prevent clickjacking
        header('X-Frame-Options: SAMEORIGIN');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Enable XSS protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy (adjust as needed for your assets)
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self';");
        
        // Permissions Policy
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        
        // Remove PHP version header
        header_remove('X-Powered-By');
    }

    /**
     * Force HTTPS in production
     */
    public static function enforceHttps() {
        if (Environment::isProduction()) {
            if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
                $redirectUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                header('Location: ' . $redirectUrl, true, 301);
                exit;
            }
            
            // HSTS header (1 year)
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
    }

    /**
     * Generate CSRF token
     */
    public static function generateCsrfToken() {
        if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        
        // Regenerate if token is older than 2 hours
        $tokenLifetime = (int) Environment::get('CSRF_TOKEN_LIFETIME', 7200);
        if (time() - $_SESSION['csrf_token_time'] > $tokenLifetime) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token
     */
    public static function validateCsrfToken($token) {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Get CSRF token input field for forms
     */
    public static function csrfField() {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Sanitize input string
     */
    public static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize email
     */
    public static function sanitizeEmail($email) {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }

    /**
     * Validate email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Rate limiting check
     */
    public static function checkRateLimit($key, $maxAttempts = 5, $decayMinutes = 5) {
        $sessionKey = 'rate_limit_' . $key;
        $timeKey = 'rate_limit_time_' . $key;
        
        if (!isset($_SESSION[$sessionKey])) {
            $_SESSION[$sessionKey] = 0;
            $_SESSION[$timeKey] = time();
        }
        
        // Reset if decay time has passed
        if (time() - $_SESSION[$timeKey] > ($decayMinutes * 60)) {
            $_SESSION[$sessionKey] = 0;
            $_SESSION[$timeKey] = time();
        }
        
        if ($_SESSION[$sessionKey] >= $maxAttempts) {
            return false;
        }
        
        $_SESSION[$sessionKey]++;
        return true;
    }

    /**
     * Reset rate limit
     */
    public static function resetRateLimit($key) {
        unset($_SESSION['rate_limit_' . $key], $_SESSION['rate_limit_time_' . $key]);
    }
}
