<?php

/**
 * Controleur d'administration centralise
 * 
 * Gere toutes les fonctionnalites d'administration de la plateforme :
 * - Tableau de bord avec statistiques
 * - Gestion des utilisateurs (CRUD, permissions)
 * - Parametres systeme et configuration
 * - Export de donnees (CSV)
 * - Analytiques et rapports
 * - Audit de securite et logs
 * - Outils de maintenance base de donnees
 * 
 * Niveaux d'acces :
 * - Permission 3+ : Dashboard, analytiques evenements, rapports
 * - Permission 5 : Toutes les fonctionnalites (Super Admin)
 * 
 * @package Controllers
 */
class AdminController {
    
    /** @var PDO Connexion a la base de donnees */
    private $db;
    
    /** @var Event Modele de gestion des evenements */
    private $eventModel;
    
    /** @var Club Modele de gestion des clubs */
    private $clubModel;
    
    /** @var User Modele de gestion des utilisateurs */
    private $userModel;

    /**
     * Constructeur - initialise les dependances
     * 
     * @param PDO $database Connexion a la base de donnees
     */
    public function __construct($database) {
        $this->db = $database;
        $this->eventModel = new Event($database);
        $this->clubModel = new Club($database);
        $this->userModel = new User($database);
    }

    // ==========================================
    // SECTION TABLEAU DE BORD (Permission 3+)
    // ==========================================

