<?php

class UserController {
    private $userModel;
    private $db;

    public function __construct($database) {
        $this->db = $database;
        $this->userModel = new User($database);
    }

    public function viewProfile() {
        validateSession();
        
        $user = $this->userModel->getUserById($_SESSION['id']);
        
        return [
            'user' => $user
        ];
    }

    public function editProfile() {
        validateSession();
        
        $user = $this->userModel->getUserById($_SESSION['id']);
        $error_msg = '';
        $success_msg = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_profile'])) {
            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $mail = trim($_POST['mail'] ?? '');

            if (!$nom || !$prenom || !$mail) {
                $error_msg = "Tous les champs sont obligatoires.";
            } else {
                $data = [
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'mail' => $mail
                ];

                if ($this->userModel->updateUser($_SESSION['id'], $data)) {
                    $success_msg = "Profil mis à jour avec succès.";
                    $_SESSION['nom'] = $nom;
                    $_SESSION['prenom'] = $prenom;
                    $user = $this->userModel->getUserById($_SESSION['id']);
                } else {
                    $error_msg = "Erreur lors de la mise à jour du profil.";
                }
            }
        }

        return [
            'user' => $user,
            'error_msg' => $error_msg,
            'success_msg' => $success_msg
        ];
    }

    public function listAllUsers() {
        checkPermission(3);
        
        $users = $this->userModel->getAllUsers();
        
        return [
            'users' => $users
        ];
    }
}
