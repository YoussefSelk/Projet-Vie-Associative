<?php
/**
 * =============================================================================
 * GESTIONNAIRE D'ERREURS
 * =============================================================================
 * 
 * Configuration robuste de la gestion des erreurs pour la production :
 * - Journalisation des erreurs dans des fichiers de log
 * - Affichage de pages d'erreur personnalisées selon l'environnement
 * - Génération de codes de référence pour le suivi du support
 * - Gestion des erreurs fatales, exceptions et shutdown
 * 
 * Comportement selon l'environnement :
 * - DEBUG activé : affiche tous les détails (fichier, ligne, trace)
 * - Production : page d'erreur conviviale avec code de référence
 * 
 * Pages d'erreur disponibles :
 * - 403 : Accès refusé
 * - 404 : Page non trouvée
 * - 500 : Erreur serveur
 * - 503 : Service indisponible
 * 
 * @author Équipe de développement EILCO
 * @version 2.0
 */

class ErrorHandler
{
    /** @var bool Indicateur d'initialisation (évite les doubles initialisations) */
    private static bool $initialized = false;
    
    /**
     * Initialise le gestionnaire d'erreurs
     * Configure les handlers et le niveau de reporting selon l'environnement
     */
    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }
        
        // Configurer le reporting d'erreurs selon l'environnement
        if (Environment::isDebug()) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', 0);
        }
        
        ini_set('log_errors', 1);
        
        // Créer le dossier de logs s'il n'existe pas
        $logDir = ROOT_PATH . '/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        ini_set('error_log', $logDir . '/error.log');
        
        // Enregistrer les handlers personnalisés
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
        
        self::$initialized = true;
    }
    
    /**
     * Génère un code de référence unique pour l'erreur
     * Utilisé pour le suivi par l'équipe de support
     * 
     * @return string Code au format ERR-XXXXXXXX
     */
    private static function generateErrorRef(): string
    {
        return 'ERR-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
    }
    
    /**
     * Convertit un numéro d'erreur PHP en nom lisible
     * 
     * @param int $errno Numéro d'erreur PHP
     * @return string Nom du type d'erreur
     */
    private static function getErrorType(int $errno): string
    {
        $types = [
            E_ERROR => 'Erreur Fatale',
            E_WARNING => 'Avertissement',
            E_PARSE => 'Erreur de Syntaxe',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Erreur Core',
            E_CORE_WARNING => 'Avertissement Core',
            E_COMPILE_ERROR => 'Erreur de Compilation',
            E_COMPILE_WARNING => 'Avertissement de Compilation',
            E_USER_ERROR => 'Erreur Utilisateur',
            E_USER_WARNING => 'Avertissement Utilisateur',
            E_USER_NOTICE => 'Notice Utilisateur',
            E_RECOVERABLE_ERROR => 'Erreur Récupérable',
            E_DEPRECATED => 'Obsolète',
            E_USER_DEPRECATED => 'Obsolète Utilisateur',
        ];
        
        return $types[$errno] ?? 'Erreur Inconnue';
    }
    
    /**
     * Handler personnalisé pour les erreurs PHP
     * Journalise l'erreur et affiche la page appropriée si fatale
     * 
     * @param int $errno Numéro d'erreur
     * @param string $errstr Message d'erreur
     * @param string $errfile Fichier où l'erreur s'est produite
     * @param int $errline Ligne de l'erreur
     * @return bool True pour indiquer que l'erreur a été traitée
     */
    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        $type = self::getErrorType($errno);
        $timestamp = date('Y-m-d H:i:s');
        $message = "[$timestamp] [$type] $errstr dans $errfile à la ligne $errline";
        
        error_log($message);
        
        // Gérer les erreurs fatales
        if ($errno & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR)) {
            self::renderErrorPage(500, $type, $errstr, $errfile, $errline);
            exit(1);
        }
        
        return true;
    }
    
    /**
     * Handler personnalisé pour les exceptions non capturées
     * Journalise l'exception avec sa trace et affiche la page d'erreur
     * 
     * @param \Throwable $exception L'exception non capturée
     */
    public static function handleException(\Throwable $exception): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $message = "[$timestamp] Exception: " . $exception->getMessage() . " dans " . $exception->getFile() . " à la ligne " . $exception->getLine();
        $message .= "\nTrace d'exécution:\n" . $exception->getTraceAsString();
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
     * Handler de shutdown pour capturer les erreurs fatales
     * Appelé automatiquement à la fin du script
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error !== null && ($error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR))) {
            $timestamp = date('Y-m-d H:i:s');
            $message = "[$timestamp] Erreur Fatale: {$error['message']} dans {$error['file']} à la ligne {$error['line']}";
            error_log($message);
            
            // Nettoyer tout output existant
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
     * Affiche une page d'erreur HTTP (403, 404, 500, 503)
     * Utilisé pour les erreurs applicatives (page non trouvée, accès refusé, etc.)
     * 
     * @param int $code Code HTTP (403, 404, 500, 503)
     * @param string|null $message Message personnalisé (optionnel)
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
            // Fallback vers la page d'erreur générique
            include ROOT_PATH . '/views/errors/error_prod.php';
        }
        exit;
    }
    
    /**
     * Rend la page d'erreur appropriée selon l'environnement
     * Mode debug : affiche tous les détails techniques
     * Production : page conviviale avec code de référence
     * 
     * @param int $httpCode Code HTTP de l'erreur
     * @param string $errorType Type d'erreur
     * @param string $errorMessage Message d'erreur
     * @param string $errorFile Fichier source de l'erreur
     * @param int $errorLine Numéro de ligne
     * @param array $stackTrace Trace d'exécution (optionnel)
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
        
        // Nettoyer tout output existant
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Préparer les variables pour les templates d'erreur
        $errorCode = $httpCode;
        $errorTitle = $errorType;
        $errorRef = self::generateErrorRef();
        
        // Journaliser la référence pour le suivi
        error_log("Référence Erreur: $errorRef - $errorType: $errorMessage dans $errorFile à la ligne $errorLine");
        
        if (Environment::isDebug()) {
            // Développement : afficher la page de debug complète
            include ROOT_PATH . '/views/errors/error_dev.php';
        } else {
            // Production : afficher une page d'erreur conviviale
            $errorPage = ROOT_PATH . "/views/errors/{$httpCode}.php";
            if (file_exists($errorPage)) {
                include $errorPage;
            } else {
                include ROOT_PATH . '/views/errors/error_prod.php';
            }
        }
    }
}

// Initialisation automatique du gestionnaire d'erreurs
ErrorHandler::init();
