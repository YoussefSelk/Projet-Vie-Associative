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
     * Retourne les dirigeants (president/secretaire) valides d'un club.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getClubLeadershipRecipients(int $clubId): array {
        $stmt = $this->db->prepare("\n            SELECT DISTINCT u.id, u.nom, u.prenom, u.mail, mc.fonction\n            FROM membres_club mc\n            INNER JOIN users u ON u.id = mc.membre_id\n            WHERE mc.club_id = ?\n              AND mc.valide = 1\n              AND mc.fonction IN ('Président', 'President', 'Secrétaire', 'Secretaire')\n              AND u.mail IS NOT NULL\n              AND u.mail <> ''\n        ");
        $stmt->execute([$clubId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Notifie president/secretaire du statut d'une demande.
     */
    private function notifyLeadershipRequestStatus(
        int $clubId,
        string $clubName,
        string $requestType,
        string $itemName,
        string $statusLabel,
        ?string $reason = null
    ): void {
        $recipients = $this->getClubLeadershipRecipients($clubId);
        if (empty($recipients)) {
            return;
        }

        $actionUrl = null;
        if (defined('BASE_URL') && is_string(BASE_URL) && BASE_URL !== '') {
            $actionUrl = rtrim(BASE_URL, '/') . '/?page=' . (($requestType === 'club') ? 'my-clubs' : 'my-events');
        }

        foreach ($recipients as $recipient) {
            $email = trim((string)($recipient['mail'] ?? ''));
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $fullName = trim((string)($recipient['prenom'] ?? '') . ' ' . (string)($recipient['nom'] ?? ''));
            if ($fullName === '') {
                $fullName = 'Membre du bureau';
            }

            $message = buildLeadershipRequestStatusEmail(
                $fullName,
                $clubName,
                $requestType,
                $itemName,
                $statusLabel,
                $reason,
                $actionUrl
            );

            $subject = '[' . strtoupper($requestType) . '] Statut demande: ' . $statusLabel;
            sendEmail($email, $subject, $message);
        }
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
    
    $error_msg = (string)($_SESSION['flash_error'] ?? '');
    $success_msg = (string)($_SESSION['flash_success'] ?? '');
    unset($_SESSION['flash_error'], $_SESSION['flash_success']);
    $user_permission = (int)($_SESSION['permission'] ?? 0);
    $is_admin = ($user_permission >= 4); 

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['validate_club'])) {
        $club_id = $_POST['club_id'] ?? null;
        $action = $_POST['action'] ?? null;
        $remarques = trim($_POST['remarques'] ?? '');

        if (!$club_id || !$action) {
            $error_msg = "Données manquantes.";
        } else {
            $clubMetaStmt = $this->db->prepare("SELECT club_id, nom_club FROM fiche_club WHERE club_id = ?");
            $clubMetaStmt->execute([$club_id]);
            $clubMeta = $clubMetaStmt->fetch(PDO::FETCH_ASSOC) ?: [];
            $clubName = (string)($clubMeta['nom_club'] ?? ('Club #' . (int)$club_id));

            // --- CAS 1 : FORCE APPROVE (Admin uniquement - Validation immédiate compl\u00e8te) ---
            if ($action === 'force_approve' && $is_admin) {
                $stmt = $this->db->prepare("UPDATE fiche_club SET validation_bde = 1, validation_admin = 1, validation_tuteur = 1, validation_finale = 1, motif_refus = NULL WHERE club_id = ?");
                if ($stmt->execute([$club_id])) {
                    $success_msg = "Club valid IMM\u00c9DIATEMENT (Validation forcée : BDE + Tuteur + Admin).";
                    $this->notifyLeadershipRequestStatus((int)$club_id, $clubName, 'club', $clubName, 'validée');
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
                        $this->notifyLeadershipRequestStatus((int)$club_id, $clubName, 'club', $clubName, 'validée');
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
                    $this->notifyLeadershipRequestStatus((int)$club_id, $clubName, 'club', $clubName, 'rejetée', $remarques);
                } else {
                    $error_msg = "Erreur lors du rejet.";
                }
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $success_msg !== '') {
        $_SESSION['flash_success'] = $success_msg;
        redirect($_SERVER['REQUEST_URI']);
    }
    
    // Récupération des listes selon le rôle :
    // - BDE (permission 3) : voit les clubs en attente de validation_bde
    // - Admin (permission >= 4) : voit les clubs validés BDE en attente admin
    if ($is_admin) {
        $pending_clubs = $this->validationModel->getPendingClubs();
    } else {
        $pending_clubs = $this->validationModel->getPendingClubsForBDE();
    }
    $rejected_clubs = $this->validationModel->getRejectedClubs();
    $validated_clubs = $this->validationModel->getValidatedClubs();

    // Envoyer dès le départ toutes les fiches nécessaires aux filtres front.
    $clubs = array_merge(
        $pending_clubs ?: [],
        $validated_clubs ?: [],
        $rejected_clubs ?: []
    );

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
    
    $error_msg = (string)($_SESSION['flash_error'] ?? '');
    $success_msg = (string)($_SESSION['flash_success'] ?? '');
    unset($_SESSION['flash_error'], $_SESSION['flash_success']);
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
            $eventMetaStmt = $this->db->prepare("SELECT fe.event_id, fe.titre, fe.club_orga, fc.nom_club FROM fiche_event fe LEFT JOIN fiche_club fc ON fc.club_id = fe.club_orga WHERE fe.event_id = ?");
            $eventMetaStmt->execute([$event_id]);
            $eventMeta = $eventMetaStmt->fetch(PDO::FETCH_ASSOC) ?: [];
            $eventTitle = (string)($eventMeta['titre'] ?? ('Evenement #' . (int)$event_id));
            $eventClubId = (int)($eventMeta['club_orga'] ?? 0);
            $eventClubName = (string)($eventMeta['nom_club'] ?? 'Club inconnu');

            // --- CAS 1 : FORCE APPROVE (Administration uniquement) ---
            if ($action === 'force_approve' && $is_admin) {
                $stmt = $this->db->prepare("UPDATE fiche_event SET validation_admin = 1, validation_bde = 1, validation_tuteur = 1, validation_finale = 1, motif_refus = NULL WHERE event_id = ?");
                if ($stmt->execute([$event_id])) {
                    $success_msg = "Événement validé IMMÉDIATEMENT par l'administration (Validation forcée).";
                    if ($eventClubId > 0) {
                        $this->notifyLeadershipRequestStatus($eventClubId, $eventClubName, 'evenement', $eventTitle, 'validée');
                    }
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

                    // RÈGLE : Validation finale si (BDE a validé) ET Tuteur ET Admin ont validé)
                    $bde_ok = ($event['validation_bde'] == 1);
                    $admin_ok = $event['validation_admin'] == 1;
                    $tuteur_ok = $event['validation_tuteur'] == 1;

                    if ($event && $bde_ok && $admin_ok && $tuteur_ok) {
                        $this->db->prepare("UPDATE fiche_event SET validation_finale = 1, motif_refus = NULL WHERE event_id = ?")->execute([$event_id]);
                        $success_msg = "Événement validé définitivement (Circuit de signatures complet).";
                        if ($eventClubId > 0) {
                            $this->notifyLeadershipRequestStatus($eventClubId, $eventClubName, 'evenement', $eventTitle, 'validée');
                        }
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
                    if ($eventClubId > 0) {
                        $this->notifyLeadershipRequestStatus($eventClubId, $eventClubName, 'evenement', $eventTitle, 'rejetée', $remarques);
                    }
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

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $success_msg !== '') {
        $_SESSION['flash_success'] = $success_msg;
        redirect($_SERVER['REQUEST_URI']);
    }
    
    // Récupération sécurisée des listes (on force un tableau vide si null)
    if ($is_admin) {
        $pendingStmt = $this->db->prepare("
            SELECT fe.*, fc.nom_club,
                   u.prenom AS responsable_prenom, u.nom AS responsable_nom, u.mail AS responsable_mail, u.promo AS responsable_promo
            FROM fiche_event fe
            LEFT JOIN fiche_club fc ON fe.club_orga = fc.club_id
            LEFT JOIN users u ON fe.id_responsable = u.id
            WHERE fe.validation_bde = 1
              AND (fe.validation_admin IS NULL OR fe.validation_admin = 0)
              AND (fe.validation_finale IS NULL OR (fe.validation_finale = 0 AND (fe.motif_refus IS NULL OR fe.motif_refus = '')))
            ORDER BY fe.date_depot DESC
        ");
        $pendingStmt->execute();
        $pending_events = $pendingStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $validatedStmt = $this->db->prepare("
            SELECT fe.*, fc.nom_club,
                   u.prenom AS responsable_prenom, u.nom AS responsable_nom, u.mail AS responsable_mail, u.promo AS responsable_promo
            FROM fiche_event fe
            LEFT JOIN fiche_club fc ON fe.club_orga = fc.club_id
            LEFT JOIN users u ON fe.id_responsable = u.id
            WHERE fe.validation_bde = 1
              AND fe.validation_finale = 1
            ORDER BY fe.date_depot DESC
        ");
        $validatedStmt->execute();
        $validated_events = $validatedStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $rejectedStmt = $this->db->prepare("
            SELECT fe.*, fc.nom_club,
                   u.prenom AS responsable_prenom, u.nom AS responsable_nom, u.mail AS responsable_mail, u.promo AS responsable_promo
            FROM fiche_event fe
            LEFT JOIN fiche_club fc ON fe.club_orga = fc.club_id
            LEFT JOIN users u ON fe.id_responsable = u.id
            WHERE fe.validation_bde = 1
              AND fe.validation_finale = 0
              AND fe.motif_refus IS NOT NULL AND fe.motif_refus != ''
            ORDER BY fe.date_depot DESC
        ");
        $rejectedStmt->execute();
        $rejected_events = $rejectedStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } else {
        $pendingStmt = $this->db->prepare("
            SELECT fe.*, fc.nom_club,
                   u.prenom AS responsable_prenom, u.nom AS responsable_nom, u.mail AS responsable_mail, u.promo AS responsable_promo
            FROM fiche_event fe
            LEFT JOIN fiche_club fc ON fe.club_orga = fc.club_id
            LEFT JOIN users u ON fe.id_responsable = u.id
            WHERE (fe.validation_bde IS NULL OR fe.validation_bde = 0)
              AND (fe.validation_finale IS NULL OR (fe.validation_finale = 0 AND (fe.motif_refus IS NULL OR fe.motif_refus = '')))
            ORDER BY fe.date_depot DESC
        ");
        $pendingStmt->execute();
        $pending_events = $pendingStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $validatedStmt = $this->db->prepare("
            SELECT fe.*, fc.nom_club,
                   u.prenom AS responsable_prenom, u.nom AS responsable_nom, u.mail AS responsable_mail, u.promo AS responsable_promo
            FROM fiche_event fe
            LEFT JOIN fiche_club fc ON fe.club_orga = fc.club_id
            LEFT JOIN users u ON fe.id_responsable = u.id
            WHERE fe.validation_bde = 1
              AND (fe.validation_finale IS NULL OR fe.validation_finale = 1 OR (fe.validation_finale = 0 AND (fe.motif_refus IS NULL OR fe.motif_refus = '')))
            ORDER BY fe.date_depot DESC
        ");
        $validatedStmt->execute();
        $validated_events = $validatedStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $rejectedStmt = $this->db->prepare("
            SELECT fe.*, fc.nom_club,
                   u.prenom AS responsable_prenom, u.nom AS responsable_nom, u.mail AS responsable_mail, u.promo AS responsable_promo
            FROM fiche_event fe
            LEFT JOIN fiche_club fc ON fe.club_orga = fc.club_id
            LEFT JOIN users u ON fe.id_responsable = u.id
            WHERE fe.validation_finale = 0
              AND fe.motif_refus IS NOT NULL AND fe.motif_refus != ''
            ORDER BY fe.date_depot DESC
        ");
        $rejectedStmt->execute();
        $rejected_events = $rejectedStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    $events = array_merge($pending_events, $validated_events);

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
    $is_tutor_scope = !$is_admin;
    // En mode tutoring, tout non-admin agit avec le scope tuteur (ses clubs uniquement)
    $is_tutor = $is_tutor_scope;
    
    if (!in_array($user_permission, [2, 3, 4, 5])) {
        ErrorHandler::renderHttpError(403, "Accès refusé.");
    }

    $error_msg = (string)($_SESSION['flash_error'] ?? '');
    $success_msg = (string)($_SESSION['flash_success'] ?? '');
    unset($_SESSION['flash_error'], $_SESSION['flash_success']);

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
                } elseif ($action === 'reject') {
                    $motifRefus = $motif !== '' ? $motif : 'Refusé par l\'administration.';
                    $this->db->prepare("UPDATE fiche_event SET validation_admin = 0, validation_finale = 0, motif_refus = ? WHERE event_id = ?")->execute([$motifRefus, $event_id]);
                    $success_msg = "Refus administrateur enregistré.";
                } else {
                    $this->db->prepare("UPDATE fiche_event SET validation_admin = 1 WHERE event_id = ?")->execute([$event_id]);
                    $success_msg = "Avis administrateur enregistré.";
                }
            } 
            elseif (isset($_POST['validate_event_bde']) && $is_bde) {
                if ($action === 'reject') {
                    $motifRefus = $motif !== '' ? $motif : 'Refusé par le BDE.';
                    $this->db->prepare("UPDATE fiche_event SET validation_bde = 0, validation_finale = 0, motif_refus = ? WHERE event_id = ?")->execute([$motifRefus, $event_id]);
                    $success_msg = "Refus BDE enregistré.";
                } else {
                    $this->db->prepare("UPDATE fiche_event SET validation_bde = 1 WHERE event_id = ?")->execute([$event_id]);
                    $success_msg = "Avis BDE enregistré.";
                }
            } 
            elseif (isset($_POST['validate_event_tutor']) && $is_tutor) {
                if ($action === 'reject') {
                    $motifRefus = $motif !== '' ? $motif : 'Refusé par le tuteur.';
                    $this->db->prepare("UPDATE fiche_event fe INNER JOIN fiche_club fc ON fe.club_orga = fc.club_id SET fe.validation_tuteur = 0, fe.validation_finale = 0, fe.motif_refus = ? WHERE fe.event_id = ? AND fc.tuteur = ?")
                             ->execute([$motifRefus, $event_id, $user_id]);
                    $success_msg = "Refus tuteur enregistré.";
                } else {
                    $this->db->prepare("UPDATE fiche_event fe INNER JOIN fiche_club fc ON fe.club_orga = fc.club_id SET fe.validation_tuteur = 1 WHERE fe.event_id = ? AND fc.tuteur = ?")
                             ->execute([$event_id, $user_id]);
                    $success_msg = "Avis tuteur enregistré.";
                }
            }

            // Vérification automatique pour validation_finale après chaque action
            $check = $this->db->prepare("SELECT validation_admin, validation_tuteur, validation_bde FROM fiche_event WHERE event_id = ?");
            $check->execute([$event_id]);
            $st = $check->fetch();
            if ($st && $st['validation_admin'] == 1 && $st['validation_tuteur'] == 1 && $st['validation_bde'] == 1) {
                $this->db->prepare("UPDATE fiche_event SET validation_finale = 1, motif_refus = NULL WHERE event_id = ?")->execute([$event_id]);
            } elseif ($st && ((string)$st['validation_admin'] === '0' || (string)$st['validation_tuteur'] === '0' || (string)$st['validation_bde'] === '0')) {
                $this->db->prepare("UPDATE fiche_event SET validation_finale = 0 WHERE event_id = ?")->execute([$event_id]);
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $success_msg !== '') {
        $_SESSION['flash_success'] = $success_msg;
        redirect($_SERVER['REQUEST_URI']);
    }

    // --- 2. RÉCUPÉRATION DES DONNÉES POUR LA VUE ---

    // Clubs dont l'utilisateur est tuteur
    $stmt = $this->db->prepare("SELECT * FROM fiche_club WHERE tuteur = ?");
    $stmt->execute([$user_id]);
    $tutored_clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Clubs en attente
        // Flux strict : les tuteurs et admins ne voient que les clubs où validation_bde = 1
        if ($is_admin) {
            $pending_clubs = $this->db->query("
                SELECT fc.*, u.nom as tuteur_nom, u.prenom as tuteur_prenom
                FROM fiche_club fc
                LEFT JOIN users u ON fc.tuteur = u.id
                WHERE fc.validation_bde = 1
                   OR fc.validation_finale IN (1, -1, 0)
            ")->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($is_tutor_scope) {
            // Tutor scope: ne charger que les fiches des clubs tutores par l'utilisateur connecte
            $stmt = $this->db->prepare("
                SELECT fc.*, u.nom as tuteur_nom, u.prenom as tuteur_prenom
                FROM fiche_club fc
                LEFT JOIN users u ON fc.tuteur = u.id
                WHERE fc.tuteur = ?
            ");
            $stmt->execute([$user_id]);
            $pending_clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else { $pending_clubs = []; }

        // Événements en attente
        if ($is_admin) {
            $pending_events = $this->db->query("
                SELECT fe.*, fc.nom_club, fc.logo_club,
                    u.prenom AS responsable_prenom,
                    u.nom    AS responsable_nom,
                    u.mail   AS responsable_mail,
                    u.promo  AS responsable_promo
                FROM fiche_event fe
                INNER JOIN fiche_club fc ON fe.club_orga = fc.club_id
                LEFT JOIN users u ON fe.id_responsable = u.id
                WHERE fe.validation_finale IS NULL
            ")->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($is_tutor_scope) {
            $stmt = $this->db->prepare("
                SELECT fe.*, fc.nom_club, fc.logo_club,
                    u.prenom AS responsable_prenom,
                    u.nom    AS responsable_nom,
                    u.mail   AS responsable_mail,
                    u.promo  AS responsable_promo
                FROM fiche_event fe
                INNER JOIN fiche_club fc ON fe.club_orga = fc.club_id
                LEFT JOIN users u ON fe.id_responsable = u.id
                WHERE fc.tuteur = ?
            ");
            $stmt->execute([$user_id]);
            $pending_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }else { $pending_events = []; }

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
