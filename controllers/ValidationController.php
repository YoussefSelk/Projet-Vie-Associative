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
                    // BDE approuve : validation_admin = 1 (pour clubs, BDE = admin de validation)
                    // Note: Pour les clubs, le workflow est: Tuteur valide -> Admin valide -> validation_finale
                    $stmt = $this->db->prepare("UPDATE fiche_club SET validation_admin = 1 WHERE club_id = ?");
                    if ($stmt->execute([$club_id])) {
                        // Verifier si le tuteur a deja valide pour appliquer validation_finale
                        $check = $this->db->prepare("SELECT validation_tuteur FROM fiche_club WHERE club_id = ?");
                        $check->execute([$club_id]);
                        $club = $check->fetch(PDO::FETCH_ASSOC);
                        
                        if ($club && $club['validation_tuteur'] == 1) {
                            // Tuteur deja valide, on peut donner la validation finale
                            $this->db->prepare("UPDATE fiche_club SET validation_finale = 1 WHERE club_id = ?")->execute([$club_id]);
                            $success_msg = "Club validé définitivement (tuteur avait déjà approuvé).";
                        } else {
                            $success_msg = "Club approuvé par l'administration. En attente de validation tuteur.";
                        }
                    } else {
                        $error_msg = "Erreur lors de la validation.";
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
            if (isset($_POST['validate_club_tutor'])) {
                $club_id = $_POST['club_id'] ?? null;
                $action = $_POST['action'] ?? null;
                $motif = $_POST['motif'] ?? null;
                
                if ($club_id && $action) {
                    $validation = ($action === 'approve') ? 1 : 0;
                    
                    // Les admins peuvent valider n'importe quel club
                    // Les tuteurs ne peuvent valider que leurs propres clubs
                    if ($is_admin) {
                        if ($action === 'reject' && $motif) {
                            $stmt = $this->db->prepare("UPDATE fiche_club SET validation_tuteur = ?, motif_refus = ? WHERE club_id = ?");
                            $result = $stmt->execute([$validation, $motif, $club_id]);
                        } else {
                            $stmt = $this->db->prepare("UPDATE fiche_club SET validation_tuteur = ? WHERE club_id = ?");
                            $result = $stmt->execute([$validation, $club_id]);
                        }
                    } else {
                        if ($action === 'reject' && $motif) {
                            $stmt = $this->db->prepare("UPDATE fiche_club SET validation_tuteur = ?, motif_refus = ? WHERE club_id = ? AND tuteur = ?");
                            $result = $stmt->execute([$validation, $motif, $club_id, $_SESSION['id']]);
                        } else {
                            $stmt = $this->db->prepare("UPDATE fiche_club SET validation_tuteur = ? WHERE club_id = ? AND tuteur = ?");
                            $result = $stmt->execute([$validation, $club_id, $_SESSION['id']]);
                        }
                    }
                    
                    if ($result && $stmt->rowCount() > 0) {
                        // Si approuve, verifier si admin a deja valide pour donner validation finale
                        if ($validation == 1) {
                            $check = $this->db->prepare("SELECT validation_admin FROM fiche_club WHERE club_id = ?");
                            $check->execute([$club_id]);
                            $club = $check->fetch(PDO::FETCH_ASSOC);
                            
                            if ($club && $club['validation_admin'] == 1) {
                                $this->db->prepare("UPDATE fiche_club SET validation_finale = 1 WHERE club_id = ?")->execute([$club_id]);
                                $success_msg = "Club validé définitivement.";
                            } else {
                                $success_msg = "Club approuvé par le tuteur. En attente de validation administration.";
                            }
                        } else {
                            $success_msg = "Club rejeté.";
                        }
                        
                        // Rafraichir la liste des clubs en attente apres la modification
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
            
            // Validation d'un evenement par le tuteur
            if (isset($_POST['validate_event_tutor'])) {
                $event_id = $_POST['event_id'] ?? null;
                $action = $_POST['action'] ?? null;
                $motif = $_POST['motif'] ?? null;
                
                if ($event_id && $action) {
                    $validation = ($action === 'approve') ? 1 : 0;
                    
                    // Les admins peuvent valider n'importe quel evenement
                    // Les tuteurs ne peuvent valider que les evenements de leurs clubs
                    if ($is_admin) {
                        if ($action === 'reject' && $motif) {
                            $stmt = $this->db->prepare("UPDATE fiche_event SET validation_tuteur = ?, motif_refus = ? WHERE event_id = ?");
                            $result = $stmt->execute([$validation, $motif, $event_id]);
                        } else {
                            $stmt = $this->db->prepare("UPDATE fiche_event SET validation_tuteur = ? WHERE event_id = ?");
                            $result = $stmt->execute([$validation, $event_id]);
                        }
                    } else {
                        // Jointure necessaire pour verifier que l'evenement appartient a un club tutore
                        if ($action === 'reject' && $motif) {
                            $stmt = $this->db->prepare("
                                UPDATE fiche_event fe
                                INNER JOIN fiche_club fc ON fe.club_orga = fc.club_id
                                SET fe.validation_tuteur = ?, fe.motif_refus = ?
                                WHERE fe.event_id = ? AND fc.tuteur = ?
                            ");
                            $result = $stmt->execute([$validation, $motif, $event_id, $_SESSION['id']]);
                        } else {
                            $stmt = $this->db->prepare("
                                UPDATE fiche_event fe
                                INNER JOIN fiche_club fc ON fe.club_orga = fc.club_id
                                SET fe.validation_tuteur = ?
                                WHERE fe.event_id = ? AND fc.tuteur = ?
                            ");
                            $result = $stmt->execute([$validation, $event_id, $_SESSION['id']]);
                        }
                    }
                    
                    if ($result && $stmt->rowCount() > 0) {
                        // Si approuve, verifier si BDE a deja valide pour donner validation finale
                        if ($validation == 1) {
                            $check = $this->db->prepare("SELECT validation_bde FROM fiche_event WHERE event_id = ?");
                            $check->execute([$event_id]);
                            $event = $check->fetch(PDO::FETCH_ASSOC);
                            
                            if ($event && $event['validation_bde'] == 1) {
                                $this->db->prepare("UPDATE fiche_event SET validation_finale = 1 WHERE event_id = ?")->execute([$event_id]);
                                $success_msg = "Événement validé définitivement.";
                            } else {
                                $success_msg = "Événement approuvé par le tuteur. En attente de validation BDE.";
                            }
                        } else {
                            $success_msg = "Événement rejeté.";
                        }
                        
                        // Rafraichir la liste des evenements en attente apres la modification
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
