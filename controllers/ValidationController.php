<?php

/**
 * Controleur de validation des clubs et evenements
 * 
 * Gere le workflow de validation a plusieurs niveaux :
 * - Validation BDE (permission 3+)
 * - Validation tuteur (permission 2+)
 * - Approbation et rejet avec remarques
 * 
 * Deux flux distincts :
 * 1. pendingClubs/pendingEvents/validateClub/validateEvent : Validation BDE
 * 2. tutoring : Validation tuteur avec filtrage par clubs tutores
 * 
 * @package Controllers
 */
class ValidationController {
    
    /** @var Validation Instance du modele de validation */
    private $validationModel;
    
    /** @var Club Instance du modele de club */
    private $clubModel;
    
    /** @var Event Instance du modele d'evenement */
    private $eventModel;
    
    /** @var PDO Connexion a la base de donnees */
    private $db;

    /**
     * Constructeur - initialise les dependances
     * 
     * @param PDO $database Connexion a la base de donnees
     */
    public function __construct($database) {
        $this->db = $database;
        $this->validationModel = new Validation($database);
        $this->clubModel = new Club($database);
        $this->eventModel = new Event($database);
    }

    /**
     * Affiche la liste des clubs en attente de validation BDE
     * Requiert permission 3 (membre BDE)
     * 
     * @return array Donnees pour la vue (liste des clubs en attente)
     */
    public function pendingClubs() {
        checkPermission(3);
        
        $clubs = $this->validationModel->getPendingClubs();
        
        return [
            'clubs' => $clubs
        ];
    }

    /**
     * Affiche la liste des evenements en attente de validation BDE
     * Requiert permission 3 (membre BDE)
     * 
     * @return array Donnees pour la vue (liste des evenements en attente)
     */
    public function pendingEvents() {
        checkPermission(3);
        
        $events = $this->validationModel->getPendingEvents();
        
        return [
            'events' => $events
        ];
    }

