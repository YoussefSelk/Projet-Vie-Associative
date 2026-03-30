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
    public function listClubs() {
        checkPermission(2); // Tuteurs (2), BDE (3) et admins (4+) peuvent voir tous les clubs
        
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

        $stmt = $this->db->query("SELECT id, nom, prenom FROM users WHERE permission = 2 ORDER BY nom ASC");
        $tuteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Mise à jour d'un club
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_club'])) {
            $club_id = $_POST['club_id'] ?? null;
            $new_nom = trim($_POST['nom_club'] ?? '');
            $new_type = trim($_POST['type_club'] ?? '');
            $new_description = trim($_POST['description'] ?? '');
            $new_campus = trim($_POST['campus'] ?? '');


             // Vérification d'accès : seuls le Président, le Secrétaire du club ou un Admin (permission >= 4) peuvent modifier
            if ($club_id) {
                $memberModel = new ClubMember($this->db);
                $current_user_id = $_SESSION['id'] ?? null;
                $current_user_permission = $_SESSION['permission'] ?? 0;
                if (!$memberModel->canEditClub((int)$club_id, (int)$current_user_id, (int)$current_user_permission)) {
                    ErrorHandler::renderHttpError(403, "Accès refusé. Seuls le Président, le Secrétaire du club ou un Administrateur peuvent modifier ce club.");
                    return [];
                }
            }

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
            'tuteurs' => $tuteurs,
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
            $creator_soutenance = isset($_POST['creator_soutenance']) ? 1 : 0;
            $creator_role = trim($_POST['creator_role'] ?? 'Président');
            $members = $_POST['members'] ?? [];
            $maxSoutenanceMembers = 5;

            // Rôles autorisés et règles
            $allowedRoles = ['Président', 'Vice-Président', 'Trésorier', 'Secrétaire', "Charge d'événement / communication", 'Membre'];
            $uniqueRoles = ['Président', 'Vice-Président', 'Trésorier', 'Secrétaire', "Charge d'événement / communication"];
            $requiredRoles = ['Président', 'Trésorier', 'Secrétaire'];
            $principalRoles = ['Président', 'Vice-Président', 'Trésorier', 'Secrétaire', "Charge d'événement / communication"];
            
            $assignedRoles = []; // Pour suivre les rôles uniques et obligatoires
            
            $creatorId = $_SESSION['id'] ?? null;
            
            if (!in_array($creator_role, $allowedRoles, true)) {
                $creator_role = 'Président';
            }
            // Ajouter le rôle du créateur aux rôles assignés
            $assignedRoles[] = $creator_role;
            // La soutenance n'est autorisée que pour les rôles principaux
            if (!in_array($creator_role, $principalRoles, true)) {
                $creator_soutenance = 0;
            }

            $soutenanceCount = ($creator_soutenance === 1) ? 1 : 0;

            $memberIds = [];
            $normalizedMembers = [];
            
            if (is_array($members)) {
                foreach ($members as $member) {
                    $memberId = !empty($member['user_id']) ? intval($member['user_id']) : 0;
                    if ($memberId <= 0 || ($creatorId && $memberId === intval($creatorId)) || isset($memberIds[$memberId])) {
                        continue;
                    }

                    $memberIds[$memberId] = true;
                    $role = trim($member['role'] ?? 'Membre');
                    
                    if (!in_array($role, $allowedRoles, true)) {
                        $role = 'Membre';
                    }

                    // --- Vérification Backend : Rôles uniques ---
                    if (in_array($role, $uniqueRoles, true)) {
                        if (in_array($role, $assignedRoles, true)) {
                            $error_msg = "Erreur de sécurité : Le rôle '{$role}' ne peut être attribué qu'à une seule personne.";
                            break;
                        }
                    }
                    $assignedRoles[] = $role;

                    // Soutenance autorisée uniquement pour les rôles principaux
                    $requestedSoutenance = (intval($member['soutenance'] ?? 0) === 1 ? 1 : 0);
                    $soutenance_membre = in_array($role, $principalRoles, true) ? $requestedSoutenance : 0;

                    if ($soutenance_membre === 1) {
                        $soutenanceCount++;
                        if ($soutenanceCount > $maxSoutenanceMembers) {
                            $error_msg = "Le quota de soutenance est dépassé : maximum {$maxSoutenanceMembers} membres en soutenance par club.";
                            break;
                        }
                    }

                    $normalizedMembers[] = [
                        'user_id' => $memberId,
                        'role' => $role,
                        'soutenance' => $soutenance_membre
                    ];
                }
            }

            // --- Vérification Backend : Rôles obligatoires ---
            if (empty($error_msg)) {
                foreach ($requiredRoles as $reqRole) {
                    if (!in_array($reqRole, $assignedRoles, true)) {
                        $error_msg = "Erreur : Le rôle de '{$reqRole}' est obligatoire pour créer le club.";
                        break;
                    }
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
                if (count($existingIds) !== count($uniqueMemberIds)) {
                    $error_msg = "Un ou plusieurs membres sélectionnés sont invalides.";
                }
            }

            if (empty($error_msg)) {
                if (!$nom_club || !$type_club || !$description || !$campus) {
                    $error_msg = "Tous les champs de base sont obligatoires.";
                } elseif ($this->clubModel->getClubByName($nom_club)) {
                    $error_msg = "Un club avec ce nom existe déjà. Veuillez choisir un autre nom.";
                } else {
                    try {
                        // LOGO UPLOAD LOGIC... (Garde ta logique d'upload ici)
                        $logo_filename = null;
                        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                            $file = $_FILES['logo'];
                            $allowed_types = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];
                            $max_size = 2 * 1024 * 1024;
                            if (in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
                                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                                $just_filename = 'club_' . uniqid() . '_' . time() . '.' . $extension;
                                $upload_path = ROOT_PATH . '/uploads/logos/' . $just_filename;
                                $logo_filename = '../uploads/logos/' . $just_filename;
                                if (!is_dir(ROOT_PATH . '/uploads/logos')) mkdir(ROOT_PATH . '/uploads/logos', 0755, true);
                                if (!move_uploaded_file($file['tmp_name'], $upload_path)) $logo_filename = null;
                            }
                        }

                        $stmt = $this->db->prepare("INSERT INTO fiche_club (nom_club, type_club, description, campus, tuteur, logo_club) VALUES (?, ?, ?, ?, ?, ?)");
                        if ($stmt->execute([$nom_club, $type_club, $description, $campus, $tuteur_id ? (string)$tuteur_id : '', $logo_filename])) {
                            $club_id = $this->db->lastInsertId();
                            
                            // Insertion du créateur
                            if ($creatorId) {
                                $this->db->prepare("INSERT INTO membres_club (club_id, membre_id, fonction, soutenance, valide) VALUES (?, ?, ?, ?, 1)")
                                         ->execute([$club_id, $creatorId, $creator_role, $creator_soutenance]);
                            }
                            
                            // Insertion des membres
                            foreach ($normalizedMembers as $member) {
                                $this->db->prepare("INSERT INTO membres_club (club_id, membre_id, fonction, soutenance, valide) VALUES (?, ?, ?, ?, 1)")
                                         ->execute([$club_id, $member['user_id'], $member['role'], $member['soutenance']]);
                            }
                            
                            if ($tuteur_id) $this->notifyTutor($tuteur_id, $nom_club, 'club');
                            redirect('index.php?page=club-view&id=' . $club_id . '&created=1');
                        } else {
                            $error_msg = "Erreur lors de la création du club.";
                        }
                    } catch (PDOException $e) {
                        ErrorHandler::logError("Club creation error: " . $e->getMessage(), 'ERROR');
                        $error_msg = "Erreur système lors de la création du club.";
                    }
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
        
        if (!$user_id) {
            redirect('index.php?page=login');
        }

        if (!$club_id) {
            redirect('index.php?page=my-clubs');
        }

        $club = $this->clubModel->getClubById($club_id);
        $error_msg = '';
        $success_msg = '';

        if (!$club) {
            $error_msg = "Club non trouvé.";
        } else {
            // Vérifier que l'utilisateur est Président/Secrétaire du club OU Admin (permission >= 4)
            $memberModel = new ClubMember($this->db);
            $current_user_permission = (int)($_SESSION['permission'] ?? 0);
            $canEdit = $memberModel->canEditClub((int)$club_id, (int)$user_id, $current_user_permission);
            
            if (!$canEdit) {
                ErrorHandler::renderHttpError(403, "Accès refusé. Seuls le Président, le Secrétaire du club ou un Administrateur peuvent modifier ce club.");
                return [];
            } elseif ($club['validation_finale'] == 1) {
                $error_msg = "Vous ne pouvez pas modifier un club déjà validé.";            
            } else {
                // Récupérer les membres actuels du club (sauf le Président)
                $currentMembers = $this->db->prepare("
                    SELECT u.id, u.nom, u.prenom, mc.fonction, mc.soutenance 
                    FROM membres_club mc 
                    INNER JOIN users u ON mc.membre_id = u.id 
                    WHERE mc.club_id = ? AND mc.fonction != 'Président'
                ");
                $currentMembers->execute([$club_id]);
                $currentMembers = $currentMembers->fetchAll(PDO::FETCH_ASSOC);
                
                // Récupérer tous les utilisateurs disponibles (sauf l'utilisateur actuel)
                $stmtUsers = $this->db->prepare("
                    SELECT id, nom, prenom, mail, promo 
                    FROM users 
                    WHERE id != ?
                    ORDER BY nom ASC, prenom ASC
                ");
                $stmtUsers->execute([(int)$user_id]);
                $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

                // Le président est conservé en base, sa soutenance compte dans le quota global.
                $stmtPresidentSoutenance = $this->db->prepare("SELECT COALESCE(MAX(soutenance), 0) FROM membres_club WHERE club_id = ? AND fonction = 'Président'");
                $stmtPresidentSoutenance->execute([$club_id]);
                $presidentSoutenance = (int)$stmtPresidentSoutenance->fetchColumn();
                
                // Traiter la soumission du formulaire
                // Traiter la soumission du formulaire
                if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_club'])) {
                    $nom_club = trim($_POST['nom_club'] ?? '');
                    $type_club = trim($_POST['type_club'] ?? '');
                    $description = trim($_POST['description'] ?? '');
                    $campus = trim($_POST['campus'] ?? '');
                    $members = $_POST['members'] ?? [];
                    $maxSoutenanceMembers = 5;

                    $allowedRoles = ['Vice-Président', 'Trésorier', 'Secrétaire', "Charge d'événement / communication", 'Membre'];
                    $uniqueRoles = ['Vice-Président', 'Trésorier', 'Secrétaire', "Charge d'événement / communication"];
                    $requiredRoles = ['Trésorier', 'Secrétaire']; // Président est déjà ignoré et conservé en base
                    $principalRoles = ['Vice-Président', 'Trésorier', 'Secrétaire', "Charge d'événement / communication"];

                    $soutenanceCount = $presidentSoutenance;

                    $assignedRoles = [];
                    $normalizedMembers = [];
                    $memberIds = [];

                    if (is_array($members)) {
                        foreach ($members as $member) {
                            $memberId = !empty($member['user_id']) ? intval($member['user_id']) : 0;
                            if ($memberId <= 0 || $memberId == $user_id || isset($memberIds[$memberId])) continue;

                            $memberIds[$memberId] = true;
                            $role = trim($member['role'] ?? 'Membre');
                            
                            if (!in_array($role, $allowedRoles, true)) {
                                $role = 'Membre';
                            }

                            // --- Vérification Backend : Rôles uniques ---
                            if (in_array($role, $uniqueRoles, true)) {
                                if (in_array($role, $assignedRoles, true)) {
                                    $error_msg = "Le rôle '{$role}' doit être attribué à une seule personne.";
                                    break;
                                }
                            }
                            $assignedRoles[] = $role;

                            $requestedSoutenance = (intval($member['soutenance'] ?? 0) === 1 ? 1 : 0);
                            $soutenance_membre = in_array($role, $principalRoles, true) ? $requestedSoutenance : 0;

                            if ($soutenance_membre === 1) {
                                $soutenanceCount++;
                                if ($soutenanceCount > $maxSoutenanceMembers) {
                                    $error_msg = "Le quota de soutenance est dépassé : maximum {$maxSoutenanceMembers} membres en soutenance par club.";
                                    break;
                                }
                            }

                            $normalizedMembers[] = [
                                'user_id' => $memberId,
                                'role' => $role,
                                'soutenance' => $soutenance_membre
                            ];
                        }
                    }

                    // --- Vérification Backend : Rôles obligatoires ---
                    if (empty($error_msg)) {
                        foreach ($requiredRoles as $reqRole) {
                            if (!in_array($reqRole, $assignedRoles, true)) {
                                $error_msg = "Erreur : Le rôle de '{$reqRole}' est obligatoire pour ce club.";
                                break;
                            }
                        }
                    }

                    if (empty($error_msg)) {
                        if (!$nom_club || !$type_club || !$description || !$campus) {
                            $error_msg = "Tous les champs sont obligatoires.";
                        } elseif (!in_array($campus, ["Calais", "Longuenesse", "Dunkerque", "Boulogne"])) {
                            $error_msg = "Campus invalide.";
                        } elseif ($this->clubModel->clubNameExists($nom_club, $club_id)) {
                            $error_msg = "Un club avec ce nom existe déjà.";
                        } else {
                            $data = [
                                'nom_club' => $nom_club,
                                'type_club' => $type_club,
                                'description' => $description,
                                'campus' => $campus
                            ];

                            if ($this->clubModel->updateClub($club_id, $data, true)) {
                                // Ne pas supprimer le Président (créateur)
                                $this->db->prepare("DELETE FROM membres_club WHERE club_id = ? AND fonction != 'Président'")->execute([$club_id]);
                                
                                foreach ($normalizedMembers as $member) {
                                    $this->db->prepare("INSERT INTO membres_club (club_id, membre_id, fonction, soutenance, valide) VALUES (?, ?, ?, ?, 1)")
                                             ->execute([$club_id, $member['user_id'], $member['role'], $member['soutenance']]);
                                }
                                
                                redirect('index.php?page=my-clubs&success=1');
                            } else {
                                $error_msg = "Erreur lors de la modification du club.";
                            }
                        }
                    }
                }
            }
        }

        return [
            'club' => $club,
            'error_msg' => $error_msg,
            'success_msg' => $success_msg,
            'presidentSoutenance' => $presidentSoutenance ?? 0,
            'currentMembers' => $currentMembers ?? [],
            'users' => $users ?? []
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

                // Vérifier si l'utilisateur connecté peut modifier ce club
                $canEditClub = false;
                if (isset($_SESSION['id'])) {
                    $memberModel = new ClubMember($this->db);
                    $canEditClub = $memberModel->canEditClub(
                        (int)$club_id,
                        (int)$_SESSION['id'],
                        (int)($_SESSION['permission'] ?? 0)
                    );
                }
            }
        }
        
        return [
            'id' => $club_id,
            'club' => $club,
            'members' => $members,
            'canEditClub' => $canEditClub ?? false,
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
                mc.fonction,
                t.nom as tuteur_nom,
                t.prenom as tuteur_prenom
            FROM membres_club mc
            JOIN users u ON mc.membre_id = u.id
            LEFT JOIN fiche_club fc ON mc.club_id = fc.club_id
            LEFT JOIN users t ON fc.tuteur = t.id
            WHERE mc.club_id = ? AND mc.valide = 1
            ORDER BY u.nom ASC
        ");
        $stmt->execute([$club_id]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Récupérer le nom du tuteur
        // Note: 'tuteur' is VARCHAR in DB, stores user ID as string
        $tutor_name = '';
        if (!empty($club['tuteur'])) {
            $tutorStmt = $this->db->prepare("SELECT nom, prenom FROM users WHERE id = ?");
            $tutorStmt->execute([$club['tuteur']]);
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
            'Fonction',
            'Tuteur du club'
        ], ';', '"', '\\'); // Point-virgule pour Excel français
        
        // Lignes de données
        foreach ($members as $member) {
            fputcsv($output, [
                $member['nom'] ?? '',
                $member['prenom'] ?? '',
                $member['mail'] ?? '',
                $member['promo'] ?? '',
                $member['fonction'] ?? '',
                $tutor_name
            ], ';', '"', '\\');
        }
        
        fclose($output);
        exit;
    }
}
