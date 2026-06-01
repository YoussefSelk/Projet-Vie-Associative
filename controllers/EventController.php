<?php
declare(strict_types=1);
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
     * Envoie une notification e-mail au tuteur du club organisateur.
     */
    private function notifyTutorForEvent(int $clubId, string $eventName): bool
    {
        try {
            $stmt = $this->db->prepare("
                SELECT u.id, u.nom, u.prenom, u.mail
                FROM fiche_club fc
                INNER JOIN users u ON u.id = CAST(fc.tuteur AS UNSIGNED)
                WHERE fc.club_id = ?
                LIMIT 1
            ");
            $stmt->execute([$clubId]);
            $tutor = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$tutor || empty($tutor['mail'])) {
                return false;
            }

            $creatorStmt = $this->db->prepare("SELECT nom, prenom FROM users WHERE id = ?");
            $creatorStmt->execute([$_SESSION['id'] ?? 0]);
            $creator = $creatorStmt->fetch(PDO::FETCH_ASSOC);
            $creatorName = $creator ? trim(($creator['prenom'] ?? '') . ' ' . ($creator['nom'] ?? '')) : 'Un étudiant';

            $actionUrl = null;
            if (defined('BASE_URL') && is_string(BASE_URL) && BASE_URL !== '') {
                $actionUrl = rtrim(BASE_URL, '/') . '/?page=tutoring';
            }

            $subject = 'Nouvelle demande de validation - événement';
            $message = buildTutorValidationNotificationEmail(
                trim(($tutor['prenom'] ?? '') . ' ' . ($tutor['nom'] ?? '')),
                $creatorName,
                'evenement',
                $eventName,
                $actionUrl
            );

            return sendEmail((string)$tutor['mail'], $subject, $message);
        } catch (\Throwable $e) {
            ErrorHandler::logError('Failed to notify tutor for event: ' . $e->getMessage(), 'WARNING', [
                'club_id' => $clubId,
                'event_name' => $eventName
            ]);
            return false;
        }
    }

    /**
     * Sanitise un chemin de logo en le contraignant au dossier uploads.
     */
    private function resolveSafeLogoPath(?string $rawLogo): ?string {
        if (empty($rawLogo)) {
            return null;
        }

        if (preg_match('#^https?://#i', $rawLogo)) {
            return $rawLogo;
        }

        $normalized = str_replace('\\', '/', rawurldecode(trim($rawLogo)));
        $normalized = ltrim($normalized, '/');

        if (str_starts_with($normalized, '../uploads/')) {
            $normalized = substr($normalized, 3);
        }

        $segments = array_filter(explode('/', $normalized), static fn ($segment) => $segment !== '');
        foreach ($segments as $segment) {
            if ($segment === '.' || $segment === '..') {
                return null;
            }
        }

        if (empty($segments)) {
            return null;
        }

        $candidate = ROOT_PATH . '/' . implode('/', $segments);
        $realCandidate = realpath($candidate);
        $realUploads = realpath(ROOT_PATH . '/uploads');

        if ($realCandidate === false || $realUploads === false || !is_file($realCandidate)) {
            return null;
        }

        $candidateUnix = str_replace('\\', '/', $realCandidate);
        $uploadsUnix = rtrim(str_replace('\\', '/', $realUploads), '/');
        if (strpos($candidateUnix, $uploadsUnix . '/') !== 0) {
            return null;
        }

        $relative = substr($candidateUnix, strlen(str_replace('\\', '/', ROOT_PATH)));
        return '/' . ltrim($relative, '/');
    }

    /**
     * Liste tous les événements validés
     * Route publique accessible à tous
     * 
     * @return array Liste des événements
     */
    public function listEvents() {
        $events = $this->eventModel->getAllValidatedEvents();
        $currentUserId = (int)($_SESSION['id'] ?? 0);

        // Normaliser et injecter les infos du club (logo, nom) pour l'affichage en liste
        if (!empty($events)) {
            $stmtClub = $this->db->prepare("SELECT logo_club, nom_club FROM fiche_club WHERE club_id = ?");
            foreach ($events as &$ev) {
                $clubId = $ev['club_orga'] ?? null;
                $logoPath = null;
                $clubName = $ev['nom_club'] ?? 'Club inconnu';

                if ($clubId) {
                    $stmtClub->execute([$clubId]);
                    $clubInfo = $stmtClub->fetch(PDO::FETCH_ASSOC);
                    if ($clubInfo) {
                        $rawLogo = $clubInfo['logo_club'] ?? null;
                        $clubName = $clubInfo['nom_club'] ?? $clubName;

                        $logoPath = $this->resolveSafeLogoPath($rawLogo);
                    }
                }

                $ev['logo_club'] = $logoPath;
                $ev['nom_club']  = $clubName;
                $ev['is_subscribed'] = false;
                $ev['subscription_count'] = 0;
            }
            unset($ev);

            $eventIds = array_values(array_unique(array_map(static fn($event) => (int)($event['event_id'] ?? 0), $events)));
            $eventIds = array_values(array_filter($eventIds, static fn($id) => $id > 0));

            if (!empty($eventIds)) {
                $placeholders = implode(',', array_fill(0, count($eventIds), '?'));
                $stmtCounts = $this->db->prepare("SELECT event_id, COUNT(*) AS count FROM abonnements WHERE event_id IN ($placeholders) GROUP BY event_id");
                $stmtCounts->execute($eventIds);
                $counts = [];
                foreach ($stmtCounts->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $counts[(int)$row['event_id']] = (int)$row['count'];
                }

                foreach ($events as &$event) {
                    $eventId = (int)($event['event_id'] ?? 0);
                    $event['subscription_count'] = (int)($counts[$eventId] ?? 0);
                }
                unset($event);
            }

            // Marquer les événements déjà abonnés pour l'utilisateur connecté.
            if ($currentUserId > 0) {
                if (!empty($eventIds)) {
                    $placeholders = implode(',', array_fill(0, count($eventIds), '?'));
                    $stmtSub = $this->db->prepare("SELECT event_id FROM abonnements WHERE id = ? AND event_id IN ($placeholders)");
                    $stmtSub->execute(array_merge([$currentUserId], $eventIds));
                    $subscribedIds = array_map('intval', $stmtSub->fetchAll(PDO::FETCH_COLUMN));
                    $subscribedMap = array_flip($subscribedIds);

                    foreach ($events as &$event) {
                        $event['is_subscribed'] = isset($subscribedMap[(int)($event['event_id'] ?? 0)]);
                    }
                    unset($event);
                }
            }
        }

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
        $stmt = $this->db->prepare("SELECT tuteur, logo_club, nom_club FROM fiche_club WHERE club_id = ?");
        $stmt->execute([$event['club_orga']]);
        $clubInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        // 3. On ajoute l'ID du tuteur au tableau $event pour la vue
        $event['tuteur_id'] = $clubInfo['tuteur'] ?? 0;
        $event['logo_club'] = $this->resolveSafeLogoPath($clubInfo['logo_club'] ?? null);
        $event['nom_club'] = $clubInfo['nom_club'] ?? 'Club inconnu';

        $subscriptionModel = new EventSubscription($this->db);
        $subscribers = $subscriptionModel->getEventSubscribers((int)$event_id);
        $subscriptionCount = count($subscribers);

        return [
            'event' => $event,
            'subscribers' => $subscribers,
            'subscription_count' => $subscriptionCount
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
        
        $error_msg = (string)($_SESSION['flash_error'] ?? '');
        $success_msg = (string)($_SESSION['flash_success'] ?? '');
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        // 1. Détermination de la liste des clubs selon le profil
        if ($user_permission === 1) {
            // UTILISATEUR NORMAL (P1) : Ne voit QUE ses clubs où il est membre validé
            $stmtClubs = $this->db->prepare("
                SELECT fc.club_id, fc.nom_club 
                FROM fiche_club fc
                INNER JOIN membres_club mc ON fc.club_id = mc.club_id
                WHERE fc.validation_finale = 1 
                AND mc.membre_id = ? 
                AND mc.valide = 1
                ORDER BY fc.nom_club ASC
            ");
            $stmtClubs->execute([$_SESSION['id']]);
        } else {
            // BDE / PERSONNEL / TUTEURS (P3, P4, P5) : Voient TOUS les clubs validés
            $stmtClubs = $this->db->query("
                SELECT club_id, nom_club 
                FROM fiche_club 
                WHERE validation_finale = 1 
                ORDER BY nom_club ASC
            ");
        }
        $clubs = $stmtClubs->fetchAll(PDO::FETCH_ASSOC);

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_event'])) {
            $nom_event = trim($_POST['nom_event'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $type_event = trim($_POST['type_event'] ?? 'event'); // Récupère 'event' ou 'activity'
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
            } elseif (strtotime($date_ev) < strtotime('+15 days midnight')) {
                $error_msg = "La date de l'événement doit être au minimum dans 15 jours.";
            } elseif ($type_event === 'event' && (!isset($_FILES['doc_organisation']) || $_FILES['doc_organisation']['error'] !== UPLOAD_ERR_OK)) {
                // Le document est obligatoire seulement pour un EVENT
                $error_msg = "Le dossier d'organisation (Gantt, Budget, Com) est obligatoire pour un événement.";
            } else {
                // Récupérer le nom du club pour le nommage des fichiers
                $stmtClub = $this->db->prepare("SELECT nom_club, tuteur FROM fiche_club WHERE club_id = ?");
                $stmtClub->execute([$club_id]);
                $clubInfo = $stmtClub->fetch(PDO::FETCH_ASSOC);
                $club_name = preg_replace('/[^A-Za-z0-9]/', '', $clubInfo['nom_club'] ?? 'Club');
                $event_title_clean = preg_replace('/[^A-Za-z0-9 ]/', '', $nom_event);
                $timestamp = time();
                
                $fiche_sanitaire_path = null;
                $affiche_path = null;
                $doc_organisation_path = null;

                // Upload du dossier d'organisation (Gantt, Budget, Com)
                if (isset($_FILES['doc_organisation']) && $_FILES['doc_organisation']['error'] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['doc_organisation']['name'], PATHINFO_EXTENSION));
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $detected_mime = $finfo->file($_FILES['doc_organisation']['tmp_name']);
                    $max_doc_size = 10 * 1024 * 1024; // 10 MB
                    if ($ext !== 'pdf' || $detected_mime !== 'application/pdf') {
                        $error_msg = "Le dossier d'organisation doit être au format PDF.";
                    } elseif ($_FILES['doc_organisation']['size'] > $max_doc_size) {
                        $error_msg = "Le dossier d'organisation ne doit pas dépasser 10 Mo.";
                    } else {
                        $upload_dir = ROOT_PATH . '/uploads/docs_organisation/';
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        $new_filename = $club_name . '_dossier_' . $event_title_clean . '_' . $timestamp . '.pdf';
                        $upload_path = $upload_dir . $new_filename;
                        if (move_uploaded_file($_FILES['doc_organisation']['tmp_name'], $upload_path)) {
                            $doc_organisation_path = '../uploads/docs_organisation/' . $new_filename;
                        } else {
                            $error_msg = "Erreur lors de l'upload du dossier d'organisation.";
                        }
                    }
                }

                // Upload fiche sanitaire (PDF)
                if (isset($_FILES['fiche_sanitaire']) && $_FILES['fiche_sanitaire']['error'] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['fiche_sanitaire']['name'], PATHINFO_EXTENSION));
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $detected_mime = $finfo->file($_FILES['fiche_sanitaire']['tmp_name']);
                    $max_fiche_size = 10 * 1024 * 1024; // 10 MB
                    if ($ext !== 'pdf' || $detected_mime !== 'application/pdf') {
                        $error_msg = "La fiche sanitaire doit être un fichier PDF valide.";
                    } elseif ($_FILES['fiche_sanitaire']['size'] > $max_fiche_size) {
                        $error_msg = "La fiche sanitaire ne doit pas dépasser 10 Mo.";
                    } else {
                        $upload_dir = ROOT_PATH . '/uploads/fiches_sanitaires/';
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        $new_filename = $club_name . '_fiche_sanitaire_' . $event_title_clean . '_' . $timestamp . '.pdf';
                        $upload_path = $upload_dir . $new_filename;
                        if (move_uploaded_file($_FILES['fiche_sanitaire']['tmp_name'], $upload_path)) {
                            $fiche_sanitaire_path = '../uploads/fiches_sanitaires/' . $new_filename;
                        } else {
                            $error_msg = "Erreur lors de l'upload de la fiche sanitaire.";
                        }
                    }
                }

                // Upload affiche (JPG, PNG, PDF)
                if (empty($error_msg) && isset($_FILES['affiche']) && $_FILES['affiche']['error'] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['affiche']['name'], PATHINFO_EXTENSION));
                    $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf'];
                    $allowed_affiche_mimes = ['image/jpeg', 'image/png', 'application/pdf'];
                    $finfo_affiche = new \finfo(FILEINFO_MIME_TYPE);
                    $detected_affiche_mime = $finfo_affiche->file($_FILES['affiche']['tmp_name']);
                    $max_affiche_size = 5 * 1024 * 1024; // 5 MB
                    if (!in_array($ext, $allowed_ext) || !in_array($detected_affiche_mime, $allowed_affiche_mimes)) {
                        $error_msg = "L'affiche doit être un fichier JPG, PNG ou PDF valide.";
                    } elseif ($_FILES['affiche']['size'] > $max_affiche_size) {
                        $error_msg = "L'affiche ne doit pas dépasser 5 Mo.";
                    } else {
                        $upload_dir = ROOT_PATH . '/uploads/affiches_event/';
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        $new_filename = $club_name . '_affiche_' . $event_title_clean . '_' . $timestamp . '.' . $ext;
                        $upload_path = $upload_dir . $new_filename;
                        if (move_uploaded_file($_FILES['affiche']['tmp_name'], $upload_path)) {
                            $affiche_path = '../uploads/affiches_event/' . $new_filename;
                        } else {
                            $error_msg = "Erreur lors de l'upload de l'affiche.";
                        }
                    }
                }

                if (empty($error_msg)) {
                    $data = [
                        'nom_event' => $nom_event,
                        'type_event' => $type_event,
                        'description' => $description,
                        'date_ev' => $date_ev,
                        'horaire_debut' => $horaire_debut,
                        'horaire_fin' => $horaire_fin,
                        'campus' => $campus,
                        'lieu' => $lieu,
                        'club_id' => $club_id,
                        'user_id' => $_SESSION['id'],
                        'financement_bde' => $financement_bde,
                        'montant' => $montant,
                        'fiche_sanitaire' => $fiche_sanitaire_path,
                        'affiche' => $affiche_path,
                        'doc_organisation' => $doc_organisation_path
                    ];

                    if ($this->eventModel->createEvent($data)) {
                        $this->notifyTutorForEvent((int)$club_id, $nom_event);
                        if ($type_event === 'activity') {
                            $success_msg = "L'activité a été créée avec succès. Elle est en attente de validation.";
                        } else {
                            $success_msg = "L'événement a été créé avec succès. Il est en attente de validation (BDE, Tuteur & Admin).";
                        }
                    } else {
                        $label = ($type_event === 'activity') ? "de l'activité" : "de l'événement";
                        $error_msg = "Erreur lors de la création " . $label . ".";
                    }
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $success_msg !== '') {
            $_SESSION['flash_success'] = $success_msg;
            redirect($_SERVER['REQUEST_URI']);
        }

        return [
            'clubs' => $clubs,
            'error_msg' => $error_msg,
            'success_msg' => $success_msg
        ];
    }


    /**
     * Modification d'un événement existant
     * Gère le changement de type, les fichiers et la réinitialisation des validations
     */
    public function updateEvent() {
        validateSession();
        $user_id = $_SESSION['id'];
        
        $event_id = $_GET['id'] ?? $_POST['event_id'] ?? null;
        if (!$event_id) redirect('?page=my-events');

        // 1. Vérification de sécurité : l'utilisateur doit être membre VALIDE du club organisateur
        $stmt = $this->db->prepare("
            SELECT fe.*, fe.titre AS nom_event, fe.club_orga AS club_id, fc.nom_club
            FROM fiche_event fe
            INNER JOIN membres_club mc ON fe.club_orga = mc.club_id
            INNER JOIN fiche_club fc ON fe.club_orga = fc.club_id
            WHERE fe.event_id = ? AND mc.membre_id = ? AND mc.valide = 1
        ");
        $stmt->execute([$event_id, $user_id]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            ErrorHandler::renderHttpError(403, "Vous n'avez pas le droit de modifier cet événement.");
        }

        // Empêcher la modification si déjà validé
        if ($event['validation_finale'] == 1) {
            redirect('?page=my-events&error=Impossible de modifier un événement déjà approuvé.');
        }

        $error_msg = (string)($_SESSION['flash_error'] ?? '');
        $success_msg = (string)($_SESSION['flash_success'] ?? '');
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_event'])) {
            // 2. Extraction des données textuelles
            $data = [
                'nom_event'       => trim($_POST['nom_event'] ?? ''),
                'description'     => trim($_POST['description'] ?? ''),
                'type_event'      => trim($_POST['type_event'] ?? 'event'),
                'date_ev'         => trim($_POST['date_ev'] ?? ''),
                'horaire_debut'   => trim($_POST['horaire_debut'] ?? ''),
                'horaire_fin'     => trim($_POST['horaire_fin'] ?? ''),
                'campus'          => trim($_POST['campus'] ?? ''),
                'lieu'            => trim($_POST['lieu'] ?? ''),
                'financement_bde' => isset($_POST['financement_bde']) ? 1 : 0,
                'montant'         => intval($_POST['montant'] ?? 0),
                // On initialise les fichiers à NULL pour que le Modèle ignore les champs vides
                'affiche'          => null,
                'doc_organisation' => null,
                'fiche_sanitaire'  => null
            ];

            // 3. Validations spécifiques
            if (empty($data['nom_event']) || empty($data['date_ev'])) {
                $error_msg = "Le nom et la date sont obligatoires.";
            } 
            // Sécurité : Si type = event et PAS de doc en base et PAS d'upload en cours
            elseif ($data['type_event'] === 'event' && empty($event['doc_organisation']) && (!isset($_FILES['doc_organisation']) || $_FILES['doc_organisation']['error'] !== UPLOAD_ERR_OK)) {
                $error_msg = "Le dossier d'organisation est obligatoire pour un événement.";
            } 
            else {
                $club_name_clean = preg_replace('/[^A-Za-z0-9]/', '', $event['nom_club']);
                $timestamp = time();

                // 4. GESTION DES UPLOADS (Seulement si de nouveaux fichiers sont fournis)
                
                // Dossier Organisation (PDF uniquement)
                if (isset($_FILES['doc_organisation']) && $_FILES['doc_organisation']['error'] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['doc_organisation']['name'], PATHINFO_EXTENSION));
                    $finfoDoc = new \finfo(FILEINFO_MIME_TYPE);
                    $docMime = $finfoDoc->file($_FILES['doc_organisation']['tmp_name']);
                    $maxDocSize = 10 * 1024 * 1024;
                    if ($ext !== 'pdf' || $docMime !== 'application/pdf') {
                        $error_msg = "Le dossier d'organisation doit être un PDF valide.";
                    } elseif ($_FILES['doc_organisation']['size'] > $maxDocSize) {
                        $error_msg = "Le dossier d'organisation ne doit pas dépasser 10 Mo.";
                    } else {
                        $uploadDir = ROOT_PATH . '/uploads/docs_organisation/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }
                        $new_filename = $club_name_clean . '_doc_upd_' . $timestamp . '.pdf';
                        if (move_uploaded_file($_FILES['doc_organisation']['tmp_name'], $uploadDir . $new_filename)) {
                            $data['doc_organisation'] = '../uploads/docs_organisation/' . $new_filename;
                        } else {
                            $error_msg = "Erreur lors de l'upload du dossier d'organisation.";
                        }
                    }
                }

                // Affiche (Images ou PDF)
                if (empty($error_msg) && isset($_FILES['affiche']) && $_FILES['affiche']['error'] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['affiche']['name'], PATHINFO_EXTENSION));
                    $allowedExt = ['jpg', 'jpeg', 'png', 'pdf'];
                    $allowedMimes = ['image/jpeg', 'image/png', 'application/pdf'];
                    $finfoAffiche = new \finfo(FILEINFO_MIME_TYPE);
                    $afficheMime = $finfoAffiche->file($_FILES['affiche']['tmp_name']);
                    $maxAfficheSize = 5 * 1024 * 1024;
                    if (!in_array($ext, $allowedExt, true) || !in_array($afficheMime, $allowedMimes, true)) {
                        $error_msg = "L'affiche doit être un fichier JPG, PNG ou PDF valide.";
                    } elseif ($_FILES['affiche']['size'] > $maxAfficheSize) {
                        $error_msg = "L'affiche ne doit pas dépasser 5 Mo.";
                    } else {
                        $uploadDir = ROOT_PATH . '/uploads/affiches_event/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }
                        $new_filename = $club_name_clean . '_affiche_upd_' . $timestamp . '.' . $ext;
                        if (move_uploaded_file($_FILES['affiche']['tmp_name'], $uploadDir . $new_filename)) {
                            $data['affiche'] = '../uploads/affiches_event/' . $new_filename;
                        } else {
                            $error_msg = "Erreur lors de l'upload de l'affiche.";
                        }
                    }
                }

                // Fiche Sanitaire (PDF)
                if (empty($error_msg) && isset($_FILES['fiche_sanitaire']) && $_FILES['fiche_sanitaire']['error'] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['fiche_sanitaire']['name'], PATHINFO_EXTENSION));
                    $finfoFiche = new \finfo(FILEINFO_MIME_TYPE);
                    $ficheMime = $finfoFiche->file($_FILES['fiche_sanitaire']['tmp_name']);
                    $maxFicheSize = 10 * 1024 * 1024;
                    if ($ext !== 'pdf' || $ficheMime !== 'application/pdf') {
                        $error_msg = "La fiche sanitaire doit être un PDF valide.";
                    } elseif ($_FILES['fiche_sanitaire']['size'] > $maxFicheSize) {
                        $error_msg = "La fiche sanitaire ne doit pas dépasser 10 Mo.";
                    } else {
                        $uploadDir = ROOT_PATH . '/uploads/fiches_sanitaires/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }
                        $new_filename = $club_name_clean . '_sanitaire_upd_' . $timestamp . '.pdf';
                        if (move_uploaded_file($_FILES['fiche_sanitaire']['tmp_name'], $uploadDir . $new_filename)) {
                            $data['fiche_sanitaire'] = '../uploads/fiches_sanitaires/' . $new_filename;
                        } else {
                            $error_msg = "Erreur lors de l'upload de la fiche sanitaire.";
                        }
                    }
                }

                // 5. Appel au Modèle pour enregistrer
                if ($this->eventModel->updateEvent((int)$event_id, $data)) {
                    $success_msg = "Événement mis à jour. Le processus de validation a été réinitialisé.";
                    // Rafraîchir les données pour la vue
                    $stmt->execute([$event_id, $user_id]);
                    $event = $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    $error_msg = "Erreur lors de la mise à jour technique de la base de données.";
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $success_msg !== '') {
            $_SESSION['flash_success'] = $success_msg;
            redirect($_SERVER['REQUEST_URI']);
        }

        // Liste des clubs réduite au club actuel pour la vue de modification
        $clubs = [[ 'club_id' => $event['club_orga'], 'nom_club' => $event['nom_club'] ]];

        return [
            'event' => $event,
            'clubs' => $clubs,
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $success_msg !== '') {
            redirect($_SERVER['REQUEST_URI']);
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
        $allowed_image_mimes = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo_img = new \finfo(FILEINFO_MIME_TYPE);

        // Nettoyage des noms pour les fichiers
        $clean_club = preg_replace('/[^A-Za-z0-9]/', '', $club_name);
        $clean_event = preg_replace('/[^A-Za-z0-9]/', '', $event_title);

        for ($i = 0; $i < count($files['name']); $i++) {
            if ($i >= 1) break;

            $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
            $detected_img_mime = $finfo_img->file($files['tmp_name'][$i]);

            if ($files['error'][$i] === UPLOAD_ERR_OK && $files['size'][$i] <= $max_size && in_array($ext, $allowed_exts) && in_array($detected_img_mime, $allowed_image_mimes)) {
                
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
            AND fe.date_ev < CURDATE() 
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
                $finfo_rapport = new \finfo(FILEINFO_MIME_TYPE);
                $detected_rapport_mime = $finfo_rapport->file($_FILES['rapport_file']['tmp_name']);
                $max_rapport_size = 20 * 1024 * 1024; // 20 MB

                if ($ext !== 'pdf' || $detected_rapport_mime !== 'application/pdf') {
                    $error_msg = "Seuls les fichiers PDF valides sont acceptés.";
                } elseif ($_FILES['rapport_file']['size'] > $max_rapport_size) {
                    $error_msg = "Le rapport ne doit pas dépasser 20 Mo.";
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
                        // Vérifier si la colonne images_event existe
                        try {
                            $stmt = $this->db->prepare("UPDATE fiche_event SET rapport_event = ?, images_event = ? WHERE event_id = ?");
                            $stmt->execute([$db_pdf_path, $images_list, $event_id]);
                        } catch (\PDOException $e) {
                            // Fallback: colonne images_event n'existe pas encore
                            $stmt = $this->db->prepare("UPDATE fiche_event SET rapport_event = ? WHERE event_id = ?");
                            $stmt->execute([$db_pdf_path, $event_id]);
                        }
                        
                        $success_msg = "Le rapport" . ($images_list ? " et les photos" : "") . " ont été déposés avec succès.";
                        
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
                        $error_msg = "Erreur lors de l'upload du rapport PDF.";
                    }
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $success_msg !== '') {
            redirect($_SERVER['REQUEST_URI']);
        }

        return [
            'events' => $events,
            'error_msg' => $error_msg,
            'success_msg' => $success_msg
        ];
    }
}