    /**
     * Tableau de bord principal avec statistiques et apercu
     * Affiche les metriques cles, activites recentes et actions rapides
     * 
     * Statistiques de base (permission 3+) :
     * - Totaux utilisateurs, clubs, evenements
     * - Elements en attente de validation
     * - Repartition par campus et permission
     * 
     * Statistiques avancees (permission 5) :
     * - Inscriptions totales aux evenements
     * - Elements rejetes
     * - Nouveaux utilisateurs (7 derniers jours)
     * - Configuration systeme
     * 
     * @return array Donnees pour la vue du dashboard
     */
    public function dashboard() {
        checkPermission(3);
        
        $stats = [];
        
        // Nombre total d'utilisateurs
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM users");
        $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Nombre total de clubs valides
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_club WHERE validation_finale = 1");
        $stats['total_clubs'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Nombre total d'evenements valides
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_event WHERE validation_finale = 1");
        $stats['total_events'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Clubs en attente de validation finale (deja valides par tuteur)
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_club WHERE validation_finale IS NULL AND validation_tuteur = 1");
        $stats['pending_clubs'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Evenements en attente de validation finale (valides par tuteur et BDE)
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_event WHERE validation_finale IS NULL AND validation_tuteur = 1 AND validation_bde = 1");
        $stats['pending_events'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Total des elements en attente
        $stats['total_pending'] = $stats['pending_clubs'] + $stats['pending_events'];
        
        // Repartition des utilisateurs par niveau de permission
        $stmt = $this->db->query("SELECT permission, COUNT(*) as count FROM users GROUP BY permission ORDER BY permission");
        $stats['users_by_permission'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Repartition des clubs par campus
        $stmt = $this->db->query("SELECT campus, COUNT(*) as count FROM fiche_club WHERE validation_finale = 1 GROUP BY campus");
        $stats['clubs_by_campus'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Statistiques avancees pour les Super Admins uniquement (permission 5)
        if (($_SESSION['permission'] ?? 0) == 5) {
            // Total des inscriptions aux evenements
            try {
                $stmt = $this->db->query("SELECT COUNT(*) as count FROM subscribe_event");
                $stats['total_subscriptions'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            } catch (Exception $e) {
                $stats['total_subscriptions'] = 0;
            }
            
            // Nombre de membres de clubs valides
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM membres_club WHERE valide = 1");
            $stats['total_club_members'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Elements rejetes
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_club WHERE validation_finale = -1");
            $stats['rejected_clubs'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_event WHERE validation_finale = -1");
            $stats['rejected_events'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Nouveaux utilisateurs inscrits (7 derniers jours)
            try {
                $stmt = $this->db->query("SELECT COUNT(*) as count FROM users WHERE date_inscription >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
                $stats['new_users_week'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            } catch (Exception $e) {
                $stats['new_users_week'] = 0;
            }
            
            // Evenements a venir (30 prochains jours)
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_event WHERE validation_finale = 1 AND date_ev BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY)");
            $stats['upcoming_events'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Configuration systeme
            try {
                $stmt = $this->db->query("SELECT * FROM config LIMIT 1");
                $stats['config'] = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $stats['config'] = ['creation_club_active' => 1];
            }
        }
        
        // Evenements par mois (6 derniers mois)
        $stmt = $this->db->query("
            SELECT 
                DATE_FORMAT(date_ev, '%Y-%m') as month,
                COUNT(*) as count 
            FROM fiche_event 
            WHERE validation_finale = 1 
                AND date_ev >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(date_ev, '%Y-%m')
            ORDER BY month ASC
        ");
        $stats['events_by_month'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Activites recentes (10 dernieres)
        $recent_activities = [];
        
        // Derniers clubs crees
        $stmt = $this->db->query("
            SELECT 'club' as type, nom_club as title, campus, club_id as sort_id 
            FROM fiche_club 
            ORDER BY club_id DESC 
            LIMIT 5
        ");
        $recent_clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Derniers evenements crees
        $stmt = $this->db->query("
            SELECT 'event' as type, titre as title, campus, date_ev as date, event_id as sort_id 
            FROM fiche_event 
            ORDER BY event_id DESC 
            LIMIT 5
        ");
        $recent_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fusionner et trier par ID (plus recent en premier)
        $recent_activities = array_merge($recent_clubs, $recent_events);
        usort($recent_activities, function($a, $b) {
            if (isset($a['date']) && isset($b['date'])) {
                return strtotime($b['date']) - strtotime($a['date']);
            }
            return $b['sort_id'] - $a['sort_id'];
        });
        $recent_activities = array_slice($recent_activities, 0, 8);
        
        // Elements en attente pour actions rapides
        $stmt = $this->db->query("
            SELECT club_id, nom_club, type_club, campus 
            FROM fiche_club 
            WHERE validation_finale IS NULL AND validation_tuteur = 1 
            LIMIT 5
        ");
        $pending_clubs_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $this->db->query("
            SELECT e.event_id, e.titre, e.campus, e.date_ev, c.nom_club 
            FROM fiche_event e
            LEFT JOIN fiche_club c ON e.club_orga = c.club_id
            WHERE e.validation_finale IS NULL AND e.validation_tuteur = 1 AND e.validation_bde = 1
            LIMIT 5
        ");
        $pending_events_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'stats' => $stats,
            'recent_activities' => $recent_activities,
            'pending_clubs_list' => $pending_clubs_list,
            'pending_events_list' => $pending_events_list
        ];
    }

    // ==========================================
    // SECTION PARAMETRES (Permission 5 - Super Admin)
    // ==========================================

    /**
     * Page des parametres d'administration
     * Accessible uniquement aux Super Admins (permission 5)
     * 
     * Fonctionnalites :
     * - Activer/desactiver la creation de clubs
     * - Activer/desactiver la creation d'evenements
     * - Mode maintenance
     * - Effacer les logs d'erreur
     * - Validation en masse des clubs et evenements
     * - Nettoyage des anciens evenements
     * 
     * @return array Donnees pour la vue des parametres
     */
    public function settings() {
        checkPermission(5);
        
        $success_msg = '';
        $error_msg = '';
        
        // Recuperer la configuration actuelle
        try {
            $stmt = $this->db->query("SELECT * FROM config LIMIT 1");
            $config = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $config = ['creation_club_active' => 1, 'creation_event_active' => 1, 'maintenance_mode' => 0];
        }
        
        // Traitement du formulaire de mise a jour
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // Mise a jour des parametres principaux
            if (isset($_POST['update_settings'])) {
                $creation_club_active = isset($_POST['creation_club_active']) ? 1 : 0;
                $creation_event_active = isset($_POST['creation_event_active']) ? 1 : 0;
                $maintenance_mode = isset($_POST['maintenance_mode']) ? 1 : 0;
                
                try {
                    $stmt = $this->db->prepare("UPDATE config SET creation_club_active = ?");
                    $stmt->execute([$creation_club_active]);
                    $success_msg = "Paramètres mis à jour avec succès.";
                    $config['creation_club_active'] = $creation_club_active;
                    $config['creation_event_active'] = $creation_event_active;
                    $config['maintenance_mode'] = $maintenance_mode;
                } catch (Exception $e) {
                    $error_msg = "Erreur lors de la mise à jour des paramètres.";
                }
            }
            
            // Effacement des logs d'erreur
            if (isset($_POST['clear_logs'])) {
                $logFile = LOGS_PATH . '/error.log';
                if (file_exists($logFile)) {
                    file_put_contents($logFile, '');
                    $success_msg = "Logs effacés avec succès.";
                }
            }
            
            // Validation en masse de tous les clubs en attente
            if (isset($_POST['bulk_validate_clubs'])) {
                try {
                    $this->db->query("UPDATE fiche_club SET validation_finale = 1 WHERE validation_finale IS NULL AND validation_tuteur = 1");
                    $success_msg = "Tous les clubs en attente ont été validés.";
                } catch (Exception $e) {
                    $error_msg = "Erreur lors de la validation des clubs.";
                }
            }
            
            // Validation en masse de tous les evenements en attente
            if (isset($_POST['bulk_validate_events'])) {
                try {
                    $this->db->query("UPDATE fiche_event SET validation_finale = 1 WHERE validation_finale IS NULL AND validation_tuteur = 1 AND validation_bde = 1");
                    $success_msg = "Tous les événements en attente ont été validés.";
                } catch (Exception $e) {
                    $error_msg = "Erreur lors de la validation des événements.";
                }
            }
            
            // Identification des anciens evenements pour archivage
            if (isset($_POST['clean_old_events'])) {
                try {
                    $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_event WHERE date_ev < DATE_SUB(NOW(), INTERVAL 1 YEAR)");
                    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                    $success_msg = "$count anciens événements identifiés (archivage disponible prochainement).";
                } catch (Exception $e) {
                    $error_msg = "Erreur lors du nettoyage.";
                }
            }
        }
        
        // Recuperer les 50 dernieres lignes du log d'erreur
        $error_logs = [];
        $logFile = LOGS_PATH . '/error.log';
        if (file_exists($logFile)) {
            $lines = file($logFile);
            $error_logs = array_slice($lines, -50);
            $error_logs = array_reverse($error_logs);
        }
        
        // Statistiques de la base de donnees (nombre d'enregistrements par table)
        $db_stats = [];
        try {
            $tables = ['users', 'fiche_club', 'fiche_event', 'membres_club', 'subscribe_event'];
            foreach ($tables as $table) {
                try {
                    $stmt = $this->db->query("SELECT COUNT(*) as count FROM $table");
                    $db_stats[$table] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                } catch (Exception $e) {
                    $db_stats[$table] = 'N/A';
                }
            }
        } catch (Exception $e) {
            // Ignorer les erreurs
        }
        
        // Statistiques avancees
        $advanced_stats = [];
        
        // Comptage des elements en attente
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_club WHERE validation_finale IS NULL AND validation_tuteur = 1");
        $advanced_stats['pending_clubs'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_event WHERE validation_finale IS NULL AND validation_tuteur = 1 AND validation_bde = 1");
        $advanced_stats['pending_events'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Comptage des elements rejetes
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_club WHERE validation_finale = -1");
        $advanced_stats['rejected_clubs'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_event WHERE validation_finale = -1");
        $advanced_stats['rejected_events'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Evenements de plus d'un an
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_event WHERE date_ev < DATE_SUB(NOW(), INTERVAL 1 YEAR)");
            $advanced_stats['old_events'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (Exception $e) {
            $advanced_stats['old_events'] = 0;
        }
        
        // Evenements passes sans rapport soumis
        try {
            $stmt = $this->db->query("
                SELECT COUNT(*) as count FROM fiche_event e 
                LEFT JOIN rapport_event r ON e.event_id = r.event_id 
                WHERE e.validation_finale = 1 AND e.date_ev < NOW() AND r.id IS NULL
            ");
            $advanced_stats['events_no_report'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (Exception $e) {
            $advanced_stats['events_no_report'] = 0;
        }
        
        // Repartition des utilisateurs par permission
        $stmt = $this->db->query("SELECT permission, COUNT(*) as count FROM users GROUP BY permission ORDER BY permission");
        $advanced_stats['users_by_permission'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 10 derniers utilisateurs inscrits
        $stmt = $this->db->query("SELECT id, nom, prenom, mail, permission FROM users ORDER BY id DESC LIMIT 10");
        $advanced_stats['recent_users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Informations systeme du serveur
        $system_info = [
            'php_version' => phpversion(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'memory_limit' => ini_get('memory_limit'),
            'max_upload' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'max_execution_time' => ini_get('max_execution_time') . 's',
            'timezone' => date_default_timezone_get(),
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
        ];
        
        // Calcul de l'espace disque utilise par les uploads
        try {
            $uploadPath = UPLOADS_PATH;
            if (is_dir($uploadPath)) {
                $size = 0;
                foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($uploadPath)) as $file) {
                    if ($file->isFile()) $size += $file->getSize();
                }
                $system_info['uploads_size'] = round($size / 1024 / 1024, 2) . ' MB';
            } else {
                $system_info['uploads_size'] = 'N/A';
            }
        } catch (Exception $e) {
            $system_info['uploads_size'] = 'N/A';
        }
        
        return [
            'config' => $config,
            'error_logs' => $error_logs,
            'db_stats' => $db_stats,
            'advanced_stats' => $advanced_stats,
            'system_info' => $system_info,
            'success_msg' => $success_msg,
            'error_msg' => $error_msg
        ];
    }

    // ==========================================
    // SECTION EXPORT DE DONNEES (Permission 5)
    // ==========================================

    /**
     * Exporte les donnees de la plateforme en CSV
     * Accessible uniquement aux Super Admins (permission 5)
     * 
     * Types d'export disponibles :
     * - users : Liste des utilisateurs
     * - clubs : Liste des clubs
     * - events : Liste des evenements
     * - subscriptions : Inscriptions aux evenements
     * - members : Membres des clubs
     * 
     * @return void (sortie directe du fichier CSV)
     */
    public function exportData() {
        checkPermission(5);
        
        $type = $_GET['type'] ?? 'users';
        
        switch ($type) {
            case 'users':
                // Export de la liste des utilisateurs
                $stmt = $this->db->query("SELECT id, nom, prenom, mail, promo, permission FROM users ORDER BY id");
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $filename = 'users_export_' . date('Y-m-d') . '.csv';
                $headers = ['ID', 'Nom', 'Prénom', 'Email', 'Promo', 'Permission'];
                break;
                
            case 'clubs':
                // Export de la liste des clubs
                $stmt = $this->db->query("SELECT club_id, nom_club, type_club, campus, validation_finale FROM fiche_club ORDER BY club_id");
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $filename = 'clubs_export_' . date('Y-m-d') . '.csv';
                $headers = ['ID', 'Nom du club', 'Type', 'Campus', 'Statut validation'];
                break;
                
            case 'events':
                // Export de la liste des evenements
                $stmt = $this->db->query("SELECT event_id, titre, date_ev, campus, validation_finale FROM fiche_event ORDER BY event_id");
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $filename = 'events_export_' . date('Y-m-d') . '.csv';
                $headers = ['ID', 'Titre', 'Date', 'Campus', 'Statut validation'];
                break;
            
            case 'subscriptions':
                // Export des inscriptions aux evenements avec details utilisateur et evenement
                $stmt = $this->db->query("
                    SELECT se.id, u.nom, u.prenom, u.mail, fe.titre, fe.date_ev
                    FROM subscribe_event se
                    JOIN users u ON se.user_id = u.id
                    JOIN fiche_event fe ON se.event_id = fe.event_id
                    ORDER BY se.id
                ");
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $filename = 'subscriptions_export_' . date('Y-m-d') . '.csv';
                $headers = ['ID', 'Nom', 'Prénom', 'Email', 'Événement', 'Date événement'];
                break;
            
            case 'members':
                // Export des membres de clubs avec details
                $stmt = $this->db->query("
                    SELECT mc.id, u.nom, u.prenom, u.mail, fc.nom_club, mc.valide
                    FROM membres_club mc
                    JOIN users u ON mc.membre_id = u.id
                    JOIN fiche_club fc ON mc.club_id = fc.club_id
                    ORDER BY mc.id
                ");
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $filename = 'club_members_export_' . date('Y-m-d') . '.csv';
                $headers = ['ID', 'Nom', 'Prénom', 'Email', 'Club', 'Validé'];
                break;
                
            default:
                redirect('index.php?page=admin-settings');
                return;
        }
        
        // En-tetes HTTP pour le telechargement CSV
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // BOM UTF-8 pour compatibilite Excel
        echo "\xEF\xBB\xBF";
        
        $output = fopen('php://output', 'w');
        fputcsv($output, $headers, ';');
        
        foreach ($data as $row) {
            fputcsv($output, array_values($row), ';');
        }
        
        fclose($output);
        exit;
    }

    // ==========================================
    // SECTION ANALYTIQUES EVENEMENTS (Permission 3+)
    // ==========================================

    /**
     * Analytiques des evenements
     * Pour le BDE et les administrateurs (permission 3+)
     * 
     * Metriques affichees :
     * - Total des evenements valides
     * - Repartition par campus et par mois
     * - Evenements les plus populaires (par inscriptions)
     * - Evenements a venir (30 prochains jours)
     * - Evenements sans rapport soumis
     * - Classement des clubs par activite
     * 
     * @return array Donnees statistiques pour la vue
     */
    public function eventAnalytics() {
        checkPermission(3);
        
        $stats = [];
        
        // Total des evenements valides
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_event WHERE validation_finale = 1");
        $stats['total_events'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Repartition des evenements par campus
        $stmt = $this->db->query("
            SELECT campus, COUNT(*) as count 
            FROM fiche_event 
            WHERE validation_finale = 1 
            GROUP BY campus 
            ORDER BY count DESC
        ");
        $stats['by_campus'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Evenements par mois (12 derniers mois)
        $stmt = $this->db->query("
            SELECT 
                DATE_FORMAT(date_ev, '%Y-%m') as month,
                COUNT(*) as count 
            FROM fiche_event 
            WHERE validation_finale = 1 
                AND date_ev >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(date_ev, '%Y-%m')
            ORDER BY month ASC
        ");
        $stats['by_month'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Top 10 des evenements les plus populaires (par nombre d'inscriptions)
        try {
            $stmt = $this->db->query("
                SELECT fe.event_id, fe.titre, fe.date_ev, fe.campus, fc.nom_club,
                    COUNT(se.id) as subscription_count
                FROM fiche_event fe
                LEFT JOIN subscribe_event se ON fe.event_id = se.event_id
                LEFT JOIN fiche_club fc ON fe.club_orga = fc.club_id
                WHERE fe.validation_finale = 1
                GROUP BY fe.event_id
                ORDER BY subscription_count DESC
                LIMIT 10
            ");
            $stats['popular_events'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $stats['popular_events'] = [];
        }
        
        // Total des inscriptions aux evenements
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM subscribe_event");
            $stats['total_subscriptions'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (Exception $e) {
            $stats['total_subscriptions'] = 0;
        }
        
        // Evenements a venir (30 prochains jours) avec compteur d'inscriptions
        $stmt = $this->db->query("
            SELECT fe.*, fc.nom_club,
                (SELECT COUNT(*) FROM subscribe_event se WHERE se.event_id = fe.event_id) as subscription_count
            FROM fiche_event fe
            LEFT JOIN fiche_club fc ON fe.club_orga = fc.club_id
            WHERE fe.validation_finale = 1 
                AND fe.date_ev BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY)
            ORDER BY fe.date_ev ASC
        ");
        $stats['upcoming_events'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Evenements passes sans rapport (30 derniers jours) - necessite attention
        try {
            $stmt = $this->db->query("
                SELECT fe.event_id, fe.titre, fe.date_ev, fc.nom_club
                FROM fiche_event fe
                LEFT JOIN fiche_club fc ON fe.club_orga = fc.club_id
                LEFT JOIN rapport_event re ON fe.event_id = re.event_id
                WHERE fe.validation_finale = 1 
                    AND fe.date_ev < NOW()
                    AND fe.date_ev >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    AND re.id IS NULL
                ORDER BY fe.date_ev DESC
                LIMIT 5
            ");
            $stats['events_without_reports'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $stats['events_without_reports'] = [];
        }
        
        // Classement des clubs par nombre d'evenements organises
        $stmt = $this->db->query("
            SELECT fc.club_id, fc.nom_club, fc.campus,
                COUNT(fe.event_id) as event_count
            FROM fiche_club fc
            LEFT JOIN fiche_event fe ON fc.club_id = fe.club_orga AND fe.validation_finale = 1
            WHERE fc.validation_finale = 1
            GROUP BY fc.club_id
            ORDER BY event_count DESC
            LIMIT 10
        ");
        $stats['club_ranking'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'stats' => $stats
        ];
    }

    // ==========================================
    // SECTION GESTION DES UTILISATEURS (Permission 5)
    // ==========================================

    /**
     * Liste tous les utilisateurs avec gestion avancee
     * Accessible uniquement aux Super Admins (permission 5)
     * 
     * Fonctionnalites :
     * - Recherche par nom, prenom, email
     * - Filtres par permission et promotion
     * - Tri dynamique sur les colonnes
     * - Affichage du nombre de clubs et inscriptions par utilisateur
     * 
     * @return array Donnees pour la vue liste utilisateurs
     */
    public function listUsers() {
        checkPermission(5);
        
        // Recuperation des parametres de recherche et filtrage
        $search = $_GET['search'] ?? '';
        $filter_permission = $_GET['permission'] ?? '';
        $filter_promo = $_GET['promo'] ?? '';
        $sort = $_GET['sort'] ?? 'id';
        $order = $_GET['order'] ?? 'DESC';
        
        // Construction de la requete avec sous-requetes pour les compteurs
        $query = "SELECT u.*, 
            (SELECT COUNT(*) FROM membres_club mc WHERE mc.membre_id = u.id AND mc.valide = 1) as clubs_count,
            (SELECT COUNT(*) FROM subscribe_event se WHERE se.user_id = u.id) as subscriptions_count
            FROM users u WHERE 1=1";
        $params = [];
        
        // Filtre de recherche textuelle
        if ($search) {
            $query .= " AND (u.nom LIKE ? OR u.prenom LIKE ? OR u.mail LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        // Filtre par niveau de permission
        if ($filter_permission !== '') {
            $query .= " AND u.permission = ?";
            $params[] = $filter_permission;
        }
        
        // Filtre par promotion
        if ($filter_promo) {
            $query .= " AND u.promo = ?";
            $params[] = $filter_promo;
        }
        
        // Validation de la colonne de tri (securite contre injection SQL)
        $allowed_sorts = ['id', 'nom', 'prenom', 'mail', 'promo', 'permission'];
        if (!in_array($sort, $allowed_sorts)) $sort = 'id';
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        
        $query .= " ORDER BY u.$sort $order";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Statistiques par niveau de permission
        $stats = [];
        $stmt = $this->db->query("SELECT permission, COUNT(*) as count FROM users GROUP BY permission ORDER BY permission");
        $stats['by_permission'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Liste des promotions disponibles pour le filtre
        $stmt = $this->db->query("SELECT DISTINCT promo FROM users WHERE promo IS NOT NULL AND promo != '' ORDER BY promo DESC");
        $promos = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        return [
            'users' => $users,
            'stats' => $stats,
            'promos' => $promos,
            'filters' => [
                'search' => $search,
                'permission' => $filter_permission,
                'promo' => $filter_promo,
                'sort' => $sort,
                'order' => $order
            ]
        ];
    }

    /**
     * Met a jour le niveau de permission d'un utilisateur
     * Accessible uniquement aux Super Admins (permission 5)
     * Protection : impossible de modifier sa propre permission
     * 
     * @return void (redirection apres traitement)
     */
    public function updatePermission() {
        checkPermission(5);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?page=admin-users');
        }
        
        $user_id = $_POST['user_id'] ?? null;
        $new_permission = $_POST['permission'] ?? null;
        
        // Validation : permission entre 0 et 5, pas de modification de sa propre permission
        if ($user_id && $new_permission !== null && $new_permission >= 0 && $new_permission <= 5) {
            if ($user_id != $_SESSION['id']) {
                $stmt = $this->db->prepare("UPDATE users SET permission = ? WHERE id = ?");
                $stmt->execute([$new_permission, $user_id]);
            }
        }
        
        // Redirection vers la page d'origine ou la liste utilisateurs
        $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php?page=admin-users';
        redirect($referer);
    }

    /**
     * Supprime un utilisateur et toutes ses donnees associees
     * Accessible uniquement aux Super Admins (permission 5)
     * Protection : impossible de supprimer son propre compte
     * 
     * Donnees supprimees :
     * - Adhesions aux clubs
     * - Inscriptions aux evenements
     * - Compte utilisateur
     * 
     * @return void (redirection apres traitement)
     */
    public function deleteUser() {
        checkPermission(5);
        
        $user_id = $_GET['id'] ?? null;
        
        // Protection contre l'auto-suppression
        if ($user_id && $user_id != $_SESSION['id']) {
            // Suppression des adhesions aux clubs
            $stmt = $this->db->prepare("DELETE FROM membres_club WHERE membre_id = ?");
            $stmt->execute([$user_id]);
            
            // Suppression des inscriptions aux evenements
            try {
                $stmt = $this->db->prepare("DELETE FROM subscribe_event WHERE user_id = ?");
                $stmt->execute([$user_id]);
            } catch (Exception $e) {}
            
            // Suppression du compte utilisateur
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
        }
        
        redirect('index.php?page=admin-users');
    }

    /**
     * Affiche les details d'un utilisateur
     * Accessible uniquement aux Super Admins (permission 5)
     * 
     * Informations affichees :
     * - Donnees du profil
     * - Clubs rejoints
     * - Inscriptions aux evenements
     * 
     * @return array Donnees pour la vue detail utilisateur
     */
    public function viewUser() {
        checkPermission(5);
        
        $user_id = $_GET['id'] ?? null;
        if (!$user_id) {
            redirect('index.php?page=admin-users');
        }
        
        // Recuperation des informations de l'utilisateur
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            redirect('index.php?page=admin-users');
        }
        
        // Clubs dont l'utilisateur est membre
        $stmt = $this->db->prepare("
            SELECT fc.*
            FROM membres_club mc
            JOIN fiche_club fc ON mc.club_id = fc.club_id
            WHERE mc.membre_id = ?
        ");
        $stmt->execute([$user_id]);
        $clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Inscriptions aux evenements
        try {
            $stmt = $this->db->prepare("
                SELECT fe.*, fc.nom_club
                FROM subscribe_event se
                JOIN fiche_event fe ON se.event_id = fe.event_id
                LEFT JOIN fiche_club fc ON fe.club_orga = fc.club_id
                WHERE se.user_id = ?
                ORDER BY fe.date_ev DESC
            ");
            $stmt->execute([$user_id]);
            $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $subscriptions = [];
        }
        
        // Journal d'activite (pour extension future)
        $activity = [];
        
        return [
            'user' => $user,
            'clubs' => $clubs,
            'subscriptions' => $subscriptions,
            'activity' => $activity
        ];
    }

    // ==========================================
    // SECTION AUDIT ET SECURITE (Permission 5)
    // ==========================================

    /**
     * Journal d'audit de securite
     * Affiche les tentatives de connexion et les erreurs systeme
     * Accessible uniquement aux Super Admins (permission 5)
     * 
     * @return array Donnees pour la vue d'audit
     */
    public function auditLog() {
        checkPermission(5);
        
        // Lecture du fichier de log de securite (100 dernieres lignes)
        $login_attempts = [];
        $securityLogFile = LOGS_PATH . '/security.log';
        if (file_exists($securityLogFile)) {
            $lines = file($securityLogFile);
            $login_attempts = array_slice($lines, -100);
            $login_attempts = array_reverse($login_attempts);
        }
        
        // Lecture du fichier de log d'erreurs (100 dernieres lignes)
        $error_logs = [];
        $errorLogFile = LOGS_PATH . '/error.log';
        if (file_exists($errorLogFile)) {
            $lines = file($errorLogFile);
            $error_logs = array_slice($lines, -100);
            $error_logs = array_reverse($error_logs);
        }
        
        // Statistiques de securite
        $stats = [];
        
        // Nombre d'evenements de securite enregistres
        $stats['security_events'] = count($login_attempts);
        $stats['error_count'] = count($error_logs);
        
        // Utilisateurs avec privileges eleves (permission 3+)
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM users WHERE permission >= 3");
        $stats['privileged_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        return [
            'login_attempts' => $login_attempts,
            'error_logs' => $error_logs,
            'stats' => $stats
        ];
    }

    // ==========================================
    // SECTION GESTION BASE DE DONNEES (Permission 5)
    // ==========================================

    /**
     * Outils de maintenance et nettoyage de la base de donnees
     * Accessible uniquement aux Super Admins (permission 5)
     * 
     * Fonctionnalites :
     * - Nettoyage des enregistrements orphelins
     * - Archivage des anciens evenements
     * - Statistiques par table
     * - Detection des problemes
     * 
     * @return array Donnees pour la vue de maintenance
     */
    public function databaseTools() {
        checkPermission(5);
        
        $success_msg = '';
        $error_msg = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Nettoyage des enregistrements orphelins
            if (isset($_POST['cleanup_orphans'])) {
                try {
                    // Suppression des membres pour des clubs inexistants
                    $stmt = $this->db->query("DELETE mc FROM membres_club mc LEFT JOIN fiche_club fc ON mc.club_id = fc.club_id WHERE fc.club_id IS NULL");
                    
                    // Suppression des inscriptions pour des evenements inexistants
                    $stmt = $this->db->query("DELETE se FROM subscribe_event se LEFT JOIN fiche_event fe ON se.event_id = fe.event_id WHERE fe.event_id IS NULL");
                    
                    // Suppression des inscriptions pour des utilisateurs inexistants
                    $stmt = $this->db->query("DELETE se FROM subscribe_event se LEFT JOIN users u ON se.user_id = u.id WHERE u.id IS NULL");
                    
                    $success_msg = "Nettoyage des enregistrements orphelins effectué.";
                } catch (Exception $e) {
                    $error_msg = "Erreur lors du nettoyage: " . $e->getMessage();
                }
            }
            
            // Archivage des anciens evenements (plus d'un an)
            if (isset($_POST['archive_old_events'])) {
                try {
                    // Marquage avec validation_finale = -2 pour distinguer des rejetes (-1)
                    $stmt = $this->db->query("UPDATE fiche_event SET validation_finale = -2 WHERE date_ev < DATE_SUB(NOW(), INTERVAL 1 YEAR) AND validation_finale = 1");
                    $count = $stmt->rowCount();
                    $success_msg = "$count événements archivés.";
                } catch (Exception $e) {
                    $error_msg = "Erreur lors de l'archivage.";
                }
            }
        }
        
        // Statistiques de chaque table
        $db_stats = [];
        $tables = ['users', 'fiche_club', 'fiche_event', 'membres_club', 'subscribe_event', 'rapport_event'];
        foreach ($tables as $table) {
            try {
                $stmt = $this->db->query("SELECT COUNT(*) as count FROM $table");
                $db_stats[$table] = [
                    'count' => $stmt->fetch(PDO::FETCH_ASSOC)['count']
                ];
            } catch (Exception $e) {
                $db_stats[$table] = ['count' => 'N/A'];
            }
        }
        
        // Detection des problemes potentiels
        $issues = [];
        
        // Membres de club orphelins (club supprime mais membre toujours present)
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM membres_club mc LEFT JOIN fiche_club fc ON mc.club_id = fc.club_id WHERE fc.club_id IS NULL");
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            if ($count > 0) {
                $issues[] = "$count membres de club orphelins";
            }
        } catch (Exception $e) {}
        
        // Evenements de plus d'un an non archives
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_event WHERE date_ev < DATE_SUB(NOW(), INTERVAL 1 YEAR) AND validation_finale = 1");
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            if ($count > 0) {
                $issues[] = "$count événements de plus d'un an";
            }
        } catch (Exception $e) {}
        
        return [
            'db_stats' => $db_stats,
            'issues' => $issues,
            'success_msg' => $success_msg,
            'error_msg' => $error_msg
        ];
    }

    // ==========================================
    // SECTION RAPPORTS (Permission 3+)
    // ==========================================

    /**
     * Generation de rapports de la plateforme
     * Pour le BDE et les administrateurs (permission 3+)
     * 
     * Types de rapports :
     * - monthly : Resume mensuel (evenements, clubs, inscriptions)
     * - clubs : Performance des clubs (membres, evenements organises)
     * - users : Engagement utilisateurs par promotion
     * 
     * @return array Donnees du rapport selectionne
     */
    public function generateReport() {
        checkPermission(3);
        
        $report_type = $_GET['type'] ?? 'monthly';
        $report_data = [];
        
        switch ($report_type) {
            case 'monthly':
                // Resume mensuel
                $month = $_GET['month'] ?? date('Y-m');
                
                // Statistiques des evenements du mois
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as total_events,
                        SUM(CASE WHEN validation_finale = 1 THEN 1 ELSE 0 END) as validated,
                        SUM(CASE WHEN validation_finale = -1 THEN 1 ELSE 0 END) as rejected
                    FROM fiche_event 
                    WHERE DATE_FORMAT(date_ev, '%Y-%m') = ?
                ");
                $stmt->execute([$month]);
                $report_data['events'] = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Nouveaux clubs du mois (approximation basee sur l'ID)
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as count 
                    FROM fiche_club 
                    WHERE DATE_FORMAT(club_id, '%Y-%m') = ?
                ");
                try {
                    $stmt->execute([$month]);
                    $report_data['new_clubs'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                } catch (Exception $e) {
                    $report_data['new_clubs'] = 0;
                }
                
                // Inscriptions aux evenements du mois
                try {
                    $stmt = $this->db->prepare("
                        SELECT COUNT(*) as count 
                        FROM subscribe_event se
                        JOIN fiche_event fe ON se.event_id = fe.event_id
                        WHERE DATE_FORMAT(fe.date_ev, '%Y-%m') = ?
                    ");
                    $stmt->execute([$month]);
                    $report_data['subscriptions'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                } catch (Exception $e) {
                    $report_data['subscriptions'] = 0;
                }
                
                $report_data['month'] = $month;
                break;
                
            case 'clubs':
                // Rapport de performance des clubs
                $stmt = $this->db->query("
                    SELECT fc.club_id, fc.nom_club, fc.campus,
                        (SELECT COUNT(*) FROM membres_club mc WHERE mc.club_id = fc.club_id AND mc.valide = 1) as members_count,
                        (SELECT COUNT(*) FROM fiche_event fe WHERE fe.club_orga = fc.club_id AND fe.validation_finale = 1) as events_count
                    FROM fiche_club fc
                    WHERE fc.validation_finale = 1
                    ORDER BY events_count DESC
                ");
                $report_data['clubs'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
                
            case 'users':
                // Rapport d'engagement utilisateurs par promotion
                $stmt = $this->db->query("
                    SELECT 
                        promo,
                        COUNT(*) as total_users,
                        AVG(CASE WHEN permission >= 2 THEN 1 ELSE 0 END) * 100 as active_percentage
                    FROM users
                    WHERE promo IS NOT NULL AND promo != ''
                    GROUP BY promo
                    ORDER BY promo DESC
                ");
                $report_data['by_promo'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
        }
        
        return [
            'report_type' => $report_type,
            'report_data' => $report_data
        ];
    }
}
