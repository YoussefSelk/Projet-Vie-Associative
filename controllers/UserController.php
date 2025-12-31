<?php

class UserController {
    private $userModel;
    private $db;

    public function __construct($database) {
        $this->db = $database;
        $this->userModel = new User($database);
    }

    /**
     * Personal dashboard for authenticated users
     * Shows personalized stats, upcoming events, club memberships
     */
    public function dashboard() {
        validateSession();
        
        $user_id = $_SESSION['id'];
        $user = $this->userModel->getUserById($user_id);
        $stats = [];
        
        // Get user's club memberships
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
        
        // Get user's event subscriptions
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
        
        // Separate upcoming and past events
        $upcoming_events = array_filter($my_subscriptions, fn($e) => $e['status'] !== 'past');
        $past_events = array_filter($my_subscriptions, fn($e) => $e['status'] === 'past');
        $stats['subscriptions_count'] = count($my_subscriptions);
        $stats['upcoming_count'] = count($upcoming_events);
        
        // Get recommended events (events from clubs user is member of, not yet subscribed)
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
        
        // Profile completion score
        $profile_fields = ['nom', 'prenom', 'mail', 'promo'];
        $filled_fields = 0;
        foreach ($profile_fields as $field) {
            if (!empty($user[$field])) $filled_fields++;
        }
        $stats['profile_completion'] = round(($filled_fields / count($profile_fields)) * 100);
        
        // Activity summary
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
    
    // Note: updatePermission() and deleteUser() have been moved to AdminController

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
