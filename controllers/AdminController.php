<?php

/**
 * AdminController - Centralized admin functionality
 * Handles all administration tasks for Super Admins (permission 5) and BDE (permission 3+)
 */
class AdminController {
    private $db;
    private $eventModel;
    private $clubModel;
    private $userModel;

    public function __construct($database) {
        $this->db = $database;
        $this->eventModel = new Event($database);
        $this->clubModel = new Club($database);
        $this->userModel = new User($database);
    }

    // ==========================================
    // DASHBOARD SECTION (Permission 3+)
    // ==========================================

    /**
     * Main admin dashboard with statistics and overview
     */
    public function dashboard() {
        checkPermission(3);
        
        $stats = [];
        
        // Total users
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM users");
        $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Total validated clubs
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_club WHERE validation_finale = 1");
        $stats['total_clubs'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Total validated events
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_event WHERE validation_finale = 1");
        $stats['total_events'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Pending clubs
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_club WHERE validation_finale IS NULL AND validation_tuteur = 1");
        $stats['pending_clubs'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Pending events
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_event WHERE validation_finale IS NULL AND validation_tuteur = 1 AND validation_bde = 1");
        $stats['pending_events'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Total pending (clubs + events)
        $stats['total_pending'] = $stats['pending_clubs'] + $stats['pending_events'];
        
        // Users by permission level
        $stmt = $this->db->query("SELECT permission, COUNT(*) as count FROM users GROUP BY permission ORDER BY permission");
        $stats['users_by_permission'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Clubs by campus
        $stmt = $this->db->query("SELECT campus, COUNT(*) as count FROM fiche_club WHERE validation_finale = 1 GROUP BY campus");
        $stats['clubs_by_campus'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Advanced stats for admins (permission 5)
        if (($_SESSION['permission'] ?? 0) == 5) {
            // Total subscriptions
            try {
                $stmt = $this->db->query("SELECT COUNT(*) as count FROM subscribe_event");
                $stats['total_subscriptions'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            } catch (Exception $e) {
                $stats['total_subscriptions'] = 0;
            }
            
            // Club members count
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM membres_club WHERE valide = 1");
            $stats['total_club_members'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Rejected items
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_club WHERE validation_finale = -1");
            $stats['rejected_clubs'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_event WHERE validation_finale = -1");
            $stats['rejected_events'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Recent user registrations (last 7 days)
            try {
                $stmt = $this->db->query("SELECT COUNT(*) as count FROM users WHERE date_inscription >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
                $stats['new_users_week'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            } catch (Exception $e) {
                $stats['new_users_week'] = 0;
            }
            
            // Upcoming events (next 30 days)
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_event WHERE validation_finale = 1 AND date_ev BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY)");
            $stats['upcoming_events'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Get system config
            try {
                $stmt = $this->db->query("SELECT * FROM config LIMIT 1");
                $stats['config'] = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $stats['config'] = ['creation_club_active' => 1];
            }
        }
        
        // Events by month (last 6 months)
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
        
        // Recent activities (last 10)
        $recent_activities = [];
        
        // Recent clubs
        $stmt = $this->db->query("
            SELECT 'club' as type, nom_club as title, campus, club_id as sort_id 
            FROM fiche_club 
            ORDER BY club_id DESC 
            LIMIT 5
        ");
        $recent_clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Recent events
        $stmt = $this->db->query("
            SELECT 'event' as type, titre as title, campus, date_ev as date, event_id as sort_id 
            FROM fiche_event 
            ORDER BY event_id DESC 
            LIMIT 5
        ");
        $recent_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Merge and sort by id (most recent first)
        $recent_activities = array_merge($recent_clubs, $recent_events);
        usort($recent_activities, function($a, $b) {
            if (isset($a['date']) && isset($b['date'])) {
                return strtotime($b['date']) - strtotime($a['date']);
            }
            return $b['sort_id'] - $a['sort_id'];
        });
        $recent_activities = array_slice($recent_activities, 0, 8);
        
        // Get pending items for quick action
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
    // SETTINGS SECTION (Permission 5 - Super Admin)
    // ==========================================

    /**
     * Admin settings page - Super Admin only (permission 5)
     */
    public function settings() {
        checkPermission(5);
        
        $success_msg = '';
        $error_msg = '';
        
        // Get current config
        try {
            $stmt = $this->db->query("SELECT * FROM config LIMIT 1");
            $config = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $config = ['creation_club_active' => 1, 'creation_event_active' => 1, 'maintenance_mode' => 0];
        }
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            
            // Clear error logs
            if (isset($_POST['clear_logs'])) {
                $logFile = LOGS_PATH . '/error.log';
                if (file_exists($logFile)) {
                    file_put_contents($logFile, '');
                    $success_msg = "Logs effacés avec succès.";
                }
            }
            
            // Bulk actions
            if (isset($_POST['bulk_validate_clubs'])) {
                try {
                    $this->db->query("UPDATE fiche_club SET validation_finale = 1 WHERE validation_finale IS NULL AND validation_tuteur = 1");
                    $success_msg = "Tous les clubs en attente ont été validés.";
                } catch (Exception $e) {
                    $error_msg = "Erreur lors de la validation des clubs.";
                }
            }
            
            if (isset($_POST['bulk_validate_events'])) {
                try {
                    $this->db->query("UPDATE fiche_event SET validation_finale = 1 WHERE validation_finale IS NULL AND validation_tuteur = 1 AND validation_bde = 1");
                    $success_msg = "Tous les événements en attente ont été validés.";
                } catch (Exception $e) {
                    $error_msg = "Erreur lors de la validation des événements.";
                }
            }
            
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
        
        // Get error logs (last 50 lines)
        $error_logs = [];
        $logFile = LOGS_PATH . '/error.log';
        if (file_exists($logFile)) {
            $lines = file($logFile);
            $error_logs = array_slice($lines, -50);
            $error_logs = array_reverse($error_logs);
        }
        
        // Get database stats
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
            // Ignore
        }
        
        // Get advanced stats
        $advanced_stats = [];
        
        // Pending counts
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_club WHERE validation_finale IS NULL AND validation_tuteur = 1");
        $advanced_stats['pending_clubs'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_event WHERE validation_finale IS NULL AND validation_tuteur = 1 AND validation_bde = 1");
        $advanced_stats['pending_events'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Rejected counts
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_club WHERE validation_finale = -1");
        $advanced_stats['rejected_clubs'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_event WHERE validation_finale = -1");
        $advanced_stats['rejected_events'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Old events
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_event WHERE date_ev < DATE_SUB(NOW(), INTERVAL 1 YEAR)");
            $advanced_stats['old_events'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (Exception $e) {
            $advanced_stats['old_events'] = 0;
        }
        
        // Events without reports
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
        
        // Users by permission
        $stmt = $this->db->query("SELECT permission, COUNT(*) as count FROM users GROUP BY permission ORDER BY permission");
        $advanced_stats['users_by_permission'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Recent users (last 10)
        $stmt = $this->db->query("SELECT id, nom, prenom, mail, permission FROM users ORDER BY id DESC LIMIT 10");
        $advanced_stats['recent_users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get system info
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
        
        // Disk usage
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
    // DATA EXPORT SECTION (Permission 5)
    // ==========================================

    /**
     * Export all data as CSV - Super Admin only
     */
    public function exportData() {
        checkPermission(5);
        
        $type = $_GET['type'] ?? 'users';
        
        switch ($type) {
            case 'users':
                $stmt = $this->db->query("SELECT id, nom, prenom, mail, promo, permission FROM users ORDER BY id");
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $filename = 'users_export_' . date('Y-m-d') . '.csv';
                $headers = ['ID', 'Nom', 'Prénom', 'Email', 'Promo', 'Permission'];
                break;
                
            case 'clubs':
                $stmt = $this->db->query("SELECT club_id, nom_club, type_club, campus, validation_finale FROM fiche_club ORDER BY club_id");
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $filename = 'clubs_export_' . date('Y-m-d') . '.csv';
                $headers = ['ID', 'Nom du club', 'Type', 'Campus', 'Statut validation'];
                break;
                
            case 'events':
                $stmt = $this->db->query("SELECT event_id, titre, date_ev, campus, validation_finale FROM fiche_event ORDER BY event_id");
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $filename = 'events_export_' . date('Y-m-d') . '.csv';
                $headers = ['ID', 'Titre', 'Date', 'Campus', 'Statut validation'];
                break;
            
            case 'subscriptions':
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
        
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // BOM for UTF-8 Excel
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
    // EVENT ANALYTICS SECTION (Permission 3+)
    // ==========================================

    /**
     * Event Analytics - For BDE and higher (permission >= 3)
     * Shows subscription stats, popular events, trends
     */
    public function eventAnalytics() {
        checkPermission(3);
        
        $stats = [];
        
        // Total validated events
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM fiche_event WHERE validation_finale = 1");
        $stats['total_events'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Events by campus
        $stmt = $this->db->query("
            SELECT campus, COUNT(*) as count 
            FROM fiche_event 
            WHERE validation_finale = 1 
            GROUP BY campus 
            ORDER BY count DESC
        ");
        $stats['by_campus'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Events by month (last 12 months)
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
        
        // Most popular events (by subscriptions)
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
        
        // Total subscriptions
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM subscribe_event");
            $stats['total_subscriptions'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (Exception $e) {
            $stats['total_subscriptions'] = 0;
        }
        
        // Upcoming events (next 30 days)
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
        
        // Events needing attention (past events without reports)
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
        
        // Club activity ranking
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
    // USER MANAGEMENT SECTION (Permission 5)
    // ==========================================

    /**
     * List all users with advanced management (Super Admin only)
     */
    public function listUsers() {
        checkPermission(5);
        
        // Get search/filter parameters
        $search = $_GET['search'] ?? '';
        $filter_permission = $_GET['permission'] ?? '';
        $filter_promo = $_GET['promo'] ?? '';
        $sort = $_GET['sort'] ?? 'id';
        $order = $_GET['order'] ?? 'DESC';
        
        // Build query
        $query = "SELECT u.*, 
            (SELECT COUNT(*) FROM membres_club mc WHERE mc.membre_id = u.id AND mc.valide = 1) as clubs_count,
            (SELECT COUNT(*) FROM subscribe_event se WHERE se.user_id = u.id) as subscriptions_count
            FROM users u WHERE 1=1";
        $params = [];
        
        if ($search) {
            $query .= " AND (u.nom LIKE ? OR u.prenom LIKE ? OR u.mail LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if ($filter_permission !== '') {
            $query .= " AND u.permission = ?";
            $params[] = $filter_permission;
        }
        
        if ($filter_promo) {
            $query .= " AND u.promo = ?";
            $params[] = $filter_promo;
        }
        
        // Validate sort column
        $allowed_sorts = ['id', 'nom', 'prenom', 'mail', 'promo', 'permission'];
        if (!in_array($sort, $allowed_sorts)) $sort = 'id';
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        
        $query .= " ORDER BY u.$sort $order";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get stats
        $stats = [];
        $stmt = $this->db->query("SELECT permission, COUNT(*) as count FROM users GROUP BY permission ORDER BY permission");
        $stats['by_permission'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get available promos for filter
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
     * Update user permission (Super Admin only)
     */
    public function updatePermission() {
        checkPermission(5);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?page=admin-users');
        }
        
        $user_id = $_POST['user_id'] ?? null;
        $new_permission = $_POST['permission'] ?? null;
        
        if ($user_id && $new_permission !== null && $new_permission >= 0 && $new_permission <= 5) {
            // Can't change own permission
            if ($user_id != $_SESSION['id']) {
                $stmt = $this->db->prepare("UPDATE users SET permission = ? WHERE id = ?");
                $stmt->execute([$new_permission, $user_id]);
            }
        }
        
        // Redirect back to referrer or admin users page
        $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php?page=admin-users';
        redirect($referer);
    }

    /**
     * Delete user (Super Admin only)
     */
    public function deleteUser() {
        checkPermission(5);
        
        $user_id = $_GET['id'] ?? null;
        
        if ($user_id && $user_id != $_SESSION['id']) {
            // Remove from club memberships
            $stmt = $this->db->prepare("DELETE FROM membres_club WHERE membre_id = ?");
            $stmt->execute([$user_id]);
            
            // Remove subscriptions
            try {
                $stmt = $this->db->prepare("DELETE FROM subscribe_event WHERE user_id = ?");
                $stmt->execute([$user_id]);
            } catch (Exception $e) {}
            
            // Delete user
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
        }
        
        redirect('index.php?page=admin-users');
    }

    /**
     * View user details (Super Admin only)
     */
    public function viewUser() {
        checkPermission(5);
        
        $user_id = $_GET['id'] ?? null;
        if (!$user_id) {
            redirect('index.php?page=admin-users');
        }
        
        // Get user details
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            redirect('index.php?page=admin-users');
        }
        
        // Get user's clubs
        $stmt = $this->db->prepare("
            SELECT fc.*
            FROM membres_club mc
            JOIN fiche_club fc ON mc.club_id = fc.club_id
            WHERE mc.membre_id = ?
        ");
        $stmt->execute([$user_id]);
        $clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get user's subscriptions
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
        
        // Get user's activity log (if exists)
        $activity = [];
        
        return [
            'user' => $user,
            'clubs' => $clubs,
            'subscriptions' => $subscriptions,
            'activity' => $activity
        ];
    }

    // ==========================================
    // AUDIT & SECURITY SECTION (Permission 5)
    // ==========================================

    /**
     * Security audit log
     */
    public function auditLog() {
        checkPermission(5);
        
        // Get login attempts log if exists
        $login_attempts = [];
        $securityLogFile = LOGS_PATH . '/security.log';
        if (file_exists($securityLogFile)) {
            $lines = file($securityLogFile);
            $login_attempts = array_slice($lines, -100);
            $login_attempts = array_reverse($login_attempts);
        }
        
        // Get recent errors
        $error_logs = [];
        $errorLogFile = LOGS_PATH . '/error.log';
        if (file_exists($errorLogFile)) {
            $lines = file($errorLogFile);
            $error_logs = array_slice($lines, -100);
            $error_logs = array_reverse($error_logs);
        }
        
        // Security stats
        $stats = [];
        
        // Failed login attempts (last 24h) - if we track them
        $stats['security_events'] = count($login_attempts);
        $stats['error_count'] = count($error_logs);
        
        // Users with high permissions
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM users WHERE permission >= 3");
        $stats['privileged_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        return [
            'login_attempts' => $login_attempts,
            'error_logs' => $error_logs,
            'stats' => $stats
        ];
    }

    // ==========================================
    // DATABASE MANAGEMENT (Permission 5)
    // ==========================================

    /**
     * Database optimization and cleanup
     */
    public function databaseTools() {
        checkPermission(5);
        
        $success_msg = '';
        $error_msg = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Cleanup orphan records
            if (isset($_POST['cleanup_orphans'])) {
                try {
                    // Delete club members for non-existent clubs
                    $stmt = $this->db->query("DELETE mc FROM membres_club mc LEFT JOIN fiche_club fc ON mc.club_id = fc.club_id WHERE fc.club_id IS NULL");
                    
                    // Delete subscriptions for non-existent events
                    $stmt = $this->db->query("DELETE se FROM subscribe_event se LEFT JOIN fiche_event fe ON se.event_id = fe.event_id WHERE fe.event_id IS NULL");
                    
                    // Delete subscriptions for non-existent users
                    $stmt = $this->db->query("DELETE se FROM subscribe_event se LEFT JOIN users u ON se.user_id = u.id WHERE u.id IS NULL");
                    
                    $success_msg = "Nettoyage des enregistrements orphelins effectué.";
                } catch (Exception $e) {
                    $error_msg = "Erreur lors du nettoyage: " . $e->getMessage();
                }
            }
            
            // Archive old events
            if (isset($_POST['archive_old_events'])) {
                try {
                    // Mark old events as archived (using a negative validation or special status)
                    $stmt = $this->db->query("UPDATE fiche_event SET validation_finale = -2 WHERE date_ev < DATE_SUB(NOW(), INTERVAL 1 YEAR) AND validation_finale = 1");
                    $count = $stmt->rowCount();
                    $success_msg = "$count événements archivés.";
                } catch (Exception $e) {
                    $error_msg = "Erreur lors de l'archivage.";
                }
            }
        }
        
        // Get database stats
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
        
        // Check for potential issues
        $issues = [];
        
        // Orphan club members
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM membres_club mc LEFT JOIN fiche_club fc ON mc.club_id = fc.club_id WHERE fc.club_id IS NULL");
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            if ($count > 0) {
                $issues[] = "$count membres de club orphelins";
            }
        } catch (Exception $e) {}
        
        // Old events
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
    // REPORTS SECTION (Permission 3+)
    // ==========================================

    /**
     * Generate platform reports
     */
    public function generateReport() {
        checkPermission(3);
        
        $report_type = $_GET['type'] ?? 'monthly';
        $report_data = [];
        
        switch ($report_type) {
            case 'monthly':
                // Monthly summary
                $month = $_GET['month'] ?? date('Y-m');
                
                // Events this month
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as total_events,
                        SUM(CASE WHEN validation_finale = 1 THEN 1 ELSE 0 END) as validated,
                        SUM(CASE WHEN validation_finale = -1 THEN 1 ELSE 0 END) as rejected
                    FROM fiche_event 
                    WHERE DATE_FORMAT(date_ev, '%Y-%m') = ?
                ");
                $stmt->execute([$month]);
                $report_data['events'] = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // New clubs this month
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
                
                // Subscriptions this month
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
                // Club performance report
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
                // User engagement report
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
