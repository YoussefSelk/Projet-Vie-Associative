<?php

class HomeController {
    private $eventModel;
    private $clubModel;
    private $db;

    public function __construct($database) {
        $this->db = $database;
        $this->eventModel = new Event($database);
        $this->clubModel = new Club($database);
    }

    public function index() {
        if (isset($_SESSION['id'])) {
            $events = $this->eventModel->getAllValidatedEvents();
            $clubs = $this->clubModel->getAllValidatedClubs();
        } else {
            $events = [];
            $clubs = [];
        }

        return [
            'events' => $events,
            'clubs' => $clubs
        ];
    }

    public function admin() {
        checkPermission(3);
        
        // Get statistics
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
        
        // Recent clubs (use club_id for ordering since no date column exists)
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
            // Use date if available, otherwise use sort_id
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
}
