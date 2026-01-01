<?php
/**
 * =============================================================================
 * CONTRÔLEUR UTILISATEUR
 * =============================================================================
 * 
 * Gère les fonctionnalités liées au profil utilisateur :
 * - Tableau de bord personnel
 * - Affichage et modification du profil
 * - Liste des utilisateurs (pour les admins)
 * 
 * Note : Les fonctions updatePermission() et deleteUser() ont été
 * déplacées vers AdminController pour une meilleure séparation des responsabilités
 * 
 * @author Équipe de développement EILCO
 * @version 2.0
 */

class UserController {
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
     * Tableau de bord personnel de l'utilisateur
     * Affiche les statistiques personnalisées, événements à venir et adhésions
     * 
     * @return array Données pour la vue
     */
    public function dashboard() {
        validateSession();
        
        $user_id = $_SESSION['id'];
        $user = $this->userModel->getUserById($user_id);
        $stats = [];
        
        // Récupérer les adhésions aux clubs de l'utilisateur
        $stmt = $this->db->prepare("
            SELECT fc.*
            FROM membres_club mc
            JOIN fiche_club fc ON mc.club_id = fc.club_id
            WHERE mc.membre_id = ? AND mc.valide = 1 AND fc.validation_finale = 1
            ORDER BY fc.nom_club
        ");
        $stmt->execute([$user_id]);
        $my_clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stats['clubs_count'] = count($my_clubs);
        
        // Récupérer les inscriptions aux événements
        try {
            $stmt = $this->db->prepare("
                SELECT fe.*, fc.nom_club,
                    CASE 
                        WHEN fe.date_ev < NOW() THEN 'past'
                        WHEN fe.date_ev <= DATE_ADD(NOW(), INTERVAL 7 DAY) THEN 'soon'
                        ELSE 'upcoming'
                    END as status
                FROM subscribe_event se
                JOIN fiche_event fe ON se.event_id = fe.event_id
                LEFT JOIN fiche_club fc ON fe.club_orga = fc.club_id
                WHERE se.user_id = ? AND fe.validation_finale = 1
                ORDER BY fe.date_ev ASC
            ");
            $stmt->execute([$user_id]);
            $my_subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $my_subscriptions = [];
        }
        
        // Séparer les événements à venir et passés
        $upcoming_events = array_filter($my_subscriptions, fn($e) => $e['status'] !== 'past');
        $past_events = array_filter($my_subscriptions, fn($e) => $e['status'] === 'past');
        $stats['subscriptions_count'] = count($my_subscriptions);
        $stats['upcoming_count'] = count($upcoming_events);
        
        // Événements recommandés (événements des clubs de l'utilisateur, non encore inscrit)
        $recommended_events = [];
        if (!empty($my_clubs)) {
            $club_ids = array_column($my_clubs, 'club_id');
            $placeholders = implode(',', array_fill(0, count($club_ids), '?'));
            
            try {
                $stmt = $this->db->prepare("
                    SELECT fe.*, fc.nom_club
                    FROM fiche_event fe
                    JOIN fiche_club fc ON fe.club_orga = fc.club_id
                    LEFT JOIN subscribe_event se ON fe.event_id = se.event_id AND se.user_id = ?
                    WHERE fe.club_orga IN ($placeholders)
                        AND fe.validation_finale = 1
                        AND fe.date_ev >= NOW()
                        AND se.id IS NULL
                    ORDER BY fe.date_ev ASC
                    LIMIT 5
                ");
                $params = array_merge([$user_id], $club_ids);
                $stmt->execute($params);
                $recommended_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $recommended_events = [];
            }
        }
        
        // Score de complétion du profil
        $profile_fields = ['nom', 'prenom', 'mail', 'promo'];
        $filled_fields = 0;
        foreach ($profile_fields as $field) {
            if (!empty($user[$field])) $filled_fields++;
        }
        $stats['profile_completion'] = round(($filled_fields / count($profile_fields)) * 100);
        
        // Résumé d'activité
        $stats['events_attended'] = count($past_events);
        
        return [
            'user' => $user,
            'stats' => $stats,
            'my_clubs' => $my_clubs,
            'upcoming_events' => array_slice($upcoming_events, 0, 5),
            'past_events' => array_slice($past_events, 0, 3),
            'recommended_events' => $recommended_events
        ];
    }

    /**
     * Affiche le profil de l'utilisateur connecté
     * 
     * @return array Données utilisateur pour la vue
     */
    public function viewProfile() {
        validateSession();
        
        $user = $this->userModel->getUserById($_SESSION['id']);
        
        return [
            'user' => $user
        ];
    }

    /**
     * Modification du profil utilisateur
     * Permet de modifier nom, prénom et email
     * 
     * @return array Données pour la vue [user, error_msg, success_msg]
     */
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
                    // Mettre à jour les données en session
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

    /**
     * Liste tous les utilisateurs
     * Accessible uniquement aux utilisateurs avec permission >= 3
     * 
     * @return array Liste des utilisateurs
     */
    public function listAllUsers() {
        checkPermission(3);
        
        $users = $this->userModel->getAllUsers();
        
        return [
            'users' => $users
        ];
    }
}
