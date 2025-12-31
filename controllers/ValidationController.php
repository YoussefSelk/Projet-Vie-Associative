<?php

class ValidationController {
    private $validationModel;
    private $clubModel;
    private $eventModel;
    private $db;

    public function __construct($database) {
        $this->db = $database;
        $this->validationModel = new Validation($database);
        $this->clubModel = new Club($database);
        $this->eventModel = new Event($database);
    }

    public function pendingClubs() {
        checkPermission(3);
        
        $clubs = $this->validationModel->getPendingClubs();
        
        return [
            'clubs' => $clubs
        ];
    }

    public function pendingEvents() {
        checkPermission(3);
        
        $events = $this->validationModel->getPendingEvents();
        
        return [
            'events' => $events
        ];
    }

    public function validateClub() {
        checkPermission(3);
        
        $error_msg = '';
        $success_msg = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['validate_club'])) {
            $club_id = $_POST['club_id'] ?? null;
            $action = $_POST['action'] ?? null;
            $remarques = trim($_POST['remarques'] ?? '');

            if (!$club_id || !$action) {
                $error_msg = "Données manquantes.";
            } else {
                if ($action === 'approve') {
                    // Approve with optional remarks
                    if ($this->validationModel->validateClub($club_id, 1, 1, 1, $remarques ?: null)) {
                        $success_msg = "Club approuvé avec succès.";
                    } else {
                        $error_msg = "Erreur lors de la validation.";
                    }
                } else {
                    // Reject with remarks
                    if ($this->validationModel->rejectClub($club_id, $remarques)) {
                        $success_msg = "Club rejeté.";
                    } else {
                        $error_msg = "Erreur lors du rejet.";
                    }
                }
            }
        }
        
        // Handle delete rejected clubs
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_club'])) {
            $club_id = $_POST['club_id'] ?? null;
            if ($club_id && $this->validationModel->deleteRejectedClub($club_id)) {
                $success_msg = "Club supprimé.";
            }
        }

        // Get pending and rejected clubs
        $clubs = $this->validationModel->getPendingClubs();
        $rejected_clubs = $this->validationModel->getRejectedClubs();

        return [
            'clubs' => $clubs,
            'rejected_clubs' => $rejected_clubs,
            'error_msg' => $error_msg,
            'success_msg' => $success_msg
        ];
    }

    public function validateEvent() {
        checkPermission(3);
        
        $error_msg = '';
        $success_msg = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['validate_event'])) {
            $event_id = $_POST['event_id'] ?? null;
            $action = $_POST['action'] ?? null;
            $remarques = trim($_POST['remarques'] ?? '');

            if (!$event_id || !$action) {
                $error_msg = "Données manquantes.";
            } else {
                if ($action === 'approve') {
                    if ($this->validationModel->validateEvent($event_id, 1, 1, 1, $remarques ?: null)) {
                        $success_msg = "Événement approuvé avec succès.";
                    } else {
                        $error_msg = "Erreur lors de la validation.";
                    }
                } else {
                    if ($this->validationModel->rejectEvent($event_id, $remarques)) {
                        $success_msg = "Événement rejeté.";
                    } else {
                        $error_msg = "Erreur lors du rejet.";
                    }
                }
            }
        }
        
        // Handle delete rejected events
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_event'])) {
            $event_id = $_POST['event_id'] ?? null;
            if ($event_id && $this->validationModel->deleteRejectedEvent($event_id)) {
                $success_msg = "Événement supprimé.";
            }
        }
        
        // Get pending and rejected events
        $events = $this->validationModel->getPendingEvents();
        $rejected_events = $this->validationModel->getRejectedEvents();

        return [
            'events' => $events,
            'rejected_events' => $rejected_events,
            'error_msg' => $error_msg,
            'success_msg' => $success_msg
        ];
    }

    public function tutoring() {
        validateSession();
        
        $is_admin = ($_SESSION['permission'] == 5);
        
        // Check if user is a tutor or admin
        $stmt = $this->db->prepare("SELECT * FROM fiche_club WHERE tuteur = ?");
        $stmt->execute([$_SESSION['id']]);
        $tutored_clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($tutored_clubs) && !$is_admin) {
            redirect('index.php');
        }
        
        $error_msg = '';
        $success_msg = '';
        
        // Get pending clubs - admins see all, tutors see only their clubs
        if ($is_admin) {
            $pending_clubs = $this->db->prepare("
                SELECT fc.*, u.nom as tuteur_nom, u.prenom as tuteur_prenom
                FROM fiche_club fc
                LEFT JOIN users u ON fc.tuteur = u.id
                WHERE fc.validation_tuteur IS NULL
            ");
            $pending_clubs->execute();
        } else {
            $pending_clubs = $this->db->prepare("
                SELECT * FROM fiche_club 
                WHERE tuteur = ? AND validation_tuteur IS NULL
            ");
            $pending_clubs->execute([$_SESSION['id']]);
        }
        $pending_clubs = $pending_clubs->fetchAll(PDO::FETCH_ASSOC);
        
        // Get pending events - admins see all, tutors see only their clubs' events
        if ($is_admin) {
            $pending_events = $this->db->prepare("
                SELECT fe.*, fc.nom_club, u.nom as tuteur_nom, u.prenom as tuteur_prenom
                FROM fiche_event fe
                INNER JOIN fiche_club fc ON fe.club_orga = fc.club_id
                LEFT JOIN users u ON fc.tuteur = u.id
                WHERE fe.validation_tuteur IS NULL
            ");
            $pending_events->execute();
        } else {
            $pending_events = $this->db->prepare("
                SELECT fe.*, fc.nom_club 
                FROM fiche_event fe
                INNER JOIN fiche_club fc ON fe.club_orga = fc.club_id
                WHERE fc.tuteur = ? AND fe.validation_tuteur IS NULL
            ");
            $pending_events->execute([$_SESSION['id']]);
        }
        $pending_events = $pending_events->fetchAll(PDO::FETCH_ASSOC);
        
        // Handle validation actions
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['validate_club_tutor'])) {
                $club_id = $_POST['club_id'] ?? null;
                $action = $_POST['action'] ?? null;
                
                if ($club_id && $action) {
                    $validation = ($action === 'approve') ? 1 : 0;
                    // Admins can validate any club, tutors only their own
                    if ($is_admin) {
                        $stmt = $this->db->prepare("UPDATE fiche_club SET validation_tuteur = ? WHERE club_id = ?");
                        $result = $stmt->execute([$validation, $club_id]);
                    } else {
                        $stmt = $this->db->prepare("UPDATE fiche_club SET validation_tuteur = ? WHERE club_id = ? AND tuteur = ?");
                        $result = $stmt->execute([$validation, $club_id, $_SESSION['id']]);
                    }
                    
                    if ($result && $stmt->rowCount() > 0) {
                        $success_msg = "Club " . ($validation ? "approuvé" : "rejeté");
                        // Refresh the pending list
                        if ($is_admin) {
                            $pending_clubs = $this->db->prepare("
                                SELECT fc.*, u.nom as tuteur_nom, u.prenom as tuteur_prenom
                                FROM fiche_club fc
                                LEFT JOIN users u ON fc.tuteur = u.id
                                WHERE fc.validation_tuteur IS NULL
                            ");
                            $pending_clubs->execute();
                        } else {
                            $pending_clubs = $this->db->prepare("SELECT * FROM fiche_club WHERE tuteur = ? AND validation_tuteur IS NULL");
                            $pending_clubs->execute([$_SESSION['id']]);
                        }
                        $pending_clubs = $pending_clubs->fetchAll(PDO::FETCH_ASSOC);
                    }
                }
            }
            
            if (isset($_POST['validate_event_tutor'])) {
                $event_id = $_POST['event_id'] ?? null;
                $action = $_POST['action'] ?? null;
                
                if ($event_id && $action) {
                    $validation = ($action === 'approve') ? 1 : 0;
                    // Admins can validate any event, tutors only their clubs' events
                    if ($is_admin) {
                        $stmt = $this->db->prepare("UPDATE fiche_event SET validation_tuteur = ? WHERE event_id = ?");
                        $result = $stmt->execute([$validation, $event_id]);
                    } else {
                        $stmt = $this->db->prepare("
                            UPDATE fiche_event fe
                            INNER JOIN fiche_club fc ON fe.club_orga = fc.club_id
                            SET fe.validation_tuteur = ?
                            WHERE fe.event_id = ? AND fc.tuteur = ?
                        ");
                        $result = $stmt->execute([$validation, $event_id, $_SESSION['id']]);
                    }
                    
                    if ($result && $stmt->rowCount() > 0) {
                        $success_msg = "Événement " . ($validation ? "approuvé" : "rejeté");
                        // Refresh the pending list
                        if ($is_admin) {
                            $pending_events = $this->db->prepare("
                                SELECT fe.*, fc.nom_club, u.nom as tuteur_nom, u.prenom as tuteur_prenom
                                FROM fiche_event fe
                                INNER JOIN fiche_club fc ON fe.club_orga = fc.club_id
                                LEFT JOIN users u ON fc.tuteur = u.id
                                WHERE fe.validation_tuteur IS NULL
                            ");
                            $pending_events->execute();
                        } else {
                            $pending_events = $this->db->prepare("
                                SELECT fe.*, fc.nom_club 
                                FROM fiche_event fe
                                INNER JOIN fiche_club fc ON fe.club_orga = fc.club_id
                                WHERE fc.tuteur = ? AND fe.validation_tuteur IS NULL
                            ");
                            $pending_events->execute([$_SESSION['id']]);
                        }
                        $pending_events = $pending_events->fetchAll(PDO::FETCH_ASSOC);
                    }
                }
            }
        }
        
        return [
            'is_admin' => $is_admin,
            'tutored_clubs' => $tutored_clubs,
            'pending_clubs' => $pending_clubs,
            'pending_events' => $pending_events,
            'error_msg' => $error_msg,
            'success_msg' => $success_msg
        ];
    }
}
