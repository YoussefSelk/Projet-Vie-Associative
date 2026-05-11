<?php
declare(strict_types=1);
/**
 * =============================================================================
 * CONTRÔLEUR D'AUTHENTIFICATION
 * =============================================================================
 * 
 * Gère toutes les opérations d'authentification :
 * - Connexion et déconnexion des utilisateurs
 * - Inscription avec vérification par email
 * - Réinitialisation de mot de passe
 * 
 * Sécurité implémentée :
 * - Limitation des tentatives de vérification (5 max par 5 minutes)
 * - Hachage bcrypt avec coût 12
 * - Régénération d'ID de session après authentification
 * - Validation stricte des mots de passe
 * 
 * @author Équipe de développement EILCO
 * @version 2.0
 */

class AuthController {
    /** @var User Modèle utilisateur */
    private $userModel;
    
    /** @var PDO Instance de connexion à la base de données */
    private $db;

    /**
     * Construit une cle de rate limiting stable (email + IP).
     */
    private function buildLoginRateLimitKey(string $email): string {
        $normalizedEmail = strtolower(trim($email));
        $clientIp = $this->resolveClientIp();
        return 'login_' . hash('sha256', $normalizedEmail . '|' . $clientIp);
    }

    /**
     * Resolve client IP deterministically for auth rate limiting.
     */
    private function resolveClientIp(): string {
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
     * Check lock status without incrementing attempts.
     */
    private function isLoginRateLimited(string $key, int $maxAttempts = 5, int $decayMinutes = 15): bool {
        $sessionKey = 'rate_limit_' . $key;
        $timeKey = 'rate_limit_time_' . $key;
        if (!isset($_SESSION[$sessionKey], $_SESSION[$timeKey])) {
            return false;
        }

        if (time() - (int)$_SESSION[$timeKey] > ($decayMinutes * 60)) {
            $_SESSION[$sessionKey] = 0;
            $_SESSION[$timeKey] = time();
            return false;
        }

        return (int)$_SESSION[$sessionKey] >= $maxAttempts;
    }

    /**
     * Register a failed login attempt.
     */
    private function registerLoginFailureAttempt(string $key, int $maxAttempts = 5, int $decayMinutes = 15): bool {
        $sessionKey = 'rate_limit_' . $key;
        $timeKey = 'rate_limit_time_' . $key;

        if (!isset($_SESSION[$sessionKey], $_SESSION[$timeKey])) {
            $_SESSION[$sessionKey] = 0;
            $_SESSION[$timeKey] = time();
        }

        if (time() - (int)$_SESSION[$timeKey] > ($decayMinutes * 60)) {
            $_SESSION[$sessionKey] = 0;
            $_SESSION[$timeKey] = time();
        }

        $_SESSION[$sessionKey]++;
        return (int)$_SESSION[$sessionKey] <= $maxAttempts;
    }

    /**
     * Valide la robustesse d'un mot de passe.
     */
    private function validateStrongPassword(string $password): ?string {
        if (strlen($password) < 8) {
            return 'Le mot de passe doit contenir au moins 8 caractères.';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return 'Le mot de passe doit contenir au moins une lettre majuscule.';
        }
        if (!preg_match('/\d/', $password)) {
            return 'Le mot de passe doit contenir au moins un chiffre.';
        }
        if (!preg_match('/[\W_]/', $password)) {
            return 'Le mot de passe doit contenir au moins un caractère spécial.';
        }
        return null;
    }

    /**
     * Genere un code de verification numerique sur 6 chiffres.
     */
    private function generateRegistrationVerificationCode(): string {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Hache un code de verification pour stockage en session.
     */
    private function hashVerificationCode(string $code): string {
        return hash('sha256', $code);
    }

    /**
     * Constructeur
     * @param PDO $database Instance de connexion PDO
     */
    public function __construct($database) {
        $this->db = $database;
        $this->userModel = new User($database);
    }

    /**
     * Récupère le niveau de permission de l'utilisateur connecté
     * 
     * @return int Niveau de permission (0 si non connecté)
     */
    public static function getPermission() {
        return isset($_SESSION['permission']) ? (int)$_SESSION['permission'] : 0;
    }

    /**
     * Vérifie si l'utilisateur est authentifié
     * 
     * @return bool True si connecté
     */
    public static function isAuthenticated() {
        return isset($_SESSION['id']) && !empty($_SESSION['id']);
    }

    /**
     * Gère la connexion et la réinitialisation de mot de passe
     * 
     * Workflow de réinitialisation :
     * - Étape 0 : Formulaire de connexion normal
     * - Étape 1 : Demande d'email pour réinitialisation
     * - Étape 2 : Vérification du code envoyé par email
     * - Étape 3 : Saisie du nouveau mot de passe
     * 
     * @return array Données pour la vue [error_message, reset_step, err]
     */
    public function login() {
        $error_message = '';
        $err = 0;

        // Rediriger si déjà connecté
        if (isset($_SESSION['id'])) {
            redirect('/index.php');
        }

        // Réinitialiser au formulaire de connexion lors d'une visite GET
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $_SESSION['reset_step'] = 0;
        }

        if (!isset($_SESSION['reset_step'])) {
            $_SESSION['reset_step'] = 0;
        }

        // Gestion de la demande de réinitialisation
        if (isset($_POST['check-email'])) {
            $_SESSION['reset_step'] = 1;
        }

        // Envoi du code de réinitialisation
        if (isset($_POST['send_reset_code']) && !empty($_POST['mail'])) {
            $mail = Security::sanitizeEmail((string)($_POST['mail'] ?? ''));
            if (!Security::validateEmail($mail)) {
                $error_message = "Email invalide.";
                $_SESSION['reset_step'] = 1;
            } else {
            $user = $this->userModel->getUserByEmail($mail);

            if ($user) {
                $_SESSION['reset_mail'] = $mail;
                $resetToken = $this->generateRegistrationVerificationCode();
                $_SESSION['reset_token_hash'] = hash('sha256', $resetToken);
                $_SESSION['reset_token_expires_at'] = time() + 300;
                $_SESSION['reset_verification_attempts'] = 0;
                $_SESSION['reset_verification_attempts_time'] = time();
                $resetEmail = buildPasswordResetEmail($user['prenom'] ?? null, $resetToken);
                $emailSent = sendEmail($mail, 'Code de reinitialisation', $resetEmail);

                if ($emailSent) {
                    $_SESSION['reset_step'] = 2;
                } else {
                    unset($_SESSION['reset_mail'], $_SESSION['reset_token_hash'], $_SESSION['reset_token_expires_at']);
                    $error_message = "Impossible d'envoyer le code de reinitialisation. Veuillez reessayer.";
                    $_SESSION['reset_step'] = 1;

                    ErrorHandler::logError('Echec envoi email reinitialisation', 'WARNING', [
                        'recipient' => $mail,
                    ]);
                }
            } else {
                $error_message = "Aucun compte trouvé avec cet email.";
                $_SESSION['reset_step'] = 1;
            }
            }
        }

        // Limitation des tentatives de vérification
        if (!isset($_SESSION['reset_verification_attempts'])) {
            $_SESSION['reset_verification_attempts'] = 0;
            $_SESSION['reset_verification_attempts_time'] = time();
        }
        
        // Réinitialiser après 5 minutes
        if (time() - (int)($_SESSION['reset_verification_attempts_time'] ?? time()) > 300) {
            $_SESSION['reset_verification_attempts'] = 0;
            $_SESSION['reset_verification_attempts_time'] = time();
        }
        
        // Vérification du code de réinitialisation (unified logic)
        if (isset($_POST['verify_reset_code']) && isset($_SESSION['reset_token_hash'])) {
            $submittedToken = trim((string)($_POST['reset_code'] ?? ''));
            $submittedHash = hash('sha256', $submittedToken);
            $isExpired = time() > (int)($_SESSION['reset_token_expires_at'] ?? 0);

            if ($isExpired) {
                unset($_SESSION['reset_token_hash'], $_SESSION['reset_token_expires_at']);
                $error_message = "Le code a expiré. Veuillez en demander un nouveau.";
                $_SESSION['reset_step'] = 1;
            } elseif (!hash_equals((string)($_SESSION['reset_token_hash'] ?? ''), $submittedHash)) {
                $_SESSION['reset_verification_attempts']++;
                $_SESSION['reset_verification_attempts_time'] = time();
                if ($_SESSION['reset_verification_attempts'] >= 5) {
                    ErrorHandler::logSecurity("Rate limit atteint - trop de tentatives de vérification", 'WARN', [
                        'email' => $_SESSION['reset_mail'] ?? 'unknown'
                    ]);
                    $error_message = "Trop de tentatives. Veuillez réessayer plus tard.";
                    $_SESSION['reset_step'] = 1;
                    unset($_SESSION['reset_token_hash'], $_SESSION['reset_token_expires_at']);
                } else {
                    ErrorHandler::logSecurity("Code de réinitialisation incorrect", 'FAIL', [
                        'email' => $_SESSION['reset_mail'] ?? 'unknown',
                        'attempts' => $_SESSION['reset_verification_attempts']
                    ]);
                    $error_message = "Code de vérification incorrect.";
                }
            } else {
                unset($_SESSION['reset_verification_attempts'], $_SESSION['reset_verification_attempts_time']);
                $_SESSION['reset_step'] = 3;
            }
        }

        // Mise à jour du mot de passe
        if (isset($_POST['reset_password'])) {
            $password = $_POST['password'];
            $cpassword = $_POST['cpassword'];
            
            $passwordError = $this->validateStrongPassword($password);
            if ($passwordError !== null) {
                $error_message = $passwordError;
            } else if ($_POST['password'] == $_POST['cpassword']) {
                $this->userModel->updatePassword($_SESSION['reset_mail'], $_POST['password']);
                unset($_SESSION['reset_mail'], $_SESSION['reset_token_hash'], $_SESSION['reset_token_expires_at']);
                session_unset();
                session_destroy();
                redirect('index.php?page=login');
            } else {
                $error_message = "Les mots de passe ne correspondent pas.";
                $err = 1;
            }
        }

        // Gestion de la connexion
        if (isset($_POST['formsend'])) {
            $mail = Security::sanitizeEmail((string)($_POST['mail'] ?? ''));
            $password = $_POST['password'] ?? '';

            if (!empty($mail) && !empty($password)) {
                $rateLimitKey = $this->buildLoginRateLimitKey($mail);
                if ($this->isLoginRateLimited($rateLimitKey, 5, 15)) {
                    ErrorHandler::logSecurity("Rate limit login atteint", 'WARN', [
                        'email' => $mail,
                        'ip' => $this->resolveClientIp()
                    ]);
                    $error_message = 'Trop de tentatives de connexion. Réessayez dans 15 minutes.';
                    return [
                        'error_message' => $error_message,
                        'reset_step' => $_SESSION['reset_step'],
                        'err' => $err
                    ];
                }

                $user = $this->userModel->authenticate($mail, $password);

                if ($user) {
                    // Régénérer l'ID de session pour prévenir la fixation de session
                    session_regenerate_id(true);
                    $_SESSION['_created'] = time();
                    $_SESSION['_last_activity'] = time();
                    
                    // Connexion réussie : stocker les infos en session
                    $_SESSION['id'] = $user['id'];
                    $_SESSION['nom'] = $user['nom'];
                    $_SESSION['prenom'] = $user['prenom'];
                    $_SESSION['permission'] = $user['permission'];
                    
                    // Log successful login
                    ErrorHandler::logSecurity("Connexion réussie", 'INFO', [
                        'user_id' => $user['id'],
                        'email' => $mail
                    ]);
                    
                    // Réinitialiser le rate limit après connexion réussie
                    Security::resetRateLimit($rateLimitKey);
                    
                    redirect('index.php');
                } else {
                    $allowed = $this->registerLoginFailureAttempt($rateLimitKey, 5, 15);
                    // Log failed login attempt
                    ErrorHandler::logSecurity("Échec de connexion - identifiants invalides", 'FAIL', [
                        'email' => $mail,
                        'ip' => $this->resolveClientIp()
                    ]);
                    $error_message = $allowed
                        ? 'Identifiants invalides'
                        : 'Trop de tentatives de connexion. Réessayez dans 15 minutes.';
                }
            } else {
                $error_message = 'Données manquantes';
            }
        }

        return [
            'error_message' => $error_message,
            'reset_step' => $_SESSION['reset_step'],
            'err' => $err
        ];
    }

    /**
     * Déconnecte l'utilisateur
     * Nettoie la session et supprime le cookie de session
     */
    public function logout() {
        // Vider toutes les variables de session
        $_SESSION = [];
        
        // Supprimer le cookie de session
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Détruire la session
        session_destroy();
        
        // Rediriger vers la page de connexion
        header('Location: index.php?page=login');
        exit;
    }

    /**
     * Gère l'inscription des nouveaux utilisateurs
     * 
     * Workflow d'inscription :
     * - Étape 0 : Formulaire d'inscription
     * - Étape 1 : Vérification du code envoyé par email
     * 
     * Validations effectuées :
     * - Tous les champs requis remplis
     * - Email valide et non existant
     * - Mot de passe : 8+ caractères, 1+ caractère spécial
     * - Confirmation du mot de passe
     * 
     * @return array Données pour la vue [error_message, success_message, reset_step]
     */
    public function register() {
        $error_message = '';
        $success_message = '';
        $reset_step = 0;

        // Rediriger si déjà connecté
        if (isset($_SESSION['id'])) {
            redirect('/index.php');
        }

        // Initialiser l'étape dans la session
        if (!isset($_SESSION['reset_step'])) {
            $_SESSION['reset_step'] = 0;
        }

        $reset_step = $_SESSION['reset_step'];

        // Suivi des tentatives de vérification
        if (!isset($_SESSION['verification_attempts'])) {
            $_SESSION['verification_attempts'] = 0;
            $_SESSION['verification_attempts_time'] = time();
        }

        // Réinitialiser après 5 minutes
        if (time() - $_SESSION['verification_attempts_time'] > 300) {
            $_SESSION['verification_attempts'] = 0;
        }

        // Étape 1 : Envoi du code de vérification
        if (isset($_POST['send_code'])) {
            $nom = $_POST['nom'] ?? '';
            $prenom = $_POST['prenom'] ?? '';
            $promo = $_POST['promo'] ?? '';
            $niveau = $_POST['niveau'] ?? '';
            $ing2_type = $_POST['ing2_type'] ?? '';
            $mail = $_POST['mail'] ?? '';
            $password = $_POST['password'] ?? '';
            $cpassword = $_POST['cpassword'] ?? '';

            // Validation des champs
            if (empty($nom) || empty($prenom) || empty($mail) || empty($password) || empty($cpassword)) {
                $error_message = 'Tous les champs sont requis';
            } elseif (empty($promo)) {
                $error_message = 'Veuillez sélectionner votre statut';
            } elseif ($promo === 'etu' && empty($niveau)) {
                $error_message = 'Veuillez sélectionner votre promotion';
            } elseif ($niveau === 'ING2' && empty($ing2_type)) {
                $error_message = 'Veuillez sélectionner FISE ou FISEA';
            } elseif (($passwordError = $this->validateStrongPassword($password)) !== null) {
                $error_message = $passwordError;
            } elseif ($password !== $cpassword) {
                $error_message = 'Les mots de passe ne correspondent pas';
            } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                $error_message = 'Email invalide';
            } else {
                // Vérifier si l'utilisateur existe déjà
                $existing_user = $this->userModel->getUserByEmail($mail);
                if ($existing_user) {
                    $error_message = 'Un compte avec cet email existe déjà';
                } else {
                    // Générer un code robuste et ne stocker que son hash en session
                    $code = $this->generateRegistrationVerificationCode();
                    $_SESSION['code_verification_hash'] = $this->hashVerificationCode($code);
                    $_SESSION['code_verification_expires_at'] = time() + 300;
                    $_SESSION['nom'] = $nom;
                    $_SESSION['prenom'] = $prenom;
                    $_SESSION['promo'] = $promo;
                    $_SESSION['niveau'] = $niveau;
                    $_SESSION['ing2_type'] = $ing2_type;
                    $_SESSION['mail'] = $mail;
                    $_SESSION['password'] = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                    
                    // Envoyer l'email avec le code
                    $subject = 'Code de verification - Inscription EILCO';
                    $verificationEmail = buildRegistrationVerificationEmail($prenom, (string)$code);
                    $emailSent = sendEmail($mail, $subject, $verificationEmail);

                    if ($emailSent) {
                        $_SESSION['reset_step'] = 1;
                        $reset_step = 1;
                    } else {
                        unset(
                            $_SESSION['code_verification_hash'],
                            $_SESSION['code_verification_expires_at'],
                            $_SESSION['nom'],
                            $_SESSION['prenom'],
                            $_SESSION['promo'],
                            $_SESSION['niveau'],
                            $_SESSION['ing2_type'],
                            $_SESSION['mail'],
                            $_SESSION['password']
                        );

                        $error_message = "Impossible d'envoyer le code de verification. Veuillez reessayer.";
                        $_SESSION['reset_step'] = 0;
                        $reset_step = 0;

                        ErrorHandler::logError('Echec envoi email verification inscription', 'WARNING', [
                            'recipient' => $mail,
                        ]);
                    }
                }
            }
        }

        // Étape 2 : Vérification du code et création de l'utilisateur
        if (isset($_POST['verify_code'])) {
            $verification_code = $_POST['verification_code'] ?? '';

            if (empty($verification_code)) {
                $error_message = 'Veuillez entrer le code de vérification';
            } elseif (!isset($_SESSION['code_verification_hash'], $_SESSION['code_verification_expires_at'])) {
                $error_message = 'Le code de vérification a expiré. Veuillez recommencer.';
                $_SESSION['reset_step'] = 0;
                $reset_step = 0;
            } elseif (time() > (int)($_SESSION['code_verification_expires_at'] ?? 0)) {
                $error_message = 'Le code de vérification a expiré. Veuillez recommencer.';
                $_SESSION['reset_step'] = 0;
                $reset_step = 0;
                unset($_SESSION['code_verification_hash'], $_SESSION['code_verification_expires_at']);
            } elseif (!hash_equals((string)$_SESSION['code_verification_hash'], $this->hashVerificationCode((string)$verification_code))) {
                $_SESSION['verification_attempts']++;
                $_SESSION['verification_attempts_time'] = time();

                if ($_SESSION['verification_attempts'] >= 5) {
                    $error_message = 'Trop de tentatives. Veuillez réessayer plus tard.';
                    $_SESSION['reset_step'] = 0;
                    $reset_step = 0;
                    unset($_SESSION['code_verification_hash'], $_SESSION['code_verification_expires_at']);
                } else {
                    $error_message = 'Code de vérification incorrect.';
                }
            } else {
                // Code correct : créer l'utilisateur
                $sessionPromo = strtolower(trim((string)($_SESSION['promo'] ?? '')));
                $sessionNiveau = trim((string)($_SESSION['niveau'] ?? ''));
                $promo_value = ($sessionPromo === 'etu' && $sessionNiveau !== '')
                    ? $sessionNiveau
                    : $sessionPromo;
                $result = $this->userModel->createUser(
                    $_SESSION['nom'],
                    $_SESSION['prenom'],
                    $_SESSION['mail'],
                    $_SESSION['password'],
                    $promo_value,
                    true // Le mot de passe est déjà haché
                );

                if ($result) {
                    $success_message = 'Inscription réussie! Vous pouvez maintenant vous connecter.';
                    // Nettoyer la session
                      unset($_SESSION['code_verification_hash'], $_SESSION['code_verification_expires_at'], $_SESSION['nom'], $_SESSION['prenom'], 
                          $_SESSION['mail'], $_SESSION['password'], $_SESSION['promo'], 
                          $_SESSION['niveau'], $_SESSION['ing2_type'], $_SESSION['verification_attempts']);
                    $_SESSION['reset_step'] = 0;
                    $reset_step = 0;
                } else {
                    $error_message = 'Une erreur est survenue lors de la création du compte';
                }
            }
        }

        return [
            'error_message' => $error_message,
            'success_message' => $success_message,
            'reset_step' => $reset_step
        ];
    }
}
