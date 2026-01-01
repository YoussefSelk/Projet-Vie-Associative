<?php
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
            $mail = $_POST['mail'];
            $user = $this->userModel->getUserByEmail($mail);

            if ($user) {
                $_SESSION['reset_mail'] = $mail;
                $_SESSION['reset_code'] = random_int(100000, 999999);
                sendEmail($mail, "Code de réinitialisation", "Votre code : " . $_SESSION['reset_code']);
                $_SESSION['reset_step'] = 2;
            } else {
                $error_message = "<p style='color: red;'>Aucun compte trouvé avec cet email.</p>";
                $_SESSION['reset_step'] = 1;
            }
        }

        // Limitation des tentatives de vérification
        if (!isset($_SESSION['verification_attempts'])) {
            $_SESSION['verification_attempts'] = 0;
            $_SESSION['verification_attempts_time'] = time();
        }
        
        // Réinitialiser après 5 minutes
        if (time() - $_SESSION['verification_attempts_time'] > 300) {
            $_SESSION['verification_attempts'] = 0;
        }
        
        // Vérification du code de réinitialisation
        if (isset($_POST['reset_code'])) {
            if ($_POST['reset_code'] != $_SESSION['reset_code']) {
                $_SESSION['verification_attempts']++;
                $_SESSION['verification_attempts_time'] = time();
                if ($_SESSION['verification_attempts'] >= 5) {
                    ErrorHandler::logSecurity("Rate limit atteint - trop de tentatives de vérification", 'WARN', [
                        'email' => $_SESSION['reset_mail'] ?? 'unknown'
                    ]);
                    die("Trop de tentatives, veuillez réessayer plus tard.");
                }
                ErrorHandler::logSecurity("Code de réinitialisation incorrect", 'FAIL', [
                    'email' => $_SESSION['reset_mail'] ?? 'unknown',
                    'attempts' => $_SESSION['verification_attempts']
                ]);
                $error_message = "<p style='color: red;'>Code de vérification incorrect.</p>";
            } else {
                unset($_SESSION['verification_attempts']);
            }
        }

        if (isset($_POST['verify_reset_code']) && isset($_SESSION['reset_code'])) {
            if ($_POST['reset_code'] == $_SESSION['reset_code']) {
                $_SESSION['reset_step'] = 3;
            } else {
                $error_message = "<p style='color: red;'>Code incorrect.</p>";
            }
        }

        // Mise à jour du mot de passe
        if (isset($_POST['reset_password'])) {
            $password = $_POST['password'];
            $cpassword = $_POST['cpassword'];
            
            if (strlen($password) < 8) {
                $error_message = "<p style='color: red;'>Le mot de passe doit contenir au moins 8 caractères.</p>";
            } else if (!preg_match('/[\W_]/', $password)) {
                $error_message = "<p style='color: red;'>Le mot de passe doit contenir au moins un caractère spécial.</p>";
            } else if ($_POST['password'] == $_POST['cpassword']) {
                $this->userModel->updatePassword($_SESSION['reset_mail'], $_POST['password']);
                unset($_SESSION['reset_mail'], $_SESSION['reset_code']);
                session_regenerate_id(true);
                session_unset();
                session_destroy();
                redirect('index.php?page=login');
            } else {
                $error_message = "<p style='color: red;'>Les mots de passe ne correspondent pas.</p>";
                $err = 1;
            }
        }

        // Gestion de la connexion
        if (isset($_POST['formsend'])) {
            $mail = $_POST['mail'] ?? '';
            $password = $_POST['password'] ?? '';

            if (!empty($mail) && !empty($password)) {
                $user = $this->userModel->authenticate($mail, $password);

                if ($user) {
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
                    
                    redirect('index.php');
                } else {
                    // Log failed login attempt
                    ErrorHandler::logSecurity("Échec de connexion - identifiants invalides", 'FAIL', [
                        'email' => $mail
                    ]);
                    $error_message = 'Identifiants invalides';
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
            } elseif ($promo === 'etu' && empty($niveau)) {
                $error_message = 'Veuillez sélectionner votre promotion';
            } elseif ($niveau === 'ING2' && empty($ing2_type)) {
                $error_message = 'Veuillez sélectionner FISE ou FISEA';
            } elseif (strlen($password) < 8) {
                $error_message = 'Le mot de passe doit contenir au moins 8 caractères';
            } elseif (!preg_match('/[\W_]/', $password)) {
                $error_message = 'Le mot de passe doit contenir au moins un caractère spécial';
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
                    // Générer le code de vérification
                    $code = random_int(100000, 999999);
                    $_SESSION['code_verification'] = $code;
                    $_SESSION['nom'] = $nom;
                    $_SESSION['prenom'] = $prenom;
                    $_SESSION['promo'] = $promo;
                    $_SESSION['niveau'] = $niveau;
                    $_SESSION['ing2_type'] = $ing2_type;
                    $_SESSION['mail'] = $mail;
                    $_SESSION['password'] = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                    
                    // Envoyer l'email avec le code
                    $subject = "Code de vérification - Inscription EILCO";
                    $message = "Bonjour $prenom,\n\nVotre code de vérification est : $code\n\nEntrez ce code pour finaliser votre inscription.\n\nCe code expire dans 5 minutes.";
                    sendEmail($mail, $subject, $message);

                    $_SESSION['reset_step'] = 1;
                    $reset_step = 1;
                }
            }
        }

        // Étape 2 : Vérification du code et création de l'utilisateur
        if (isset($_POST['verify_code'])) {
            $verification_code = $_POST['verification_code'] ?? '';

            if (empty($verification_code)) {
                $error_message = 'Veuillez entrer le code de vérification';
            } elseif (!isset($_SESSION['code_verification'])) {
                $error_message = 'Le code de vérification a expiré. Veuillez recommencer.';
                $_SESSION['reset_step'] = 0;
                $reset_step = 0;
            } elseif ($verification_code != $_SESSION['code_verification']) {
                $_SESSION['verification_attempts']++;
                $_SESSION['verification_attempts_time'] = time();

                if ($_SESSION['verification_attempts'] >= 5) {
                    $error_message = 'Trop de tentatives. Veuillez réessayer plus tard.';
                    $_SESSION['reset_step'] = 0;
                    $reset_step = 0;
                    unset($_SESSION['code_verification']);
                } else {
                    $error_message = 'Code de vérification incorrect.';
                }
            } else {
                // Code correct : créer l'utilisateur
                $promo_value = $_SESSION['niveau'] ?? $_SESSION['promo'];
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
                    unset($_SESSION['code_verification'], $_SESSION['nom'], $_SESSION['prenom'], 
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
