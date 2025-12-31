<?php

class EventController {
    private $eventModel;
    private $db;

    public function __construct($database) {
        $this->db = $database;
        $this->eventModel = new Event($database);
    }

    public function listEvents() {
        // Public route - anyone can view events
        $events = $this->eventModel->getAllValidatedEvents();
        
        return [
            'events' => $events
        ];
    }

    public function viewEvent() {
        // Public route - anyone can view event details
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

    public function createEvent() {
        checkPermission(2);
        
        $error_msg = '';
        $success_msg = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_event'])) {
            $nom_event = trim($_POST['nom_event'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $date_event = trim($_POST['date_event'] ?? '');
            $campus = trim($_POST['campus'] ?? '');

            if (!$nom_event || !$description || !$date_event || !$campus) {
                $error_msg = "Tous les champs sont obligatoires.";
            } else {
                $data = [
                    'nom_event' => $nom_event,
                    'description' => $description,
                    'date_event' => $date_event,
                    'user_id' => $_SESSION['id'],
                    'campus' => $campus
                ];

                if ($this->eventModel->createEvent($data)) {
                    $success_msg = "Événement créé avec succès.";
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
            $date_event = trim($_POST['date_event'] ?? '');
            $campus = trim($_POST['campus'] ?? '');

            if (!$nom_event || !$description || !$date_event || !$campus) {
                $error_msg = "Tous les champs sont obligatoires.";
            } else {
                $data = [
                    'nom_event' => $nom_event,
                    'description' => $description,
                    'date_event' => $date_event,
                    'campus' => $campus
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

    public function myEvents() {
        validateSession();
        
        $events = $this->eventModel->getEventsByUser($_SESSION['id']);
        
        return [
            'events' => $events
        ];
    }

    public function eventReport() {
        validateSession();
        
        $error_msg = '';
        $success_msg = '';
        
        // Get user's clubs events
        $stmt = $this->db->prepare("
            SELECT fe.* FROM fiche_event fe
            INNER JOIN membres_club mc ON fe.club_orga = mc.club_id
            WHERE mc.membre_id = ? AND mc.valide = 1 AND fe.validation_finale = 1
            ORDER BY fe.date_ev DESC
        ");
        $stmt->execute([$_SESSION['id']]);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_report'])) {
            $event_id = $_POST['event_id'] ?? null;
            $rapport = trim($_POST['rapport'] ?? '');
            
            if (!$event_id || !$rapport) {
                $error_msg = "Tous les champs sont obligatoires.";
            } else {
                // Handle file upload if present
                $rapport_file = null;
                if (isset($_FILES['rapport_file']) && $_FILES['rapport_file']['error'] == 0) {
                    $allowed = ['pdf', 'doc', 'docx'];
                    $filename = $_FILES['rapport_file']['name'];
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    
                    if (in_array($ext, $allowed)) {
                        $new_filename = uniqid() . '.' . $ext;
                        $upload_path = ROOT_PATH . '/uploads/rapports/' . $new_filename;
                        
                        if (move_uploaded_file($_FILES['rapport_file']['tmp_name'], $upload_path)) {
                            $rapport_file = $new_filename;
                        }
                    }
                }
                
                $stmt = $this->db->prepare("INSERT INTO rapport_event (event_id, user_id, rapport, fichier, date_depot) VALUES (?, ?, ?, ?, NOW())");
                if ($stmt->execute([$event_id, $_SESSION['id'], $rapport, $rapport_file])) {
                    $success_msg = "Rapport déposé avec succès.";
                } else {
                    $error_msg = "Erreur lors du dépôt du rapport.";
                }
            }
        }

        return [
            'events' => $events,
            'error_msg' => $error_msg,
            'success_msg' => $success_msg
        ];
    }
    
    // Note: analytics() function has been moved to AdminController
}
