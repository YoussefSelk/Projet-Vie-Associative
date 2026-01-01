<?php
/**
 * =============================================================================
 * CONTRÔLEUR DES CLUBS
 * =============================================================================
 * 
 * Gère toutes les opérations liées aux clubs associatifs :
 * - Liste et affichage des clubs
 * - Création et modification de clubs
 * - Gestion des membres
 * - Export CSV des membres
 * - Notification des tuteurs
 * 
 * Niveaux de permission requis :
 * - Visualisation : tous les utilisateurs connectés
 * - Création/Modification : permission >= 3 (admin)
 * 
 * @author Équipe de développement EILCO
 * @version 2.0
 */

class ClubController {
    /** @var Club Modèle des clubs */
    private $clubModel;
    
    /** @var PDO Instance de connexion à la base de données */
    private $db;

    /**
     * Constructeur
     * @param PDO $database Instance de connexion PDO
     */
    public function __construct($database) {
        $this->db = $database;
        $this->clubModel = new Club($database);
    }

    /**
     * Liste tous les clubs validés pour l'administration
     * Permet la recherche et modification des clubs
     * 
     * @return array Données pour la vue
     */
    public function listClubs() {
        checkPermission(3);
        
        $clubs = $this->clubModel->getAllValidatedClubs();
        $req_club = null;
        $update_msg = '';
        $error_msg = '';
        $success_msg = '';

        // Recherche d'un club par nom
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['club'])) {
            $club = $this->clubModel->getClubByName($_POST['club']);
            if ($club) {
                $req_club = $club;
            }
        }

        // Mise à jour d'un club
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_club'])) {
            $club_id = $_POST['club_id'] ?? null;
            $new_nom = trim($_POST['nom_club'] ?? '');
            $new_type = trim($_POST['type_club'] ?? '');
            $new_description = trim($_POST['description'] ?? '');
            $new_campus = trim($_POST['campus'] ?? '');

            if (!$club_id) {
                $error_msg = "ID du club manquant.";
            } elseif (!$new_nom) {
                $error_msg = "Le nom du club est obligatoire.";
            } elseif (!$new_type) {
                $error_msg = "Le type du club est obligatoire.";
            } elseif (!$new_description) {
                $error_msg = "La description du club est obligatoire.";
            } elseif (!in_array($new_campus, ["Calais", "Longuenesse", "Dunkerque", "Boulogne"])) {
                $error_msg = "Campus invalide.";
            } else {
                $data = [
                    'nom_club' => $new_nom,
                    'type_club' => $new_type,
                    'description' => $new_description,
                    'campus' => $new_campus
                ];

                if ($this->clubModel->updateClub($club_id, $data)) {
                    $success_msg = "Informations mises à jour avec succès.";
                    $req_club = $this->clubModel->getClubById($club_id);
                } else {
                    $error_msg = "Erreur lors de la mise à jour.";
                }
            }
        }

        return [
            'clubs' => $clubs,
            'req_club' => $req_club,
            'error_msg' => $error_msg,
            'success_msg' => $success_msg,
            'update_msg' => $update_msg
        ];
    }

    /**
     * Création d'un nouveau club
     * Gère les projets associatifs avec validation tuteur
     * 
     * @return array Données pour la vue [error_msg, success_msg]
     */
    public function createClub() {
        checkPermission(3);
        
        $error_msg = '';
        $success_msg = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_club'])) {
            $nom_club = trim($_POST['nom_club'] ?? '');
            $type_club = trim($_POST['type_club'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $campus = trim($_POST['campus'] ?? '');
            $tuteur_id = !empty($_POST['tuteur_id']) ? intval($_POST['tuteur_id']) : null;
            $projet_associatif = isset($_POST['projet_associatif']) ? 1 : 0;
            $soutenance = isset($_POST['soutenance']) ? 1 : 0;
            $soutenance_date = !empty($_POST['soutenance_date']) ? $_POST['soutenance_date'] : null;
            $members = $_POST['members'] ?? [];

            if (!$nom_club || !$type_club || !$description || !$campus) {
                $error_msg = "Tous les champs sont obligatoires.";
            } 
            // Check for duplicate club name
            elseif ($this->clubModel->getClubByName($nom_club)) {
                $error_msg = "Un club avec ce nom existe déjà. Veuillez choisir un autre nom.";
            }
            // Check member count for projet associatif
            elseif ($projet_associatif && count(array_filter($members, function($m) { return !empty($m['email']); })) < 3) {
                $error_msg = "Un projet associatif nécessite au moins 3 membres fondateurs.";
            }
            else {
                try {
                    // Create the club
                    $stmt = $this->db->prepare("
                        INSERT INTO fiche_club (nom_club, type_club, description, campus, tuteur_id, projet_associatif, soutenance_date, validation_finale, createur_id) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, NULL, ?)
                    ");
                    $result = $stmt->execute([
                        $nom_club,
                        $type_club,
                        $description,
                        $campus,
                        $tuteur_id,
                        $projet_associatif,
                        $soutenance ? $soutenance_date : null,
                        $_SESSION['id']
                    ]);
                    
                    if ($result) {
                        $club_id = $this->db->lastInsertId();
                        
                        // Add members if provided
                        if (!empty($members)) {
                            $memberModel = new ClubMember($this->db);
                            foreach ($members as $member) {
                                if (!empty($member['email'])) {
                                    // Find user by email
                                    $userStmt = $this->db->prepare("SELECT id FROM users WHERE mail = ?");
                                    $userStmt->execute([$member['email']]);
                                    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
                                    
                                    if ($user) {
                                        // Check if member already exists
                                        $checkStmt = $this->db->prepare("SELECT id FROM membres_club WHERE club_id = ? AND membre_id = ?");
                                        $checkStmt->execute([$club_id, $user['id']]);
                                        if (!$checkStmt->fetch()) {
                                            // Add member with role
                                            $insertStmt = $this->db->prepare("INSERT INTO membres_club (club_id, membre_id, role, valide) VALUES (?, ?, ?, 1)");
                                            $insertStmt->execute([$club_id, $user['id'], $member['role'] ?? 'membre']);
                                        }
                                    }
                                }
                            }
                        }
                        
                        // Send notification to tutor if assigned
                        if ($tuteur_id) {
                            $this->notifyTutor($tuteur_id, $nom_club, 'club');
                        }
                        
                        $success_msg = "Club créé avec succès. Il est en attente de validation.";
                    } else {
                        $error_msg = "Erreur lors de la création du club.";
                    }
                } catch (PDOException $e) {
                    error_log("Club creation error: " . $e->getMessage());
                    $error_msg = "Erreur lors de la création du club.";
                }
            }
        }

        return [
            'error_msg' => $error_msg,
            'success_msg' => $success_msg
        ];
    }

    /**
     * Affiche les détails d'un club
     * Accessible à tous les utilisateurs
     * 
     * @return array Données du club, membres, événements et tuteur
     */
    public function viewClub() {
        $club_id = $_GET['id'] ?? null;
        
        $club = null;
        $members = [];
        $events = [];
        $tutor = null;
        $error_msg = '';

        if (!$club_id) {
            $error_msg = "ID du club manquant.";
        } else {
            $club = $this->clubModel->getClubById($club_id);
            if (!$club) {
                $error_msg = "Club non trouvé.";
            } else {
                
                // Récupérer les membres du club
                try {
                    $memberModel = new ClubMember($this->db);
                    $members = $memberModel->getClubMembers($club_id);
                } catch (Exception $e) {
                    $members = [];
                }
                
                // Récupérer les événements du club
                try {
                    $stmt = $this->db->prepare("SELECT * FROM fiche_event WHERE club_orga = ? AND validation_finale = 1 ORDER BY date_ev DESC LIMIT 5");
                    $stmt->execute([$club_id]);
                    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e) {
                    $events = [];
                }
                
                // Récupérer les infos du tuteur si présent
                if (!empty($club['tuteur_id'])) {
                    try {
                        $stmt = $this->db->prepare("SELECT nom, prenom, mail FROM users WHERE id = ?");
                        $stmt->execute([$club['tuteur_id']]);
                        $tutor = $stmt->fetch(PDO::FETCH_ASSOC);
                    } catch (Exception $e) {
                        $tutor = null;
                    }
                }
            }
        }
        
        return [
            'id' => $club_id,
            'club' => $club,
            'members' => $members,
            'events' => $events,
            'tutor' => $tutor,
            'error_msg' => $error_msg
        ];
    }
    
    /**
     * Envoie une notification par email au tuteur
     * Informé lors de la création d'un nouveau club ou événement
     * 
     * @param int $tuteur_id Identifiant du tuteur
     * @param string $item_name Nom du club ou événement
     * @param string $type Type d'élément ('club' ou 'event')
     * @return bool Succès de l'envoi
     */
    private function notifyTutor($tuteur_id, $item_name, $type = 'club') {
        try {
            // Get tutor info
            $stmt = $this->db->prepare("SELECT nom, prenom, mail FROM users WHERE id = ?");
            $stmt->execute([$tuteur_id]);
            $tutor = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$tutor || empty($tutor['mail'])) {
                return false;
            }
            
            // Get creator info
            $creatorStmt = $this->db->prepare("SELECT nom, prenom FROM users WHERE id = ?");
            $creatorStmt->execute([$_SESSION['id']]);
            $creator = $creatorStmt->fetch(PDO::FETCH_ASSOC);
            $creator_name = $creator ? $creator['prenom'] . ' ' . $creator['nom'] : 'Un étudiant';
            
            $type_label = ($type === 'club') ? 'club' : 'événement';
            $subject = "Nouvelle demande de validation - $type_label";
            
            $message = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #0066cc; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; background: #f8f9fa; }
                    .btn { display: inline-block; padding: 12px 24px; background: #0066cc; color: white; text-decoration: none; border-radius: 5px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Vie Étudiante EILCO</h2>
                    </div>
                    <div class='content'>
                        <p>Bonjour {$tutor['prenom']} {$tutor['nom']},</p>
                        <p>{$creator_name} a créé un nouveau $type_label qui requiert votre validation :</p>
                        <p><strong>$item_name</strong></p>
                        <p>Veuillez vous connecter à la plateforme pour valider ou refuser cette demande.</p>
                        <p><a href='https://vie-etudiante.eilco.fr/?page=tutoring' class='btn'>Accéder aux validations</a></p>
                        <p>Cordialement,<br>L'équipe Vie Étudiante EILCO</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            // Send email using PHPMailer
            if (function_exists('sendEmail')) {
                return sendEmail($tutor['mail'], $subject, $message);
            }
            
            // Fallback to basic mail
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8\r\n";
            $headers .= "From: noreply@eilco.univ-littoral.fr\r\n";
            
            return mail($tutor['mail'], $subject, $message, $headers);
            
        } catch (Exception $e) {
            error_log("Failed to notify tutor: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Exporte la liste des membres d'un club en CSV
     * Format compatible Excel avec encodage UTF-8 et séparateur point-virgule
     * 
     * @return void (sortie directe du fichier CSV)
     */
    public function exportMembers() {
        checkPermission(3);
        
        $club_id = $_GET['club_id'] ?? null;
        
        if (!$club_id) {
            redirect('index.php?page=club-list');
        }
        
        $club = $this->clubModel->getClubById($club_id);
        if (!$club) {
            redirect('index.php?page=club-list');
        }
        
        // Récupérer les membres avec tous les détails
        $stmt = $this->db->prepare("
            SELECT 
                u.nom,
                u.prenom,
                u.mail,
                u.promo,
                mc.date_adhesion,
                t.nom as tuteur_nom,
                t.prenom as tuteur_prenom
            FROM membres_club mc
            JOIN users u ON mc.membre_id = u.id
            LEFT JOIN fiche_club fc ON mc.club_id = fc.club_id
            LEFT JOIN users t ON fc.tuteur_id = t.id
            WHERE mc.club_id = ? AND mc.valide = 1
            ORDER BY u.nom ASC
        ");
        $stmt->execute([$club_id]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Récupérer le nom du tuteur
        $tutor_name = '';
        if (!empty($club['tuteur_id'])) {
            $tutorStmt = $this->db->prepare("SELECT nom, prenom FROM users WHERE id = ?");
            $tutorStmt->execute([$club['tuteur_id']]);
            $tutor = $tutorStmt->fetch(PDO::FETCH_ASSOC);
            if ($tutor) {
                $tutor_name = $tutor['prenom'] . ' ' . $tutor['nom'];
            }
        }
        
        // Générer le CSV avec BOM pour compatibilité Excel UTF-8
        $filename = 'membres_' . preg_replace('/[^a-zA-Z0-9]/', '_', $club['nom_club']) . '_' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // BOM UTF-8 pour Excel
        echo "\xEF\xBB\xBF";
        
        $output = fopen('php://output', 'w');
        
        // En-tête des colonnes
        fputcsv($output, [
            'Nom',
            'Prénom',
            'Email',
            'Promotion',
            'Date d\'adhésion',
            'Tuteur du club'
        ], ';'); // Point-virgule pour Excel français
        
        // Lignes de données
        foreach ($members as $member) {
            fputcsv($output, [
                $member['nom'] ?? '',
                $member['prenom'] ?? '',
                $member['mail'] ?? '',
                $member['promo'] ?? '',
                $member['date_adhesion'] ? date('d/m/Y', strtotime($member['date_adhesion'])) : '',
                $tutor_name
            ], ';');
        }
        
        fclose($output);
        exit;
    }
}
