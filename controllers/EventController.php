<?php
/**
 * =============================================================================
 * CONTRÔLEUR DES ÉVÉNEMENTS
 * =============================================================================
 * 
 * Gère toutes les opérations liées aux événements :
 * - Liste et affichage des événements
 * - Création et modification d'événements
 * - Événements de l'utilisateur
 * - Dépôt de rapports post-événement
 * 
 * Note : La fonction analytics() a été déplacée vers AdminController
 * 
 * @author Équipe de développement EILCO
 * @version 2.0
 */

class EventController {
    /** @var Event Modèle des événements */
    private $eventModel;
    
    /** @var PDO Instance de connexion à la base de données */
    private $db;

    /**
     * Constructeur
     * @param PDO $database Instance de connexion PDO
     */
    public function __construct($database) {
        $this->db = $database;
        $this->eventModel = new Event($database);
    }

    /**
     * Liste tous les événements validés
     * Route publique accessible à tous
     * 
     * @return array Liste des événements
     */
    public function listEvents() {
        $events = $this->eventModel->getAllValidatedEvents();
        
        return [
            'events' => $events
        ];
    }

    /**
     * Affiche les détails d'un événement
     * Route publique accessible à tous
     * 
     * @return array Données de l'événement
     */
    public function viewEvent() {
        $event_id = $_GET['id'] ?? null;
        if (!$event_id) { redirect('index.php'); }

        // 1. On garde votre fonction habituelle (pas de casse)
        $event = $this->eventModel->getEventById($event_id);
        
        if (!$event) { redirect('index.php'); }

        // 2. On cherche qui est le tuteur du club organisateur de cet event
        // club_orga est bien dans $event
        $stmt = $this->db->prepare("SELECT tuteur FROM fiche_club WHERE club_id = ?");
        $stmt->execute([$event['club_orga']]);
        $clubInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        // 3. On ajoute l'ID du tuteur au tableau $event pour la vue
        $event['tuteur_id'] = $clubInfo['tuteur'] ?? 0;

        return [
            'event' => $event
        ];
    }

