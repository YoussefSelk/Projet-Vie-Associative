<?php

class EventSubscription {
    private $db;
    private $tableExists = null;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Check if the subscribe_event table exists, create it if not
     */
    private function ensureTableExists() {
        if ($this->tableExists === true) {
            return true;
        }
        
        try {
            $stmt = $this->db->query("SELECT 1 FROM subscribe_event LIMIT 1");
            $this->tableExists = true;
            return true;
        } catch (PDOException $e) {
            // Table doesn't exist, create it
            try {
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS subscribe_event (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        event_id INT NOT NULL,
                        user_id INT NOT NULL,
                        date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
                        UNIQUE KEY unique_subscription (event_id, user_id),
                        INDEX idx_event (event_id),
                        INDEX idx_user (user_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                $this->tableExists = true;
                return true;
            } catch (PDOException $e2) {
                error_log("Failed to create subscribe_event table: " . $e2->getMessage());
                return false;
            }
        }
    }

    public function getEventSubscribers($event_id) {
        if (!$this->ensureTableExists()) {
            return [];
        }
        
        $stmt = $this->db->prepare("SELECT se.*, u.nom, u.prenom FROM subscribe_event se 
                                    JOIN users u ON se.user_id = u.id 
                                    WHERE se.event_id = ?");
        $stmt->execute([$event_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserSubscriptions($user_id) {
        if (!$this->ensureTableExists()) {
            return [];
        }
        
        $stmt = $this->db->prepare("SELECT fe.* FROM fiche_event fe 
                                    JOIN subscribe_event se ON fe.event_id = se.event_id 
                                    WHERE se.user_id = ? AND fe.validation_finale = 1
                                    ORDER BY fe.date_ev DESC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function subscribeToEvent($event_id, $user_id) {
        if (!$this->ensureTableExists()) {
            return false;
        }
        
        // Check if already subscribed
        if ($this->isSubscribed($event_id, $user_id)) {
            return true;
        }
        
        $stmt = $this->db->prepare("INSERT INTO subscribe_event (event_id, user_id) VALUES (?, ?)");
        return $stmt->execute([$event_id, $user_id]);
    }

    public function unsubscribeFromEvent($event_id, $user_id) {
        if (!$this->ensureTableExists()) {
            return false;
        }
        
        $stmt = $this->db->prepare("DELETE FROM subscribe_event WHERE event_id = ? AND user_id = ?");
        return $stmt->execute([$event_id, $user_id]);
    }

    public function isSubscribed($event_id, $user_id) {
        if (!$this->ensureTableExists()) {
            return false;
        }
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM subscribe_event WHERE event_id = ? AND user_id = ?");
        $stmt->execute([$event_id, $user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }
    
    public function getSubscriptionCount($event_id) {
        if (!$this->ensureTableExists()) {
            return 0;
        }
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM subscribe_event WHERE event_id = ?");
        $stmt->execute([$event_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
}