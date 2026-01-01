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
        if (!$event_id) {
            redirect('index.php');
        }

        $event = $this->eventModel->getEventById($event_id);
        if (!$event) {
            redirect('index.php');
        }

        return [
            'event' => $event
        ];
    }

    /**
     * Création d'un nouvel événement
     * Nécessite permission >= 2 (membre de bureau)
     * 
     * Structure BD fiche_event: event_id, date_depot, validation_admin, validation_bde, 
     * validation_tuteur, validation_soutenance, titre, club_orga, campus, date_ev, 
     * horaire_debut, horaire_fin, lieu, id_responsable, description, financement_bde, 
     * montant, fiche_sanitaire, affiche, rapport_event, motif_refus, validation_finale
     * 
     * @return array Données pour la vue [error_msg, success_msg]
     */
    public function createEvent() {
        checkPermission(2);
        
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
     * 
     * @return array Liste des événements de l'utilisateur
     */
    public function myEvents() {
        validateSession();
        
        $events = $this->eventModel->getEventsByUser($_SESSION['id']);
        
        return [
            'events' => $events
        ];
    }

    /**
     * Dépôt de rapport post-événement
     * Permet aux membres de club de déposer un rapport après un événement
     * 
     * Fichiers acceptés : PDF, DOC, DOCX
     * Le rapport est stocké dans la colonne rapport_event de fiche_event (VARCHAR 255 = chemin du fichier)
     * 
     * @return array Données pour la vue [events, error_msg, success_msg]
     */
    public function eventReport() {
        validateSession();
        
        $error_msg = '';
        $success_msg = '';
        
        // Récupérer tous les événements validés des clubs de l'utilisateur qui n'ont pas encore de rapport
        // Note: On récupère les événements de tous les clubs dont l'utilisateur est membre
        $stmt = $this->db->prepare("
            SELECT fe.*, fc.nom_club FROM fiche_event fe
            INNER JOIN membres_club mc ON fe.club_orga = mc.club_id
            INNER JOIN fiche_club fc ON fe.club_orga = fc.club_id
            WHERE mc.membre_id = ? 
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
                // Gestion de l'upload de fichier rapport
                $allowed = ['pdf'];
                $filename = $_FILES['rapport_file']['name'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if (!in_array($ext, $allowed)) {
                    $error_msg = "Seuls les fichiers PDF sont acceptés.";
                } else {
                    // Récupérer les infos de l'événement pour le nom du fichier
                    $stmtEvent = $this->db->prepare("SELECT fe.titre, fc.nom_club FROM fiche_event fe INNER JOIN fiche_club fc ON fe.club_orga = fc.club_id WHERE fe.event_id = ?");
                    $stmtEvent->execute([$event_id]);
                    $eventInfo = $stmtEvent->fetch(PDO::FETCH_ASSOC);
                    
                    // Générer le nom du fichier: NomClub_TitreEvent_timestamp.pdf
                    $club_name = preg_replace('/[^A-Za-z0-9]/', '', $eventInfo['nom_club'] ?? 'Club');
                    $event_title = preg_replace('/[^A-Za-z0-9]/', '', $eventInfo['titre'] ?? 'Event');
                    $new_filename = $club_name . '_' . $event_title . '_' . time() . '.' . $ext;
                    $upload_path = ROOT_PATH . '/uploads/rapports/' . $new_filename;
                    $db_path = '../uploads/rapports/' . $new_filename;
                    
                    if (move_uploaded_file($_FILES['rapport_file']['tmp_name'], $upload_path)) {
                        // Mettre à jour la colonne rapport_event dans fiche_event
                        $stmt = $this->db->prepare("UPDATE fiche_event SET rapport_event = ? WHERE event_id = ?");
                        if ($stmt->execute([$db_path, $event_id])) {
                            $success_msg = "Rapport déposé avec succès.";
                            
                            // Rafraîchir la liste des événements
                            $stmt = $this->db->prepare("
                                SELECT fe.*, fc.nom_club FROM fiche_event fe
                                INNER JOIN membres_club mc ON fe.club_orga = mc.club_id
                                INNER JOIN fiche_club fc ON fe.club_orga = fc.club_id
                                WHERE mc.membre_id = ? 
                                  AND fe.validation_finale = 1
                                  AND (fe.rapport_event IS NULL OR fe.rapport_event = '')
                                ORDER BY fe.date_ev DESC
                            ");
                            $stmt->execute([$_SESSION['id']]);
                            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        } else {
                            $error_msg = "Erreur lors de l'enregistrement du rapport.";
                        }
                    } else {
                        $error_msg = "Erreur lors de l'upload du fichier.";
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
