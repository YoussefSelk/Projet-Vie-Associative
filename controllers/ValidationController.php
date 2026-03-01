<?php
declare(strict_types=1);

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
        
        $clubs = $this->validationModel->getPendingClubsForBDE();
        
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
     * Le BDE valide validation_bde (premiere etape du workflow)
     * L'admin peut forcer la validation complete (validation_bde + validation_tuteur + validation_admin + validation_finale)
     * La validation finale ne passe a 1 QUE SI validation_bde = 1 ET validation_tuteur = 1 ET validation_admin = 1
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
    $user_permission = (int)($_SESSION['permission'] ?? 0);
    $is_admin = ($user_permission >= 4); 

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['validate_club'])) {
        $club_id = $_POST['club_id'] ?? null;
        $action = $_POST['action'] ?? null;
        $remarques = trim($_POST['remarques'] ?? '');

        if (!$club_id || !$action) {
            $error_msg = "Données manquantes.";
        } else {
            // --- CAS 1 : FORCE APPROVE (Admin uniquement - Validation immédiate compl\u00e8te) ---
            if ($action === 'force_approve' && $is_admin) {
                $stmt = $this->db->prepare("UPDATE fiche_club SET validation_bde = 1, validation_admin = 1, validation_tuteur = 1, validation_finale = 1, motif_refus = NULL WHERE club_id = ?");
                if ($stmt->execute([$club_id])) {
                    $success_msg = "Club valid IMM\u00c9DIATEMENT (Validation forcée : BDE + Tuteur + Admin).";
                } else {
                    $error_msg = "Erreur lors de la validation forcée.";
                }

            // --- CAS 2 : APPROVE (Selon le rôle de l'utilisateur) ---
            } elseif ($action === 'approve') {
                if ($is_admin) {
                    // L'admin valide validation_admin
                    $stmt = $this->db->prepare("UPDATE fiche_club SET validation_admin = 1 WHERE club_id = ?");
                } else {
                    // Le BDE (permission 3) valide validation_bde
                    $stmt = $this->db->prepare("UPDATE fiche_club SET validation_bde = 1 WHERE club_id = ?");
                }
                
                if ($stmt->execute([$club_id])) {
                    // Vérifier si les 3 signatures sont complètes
                    $check = $this->db->prepare("SELECT validation_bde, validation_tuteur, validation_admin FROM fiche_club WHERE club_id = ?");
                    $check->execute([$club_id]);
                    $club = $check->fetch(PDO::FETCH_ASSOC);

                    if ($club && $club['validation_bde'] == 1 && $club['validation_tuteur'] == 1 && $club['validation_admin'] == 1) {
                        // BDE OK + Tuteur OK + Admin OK = Validation finale
                        $this->db->prepare("UPDATE fiche_club SET validation_finale = 1, motif_refus = NULL WHERE club_id = ?")->execute([$club_id]);
                        $success_msg = "Club approuvé définitivement. Toutes les signatures sont complètes, le club est maintenant actif.";
                    } else {
                        if ($is_admin) {
                            $success_msg = "Approbation admin enregistrée. En attente des autres signatures requises.";
                        } else {
                            $success_msg = "Approbation BDE enregistrée. Le club est maintenant visible par les tuteurs et administrateurs pour validation.";
                        }
                    }
                } else {
                    $error_msg = "Erreur lors de la validation.";
                }

            // --- CAS 3 : REJET (Réinitialise tout) ---
            } elseif ($action === 'reject') {
                $stmt = $this->db->prepare("UPDATE fiche_club SET validation_bde = 0, validation_admin = 0, validation_tuteur = 0, validation_finale = -1, motif_refus = ? WHERE club_id = ?");
                if ($stmt->execute([$remarques, $club_id])) {
                    $success_msg = "Club rejeté. Toutes les validations ont été annulées.";
                } else {
                    $error_msg = "Erreur lors du rejet.";
                }
            }
        }
    }
    
    // Récupération des listes selon le rôle :
    // - BDE (permission 3) : voit les clubs en attente de validation_bde
    // - Admin (permission >= 4) : ne voit que les clubs déjà validés par le BDE (sauf force_approve ci-dessus)
    if ($is_admin) {
        $clubs = $this->validationModel->getPendingClubs();
    } else {
        $clubs = $this->validationModel->getPendingClubsForBDE();
    }
    $rejected_clubs = $this->validationModel->getRejectedClubs();

    return [
        'clubs' => $clubs,
        'rejected_clubs' => $rejected_clubs,
        'is_admin' => $is_admin,
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
    // Vérifie que l'utilisateur a au moins le niveau BDE (3)
    checkPermission(3);
    
    $error_msg = '';
    $success_msg = '';
    $user_permission = (int)($_SESSION['permission'] ?? 0);
    $is_admin = ($user_permission >= 4); 

        // Traitement de la validation ou du rejet d'un evenement
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['validate_event'])) {
            $event_id = $_POST['event_id'] ?? null;
            $action = $_POST['action'] ?? null;
            $remarques = trim($_POST['remarques'] ?? $_POST['motif'] ?? '');

        if (!$event_id || !$action) {
            $error_msg = "Données manquantes.";
        } else {
            // --- CAS 1 : FORCE APPROVE (Administration uniquement) ---
            if ($action === 'force_approve' && $is_admin) {
                $stmt = $this->db->prepare("UPDATE fiche_event SET validation_admin = 1, validation_bde = 1, validation_tuteur = 1, validation_finale = 1, motif_refus = NULL WHERE event_id = ?");
                if ($stmt->execute([$event_id])) {
                    $success_msg = "Événement validé IMMÉDIATEMENT par l'administration (Validation forcée).";
                } else {
                    $error_msg = "Erreur lors de la validation forcée.";
                }

            // --- CAS 2 : APPROVE (Validation selon le rôle + Vérification croisée) ---
            } elseif ($action === 'approve') {
                $stmt = null;
                
                // On détermine quelle colonne impacter selon le rôle de celui qui clique
                if ($user_permission == 3) {
                    $stmt = $this->db->prepare("UPDATE fiche_event SET validation_bde = 1 WHERE event_id = ?");
                } elseif ($user_permission >= 4) {
                    $stmt = $this->db->prepare("UPDATE fiche_event SET validation_admin = 1 WHERE event_id = ?");
                }

                if ($stmt && $stmt->execute([$event_id])) {
                    // 2. On vérifie les conditions pour la validation finale
                    $check = $this->db->prepare("SELECT validation_tuteur, validation_admin, validation_bde FROM fiche_event WHERE event_id = ?");
                    $check->execute([$event_id]);
                    $event = $check->fetch(PDO::FETCH_ASSOC);

                    // RÈGLE : Validation finale si (BDE a validé) ET (Tuteur OU Admin a validé)
                    $bde_ok = ($event['validation_bde'] == 1);
                    $admin_or_tutor_ok = ($event['validation_admin'] == 1 || $event['validation_tuteur'] == 1);

                    if ($event && $bde_ok && $admin_or_tutor_ok) {
                        $this->db->prepare("UPDATE fiche_event SET validation_finale = 1, motif_refus = NULL WHERE event_id = ?")->execute([$event_id]);
                        $success_msg = "Événement validé définitivement (Circuit de signatures complet).";
                    } else {
                        $success_msg = "Votre approbation a été enregistrée. En attente des autres signatures requises.";
                    }
                } else {
                    $error_msg = "Erreur lors de la validation ou permission insuffisante.";
                }

            // --- CAS 3 : REJET (Validation finale = 0) ---
            } elseif ($action === 'reject') {
                // On remet tout à 0 pour bloquer l'événement
                $stmt = $this->db->prepare("UPDATE fiche_event SET validation_bde = 0, validation_admin = 0, validation_tuteur = 0, validation_finale = 0, motif_refus = ? WHERE event_id = ?");
                if ($stmt->execute([$remarques, $event_id])) {
                    $success_msg = "Événement rejeté.";
                } else {
                    $error_msg = "Erreur lors du rejet.";
                }
            }
        }
    }

    // Traitement de la suppression
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_event'])) {
        $event_id = $_POST['event_id'] ?? null;
        if ($event_id && $this->validationModel->deleteRejectedEvent($event_id)) {
            $success_msg = "Événement supprimé avec succès.";
        }
    }
    
    // Récupération sécurisée des listes (on force un tableau vide si null)
    $events = $this->validationModel->getPendingEvents() ?: [];
    $rejected_events = $this->validationModel->getRejectedEvents() ?: [];

    return [
        'events' => $events,
        'rejected_events' => $rejected_events,
        'is_admin' => $is_admin,
        'is_bde' => ($user_permission == 3),
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
    validateSession();
    
    $user_id = $_SESSION['id'];
    $user_permission = (int)($_SESSION['permission'] ?? 0);
    $is_admin = ($user_permission >= 4); 
    $is_bde = ($user_permission == 3);   
    $is_tutor = ($user_permission == 2); 
    
    if (!in_array($user_permission, [2, 3, 4, 5])) {
        ErrorHandler::renderHttpError(403, "Accès refusé.");
    }

    $error_msg = '';
    $success_msg = '';

    // --- 1. TRAITEMENT DES ACTIONS (POST) ---
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        
        // A. VALIDATION CLUB (ADMIN OU TUTEUR)
        if (isset($_POST['validate_club_admin'])) {
            $club_id = $_POST['club_id'] ?? null;
            $action = $_POST['action'] ?? null;
            $motif = trim($_POST['motif'] ?? '');

            if ($club_id && $action) {
                if ($is_admin) {
                    if ($action === 'force_approve') {
                        $this->db->prepare("UPDATE fiche_club SET validation_bde = 1, validation_admin = 1, validation_tuteur = 1, validation_finale = 1, motif_refus = NULL WHERE club_id = ?")->execute([$club_id]);
                        $success_msg = "Club validé IMMÉDIATEMENT (Validation forcée : BDE + Tuteur + Admin).";
                    } elseif ($action === 'approve') {
                        $this->db->prepare("UPDATE fiche_club SET validation_admin = 1 WHERE club_id = ?")->execute([$club_id]);
                        // Vérification croisée : BDE + Tuteur + Admin = validation finale
                        $check = $this->db->prepare("SELECT validation_bde, validation_tuteur FROM fiche_club WHERE club_id = ?");
                        $check->execute([$club_id]);
                        $status = $check->fetch(PDO::FETCH_ASSOC);
                        if ($status && $status['validation_bde'] == 1 && $status['validation_tuteur'] == 1) {
                            $this->db->prepare("UPDATE fiche_club SET validation_finale = 1, motif_refus = NULL WHERE club_id = ?")->execute([$club_id]);
                            $success_msg = "Club approuvé définitivement (BDE + Tuteur + Admin validés).";
                        } else {
                            $success_msg = "Approbation admin enregistrée. En attente des autres signatures requises.";
                        }
                    } elseif ($action === 'reject') {
                        $this->db->prepare("UPDATE fiche_club SET validation_bde = 0, validation_admin = 0, validation_tuteur = 0, validation_finale = -1, motif_refus = ? WHERE club_id = ?")->execute([$motif, $club_id]);
                        $success_msg = "Club rejeté par l'administrateur.";
                    }
                } elseif ($is_tutor) {
                    if ($action === 'approve') {
                        $this->db->prepare("UPDATE fiche_club SET validation_tuteur = 1 WHERE club_id = ? AND tuteur = ?")
                                 ->execute([$club_id, $user_id]);
                        // Vérification croisée : BDE + Tuteur + Admin = validation finale
                        $check = $this->db->prepare("SELECT validation_bde, validation_admin FROM fiche_club WHERE club_id = ?");
                        $check->execute([$club_id]);
                        $status = $check->fetch(PDO::FETCH_ASSOC);
                        if ($status && $status['validation_bde'] == 1 && $status['validation_admin'] == 1) {
                            $this->db->prepare("UPDATE fiche_club SET validation_finale = 1, motif_refus = NULL WHERE club_id = ?")->execute([$club_id]);
                            $success_msg = "Club approuvé définitivement (BDE + Tuteur + Admin validés).";
                        } else {
                            $success_msg = "Approbation tuteur enregistrée. En attente des autres signatures requises.";
                        }
                    } elseif ($action === 'reject') {
                        $this->db->prepare("UPDATE fiche_club SET validation_tuteur = 0, validation_finale = -1, motif_refus = ? WHERE club_id = ? AND tuteur = ?")
                                 ->execute([$motif, $club_id, $user_id]);
                        $success_msg = "Club rejeté par le tuteur.";
                    }
                }
            }
        }

        // B. VALIDATION ÉVÉNEMENT (ADMIN, BDE OU TUTEUR)
        $event_id = $_POST['event_id'] ?? null;
        $action = $_POST['action'] ?? null;
        $motif = trim($_POST['motif'] ?? '');

        if ($event_id && $action) {
            if (isset($_POST['validate_event_admin']) && $is_admin) {
                if ($action === 'force_approve') {
                    $this->db->prepare("UPDATE fiche_event SET validation_admin = 1, validation_bde = 1, validation_tuteur = 1, validation_finale = 1, motif_refus = NULL WHERE event_id = ?")->execute([$event_id]);
                    $success_msg = "Événement validé IMMÉDIATEMENT (Validation forcée).";
                } else {
                    $this->db->prepare("UPDATE fiche_event SET validation_admin = 1 WHERE event_id = ?")->execute([$event_id]);
                }
                $success_msg = "Action administrateur effectuée.";
            } 
            elseif (isset($_POST['validate_event_bde']) && $is_bde) {
                $val = ($action === 'approve') ? 1 : 0;
                $this->db->prepare("UPDATE fiche_event SET validation_bde = ? WHERE event_id = ?")->execute([$val, $event_id]);
                $success_msg = "Avis BDE enregistré.";
            } 
            elseif (isset($_POST['validate_event_tutor']) && $is_tutor) {
                $val = ($action === 'approve') ? 1 : 0;
                $this->db->prepare("UPDATE fiche_event fe INNER JOIN fiche_club fc ON fe.club_orga = fc.club_id SET fe.validation_tuteur = ? WHERE fe.event_id = ? AND fc.tuteur = ?")
                         ->execute([$val, $event_id, $user_id]);
                $success_msg = "Avis tuteur enregistré.";
            }

            // Vérification automatique pour validation_finale après chaque action
            $check = $this->db->prepare("SELECT validation_admin, validation_tuteur, validation_bde FROM fiche_event WHERE event_id = ?");
            $check->execute([$event_id]);
            $st = $check->fetch();
            if ($st && $st['validation_admin'] == 1 && $st['validation_tuteur'] == 1 && $st['validation_bde'] == 1) {
                $this->db->prepare("UPDATE fiche_event SET validation_finale = 1 WHERE event_id = ?")->execute([$event_id]);
            }
        }
    }

    // --- 2. RÉCUPÉRATION DES DONNÉES POUR LA VUE ---

    // Clubs dont l'utilisateur est tuteur
    $stmt = $this->db->prepare("SELECT * FROM fiche_club WHERE tuteur = ?");
    $stmt->execute([$user_id]);
    $tutored_clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Clubs en attente
        // Flux strict : les tuteurs et admins ne voient que les clubs où validation_bde = 1
        if ($is_admin) {
            $pending_clubs = $this->db->query("SELECT fc.*, u.nom as tuteur_nom FROM fiche_club fc LEFT JOIN users u ON fc.tuteur = u.id WHERE (fc.validation_finale IS NULL OR fc.validation_finale = 0) AND fc.validation_bde = 1")->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($is_tutor) {
            $stmt = $this->db->prepare("SELECT * FROM fiche_club WHERE tuteur = ? AND validation_tuteur IS NULL AND validation_bde = 1");
            $stmt->execute([$user_id]);
            $pending_clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else { $pending_clubs = []; }

        // Événements en attente
        if ($is_admin) {
            $pending_events = $this->db->query("SELECT fe.*, fc.nom_club FROM fiche_event fe INNER JOIN fiche_club fc ON fe.club_orga = fc.club_id WHERE fe.validation_finale IS NULL")->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($is_bde) {
            $pending_events = $this->db->query("SELECT fe.*, fc.nom_club FROM fiche_event fe INNER JOIN fiche_club fc ON fe.club_orga = fc.club_id WHERE fe.validation_bde IS NULL")->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($is_tutor) {
            $stmt = $this->db->prepare("SELECT fe.*, fc.nom_club FROM fiche_event fe INNER JOIN fiche_club fc ON fe.club_orga = fc.club_id WHERE fc.tuteur = ? AND fe.validation_tuteur IS NULL ");
            $stmt->execute([$user_id]);
            $pending_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else { $pending_events = []; }

            return [
                'is_admin' => $is_admin,
                'is_bde' => $is_bde,
                'is_tutor' => $is_tutor,
                'tutored_clubs' => $tutored_clubs,
                'pending_clubs' => $pending_clubs,
                'pending_events' => $pending_events,
                'error_msg' => $error_msg,
                'success_msg' => $success_msg
            ];
        }
    }