    /**
     * Gere la validation/rejet des clubs par le BDE
     * Traite les actions POST : approve, reject, delete
     * Requiert permission 3 (membre BDE)
     * 
     * Le BDE ne valide que validation_bde (pas la validation finale)
     * La validation finale est geree separement par l'admin
     * 
     * Actions possibles :
     * - validate_club : Approuver ou rejeter un club (validation BDE)
     * - delete_club : Supprimer un club rejete
     * 
     * @return array Donnees pour la vue (clubs en attente, clubs rejetes, messages)
     */
    public function validateClub() {
        checkPermission(3);
        
        $error_msg = '';
        $success_msg = '';

        // Traitement de la validation ou du rejet d'un club
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['validate_club'])) {
            $club_id = $_POST['club_id'] ?? null;
            $action = $_POST['action'] ?? null;
            $remarques = trim($_POST['remarques'] ?? '');

            if (!$club_id || !$action) {
                $error_msg = "Données manquantes.";
            } else {
                if ($action === 'approve') {
                    // Vérifier si l'utilisateur est tuteur du club ou admin système
                    $user_id = $_SESSION['id'] ?? null;
                    $user_permission = $_SESSION['permission'] ?? 0;
                    
                    // Récupérer le tuteur du club
                    $tutorCheck = $this->db->prepare("SELECT tuteur FROM fiche_club WHERE club_id = ?");
                    $tutorCheck->execute([$club_id]);
                    $club = $tutorCheck->fetch(PDO::FETCH_ASSOC);
                    $club_tutor = $club['tuteur'] ?? null;
                    
                    $is_tutor = ($club_tutor && $club_tutor == $user_id);
                    $is_admin = ($user_permission >= 5);
                    
                    if ($is_tutor) {
                        // Le tuteur approuve : mise à jour de validation_tuteur
                        $stmt = $this->db->prepare("UPDATE fiche_club SET validation_tuteur = 1 WHERE club_id = ?");
                        if ($stmt->execute([$club_id])) {
                            $success_msg = "Club approuvé par le tuteur. En attente de validation administrateur.";
                        } else {
                            $error_msg = "Erreur lors de la validation.";
                        }
                    } else {
                        // L'admin approuve : mise à jour de validation_admin et validation_finale = 1
                        $stmt = $this->db->prepare("UPDATE fiche_club SET validation_admin = 1, validation_finale = 1 WHERE club_id = ?");
                        if ($stmt->execute([$club_id])) {
                            $success_msg = "Club validé définitivement par l'administration.";
                        } else {
                            $error_msg = "Erreur lors de la validation.";
                        }
                    }
                } else {
                    // Rejet avec remarques obligatoires
                    if ($this->validationModel->rejectClub($club_id, $remarques)) {
                        $success_msg = "Club rejeté.";
                    } else {
                        $error_msg = "Erreur lors du rejet.";
                    }
                }
            }
        }
        
        // Traitement de la suppression d'un club rejete
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_club'])) {
            $club_id = $_POST['club_id'] ?? null;
            if ($club_id && $this->validationModel->deleteRejectedClub($club_id)) {
                $success_msg = "Club supprimé.";
            }
        }

        // Recuperation des listes de clubs pour l'affichage
        $clubs = $this->validationModel->getPendingClubs();
        $rejected_clubs = $this->validationModel->getRejectedClubs();

        return [
            'clubs' => $clubs,
            'rejected_clubs' => $rejected_clubs,
            'error_msg' => $error_msg,
            'success_msg' => $success_msg
        ];
    }

    /**
     * Gere la validation/rejet des evenements par le BDE
     * Traite les actions POST : approve, reject, delete
     * Requiert permission 3 (membre BDE)
     * 
     * Le BDE ne valide que validation_bde
     * La validation finale necessite: validation_bde = 1 ET (validation_tuteur = 1 OU validation_admin = 1)
     * 
     * Actions possibles :
     * - validate_event : Approuver ou rejeter un evenement (validation BDE)
     * - delete_event : Supprimer un evenement rejete
     * 
     * @return array Donnees pour la vue (evenements en attente, rejetes, messages)
     */
    public function validateEvent() {
        checkPermission(3);
        
        $error_msg = '';
        $success_msg = '';

        // Traitement de la validation ou du rejet d'un evenement
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['validate_event'])) {
            $event_id = $_POST['event_id'] ?? null;
            $action = $_POST['action'] ?? null;
            $remarques = trim($_POST['remarques'] ?? '');

            if (!$event_id || !$action) {
                $error_msg = "Données manquantes.";
            } else {
                if ($action === 'approve') {
                    // BDE approuve : validation_bde = 1
                    $stmt = $this->db->prepare("UPDATE fiche_event SET validation_bde = 1 WHERE event_id = ?");
                    if ($stmt->execute([$event_id])) {
                        // Verifier si tuteur OU admin a deja valide pour appliquer validation_finale
                        $check = $this->db->prepare("SELECT validation_tuteur, validation_admin FROM fiche_event WHERE event_id = ?");
                        $check->execute([$event_id]);
                        $event = $check->fetch(PDO::FETCH_ASSOC);
                        
                        if ($event && ($event['validation_tuteur'] == 1 || $event['validation_admin'] == 1)) {
                            // Tuteur ou admin a deja valide, on peut donner la validation finale
                            $this->db->prepare("UPDATE fiche_event SET validation_finale = 1 WHERE event_id = ?")->execute([$event_id]);
                            $success_msg = "Événement validé définitivement.";
                        } else {
                            $success_msg = "Événement approuvé par le BDE. En attente de validation tuteur ou admin.";
                        }
                    } else {
                        $error_msg = "Erreur lors de la validation.";
                    }
                } else {
                    // Rejet avec remarques obligatoires
                    if ($this->validationModel->rejectEvent($event_id, $remarques)) {
                        $success_msg = "Événement rejeté.";
                    } else {
                        $error_msg = "Erreur lors du rejet.";
                    }
                }
            }
        }
        
        // Traitement de la suppression d'un evenement rejete
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_event'])) {
            $event_id = $_POST['event_id'] ?? null;
            if ($event_id && $this->validationModel->deleteRejectedEvent($event_id)) {
                $success_msg = "Événement supprimé.";
            }
        }
        
        // Recuperation des listes d'evenements pour l'affichage
        $events = $this->validationModel->getPendingEvents();
        $rejected_events = $this->validationModel->getRejectedEvents();

        return [
            'events' => $events,
            'rejected_events' => $rejected_events,
            'error_msg' => $error_msg,
            'success_msg' => $success_msg
        ];
    }

    /**
     * Interface de validation pour les tuteurs
     * Permet aux tuteurs de valider les clubs et evenements de leurs clubs
     * Les administrateurs voient tout, les tuteurs voient seulement leurs clubs
     * 
     * Niveaux d'acces :
     * - Admin (permission 5) : Voit et valide tout
     * - Tuteur (permission 2+) : Voit et valide uniquement ses clubs
     * - Autres : Acces refuse (erreur 403)
     * 
     * @return array Donnees pour la vue
     */
    public function tutoring() {
        // Verification de la session - obligatoire
        validateSession();
        
        // Determiner le niveau d'acces de l'utilisateur
        $is_admin = ($_SESSION['permission'] == 5);
        $is_tutor = ($_SESSION['permission'] >= 2);
        
        // Verification des permissions : doit etre au moins tuteur (permission 2)
        if (!$is_tutor) {
            ErrorHandler::renderHttpError(403, "Vous devez être tuteur pour accéder à cette page.");
        }
        
        // Recuperer les clubs tutores par l'utilisateur connecte
        $stmt = $this->db->prepare("SELECT * FROM fiche_club WHERE tuteur = ?");
        $stmt->execute([$_SESSION['id']]);
        $tutored_clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Si pas de clubs tutores et pas admin, refuser l'acces
        if (empty($tutored_clubs) && !$is_admin) {
            ErrorHandler::renderHttpError(403, "Vous n'êtes tuteur d'aucun club. Vous devez être assigné comme tuteur d'un club pour accéder à cette page.");
        }
        
        $error_msg = '';
        $success_msg = '';
        
        // Recuperer les clubs en attente de validation tuteur
        // Les admins voient tous les clubs, les tuteurs voient seulement leurs clubs
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
        
        // Recuperer les evenements en attente de validation tuteur
        // Filtrage similaire : admins voient tout, tuteurs voient leurs clubs
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
        
        // Traitement des actions de validation
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            // Validation d'un club par le tuteur
            if (isset($_POST['validate_club_admin'])) {
            $club_id = $_POST['club_id'] ?? null;
            $action = $_POST['action'] ?? null;
            $motif = trim($_POST['motif'] ?? ''); // Récupération du motif de rejet

            if ($club_id && $action) {
                if ($is_admin) {
                    // --- LOGIQUE ADMINISTRATEUR ---
                    if ($action === 'approve') {
                        // L'admin approuve : validation_admin = 1 ET validation_finale = 1
                        $stmt = $this->db->prepare("UPDATE fiche_club SET validation_admin = 1, validation_finale = 1, motif_refus = NULL WHERE club_id = ?");
                        $result = $stmt->execute([$club_id]);
                        $success_msg = "Club validé définitivement par l'administration.";
                    } else {
                        // L'admin rejette : validation_admin = 0 SEULEMENT
                        // Ne pas toucher validation_finale, ça reste chez l'admin (n'apparait pas chez tuteur)
                        $stmt = $this->db->prepare("UPDATE fiche_club SET validation_admin = 0, motif_refus = ? WHERE club_id = ?");
                        $result = $stmt->execute([$motif, $club_id]);
                        $success_msg = "Club rejeté par l'administration.";
                    }
                } else {
                    // --- LOGIQUE TUTEUR ---
                    $val = ($action === 'approve') ? 1 : 0;
                    // Le tuteur ne modifie que sa colonne. La validation finale reste à NULL.
                    $stmt = $this->db->prepare("UPDATE fiche_club SET validation_tuteur = ?, motif_refus = ? WHERE club_id = ? AND tuteur = ?");
                    $result = $stmt->execute([$val, $motif, $club_id, $_SESSION['id']]);
                    $success_msg = "Avis du tuteur enregistré. En attente de la décision de l'administration.";
                }

                // --- RAFRAICHISSEMENT DE LA LISTE ---
                if ($result) {
                    if ($is_admin) {
                        // L'admin voit les clubs où validation_admin est encore NULL
                        $pending_clubs = $this->db->prepare("
                            SELECT fc.*, u.nom as tuteur_nom, u.prenom as tuteur_prenom 
                            FROM fiche_club fc 
                            LEFT JOIN users u ON fc.tuteur = u.id 
                            WHERE fc.validation_admin IS NULL
                        ");
                        $pending_clubs->execute();
                    } else {
                        // Le tuteur voit ses clubs où validation_tuteur est encore NULL
                        $pending_clubs = $this->db->prepare("SELECT * FROM fiche_club WHERE tuteur = ? AND validation_tuteur IS NULL");
                        $pending_clubs->execute([$_SESSION['id']]);
                    }
                    $pending_clubs = $pending_clubs->fetchAll(PDO::FETCH_ASSOC);
                }
            }
        }
            
            // Validation d'un evenement par l'administrateur
            if (isset($_POST['validate_event_admin'])) {
                $event_id = $_POST['event_id'] ?? null;
                $action = $_POST['action'] ?? null;
                $motif = trim($_POST['motif'] ?? '');

                if ($event_id && $action && $is_admin) {
                    if ($action === 'approve') {
                        // L'admin approuve : validation_admin = 1 ET validation_finale = 1
                        $stmt = $this->db->prepare("UPDATE fiche_event SET validation_admin = 1, validation_finale = 1, motif_refus = NULL WHERE event_id = ?");
                        $result = $stmt->execute([$event_id]);
                        $success_msg = "Événement validé définitivement par l'administration.";
                    } else {
                        // L'admin rejette : validation_admin = 0 SEULEMENT
                        $stmt = $this->db->prepare("UPDATE fiche_event SET validation_admin = 0, motif_refus = ? WHERE event_id = ?");
                        $result = $stmt->execute([$motif, $event_id]);
                        $success_msg = "Événement rejeté par l'administration.";
                    }

                    // Rafraichir la liste des evenements en attente
                    if ($result) {
                        $pending_events = $this->db->prepare("
                            SELECT fe.*, fc.nom_club, u.nom as tuteur_nom, u.prenom as tuteur_prenom
                            FROM fiche_event fe
                            INNER JOIN fiche_club fc ON fe.club_orga = fc.club_id
                            LEFT JOIN users u ON fc.tuteur = u.id
                            WHERE fe.validation_admin IS NULL
                        ");
                        $pending_events->execute();
                        $pending_events = $pending_events->fetchAll(PDO::FETCH_ASSOC);
                    }
                }
            }

            // Validation d'un evenement par le tuteur
            if (isset($_POST['validate_event_tutor'])) {
                $event_id = $_POST['event_id'] ?? null;
                $action = $_POST['action'] ?? null;
                $motif = trim($_POST['motif'] ?? '');
                
                if ($event_id && $action && !$is_admin) {
                    $val = ($action === 'approve') ? 1 : 0;
                    
                    // Le tuteur ne modifie que validation_tuteur, pas validation_finale
                    $stmt = $this->db->prepare("
                        UPDATE fiche_event fe
                        INNER JOIN fiche_club fc ON fe.club_orga = fc.club_id
                        SET fe.validation_tuteur = ?, fe.motif_refus = ?
                        WHERE fe.event_id = ? AND fc.tuteur = ?
                    ");
                    $result = $stmt->execute([$val, $motif, $event_id, $_SESSION['id']]);
                    
                    if ($result && $stmt->rowCount() > 0) {
                        if ($val == 1) {
                            $success_msg = "Avis du tuteur enregistré. En attente de la décision de l'administration.";
                        } else {
                            $success_msg = "Événement rejeté par le tuteur.";
                        }
                        
                        // Rafraichir la liste des evenements en attente
                        $pending_events = $this->db->prepare("
                            SELECT fe.*, fc.nom_club 
                            FROM fiche_event fe
                            INNER JOIN fiche_club fc ON fe.club_orga = fc.club_id
                            WHERE fc.tuteur = ? AND fe.validation_tuteur IS NULL
                        ");
                        $pending_events->execute([$_SESSION['id']]);
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





