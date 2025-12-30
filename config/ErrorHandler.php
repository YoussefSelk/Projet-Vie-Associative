<?php
/**
 * Error Handler Configuration
 * Production-ready error handling with proper logging
 */

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

// Custom error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $error_types = [
        E_ERROR => 'Error',
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

    $type = isset($error_types[$errno]) ? $error_types[$errno] : 'Unknown';
    $timestamp = date('Y-m-d H:i:s');
    $message = "[$timestamp] [$type] $errstr in $errfile on line $errline";
    
    error_log($message);
    
    // Don't suppress fatal errors
    if ($errno & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR)) {
        if (Environment::isDebug()) {
            echo "<h1>Error</h1><p>$errstr</p><p>File: $errfile on line $errline</p>";
        } else {
            echo "An error occurred. Please try again later.";
        }
        exit(1);
    }
    
    return true;
});

// Custom exception handler
set_exception_handler(function($exception) {
    $timestamp = date('Y-m-d H:i:s');
    $message = "[$timestamp] Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();
    $message .= "\nStack trace:\n" . $exception->getTraceAsString();
    error_log($message);
    
    if (php_sapi_name() !== 'cli') {
        http_response_code(500);
        if (Environment::isDebug()) {
            echo "<h1>Exception</h1>";
            echo "<p><strong>Message:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>";
            echo "<p><strong>File:</strong> " . htmlspecialchars($exception->getFile()) . " on line " . $exception->getLine() . "</p>";
            echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
        } else {
            echo "An error occurred. Please try again later.";
        }
    }
    exit(1);
});

// Register shutdown handler to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && ($error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR))) {
        $timestamp = date('Y-m-d H:i:s');
        $message = "[$timestamp] Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}";
        error_log($message);
        
        if (!Environment::isDebug()) {
            // Clear any output and show generic error
            if (ob_get_level()) {
                ob_end_clean();
            }
            http_response_code(500);
            echo "An error occurred. Please try again later.";
        }
    }
});