    /**
     * Création d'un nouvel événement
     * Nécessite permission 1, 3, 4 ou 5 (exclut permission 2)
     * 
     * Structure BD fiche_event: event_id, date_depot, validation_admin, validation_bde, 
     * validation_tuteur, validation_soutenance, titre, club_orga, campus, date_ev, 
     * horaire_debut, horaire_fin, lieu, id_responsable, description, financement_bde, 
     * montant, fiche_sanitaire, affiche, rapport_event, motif_refus, validation_finale
     * 
     * @return array Données pour la vue [error_msg, success_msg]
     */
    public function createEvent() {
        validateSession();
        $user_permission = (int)($_SESSION['permission'] ?? 0);
        if (!in_array($user_permission, [1, 3, 4, 5], true)) {
            ErrorHandler::renderHttpError(403, "Vous n'avez pas les permissions nécessaires pour créer un événement.");
        }
        
        $error_msg = '';
        $success_msg = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_event'])) {
            // Get form data - respect actual DB column names
            $nom_event = trim($_POST['nom_event'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $date_ev = trim($_POST['date_ev'] ?? '');
            $horaire_debut = trim($_POST['horaire_debut'] ?? '13:30');
            $horaire_fin = trim($_POST['horaire_fin'] ?? '17:30');
            $campus = trim($_POST['campus'] ?? '');
            $lieu = trim($_POST['lieu'] ?? '');
            $club_id = !empty($_POST['club_id']) ? intval($_POST['club_id']) : null;
            $financement_bde = isset($_POST['financement_bde']) ? 1 : 0;
            $montant = intval($_POST['montant'] ?? 0);

            // Validation
            if (!$nom_event || !$description || !$date_ev || !$campus || !$lieu) {
                $error_msg = "Tous les champs obligatoires doivent être remplis.";
            } elseif (!$club_id) {
                $error_msg = "Veuillez sélectionner un club organisateur.";
            } else {
                $data = [
                    'nom_event' => $nom_event,
                    'description' => $description,
                    'date_ev' => $date_ev,
                    'horaire_debut' => $horaire_debut,
                    'horaire_fin' => $horaire_fin,
                    'campus' => $campus,
                    'lieu' => $lieu,
                    'club_id' => $club_id,
                    'user_id' => $_SESSION['id'],
                    'financement_bde' => $financement_bde,
                    'montant' => $montant
                ];

                if ($this->eventModel->createEvent($data)) {
                    $success_msg = "Événement créé avec succès. Il est en attente de validation.";
                } else {
                    $error_msg = "Erreur lors de la création de l'événement.";
                }
            }
        }

        return [
            'error_msg' => $error_msg,
            'success_msg' => $success_msg
        ];
    }

    /**
     * Modification d'un événement existant
     * Nécessite permission >= 2 (membre de bureau)
     * 
     * @return array Données pour la vue [event, error_msg, success_msg]
     */
    public function updateEvent() {
        checkPermission(2);
        
        $event_id = $_GET['id'] ?? null;
        if (!$event_id) {
            redirect('index.php');
        }

        $event = $this->eventModel->getEventById($event_id);
        if (!$event) {
            redirect('index.php');
        }

        $error_msg = '';
        $success_msg = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_event'])) {
            $nom_event = trim($_POST['nom_event'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $date_ev = trim($_POST['date_ev'] ?? '');
            $horaire_debut = trim($_POST['horaire_debut'] ?? '');
            $horaire_fin = trim($_POST['horaire_fin'] ?? '');
            $campus = trim($_POST['campus'] ?? '');
            $lieu = trim($_POST['lieu'] ?? '');

            if (!$nom_event || !$description || !$date_ev || !$campus) {
                $error_msg = "Tous les champs obligatoires doivent être remplis.";
            } else {
                $data = [
                    'nom_event' => $nom_event,
                    'description' => $description,
                    'date_ev' => $date_ev,
                    'horaire_debut' => $horaire_debut,
                    'horaire_fin' => $horaire_fin,
                    'campus' => $campus,
                    'lieu' => $lieu
                ];

                if ($this->eventModel->updateEvent($event_id, $data)) {
                    $success_msg = "Événement mis à jour avec succès.";
                    $event = $this->eventModel->getEventById($event_id);
                } else {
                    $error_msg = "Erreur lors de la mise à jour.";
                }
            }
        }

        return [
            'event' => $event,
            'error_msg' => $error_msg,
            'success_msg' => $success_msg
        ];
    }

    /**
     * Liste les événements des clubs de l'utilisateur
     * Permet également la suppression des événements refusés
     * 
     * @return array Liste des événements de l'utilisateur
     */
    public function myEvents() {
        validateSession();
        
        $user_id = $_SESSION['id'];
        $error_msg = '';
        $success_msg = '';

        // Suppression d'un événement refusé
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_event'])) {
            $event_id = $_POST['event_id'] ?? null;
            
            if ($event_id) {
                // Vérifier que l'utilisateur est bien membre du club organisateur
                $stmt = $this->db->prepare("
                    SELECT fe.event_id, fe.validation_finale, fe.motif_refus
                    FROM fiche_event fe
                    INNER JOIN membres_club mc ON fe.club_orga = mc.club_id
                    WHERE fe.event_id = ? AND mc.membre_id = ? AND mc.valide = 1
                ");
                $stmt->execute([$event_id, $user_id]);
                $event = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($event) {
                    // Vérifier que l'événement est refusé (validation_finale = 0 avec motif_refus)
                    if ($event['validation_finale'] == 0 && !empty($event['motif_refus'])) {
                        if ($this->eventModel->deleteEvent($event_id)) {
                            $success_msg = "Événement supprimé avec succès.";
                        } else {
                            $error_msg = "Erreur lors de la suppression de l'événement.";
                        }
                    } else {
                        $error_msg = "Vous ne pouvez supprimer que les événements refusés.";
                    }
                } else {
                    $error_msg = "Vous n'avez pas la permission de supprimer cet événement.";
                }
            }
        }

        $events = $this->eventModel->getEventsByUser($user_id);
        
        return [
            'events' => $events,
            'error_msg' => $error_msg,
            'success_msg' => $success_msg
        ];
    }



    /**
     * Gère spécifiquement l'upload des images souvenirs
     * @param array $files Le tableau $_FILES['event_photos']
     * @param int $event_id
     * @return string|null Liste des images ou null
     */
    private function uploadEventImages($files, $event_id, $club_name, $event_title) {
        if (empty($files['name'][0])) return null;

        $dest_path = ROOT_PATH . '/uploads/images_events/';
        if (!is_dir($dest_path)) mkdir($dest_path, 0775, true);

        $uploaded_paths = [];
        $max_size = 512000; // 500 Ko
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];

        // Nettoyage des noms pour les fichiers
        $clean_club = preg_replace('/[^A-Za-z0-9]/', '', $club_name);
        $clean_event = preg_replace('/[^A-Za-z0-9]/', '', $event_title);

        for ($i = 0; $i < count($files['name']); $i++) {
            if ($i >= 5) break;

            $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
            
            if ($files['error'][$i] === UPLOAD_ERR_OK && $files['size'][$i] <= $max_size && in_array($ext, $allowed_exts)) {
                
                // Format : NomClub_TitreEvent_timestamp_index.ext
                $new_name = $clean_club . '_' . $clean_event . '_' . time() . '_' . $i . '.' . $ext;
                
                if (move_uploaded_file($files['tmp_name'][$i], $dest_path . $new_name)) {
                    $uploaded_paths[] = '../uploads/images_events/' . $new_name;
                }
            }
        }
        return !empty($uploaded_paths) ? implode(',', $uploaded_paths) : null;
}



    /**
     * Dépôt de rapport post-événement
     * Permet aux membres de club de déposer un rapport après un événement
     * 
     * Fichiers acceptés : PDF uniquement
     * Le rapport est stocké dans la colonne rapport_event de fiche_event (VARCHAR 255 = chemin du fichier)
     * 
     * Conditions pour déposer un rapport:
     * - Être membre VALIDE d'un club (mc.valide = 1)
     * - L'événement doit être validé (validation_finale = 1)
     * - L'événement ne doit pas déjà avoir de rapport
     * 
     * @return array Données pour la vue [events, error_msg, success_msg]
     */
    public function eventReport() {
        validateSession();
        
        $error_msg = '';
        $success_msg = '';
        
        // Récupérer les événements éligibles
        $stmt = $this->db->prepare("
            SELECT fe.*, fc.nom_club 
            FROM fiche_event fe
            INNER JOIN membres_club mc ON fe.club_orga = mc.club_id
            INNER JOIN fiche_club fc ON fe.club_orga = fc.club_id
            WHERE mc.membre_id = ? 
            AND mc.valide = 1
            AND fc.validation_finale = 1
            AND fe.validation_finale = 1
            AND (fe.rapport_event IS NULL OR fe.rapport_event = '')
            ORDER BY fe.date_ev DESC
        ");
        $stmt->execute([$_SESSION['id']]);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_report'])) {
            $event_id = $_POST['event_id'] ?? null;
            
            if (!$event_id) {
                $error_msg = "Veuillez sélectionner un événement.";
            } elseif (!isset($_FILES['rapport_file']) || $_FILES['rapport_file']['error'] != 0) {
                $error_msg = "Veuillez télécharger un fichier de rapport (PDF).";
            } else {
                $ext = strtolower(pathinfo($_FILES['rapport_file']['name'], PATHINFO_EXTENSION));
                
                if ($ext !== 'pdf') {
                    $error_msg = "Seuls les fichiers PDF sont acceptés.";
                } else {
                    // Récupération des infos pour le nommage
                    $stmtEvent = $this->db->prepare("SELECT fe.titre, fc.nom_club FROM fiche_event fe INNER JOIN fiche_club fc ON fe.club_orga = fc.club_id WHERE fe.event_id = ?");
                    $stmtEvent->execute([$event_id]);
                    $eventInfo = $stmtEvent->fetch(PDO::FETCH_ASSOC);
                    
                    $club_name = $eventInfo['nom_club'] ?? 'Club';
                    $event_title = $eventInfo['titre'] ?? 'Event';

                    // 1. Préparation du dossier et nom du rapport PDF
                    $clean_club = preg_replace('/[^A-Za-z0-9]/', '', $club_name);
                    $clean_event = preg_replace('/[^A-Za-z0-9]/', '', $event_title);
                    
                    $pdf_dir = ROOT_PATH . '/uploads/rapports/';
                    if (!is_dir($pdf_dir)) mkdir($pdf_dir, 0775, true);

                    $pdf_filename = $clean_club . '_' . $clean_event . '_' . time() . '.pdf';
                    $full_pdf_path = $pdf_dir . $pdf_filename;
                    $db_pdf_path = '../uploads/rapports/' . $pdf_filename;
                    
                    if (move_uploaded_file($_FILES['rapport_file']['tmp_name'], $full_pdf_path)) {
                        
                        // 2. Gestion des images (Dossier uploads/images_events/)
                        $images_list = $this->uploadEventImages($_FILES['event_photos'] ?? [], $event_id, $club_name, $event_title);

                        // 3. Mise à jour BDD
                        $stmt = $this->db->prepare("UPDATE fiche_event SET rapport_event = ?, images_event = ? WHERE event_id = ?");
                        
                        if ($stmt->execute([$db_pdf_path, $images_list, $event_id])) {
                            $success_msg = "Le rapport et les photos ont été déposés avec succès.";
                            
                            // Rafraîchir la liste (Logique identique à l'initiale)
                            $stmt = $this->db->prepare("
                                SELECT fe.*, fc.nom_club 
                                FROM fiche_event fe
                                INNER JOIN membres_club mc ON fe.club_orga = mc.club_id
                                INNER JOIN fiche_club fc ON fe.club_orga = fc.club_id
                                WHERE mc.membre_id = ? 
                                AND mc.valide = 1
                                AND fc.validation_finale = 1
                                AND fe.validation_finale = 1
                                AND (fe.rapport_event IS NULL OR fe.rapport_event = '')
                                ORDER BY fe.date_ev DESC
                            ");
                            $stmt->execute([$_SESSION['id']]);
                            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        } else {
                            $error_msg = "Erreur lors de l'enregistrement en base de données.";
                        }
                    } else {
                        $error_msg = "Erreur lors de l'upload du rapport PDF.";
                    }
                }
            }
        }

        return [
            'events' => $events,
            'error_msg' => $error_msg,
            'success_msg' => $success_msg
        ];
    }
}
