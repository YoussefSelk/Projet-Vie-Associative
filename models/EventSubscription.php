<?php
/**
 * =============================================================================
 * MODÈLE INSCRIPTION AUX ÉVÉNEMENTS
 * =============================================================================
 * 
 * Gère les inscriptions des utilisateurs aux événements :
 * - Inscription et désinscription
 * - Vérification du statut d'inscription
 * - Comptage des participants
 * - Création automatique de la table si elle n'existe pas
 * 
 * Table associée : subscribe_event
 * Tables liées : users, fiche_event
 * 
 * @author Équipe de développement EILCO
 * @version 2.0
 */

class EventSubscription {
    /** @var PDO Instance de connexion à la base de données */
    private $db;
    
    /** @var bool|null Cache pour éviter de vérifier la table plusieurs fois */
    private $tableExists = null;

    /**
     * Constructeur
     * @param PDO $database Instance de connexion PDO
     */
    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Vérifie si la table subscribe_event existe, la crée sinon
     * Utilise un cache pour éviter les vérifications répétées
     * 
     * @return bool True si la table est disponible
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
            // La table n'existe pas, la créer
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
                error_log("Échec de création de la table subscribe_event: " . $e2->getMessage());
                return false;
            }
        }
    }

    /**
     * Récupère tous les inscrits à un événement
     * Inclut les informations utilisateur (nom, prénom)
     * 
     * @param int $event_id Identifiant de l'événement
     * @return array Liste des inscrits avec leurs informations
     */
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

    /**
     * Récupère tous les événements auxquels un utilisateur est inscrit
     * Seuls les événements validés sont retournés
     * 
     * @param int $user_id Identifiant de l'utilisateur
     * @return array Liste des événements triés par date décroissante
     */
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

    /**
     * Inscrit un utilisateur à un événement
     * Vérifie d'abord si l'utilisateur n'est pas déjà inscrit
     * 
     * @param int $event_id Identifiant de l'événement
     * @param int $user_id Identifiant de l'utilisateur
     * @return bool Succès de l'inscription
     */
    public function subscribeToEvent($event_id, $user_id) {
        if (!$this->ensureTableExists()) {
            return false;
        }
        
        // Vérifier si déjà inscrit
        if ($this->isSubscribed($event_id, $user_id)) {
            return true;
        }
        
        $stmt = $this->db->prepare("INSERT INTO subscribe_event (event_id, user_id) VALUES (?, ?)");
        return $stmt->execute([$event_id, $user_id]);
    }

    /**
     * Désinscrit un utilisateur d'un événement
     * 
     * @param int $event_id Identifiant de l'événement
     * @param int $user_id Identifiant de l'utilisateur
     * @return bool Succès de la désinscription
     */
    public function unsubscribeFromEvent($event_id, $user_id) {
        if (!$this->ensureTableExists()) {
            return false;
        }
        
        $stmt = $this->db->prepare("DELETE FROM subscribe_event WHERE event_id = ? AND user_id = ?");
        return $stmt->execute([$event_id, $user_id]);
    }

    /**
     * Vérifie si un utilisateur est inscrit à un événement
     * 
     * @param int $event_id Identifiant de l'événement
     * @param int $user_id Identifiant de l'utilisateur
     * @return bool True si l'utilisateur est inscrit
     */
    public function isSubscribed($event_id, $user_id) {
        if (!$this->ensureTableExists()) {
            return false;
        }
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM subscribe_event WHERE event_id = ? AND user_id = ?");
        $stmt->execute([$event_id, $user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }
    
    /**
     * Compte le nombre d'inscrits à un événement
     * 
     * @param int $event_id Identifiant de l'événement
     * @return int Nombre d'inscrits
     */
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