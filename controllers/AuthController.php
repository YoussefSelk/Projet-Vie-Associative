<?php

class AuthController {
    private $userModel;
    private $db;

    public function __construct($database) {
        $this->db = $database;
        $this->userModel = new User($database);
    }

    public function login() {
        $error_message = '';
        $err = 0;

        if (isset($_SESSION['id'])) {
            redirect('/index.php');
        }

        // Reset to login form on fresh page visit (GET request)
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $_SESSION['reset_step'] = 0;
        }

        if (!isset($_SESSION['reset_step'])) {
            $_SESSION['reset_step'] = 0;
        }

        // Handle password reset request
        if (isset($_POST['check-email'])) {
            $_SESSION['reset_step'] = 1;
        }

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

        if (!isset($_SESSION['verification_attempts'])) {
            $_SESSION['verification_attempts'] = 0;
            $_SESSION['verification_attempts_time'] = time();
        }
        if (time() - $_SESSION['verification_attempts_time'] > 300) {
            $_SESSION['verification_attempts'] = 0;
        }
        if (isset($_POST['reset_code'])) {
            if ($_POST['reset_code'] != $_SESSION['reset_code']) {
                $_SESSION['verification_attempts']++;
                $_SESSION['verification_attempts_time'] = time();
                if ($_SESSION['verification_attempts'] >= 5) {
                    die("Trop de tentatives, veuillez réessayer plus tard.");
                }
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

        // Handle login
        if (isset($_POST['formsend'])) {
            $mail = $_POST['mail'] ?? '';
            $password = $_POST['password'] ?? '';

            if (!empty($mail) && !empty($password)) {
                $user = $this->userModel->authenticate($mail, $password);

                if ($user) {
                    $_SESSION['id'] = $user['id'];
                    $_SESSION['nom'] = $user['nom'];
                    $_SESSION['prenom'] = $user['prenom'];
                    $_SESSION['permission'] = $user['permission'];
                    redirect('index.php');
                } else {
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

    public function logout() {
        session_unset();
        session_destroy();
        redirect('index.php?page=login');
    }

    public function register() {
        $error_message = '';
        $success_message = '';
        $reset_step = 0;

        if (isset($_SESSION['id'])) {
            redirect('/index.php');
        }

        // Initialize reset_step in session
        if (!isset($_SESSION['reset_step'])) {
            $_SESSION['reset_step'] = 0;
        }

        $reset_step = $_SESSION['reset_step'];

        // Track verification attempts
        if (!isset($_SESSION['verification_attempts'])) {
            $_SESSION['verification_attempts'] = 0;
            $_SESSION['verification_attempts_time'] = time();
        }

        // Reset attempts after 5 minutes
        if (time() - $_SESSION['verification_attempts_time'] > 300) {
            $_SESSION['verification_attempts'] = 0;
        }

        // Step 1: Send verification code
        if (isset($_POST['send_code'])) {
            $nom = $_POST['nom'] ?? '';
            $prenom = $_POST['prenom'] ?? '';
            $promo = $_POST['promo'] ?? '';
            $niveau = $_POST['niveau'] ?? '';
            $ing2_type = $_POST['ing2_type'] ?? '';
            $mail = $_POST['mail'] ?? '';
            $password = $_POST['password'] ?? '';
            $cpassword = $_POST['cpassword'] ?? '';

            // Validation
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
                // Check if user already exists
                $existing_user = $this->userModel->getUserByEmail($mail);
                if ($existing_user) {
                    $error_message = 'Un compte avec cet email existe déjà';
                } else {
                    // Generate verification code
                    $code = random_int(100000, 999999);
                    $_SESSION['code_verification'] = $code;
                    $_SESSION['nom'] = $nom;
                    $_SESSION['prenom'] = $prenom;
                    $_SESSION['promo'] = $promo;
                    $_SESSION['niveau'] = $niveau;
                    $_SESSION['ing2_type'] = $ing2_type;
                    $_SESSION['mail'] = $mail;
                    $_SESSION['password'] = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                    
                    // Send email with code
                    $subject = "Code de vérification - Inscription EILCO";
                    $message = "Bonjour $prenom,\n\nVotre code de vérification est : $code\n\nEntrez ce code pour finaliser votre inscription.\n\nCe code expire dans 5 minutes.";
                    sendEmail($mail, $subject, $message);

                    $_SESSION['reset_step'] = 1;
                    $reset_step = 1;
                }
            }
        }

        // Step 2: Verify code and create user
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
                // Code is correct - create user
                $promo_value = $_SESSION['niveau'] ?? $_SESSION['promo'];
                $result = $this->userModel->createUser(
                    $_SESSION['nom'],
                    $_SESSION['prenom'],
                    $_SESSION['mail'],
                    $_SESSION['password'],
                    $promo_value
                );

                if ($result) {
                    $success_message = 'Inscription réussie! Vous pouvez maintenant vous connecter.';
                    // Clean up session
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
