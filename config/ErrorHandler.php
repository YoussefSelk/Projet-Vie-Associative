<?php
/**
 * Error Handler Configuration
 * Production-ready error handling with proper logging and custom error pages
 */

class ErrorHandler
{
    private static bool $initialized = false;
    
    /**
     * Initialize the error handler
     */
    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }
        
        // Set error reporting based on environment
        if (Environment::isDebug()) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', 0);
        }
        
        ini_set('log_errors', 1);
        
        // Set log file location
        $logDir = ROOT_PATH . '/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        ini_set('error_log', $logDir . '/error.log');
        
        // Register handlers
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
        
        self::$initialized = true;
    }
    
    /**
     * Generate a unique error reference code
     */
    private static function generateErrorRef(): string
    {
        return 'ERR-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
    }
    
    /**
     * Get error type name from error number
     */
    private static function getErrorType(int $errno): string
    {
        $types = [
            E_ERROR => 'Fatal Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parse Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated',
        ];
        
        return $types[$errno] ?? 'Unknown Error';
    }
    
    /**
     * Custom error handler
     */
    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        $type = self::getErrorType($errno);
        $timestamp = date('Y-m-d H:i:s');
        $message = "[$timestamp] [$type] $errstr in $errfile on line $errline";
        
        error_log($message);
        
        // Handle fatal errors
        if ($errno & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR)) {
            self::renderErrorPage(500, $type, $errstr, $errfile, $errline);
            exit(1);
        }
        
        return true;
    }
    
    /**
     * Custom exception handler
     */
    public static function handleException(\Throwable $exception): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $message = "[$timestamp] Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();
        $message .= "\nStack trace:\n" . $exception->getTraceAsString();
        error_log($message);
        
        if (php_sapi_name() !== 'cli') {
            self::renderErrorPage(
                500,
                get_class($exception),
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine(),
                $exception->getTrace()
            );
        }
        exit(1);
    }
    
    /**
     * Shutdown handler for fatal errors
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error !== null && ($error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR))) {
            $timestamp = date('Y-m-d H:i:s');
            $message = "[$timestamp] Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}";
            error_log($message);
            
            // Clear any output
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            self::renderErrorPage(
                500,
                self::getErrorType($error['type']),
                $error['message'],
                $error['file'],
                $error['line']
            );
        }
    }
    
    /**
     * Render an HTTP error page (403, 404, 500, 503)
     */
    public static function renderHttpError(int $code, ?string $message = null): void
    {
        $messages = [
            403 => 'Accès refusé',
            404 => 'Page non trouvée',
            500 => 'Erreur serveur interne',
            503 => 'Service temporairement indisponible',
        ];
        
        http_response_code($code);
        
        $errorCode = $code;
        $errorTitle = $messages[$code] ?? 'Erreur';
        $errorMessage = $message ?? $errorTitle;
        $errorRef = self::generateErrorRef();
        
        $errorPage = ROOT_PATH . "/views/errors/{$code}.php";
        if (file_exists($errorPage)) {
            include $errorPage;
        } else {
            // Fallback to generic production error page
            include ROOT_PATH . '/views/errors/error_prod.php';
        }
        exit;
    }
    
    /**
     * Render the appropriate error page based on environment
     */
    private static function renderErrorPage(
        int $httpCode,
        string $errorType,
        string $errorMessage,
        string $errorFile,
        int $errorLine,
        array $stackTrace = []
    ): void {
        http_response_code($httpCode);
        
        // Clear any existing output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Prepare variables for error templates
        $errorCode = $httpCode;
        $errorTitle = $errorType;
        $errorRef = self::generateErrorRef();
        
        // Log the error reference for support tracking
        error_log("Error Reference: $errorRef - $errorType: $errorMessage in $errorFile on line $errorLine");
        
        if (Environment::isDebug()) {
            // Development: Show full debug page
            include ROOT_PATH . '/views/errors/error_dev.php';
        } else {
            // Production: Show friendly error page
            $errorPage = ROOT_PATH . "/views/errors/{$httpCode}.php";
            if (file_exists($errorPage)) {
                include $errorPage;
            } else {
                include ROOT_PATH . '/views/errors/error_prod.php';
            }
        }
    }
}

// Initialize the error handler
ErrorHandler::init();
