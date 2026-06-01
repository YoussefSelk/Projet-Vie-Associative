<?php
declare(strict_types=1);

/**
 * Contrôleur d'administration centralisé
 *
 * Gère toutes les fonctionnalités d'administration de la plateforme :
 * - Tableau de bord avec statistiques
 * - Gestion des utilisateurs (CRUD, permissions)
 * - Paramètres système et configuration
 * - Export de données (CSV)
 * - Analytiques et rapports
 * - Audit de sécurité et logs
 * - Outils de maintenance base de données
 *
 * Niveaux d'accès :
 * - Permission 3+ : Dashboard, analytiques événements, rapports
 * - Permission 5 : Toutes les fonctionnalités (Super Admin)
 * 
 * @package Controllers
 */
class AdminController {
    
    /** @var PDO Connexion à la base de données */
    private $db;

    /** @var Event Modèle de gestion des événements */
    private $eventModel;

    /** @var Club Modèle de gestion des clubs */
    private $clubModel;

    /** @var User Modèle de gestion des utilisateurs */
    private $userModel;

    /**
     * Constructeur - initialise les dépendances
     *
     * @param PDO $database Connexion à la base de données
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
     * Tableau de bord principal avec statistiques et aperçu
     * Affiche les métriques clés, activités récentes et actions rapides
     *
     * Statistiques de base (permission 3+) :
     * - Totaux utilisateurs, clubs, événements
     * - Éléments en attente de validation
     * - Répartition par campus et permission
     *
     * Statistiques avancées (permission 5) :
     * - Inscriptions totales aux événements
     * - Éléments rejetés
     * - Nouveaux utilisateurs (7 derniers jours)
     * - Configuration système
     *
     * @return array Données pour la vue du dashboard
     */
    public function dashboard() {
        checkPermission(3);
        
        $stats = [];
        
        // Nombre total d'utilisateurs
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM users");
        $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Nombre total de clubs validés
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_club WHERE validation_finale = 1");
        $stats['total_clubs'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Nombre total d'événements validés
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_event WHERE validation_finale = 1");
        $stats['total_events'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Clubs en attente de validation finale (déjà validés par tuteur)
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_club WHERE (validation_finale IS NULL OR validation_finale = 0) AND validation_tuteur = 1");
        $stats['pending_clubs'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Événements en attente de validation finale (validés par BDE ET tuteur ou admin)
        // La validation tuteur est optionnelle si admin a validé (tuteur peut être absent)
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_event WHERE validation_finale IS NULL AND validation_bde = 1 AND (validation_tuteur = 1 OR validation_admin = 1)");
        $stats['pending_events'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Total des éléments en attente
        $stats['total_pending'] = $stats['pending_clubs'] + $stats['pending_events'];

        // Répartition des utilisateurs par niveau de permission
        $stmt = $this->db->query("SELECT permission, COUNT(*) as count FROM users GROUP BY permission ORDER BY permission");
        $stats['users_by_permission'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Répartition des clubs par campus
        $stmt = $this->db->query("SELECT campus, COUNT(*) as count FROM fiche_club WHERE validation_finale = 1 GROUP BY campus");
        $stats['clubs_by_campus'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Statistiques avancées pour les Super Admins uniquement (permission 5)
        if (($_SESSION['permission'] ?? 0) == 5) {
            // Total des inscriptions aux événements (table abonnements)
            try {
                $stmt = $this->db->query("SELECT COUNT(*) as count FROM abonnements");
                $stats['total_subscriptions'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            } catch (Exception $e) {
                $stats['total_subscriptions'] = 0;
            }
            
            // Nombre de membres de clubs validés
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM membres_club WHERE valide = 1");
            $stats['total_club_members'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            // Éléments rejetés
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
            
            // Événements à venir (30 prochains jours)
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_event WHERE validation_finale = 1 AND date_ev BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY)");
            $stats['upcoming_events'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            // Configuration système
            try {
                $stmt = $this->db->query("SELECT * FROM config LIMIT 1");
                $stats['config'] = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $stats['config'] = ['creation_club_active' => 1];
            }
        }
        
        // Événements par mois (6 derniers mois)
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
        
        // Activités récentes (10 dernières)
        $recent_activities = [];

        // Derniers clubs créés
        $stmt = $this->db->query("
            SELECT 'club' as type, nom_club as title, campus, club_id as sort_id 
            FROM fiche_club 
            ORDER BY club_id DESC 
            LIMIT 5
        ");
        $recent_clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Derniers événements créés
        $stmt = $this->db->query("
            SELECT 'event' as type, titre as title, campus, date_ev as date, event_id as sort_id 
            FROM fiche_event 
            ORDER BY event_id DESC 
            LIMIT 5
        ");
        $recent_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fusionner et trier par ID (plus récent en premier)
        $recent_activities = array_merge($recent_clubs, $recent_events);
        usort($recent_activities, function($a, $b) {
            if (isset($a['date']) && isset($b['date'])) {
                return strtotime($b['date']) - strtotime($a['date']);
            }
            return $b['sort_id'] - $a['sort_id'];
        });
        $recent_activities = array_slice($recent_activities, 0, 8);
        
        // Éléments en attente pour actions rapides
        $stmt = $this->db->query("
            SELECT club_id, nom_club, type_club, campus 
            FROM fiche_club 
            WHERE (validation_finale IS NULL OR validation_finale = 0) AND validation_tuteur = 1 
            LIMIT 5
        ");
        $pending_clubs_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Événements en attente : BDE validé ET (tuteur OU admin validé)
        $stmt = $this->db->query("
            SELECT e.event_id, e.titre, e.campus, e.date_ev, c.nom_club
            FROM fiche_event e
            LEFT JOIN fiche_club c ON e.club_orga = c.club_id
            WHERE e.validation_finale IS NULL AND e.validation_bde = 1 AND (e.validation_tuteur = 1 OR e.validation_admin = 1)
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
    // SECTION PARAMÈTRES (Permission 5 - Super Admin)
    // ==========================================

    /**
     * Page des paramètres d'administration
     * Accessible uniquement aux Super Admins (permission 5)
     *
     * Fonctionnalités :
     * - Activer/désactiver la création de clubs
     * - Activer/désactiver la création d'événements
     * - Mode maintenance
     * - Effacer les logs d'erreur
     * - Validation en masse des clubs et événements
     * - Nettoyage des anciens événements
     *
     * @return array Données pour la vue des paramètres
     */
    public function settings() {
        checkPermission(5);
        
        $success_msg = '';
        $error_msg = '';
        
        // Récupérer la configuration actuelle
        try {
            $stmt = $this->db->query("SELECT * FROM config LIMIT 1");
            $config = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $config = ['creation_club_active' => 1, 'creation_event_active' => 1, 'maintenance_mode' => 0];
        }
        
        // Traitement du formulaire de mise à jour
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Mise à jour des paramètres principaux
            if (isset($_POST['update_settings'])) {
                $creation_club_active = isset($_POST['creation_club_active']) ? 1 : 0;
                $creation_event_active = isset($_POST['creation_event_active']) ? 1 : 0;
                $maintenance_mode = isset($_POST['maintenance_mode']) ? 1 : 0;
                
                try {
                    // S'assurer que les colonnes existent (ajout si nécessaire)
                    try {
                        $this->db->exec("ALTER TABLE config ADD COLUMN IF NOT EXISTS creation_event_active TINYINT(1) NOT NULL DEFAULT 1");
                        $this->db->exec("ALTER TABLE config ADD COLUMN IF NOT EXISTS maintenance_mode TINYINT(1) NOT NULL DEFAULT 0");
                    } catch (Exception $e) {
                        // Colonnes déjà existantes ou non supporté
                    }
                    
                    $stmt = $this->db->prepare("UPDATE config SET creation_club_active = ?, creation_event_active = ?, maintenance_mode = ?");
                    $stmt->execute([$creation_club_active, $creation_event_active, $maintenance_mode]);
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
                    $this->db->query("UPDATE fiche_club SET validation_finale = 1 WHERE (validation_finale IS NULL OR validation_finale = 0) AND validation_tuteur = 1");
                    $success_msg = "Tous les clubs en attente ont été validés.";
                } catch (Exception $e) {
                    $error_msg = "Erreur lors de la validation des clubs.";
                }
            }
            
            // Validation en masse de tous les événements en attente
            // Valide les events avec BDE + (tuteur OU admin)
            if (isset($_POST['bulk_validate_events'])) {
                try {
                    $this->db->query("UPDATE fiche_event SET validation_finale = 1 WHERE validation_finale IS NULL AND validation_bde = 1 AND (validation_tuteur = 1 OR validation_admin = 1)");
                    $success_msg = "Tous les événements en attente ont été validés.";
                } catch (Exception $e) {
                    $error_msg = "Erreur lors de la validation des événements.";
                }
            }
            
            // Identification des anciens événements pour archivage
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $success_msg !== '') {
            redirect($_SERVER['REQUEST_URI']);
        }
        
        // Récupérer les 50 dernières lignes du log d'erreur
        $error_logs = [];
        $logFile = LOGS_PATH . '/error.log';
        if (file_exists($logFile)) {
            $lines = file($logFile);
            $error_logs = array_slice($lines, -50);
            $error_logs = array_reverse($error_logs);
        }
        
        // Statistiques de la base de données (nombre d'enregistrements par table)
        $db_stats = [];
        try {
            $tables = ['users', 'fiche_club', 'fiche_event', 'membres_club', 'abonnements'];
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
        
        // Statistiques avancées
        $advanced_stats = [];

        // Comptage des éléments en attente
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_club WHERE (validation_finale IS NULL OR validation_finale = 0) AND validation_tuteur = 1");
        $advanced_stats['pending_clubs'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Événements : BDE validé ET (tuteur OU admin validé)
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_event WHERE validation_finale IS NULL AND validation_bde = 1 AND (validation_tuteur = 1 OR validation_admin = 1)");
        $advanced_stats['pending_events'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Comptage des éléments rejetés
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_club WHERE validation_finale = -1");
        $advanced_stats['rejected_clubs'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_event WHERE validation_finale = -1");
        $advanced_stats['rejected_events'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Événements de plus d'un an
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_event WHERE date_ev < DATE_SUB(NOW(), INTERVAL 1 YEAR)");
            $advanced_stats['old_events'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (Exception $e) {
            $advanced_stats['old_events'] = 0;
        }
        
        // Événements passés sans rapport soumis (rapport stocké dans fiche_event.rapport_event)
        try {
            $stmt = $this->db->query("
                SELECT COUNT(*) as count FROM fiche_event 
                WHERE validation_finale = 1 
                  AND date_ev < NOW() 
                  AND (rapport_event IS NULL OR rapport_event = '')
            ");
            $advanced_stats['events_no_report'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (Exception $e) {
            $advanced_stats['events_no_report'] = 0;
        }
        
        // Répartition des utilisateurs par permission
        $stmt = $this->db->query("SELECT permission, COUNT(*) as count FROM users GROUP BY permission ORDER BY permission");
        $advanced_stats['users_by_permission'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 10 derniers utilisateurs inscrits
        $stmt = $this->db->query("SELECT id, nom, prenom, mail, permission FROM users ORDER BY id DESC LIMIT 10");
        $advanced_stats['recent_users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Informations système du serveur
        $isProduction = Environment::isProduction();
        $system_info = [
            'php_version' => phpversion(),
            'server_software' => $isProduction ? 'Hidden in production' : ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'),
            'memory_limit' => ini_get('memory_limit'),
            'max_upload' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'max_execution_time' => ini_get('max_execution_time') . 's',
            'timezone' => date_default_timezone_get(),
            'document_root' => $isProduction ? 'Hidden in production' : ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'),
        ];
        
        // Calcul de l'espace disque utilisé par les uploads
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
    // SECTION EXPORT DE DONNÉES (Permission 5)
    // ==========================================

    /**
     * Exporte les données de la plateforme en CSV
     * Accessible uniquement aux Super Admins (permission 5)
     *
     * Types d'export disponibles :
     * - users : Liste des utilisateurs
     * - clubs : Liste des clubs
     * - events : Liste des événements
     * - subscriptions : Inscriptions aux événements
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
                // Export de la liste des événements
                $stmt = $this->db->query("SELECT event_id, titre, date_ev, campus, validation_finale FROM fiche_event ORDER BY event_id");
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $filename = 'events_export_' . date('Y-m-d') . '.csv';
                $headers = ['ID', 'Titre', 'Date', 'Campus', 'Statut validation'];
                break;
            
            case 'subscriptions':
                // Export des inscriptions aux événements avec détails utilisateur et événement
                // Table abonnements: id = user_id, event_id, date_abonnement
                $stmt = $this->db->query("
                    SELECT a.id as user_id, u.nom, u.prenom, u.mail, fe.titre, fe.date_ev, a.date_abonnement
                    FROM abonnements a
                    JOIN users u ON a.id = u.id
                    JOIN fiche_event fe ON a.event_id = fe.event_id
                    ORDER BY a.date_abonnement DESC
                ");
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $filename = 'subscriptions_export_' . date('Y-m-d') . '.csv';
                $headers = ['User ID', 'Nom', 'Prénom', 'Email', 'Événement', 'Date événement', 'Date inscription'];
                break;
            
            case 'members':
                // Export des membres de clubs avec détails
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
        
        // En-têtes HTTP pour le téléchargement CSV
        // Nettoyage du buffer avant les headers
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
        fputcsv($tmp, $headers, "\t", '"', "\0");

        foreach ($data as $row) {
            $cleaned = array_map(static function ($v) {
                return str_replace(["\r\n", "\r", "\n"], ' ', trim((string)($v ?? '')));
            }, array_values($row));
            fputcsv($tmp, $cleaned, "\t", '"', "\0");
        }

        // Étape 2 : lire le CSV UTF-8 depuis le buffer
        rewind($tmp);
        $csv = stream_get_contents($tmp);
        fclose($tmp);

        // Étape 3 : BOM UTF-16 LE + conversion UTF-8 → UTF-16 LE
        echo "\xFF\xFE" . mb_convert_encoding($csv, 'UTF-16LE', 'UTF-8');
        
        fclose($output);
        exit;
    }

    // ==========================================
    // SECTION ANALYTIQUES ÉVÉNEMENTS (Permission 3+)
    // ==========================================

    /**
     * Analytiques des événements
     * Pour le BDE et les administrateurs (permission 3+)
     *
     * Métriques affichées :
     * - Total des événements validés
     * - Répartition par campus et par mois
     * - Événements les plus populaires (par inscriptions)
     * - Événements à venir (30 prochains jours)
     * - Événements sans rapport soumis
     * - Classement des clubs par activité
     *
     * @return array Données statistiques pour la vue
     */
    public function eventAnalytics() {
        checkPermission(3);
        
        $stats = [];
        
        // Total des événements validés
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_event WHERE validation_finale = 1");
        $stats['total_events'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Répartition des événements par campus
        $stmt = $this->db->query("
            SELECT campus, COUNT(*) as count 
            FROM fiche_event 
            WHERE validation_finale = 1 
            GROUP BY campus 
            ORDER BY count DESC
        ");
        $stats['by_campus'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Événements par mois (12 derniers mois)
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
        
        // Top 10 des événements les plus populaires (par nombre d'inscriptions)
        try {
            $stmt = $this->db->query("
                SELECT fe.event_id, fe.titre, fe.date_ev, fe.campus, fc.nom_club,
                    COUNT(a.event_id) as subscription_count
                FROM fiche_event fe
                LEFT JOIN abonnements a ON fe.event_id = a.event_id
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
        
        // Total des inscriptions aux événements (table abonnements)
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM abonnements");
            $stats['total_subscriptions'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (Exception $e) {
            $stats['total_subscriptions'] = 0;
        }

        // Événements à venir (30 prochains jours) avec compteur d'inscriptions
        $stmt = $this->db->query("
            SELECT fe.*, fc.nom_club,
                (SELECT COUNT(*) FROM abonnements a WHERE a.event_id = fe.event_id) as subscription_count
            FROM fiche_event fe
            LEFT JOIN fiche_club fc ON fe.club_orga = fc.club_id
            WHERE fe.validation_finale = 1 
                AND fe.date_ev BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY)
            ORDER BY fe.date_ev ASC
        ");
        $stats['upcoming_events'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Événements passés sans rapport (30 derniers jours) - rapport stocké dans fiche_event.rapport_event
        try {
            $stmt = $this->db->query("
                SELECT fe.event_id, fe.titre, fe.date_ev, fc.nom_club
                FROM fiche_event fe
                LEFT JOIN fiche_club fc ON fe.club_orga = fc.club_id
                WHERE fe.validation_finale = 1 
                    AND fe.date_ev < NOW()
                    AND fe.date_ev >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    AND (fe.rapport_event IS NULL OR fe.rapport_event = '')
                ORDER BY fe.date_ev DESC
                LIMIT 5
            ");
            $stats['events_without_reports'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $stats['events_without_reports'] = [];
        }
        
        // Classement des clubs par nombre d'événements organisés
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
     * Liste tous les utilisateurs avec gestion avancée
     * Accessible uniquement aux Super Admins (permission 5)
     *
     * Fonctionnalités :
     * - Recherche par nom, prénom, email
     * - Filtres par permission et promotion
     * - Tri dynamique sur les colonnes
     * - Affichage du nombre de clubs et inscriptions par utilisateur
     *
     * @return array Données pour la vue liste utilisateurs
     */
    public function listUsers() {
        checkPermission(5);
        
        // Récupération des paramètres de recherche et filtrage
        $search = $_GET['search'] ?? '';
        $filter_permission = $_GET['permission'] ?? '';
        $filter_promo = $_GET['promo'] ?? '';
        $sort = $_GET['sort'] ?? 'id';
        $order = $_GET['order'] ?? 'DESC';
        
        // Construction de la requête avec sous-requêtes pour les compteurs
        // Table abonnements: id = user_id
        $query = "SELECT u.*, 
            (SELECT COUNT(*) FROM membres_club mc WHERE mc.membre_id = u.id AND mc.valide = 1) as clubs_count,
            (SELECT COUNT(*) FROM abonnements a WHERE a.id = u.id) as subscriptions_count
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
        
        // Validation de la colonne de tri (sécurité contre injection SQL)
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
     * Met à jour le niveau de permission d'un utilisateur
     * Accessible uniquement aux Super Admins (permission 5)
     * Protection : impossible de modifier sa propre permission
     *
     * @return void (redirection après traitement)
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
     * Supprime un utilisateur et toutes ses données associées
     * Accessible uniquement aux Super Admins (permission 5)
     * Protection : impossible de supprimer son propre compte
     *
     * Données supprimées :
     * - Adhésions aux clubs
     * - Inscriptions aux événements
     * - Compte utilisateur
     *
     * @return void (redirection après traitement)
     */
    public function deleteUser() {
        checkPermission(5);
        
        // Require POST method for destructive action
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?page=admin-users');
            return;
        }
        
        $user_id = $_POST['id'] ?? null;
        
        // Protection contre l'auto-suppression
        if ($user_id && $user_id != $_SESSION['id']) {
            // Suppression des adhésions aux clubs
            $stmt = $this->db->prepare("DELETE FROM membres_club WHERE membre_id = ?");
            $stmt->execute([$user_id]);
            
            // Suppression des inscriptions aux événements (table abonnements, id = user_id)
            try {
                $stmt = $this->db->prepare("DELETE FROM abonnements WHERE id = ?");
                $stmt->execute([$user_id]);
            } catch (Exception $e) {}
            
            // Suppression du compte utilisateur
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
        }
        
        redirect('index.php?page=admin-users');
    }

    /**
     * Affiche les détails d'un utilisateur
     * Accessible uniquement aux Super Admins (permission 5)
     *
     * Informations affichées :
     * - Données du profil
     * - Clubs rejoints
     * - Inscriptions aux événements
     *
     * @return array Données pour la vue détail utilisateur
     */
    public function viewUser() {
        checkPermission(5);
        
        $user_id = $_GET['id'] ?? null;
        if (!$user_id) {
            redirect('index.php?page=admin-users');
        }
        
        // Récupération des informations de l'utilisateur
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
        
        // Inscriptions aux événements (table abonnements, id = user_id)
        try {
            $stmt = $this->db->prepare("
                SELECT fe.*, fc.nom_club, a.date_abonnement
                FROM abonnements a
                JOIN fiche_event fe ON a.event_id = fe.event_id
                LEFT JOIN fiche_club fc ON fe.club_orga = fc.club_id
                WHERE a.id = ?
                ORDER BY fe.date_ev DESC
            ");
            $stmt->execute([$user_id]);
            $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $subscriptions = [];
        }
        
        // Journal d'activité (pour extension future)
        $activity = [];
        
        return [
            'user' => $user,
            'clubs' => $clubs,
            'subscriptions' => $subscriptions,
            'activity' => $activity
        ];
    }

    // ==========================================
    // SECTION AUDIT ET SÉCURITÉ (Permission 5)
    // ==========================================

    /**
     * Journal d'audit de sécurité
     * Affiche les tentatives de connexion et les erreurs système
     * Accessible uniquement aux Super Admins (permission 5)
     *
     * @return array Données pour la vue d'audit
     */
    public function auditLog() {
        checkPermission(5);
        
        // Lecture du fichier de log de sécurité (100 dernières lignes)
        $login_attempts = [];
        $securityLogFile = LOGS_PATH . '/security.log';
        if (file_exists($securityLogFile)) {
            $lines = file($securityLogFile);
            $login_attempts = array_slice($lines, -100);
            $login_attempts = array_reverse($login_attempts);
        }
        
        // Lecture du fichier de log d'erreurs (100 dernières lignes)
        $error_logs = [];
        $errorLogFile = LOGS_PATH . '/error.log';
        if (file_exists($errorLogFile)) {
            $lines = file($errorLogFile);
            $error_logs = array_slice($lines, -100);
            $error_logs = array_reverse($error_logs);
        }
        
        // Statistiques de sécurité
        $stats = [];

        // Nombre d'événements de sécurité enregistrés
        $stats['security_events'] = count($login_attempts);
        $stats['error_count'] = count($error_logs);
        
        // Utilisateurs avec privilèges élevés (permission 3+)
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM users WHERE permission >= 3");
        $stats['privileged_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        return [
            'login_attempts' => $login_attempts,
            'error_logs' => $error_logs,
            'stats' => $stats
        ];
    }

    // ==========================================
    // SECTION GESTION BASE DE DONNÉES (Permission 5)
    // ==========================================

    /**
     * Outils de maintenance et nettoyage de la base de données
     * Accessible uniquement aux Super Admins (permission 5)
     *
     * Fonctionnalités :
     * - Nettoyage des enregistrements orphelins
     * - Archivage des anciens événements
     * - Statistiques par table
     * - Détection des problèmes
     *
     * @return array Données pour la vue de maintenance
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
                    
                    // Suppression des inscriptions pour des événements inexistants
                    $stmt = $this->db->query("DELETE a FROM abonnements a LEFT JOIN fiche_event fe ON a.event_id = fe.event_id WHERE fe.event_id IS NULL");
                    
                    // Suppression des inscriptions pour des utilisateurs inexistants
                    $stmt = $this->db->query("DELETE a FROM abonnements a LEFT JOIN users u ON a.id = u.id WHERE u.id IS NULL");
                    
                    $success_msg = "Nettoyage des enregistrements orphelins effectué.";
                } catch (Exception $e) {
                    $error_msg = "Erreur lors du nettoyage: " . $e->getMessage();
                }
            }
            
            // Archivage des anciens événements (plus d'un an)
            if (isset($_POST['archive_old_events'])) {
                try {
                    // Marquage avec validation_finale = -2 pour distinguer des rejetés (-1)
                    $stmt = $this->db->query("UPDATE fiche_event SET validation_finale = -2 WHERE date_ev < DATE_SUB(NOW(), INTERVAL 1 YEAR) AND validation_finale = 1");
                    $count = $stmt->rowCount();
                    $success_msg = "$count événements archivés.";
                } catch (Exception $e) {
                    $error_msg = "Erreur lors de l'archivage.";
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $success_msg !== '') {
            redirect($_SERVER['REQUEST_URI']);
        }
        
        // Statistiques de chaque table (tables existantes dans la BD)
        $db_stats = [];
        $tables = ['users', 'fiche_club', 'fiche_event', 'membres_club', 'abonnements', 'mails', 'config', 'ville'];
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
        
        // Détection des problèmes potentiels
        $issues = [];
        
        // Membres de club orphelins (club supprimé mais membre toujours présent)
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM membres_club mc LEFT JOIN fiche_club fc ON mc.club_id = fc.club_id WHERE fc.club_id IS NULL");
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            if ($count > 0) {
                $issues[] = "$count membres de club orphelins";
            }
        } catch (Exception $e) {}
        
        // Événements de plus d'un an non archivés
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
     * Génération de rapports de la plateforme
     * Pour le BDE et les administrateurs (permission 3+)
     *
     * Types de rapports :
     * - monthly : Résumé mensuel (événements, clubs, inscriptions)
     * - clubs : Performance des clubs (membres, événements organisés)
     * - users : Engagement utilisateurs par promotion
     *
     * @return array Données du rapport sélectionné
     */
    public function generateReport() {
        checkPermission(3);
        
        $report_type = $_GET['type'] ?? 'monthly';
        $report_data = [];
        
        switch ($report_type) {
            case 'monthly':
                // Résumé mensuel
                $month = $_GET['month'] ?? date('Y-m');

                // Statistiques des événements du mois
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as total_events,
                        SUM(CASE WHEN validation_finale = 1 THEN 1 ELSE 0 END) as validated,
                        SUM(CASE WHEN validation_finale = -1 THEN 1 ELSE 0 END) as rejected
                    FROM fiche_event 
                    WHERE DATE_FORMAT(date_ev, '%Y-%m') = ?
                ");
                $stmt->execute([$month]);
                $report_data['events'] = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Nombre total de clubs (pas de colonne date_creation dans fiche_club)
                $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_club WHERE validation_finale = 1");
                $report_data['new_clubs'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
                
                // Inscriptions aux événements du mois
                try {
                    $stmt = $this->db->prepare("
                        SELECT COUNT(*) as count 
                        FROM abonnements a
                        JOIN fiche_event fe ON a.event_id = fe.event_id
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

    /**
     * Consultation des rapports d'événements déposés
     * Affiche la liste des événements avec rapports soumis
     * 
     * @return array Données pour la vue de consultation des rapports
     */
    public function eventReports() {
        checkPermission(2);
        
        $eventReportModel = new EventReport($this->db);
        
        // Récupérer les événements avec rapports
        $eventsWithReports = $eventReportModel->getEventsWithReports();
        
        // Récupérer les événements sans rapports (pour statistiques)
        $eventsWithoutReports = $eventReportModel->getEventsWithoutReports();
        
        // Statistiques
        $stats = [
            'total_with_reports' => count($eventsWithReports),
            'total_without_reports' => count($eventsWithoutReports),
            'completion_rate' => 0
        ];
        
        $totalPastEvents = $stats['total_with_reports'] + $stats['total_without_reports'];
        if ($totalPastEvents > 0) {
            $stats['completion_rate'] = round(($stats['total_with_reports'] / $totalPastEvents) * 100, 1);
        }
        
        return [
            'events_with_reports' => $eventsWithReports,
            'events_without_reports' => $eventsWithoutReports,
            'stats' => $stats
        ];
    }
}
