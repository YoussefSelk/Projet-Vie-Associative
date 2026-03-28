<?php
declare(strict_types=1);
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
    public function browseClubs() {
        $stmt = $this->db->prepare("
            SELECT fc.*, COUNT(mc.membre_id) AS membres_count
            FROM fiche_club fc
            LEFT JOIN membres_club mc ON mc.club_id = fc.club_id AND mc.valide = 1
            WHERE fc.validation_finale = 1
            GROUP BY fc.club_id
            ORDER BY fc.nom_club ASC
        ");
        $stmt->execute();
        $clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['clubs' => $clubs];
    }

    public function listClubs() {
        checkPermission(2);

        $user_id = (int)($_SESSION['id'] ?? 0);
        $userPermission = (int)($_SESSION['permission'] ?? 0);
        $isTuteurOnly = ($userPermission === 2);

        // Tuteurs : seulement leurs clubs. BDE/Admin : tous les clubs validés.
        if ($isTuteurOnly) {
            $stmt = $this->db->prepare("SELECT * FROM fiche_club WHERE validation_finale = 1 AND tuteur = ? ORDER BY nom_club ASC");
            $stmt->execute([$user_id]);
            $clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $clubs = $this->clubModel->getAllValidatedClubs();
        }

        $req_club = null;
        $update_msg = '';
        $error_msg = '';
        $success_msg = '';

        // Recherche d'un club par nom (BDE/Admin uniquement)
        if (!$isTuteurOnly && $_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['club'])) {
            $club = $this->clubModel->getClubByName($_POST['club']);
            if ($club) {
                $req_club = $club;
            }
        }

        $tuteurs = [];
        if (!$isTuteurOnly) {
            $stmt = $this->db->query("SELECT id, nom, prenom FROM users WHERE permission = 2 ORDER BY nom ASC");
            $tuteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            'clubs'         => $clubs,
            'tuteurs'       => $tuteurs,
            'req_club'      => $req_club,
            'error_msg'     => $error_msg,
            'success_msg'   => $success_msg,
            'update_msg'    => $update_msg,
            'is_tuteur_only' => $isTuteurOnly,
        ];
    }

    /**
     * Création d'un nouveau club
     * Gère les projets associatifs avec validation tuteur
     * 
     * @return array Données pour la vue [error_msg, success_msg]
     */
    public function createClub() {
        // Route already ensures auth, no specific permission level required
        
        $error_msg = '';
        $success_msg = '';
        
        // Get tutors list (permission = 2 = tuteur)
        $tutors = $this->db->query("
            SELECT id, nom, prenom 
            FROM users 
            WHERE permission = 2 
            ORDER BY nom ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        // Get all users for member selection (exclude current user who will be added automatically)
        $currentUserId = (int)($_SESSION['id'] ?? 0);
        $stmtUsers = $this->db->prepare("
            SELECT id, nom, prenom, mail, promo 
            FROM users 
            WHERE id != ?
            ORDER BY nom ASC, prenom ASC
        ");
        $stmtUsers->execute([$currentUserId]);
        $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_club'])) {
            $nom_club = trim($_POST['nom_club'] ?? '');
            $type_club = trim($_POST['type_club'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $campus = trim($_POST['campus'] ?? '');
            $tuteur_id = !empty($_POST['tuteur_id']) ? intval($_POST['tuteur_id']) : null;
            $projet_associatif = isset($_POST['projet_associatif']) ? 1 : 0;
            $soutenance = isset($_POST['soutenance']) ? 1 : 0;
            $soutenance_date = !empty($_POST['soutenance_date']) ? $_POST['soutenance_date'] : null;
            $creator_role = trim($_POST['creator_role'] ?? 'Président');
            $members = $_POST['members'] ?? [];

            // Validation serveur: minimum 3 fondateurs (vous + 2 autres)
            // + normalisation (IDs uniques, rôles autorisés) pour éviter les contournements.
            $creatorId = $_SESSION['id'] ?? null;
            $allowedRoles = ['Président', 'Vice-Président', 'Trésorier', 'Secrétaire', 'Membre'];
            if (!in_array($creator_role, $allowedRoles, true)) {
                $creator_role = 'Président';
            }

            $memberIds = [];
            $normalizedMembers = [];
            if (is_array($members)) {
                foreach ($members as $member) {
                    $memberId = !empty($member['user_id']) ? intval($member['user_id']) : 0;
                    if ($memberId <= 0) continue;
                    if ($creatorId && $memberId === intval($creatorId)) continue;
                    if (isset($memberIds[$memberId])) continue;

                    $memberIds[$memberId] = true;
                    $role = trim($member['role'] ?? 'Membre');
                    if (!in_array($role, $allowedRoles, true)) {
                        $role = 'Membre';
                    }

                    $normalizedMembers[] = [
                        'user_id' => $memberId,
                        'role' => $role
                    ];
                }
            }

            $uniqueMemberIds = array_keys($memberIds);
            if ($creatorId && count($uniqueMemberIds) < 2) {
                $error_msg = "La création d'un club nécessite au moins 3 personnes (vous + 2 autres membres fondateurs).";
            }

            // Vérifier que les IDs existent réellement en base
            if (empty($error_msg) && !empty($uniqueMemberIds)) {
                $placeholders = implode(',', array_fill(0, count($uniqueMemberIds), '?'));
                $checkUsersStmt = $this->db->prepare("SELECT id FROM users WHERE id IN ($placeholders)");
                $checkUsersStmt->execute($uniqueMemberIds);
                $existingIds = $checkUsersStmt->fetchAll(PDO::FETCH_COLUMN, 0);
                $existingIds = array_map('intval', $existingIds);
                sort($existingIds);
                $submittedIds = array_map('intval', $uniqueMemberIds);
                sort($submittedIds);
                if ($existingIds !== $submittedIds) {
                    $error_msg = "Un ou plusieurs membres sélectionnés sont invalides. Veuillez les re-sélectionner depuis la liste.";
                }
            }

            if (!$nom_club || !$type_club || !$description || !$campus) {
                $error_msg = "Tous les champs sont obligatoires.";
            } 
            // Check for duplicate club name
            elseif ($this->clubModel->getClubByName($nom_club)) {
                $error_msg = "Un club avec ce nom existe déjà. Veuillez choisir un autre nom.";
            }
            else {
                try {
                    // Handle logo upload
                    $logo_filename = null;
                    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                        $file = $_FILES['logo'];
                        $allowed_types = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];
                        $max_size = 2 * 1024 * 1024; // 2MB
                        $finfo_logo = new \finfo(FILEINFO_MIME_TYPE);
                        $detected_logo_mime = $finfo_logo->file($file['tmp_name']);

                        if (!in_array($detected_logo_mime, $allowed_types)) {
                            $error_msg = "Format de logo non supporté. Utilisez PNG, JPG, GIF ou WebP.";
                        } elseif ($file['size'] > $max_size) {
                            $error_msg = "Le logo est trop volumineux. Taille maximale : 2 Mo.";
                        } else {
                            // Generate unique filename
                            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                            $just_filename = 'club_' . uniqid() . '_' . time() . '.' . $extension;
                            $upload_path = ROOT_PATH . '/uploads/logos/' . $just_filename;
                            // Store relative path for database (matching existing format)
                            $logo_filename = '../uploads/logos/' . $just_filename;
                            
                            // Ensure upload directory exists
                            if (!is_dir(ROOT_PATH . '/uploads/logos')) {
                                mkdir(ROOT_PATH . '/uploads/logos', 0755, true);
                            }
                            
                            if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                                $error_msg = "Erreur lors de l'upload du logo.";
                                $logo_filename = null;
                            }
                        }
                    }
                    
                    if (empty($error_msg)) {
                        // Create the club - respect actual DB structure
                        // Table fiche_club: club_id, nom_club, type_club, description, logo_club, tuteur, campus,
                        //                   validation_admin, validation_tuteur, motif_refus, validation_finale
                        $stmt = $this->db->prepare("
                            INSERT INTO fiche_club (nom_club, type_club, description, campus, tuteur, logo_club, validation_admin, validation_tuteur, validation_finale) 
                            VALUES (?, ?, ?, ?, ?, ?, NULL, NULL, NULL)
                        ");
                        $result = $stmt->execute([
                            $nom_club,
                            $type_club,
                            $description,
                            $campus,
                            $tuteur_id ? (string)$tuteur_id : '', // tuteur is VARCHAR in DB, empty string if no tutor
                            $logo_filename
                        ]);
                    
                    if ($result) {
                        $club_id = $this->db->lastInsertId();
                        
                        // Add the creator as a member with their chosen role
                        if ($creatorId) {
                            $insertStmt = $this->db->prepare("INSERT INTO membres_club (club_id, membre_id, fonction, soutenance, valide) VALUES (?, ?, ?, 0, 1)");
                            $insertStmt->execute([$club_id, $creatorId, $creator_role]);
                        }
                        
                        // Add selected members
                        if (!empty($normalizedMembers)) {
                            foreach ($normalizedMembers as $member) {
                                $memberId = intval($member['user_id']);
                                if (!$memberId || ($creatorId && $memberId === intval($creatorId))) continue;

                                // Check if member already exists
                                $checkStmt = $this->db->prepare("SELECT id FROM membres_club WHERE club_id = ? AND membre_id = ?");
                                $checkStmt->execute([$club_id, $memberId]);
                                if (!$checkStmt->fetch()) {
                                    $insertStmt = $this->db->prepare("INSERT INTO membres_club (club_id, membre_id, fonction, soutenance, valide) VALUES (?, ?, ?, 0, 1)");
                                    $insertStmt->execute([$club_id, $memberId, $member['role']]);
                                }
                            }
                        }
                        
                        // Send notification to tutor if assigned
                        if ($tuteur_id) {
                            $this->notifyTutor($tuteur_id, $nom_club, 'club');
                        }
                        
                        // Redirection vers la page de détails du club pour afficher immédiatement les membres
                        redirect('index.php?page=club-view&id=' . $club_id . '&created=1');
                    } else {
                        $error_msg = "Erreur lors de la création du club.";
                    }
                    } // End of if (empty($error_msg))
                } catch (PDOException $e) {
                    ErrorHandler::logError("Club creation error: " . $e->getMessage(), 'ERROR', [
                        'club_name' => $nom_club,
                        'user_id' => $_SESSION['id'] ?? null
                    ]);
                    $error_msg = "Erreur lors de la création du club.";
                }
            }
        }

        return [
            'error_msg' => $error_msg,
            'success_msg' => $success_msg,
            'tutors' => $tutors,
            'users' => $users
        ];
    }

    /**
     * Affiche la liste des demandes de clubs créés par l'utilisateur connecté
     * Permet de voir l'état de validation et de modifier les clubs refusés
     * 
     * @return array Données des clubs de l'utilisateur
     */
    public function myClubs() {
        $user_id = $_SESSION['id'] ?? null;
        
        if (!$user_id) {
            redirect('index.php?page=login');
        }

        $clubs = $this->clubModel->getClubsByUser($user_id);
        $error_msg = '';
        $success_msg = '';

        // Suppression d'un club refusé
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_club'])) {
            $club_id = $_POST['club_id'] ?? null;
            
            if ($club_id) {
                // Vérifier que l'utilisateur est bien le créateur (Président)
                $stmt = $this->db->prepare("
                    SELECT mc.fonction FROM membres_club mc
                    WHERE mc.club_id = ? AND mc.membre_id = ? AND mc.fonction = 'Président'
                ");
                $stmt->execute([$club_id, $user_id]);
                
                if ($stmt->fetch()) {
                    // Vérifier que le club est refusé (validation_finale = -1 for rejected clubs)
                    $club = $this->clubModel->getClubById($club_id);
                    if ($club && ($club['validation_finale'] == -1 || $club['validation_finale'] === 0)) {
                        if ($this->clubModel->deleteClub($club_id)) {
                            $success_msg = "Club supprimé avec succès.";
                            $clubs = $this->clubModel->getClubsByUser($user_id);
                        } else {
                            $error_msg = "Erreur lors de la suppression du club.";
                        }
                    } else {
                        $error_msg = "Vous ne pouvez supprimer que les clubs refusés.";
                    }
                } else {
                    $error_msg = "Vous n'avez pas la permission de supprimer ce club.";
                }
            }
        }

        return [
            'clubs' => $clubs,
            'error_msg' => $error_msg,
            'success_msg' => $success_msg
        ];
    }

    /**
     * Édite un club refusé pour le resoummettre à validation
     * Vérifie que l'utilisateur est bien le créateur (Président)
     * 
     * @return array Données du club et messages de statut
     */
    public function editClub() {
        $user_id = $_SESSION['id'] ?? null;
        $club_id = $_GET['id'] ?? null;
        $userPermission = (int)($_SESSION['permission'] ?? 0);
        $isAdmin = $userPermission >= 4;

        if (!$user_id) {
            redirect('index.php?page=login');
        }

        if (!$club_id) {
            redirect('index.php?page=my-clubs');
        }

        $club = $this->clubModel->getClubById($club_id);
        $error_msg = '';
        $success_msg = '';
        $currentMembers = [];
        $users = [];
        $is_admin_force = false;

        if (!$club) {
            $error_msg = "Club non trouvé.";
        } else {
            // Vérifier que l'utilisateur est bien un membre du bureau (Président ou Secrétaire)
            $stmt = $this->db->prepare("
                SELECT mc.fonction FROM membres_club mc
                WHERE mc.club_id = ? AND mc.membre_id = ? AND mc.fonction IN ('Président', 'Secrétaire')
            ");
            $stmt->execute([$club_id, $user_id]);
            $isBureau = (bool)$stmt->fetch();

            // Les admins (≥4) peuvent modifier n'importe quel club, validé ou non
            if (!$isBureau && !$isAdmin) {
                $error_msg = "Vous n'avez pas la permission de modifier ce club. Seuls le Président et le Secrétaire peuvent modifier le club.";
            } elseif ($club['validation_finale'] == 1 && !$isAdmin) {
                $error_msg = "Vous ne pouvez pas modifier un club déjà validé.";
            } else {
                // Flag : admin forçant la modification d'un club déjà validé
                $is_admin_force = $isAdmin && ($club['validation_finale'] == 1);

                // Récupérer les membres actuels du club (sauf le Président)
                $memberStmt = $this->db->prepare("
                    SELECT u.id, u.nom, u.prenom, mc.fonction
                    FROM membres_club mc
                    INNER JOIN users u ON mc.membre_id = u.id
                    WHERE mc.club_id = ? AND mc.fonction != 'Président'
                ");
                $memberStmt->execute([$club_id]);
                $currentMembers = $memberStmt->fetchAll(PDO::FETCH_ASSOC);

                // Récupérer tous les utilisateurs disponibles (sauf l'utilisateur actuel)
                $stmtUsers = $this->db->prepare("
                    SELECT id, nom, prenom, mail, promo
                    FROM users
                    WHERE id != ?
                    ORDER BY nom ASC, prenom ASC
                ");
                $stmtUsers->execute([(int)$user_id]);
                $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

                // Traiter la soumission du formulaire
                if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_club'])) {
                    $nom_club = trim($_POST['nom_club'] ?? '');
                    $type_club = trim($_POST['type_club'] ?? '');
                    $description = trim($_POST['description'] ?? '');
                    $campus = trim($_POST['campus'] ?? '');
                    $members = $_POST['members'] ?? [];

                    if (!$nom_club || !$type_club || !$description || !$campus) {
                        $error_msg = "Tous les champs sont obligatoires.";
                    } elseif (!in_array($campus, ["Calais", "Longuenesse", "Dunkerque", "Boulogne"])) {
                        $error_msg = "Campus invalide.";
                    } else {
                        if ($this->clubModel->clubNameExists($nom_club, $club_id)) {
                            $error_msg = "Un club avec ce nom existe déjà.";
                        } else {
                            $data = [
                                'nom_club' => $nom_club,
                                'type_club' => $type_club,
                                'description' => $description,
                                'campus' => $campus
                            ];

                            // Admin : ne pas réinitialiser la validation (modification directe)
                            // Utilisateur normal : réinitialise la validation pour resoumission
                            $resetValidation = !$isAdmin;

                            if ($this->clubModel->updateClub($club_id, $data, $resetValidation)) {
                                // Supprimer tous les membres non-Président puis réinsérer
                                $deleteStmt = $this->db->prepare("DELETE FROM membres_club WHERE club_id = ? AND fonction != 'Président'");
                                $deleteStmt->execute([$club_id]);

                                if (!empty($members)) {
                                    foreach ($members as $member) {
                                        $memberId = !empty($member['user_id']) ? intval($member['user_id']) : null;
                                        if ($memberId && $memberId != $user_id) {
                                            $checkStmt = $this->db->prepare("SELECT id FROM membres_club WHERE club_id = ? AND membre_id = ?");
                                            $checkStmt->execute([$club_id, $memberId]);
                                            if (!$checkStmt->fetch()) {
                                                $insertStmt = $this->db->prepare("INSERT INTO membres_club (club_id, membre_id, fonction, soutenance, valide) VALUES (?, ?, ?, 0, 1)");
                                                $insertStmt->execute([$club_id, $memberId, $member['role'] ?? 'Membre']);
                                            }
                                        }
                                    }
                                }

                                if ($isAdmin) {
                                    $_SESSION['flash_success'] = 'Modification administrateur enregistrée avec succès.';
                                    redirect('index.php?page=club-view&id=' . $club_id);
                                } else {
                                    redirect('index.php?page=my-clubs&success=1');
                                }
                            } else {
                                $error_msg = "Erreur lors de la modification du club.";
                            }
                        }
                    }
                }
            }
        }

        return [
            'club'           => $club,
            'error_msg'      => $error_msg,
            'success_msg'    => $success_msg,
            'currentMembers' => $currentMembers,
            'users'          => $users,
            'is_admin_force' => $is_admin_force,
            'is_admin'       => $isAdmin,
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
                // Note: In DB, 'tuteur' column stores user ID as VARCHAR
                if (!empty($club['tuteur'])) {
                    try {
                        $stmt = $this->db->prepare("SELECT nom, prenom, mail FROM users WHERE id = ?");
                        $stmt->execute([$club['tuteur']]);
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
                        <p><a href='" . (defined('BASE_URL') ? BASE_URL : '') . "/?page=tutoring' class='btn'>Accéder aux validations</a></p>
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
            ErrorHandler::logError("Failed to notify tutor: " . $e->getMessage(), 'WARNING', [
                'tutor_id' => $tuteur_id ?? null,
                'item_name' => $item_name ?? null
            ]);
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
        checkPermission(2);

        $user_id = (int)($_SESSION['id'] ?? 0);
        $userPermission = (int)($_SESSION['permission'] ?? 0);
        $club_id = $_GET['club_id'] ?? null;

        if (!$club_id) {
            redirect('index.php?page=club-list');
        }

        $club = $this->clubModel->getClubById($club_id);
        if (!$club) {
            redirect('index.php?page=club-list');
        }

        // Tuteurs : vérifier que ce club leur est bien assigné
        if ($userPermission === 2 && (string)($club['tuteur'] ?? '') !== (string)$user_id) {
            ErrorHandler::renderHttpError(403, "Vous n'êtes pas le tuteur de ce club.");
        }
        
        // Récupérer les membres avec tous les détails (incl. soutenance et tuteur via JOIN)
        $stmt = $this->db->prepare("
            SELECT
                mc.membre_id,
                u.nom,
                u.prenom,
                u.mail,
                u.promo,
                COALESCE(NULLIF(TRIM(mc.fonction), ''), 'Membre') AS fonction,
                mc.soutenance,
                CONCAT(t.prenom, ' ', t.nom) AS tuteur_fullname
            FROM membres_club mc
            JOIN  users u  ON u.id  = mc.membre_id
            JOIN  fiche_club fc ON fc.club_id = mc.club_id
            LEFT JOIN users t  ON t.id = CAST(fc.tuteur AS UNSIGNED)
            WHERE mc.club_id = ? AND mc.valide = 1
            ORDER BY u.nom ASC, u.prenom ASC
        ");
        $stmt->execute([$club_id]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Le tuteur est désormais extrait directement dans la requête principale (tuteur_fullname).
        // On conserve néanmoins un fallback en cas de LEFT JOIN NULL.
        $tutor_name = '';
        if (!empty($members)) {
            $tutor_name = trim($members[0]['tuteur_fullname'] ?? '');
        }
        if ($tutor_name === '' && !empty($club['tuteur'])) {
            $tutorStmt = $this->db->prepare("SELECT nom, prenom FROM users WHERE id = ?");
            $tutorStmt->execute([$club['tuteur']]);
            $tutor = $tutorStmt->fetch(PDO::FETCH_ASSOC);
            if ($tutor) {
                $tutor_name = $tutor['prenom'] . ' ' . $tutor['nom'];
            }
        }
        
        // Générer le CSV avec BOM pour compatibilité Excel UTF-8
        $filename = 'membres_' . preg_replace('/[^a-zA-Z0-9]/', '_', $club['nom_club']) . '_' . date('Y-m-d') . '.csv';

        // Nettoyage du buffer de sortie avant d'émettre les headers
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=UTF-16LE');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Étape 1 : construire le CSV UTF-8 en mémoire
        $tmp = fopen('php://temp', 'r+b');

        // En-tête des colonnes
        fputcsv($tmp, [
            'Nom',
            'Prénom',
            'Email',
            'Promotion / Spécialité',
            'Rôle',
            'Soutenance',
            'Tuteur du club'
        ], "\t", '"', "\0");

        // Lignes de données
        foreach ($members as $member) {
            $soutenance = !empty($member['soutenance']) ? 'Oui' : 'Non';
            $memberTutor = trim($member['tuteur_fullname'] ?? '');
            if ($memberTutor === '') {
                $memberTutor = $tutor_name;
            }
            if ($memberTutor === ' ' || $memberTutor === '') {
                $memberTutor = 'Non assigné';
            }
            $row = [
                $member['nom']      ?? '',
                $member['prenom']   ?? '',
                $member['mail']     ?? '',
                $member['promo']    ?? '',
                $member['fonction'] ?? 'Membre',
                $soutenance,
                $memberTutor,
            ];
            $row = array_map(static function($v) {
                return str_replace(["\r\n", "\r", "\n"], ' ', trim((string)$v));
            }, $row);
            fputcsv($tmp, $row, "\t", '"', "\0");
        }

        // Étape 2 : lire le CSV UTF-8 depuis le buffer
        rewind($tmp);
        $csv = stream_get_contents($tmp);
        fclose($tmp);

        // Étape 3 : BOM UTF-16 LE + conversion UTF-8 → UTF-16 LE
        // Excel (toutes versions, toutes locales) détecte automatiquement le BOM
        // et interprète la tabulation comme séparateur de colonnes.
        echo "\xFF\xFE" . mb_convert_encoding($csv, 'UTF-16LE', 'UTF-8');
        exit;
    }
}
