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
 * 
 * Table associée : abonnements
 * Colonnes : id (user_id), event_id, date_abonnement
 * Tables liées : users, fiche_event
 * 
 * @author Équipe de développement EILCO
 * @version 2.1
 */

class EventSubscription {
    /** @var PDO Instance de connexion à la base de données */
    private $db;

    /**
     * Constructeur
     * @param PDO $database Instance de connexion PDO
     */
    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Récupère tous les inscrits à un événement
     * Inclut les informations utilisateur (nom, prénom)
     * 
     * @param int $event_id Identifiant de l'événement
     * @return array Liste des inscrits avec leurs informations
     */
    public function getEventSubscribers($event_id) {
        $stmt = $this->db->prepare("
            SELECT a.id as user_id, a.event_id, a.date_abonnement, u.nom, u.prenom 
            FROM abonnements a 
            JOIN users u ON a.id = u.id 
            WHERE a.event_id = ?
        ");
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
        $stmt = $this->db->prepare("
            SELECT fe.* FROM fiche_event fe 
            JOIN abonnements a ON fe.event_id = a.event_id 
            WHERE a.id = ? AND fe.validation_finale = 1
            ORDER BY fe.date_ev DESC
        ");
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
        // Vérifier si déjà inscrit
        if ($this->isSubscribed($event_id, $user_id)) {
            return true;
        }
        
        try {
            $stmt = $this->db->prepare("INSERT INTO abonnements (id, event_id, date_abonnement) VALUES (?, ?, NOW())");
            return $stmt->execute([$user_id, $event_id]);
        } catch (PDOException $e) {
            ErrorHandler::logError("Erreur inscription événement: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }

    /**
     * Désinscrit un utilisateur d'un événement
     * 
     * @param int $event_id Identifiant de l'événement
     * @param int $user_id Identifiant de l'utilisateur
     * @return bool Succès de la désinscription
     */
    public function unsubscribeFromEvent($event_id, $user_id) {
        $stmt = $this->db->prepare("DELETE FROM abonnements WHERE id = ? AND event_id = ?");
        return $stmt->execute([$user_id, $event_id]);
    }

    /**
     * Vérifie si un utilisateur est inscrit à un événement
     * 
     * @param int $event_id Identifiant de l'événement
     * @param int $user_id Identifiant de l'utilisateur
     * @return bool True si l'utilisateur est inscrit
     */
    public function isSubscribed($event_id, $user_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM abonnements WHERE id = ? AND event_id = ?");
        $stmt->execute([$user_id, $event_id]);
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
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM abonnements WHERE event_id = ?");
        $stmt->execute([$event_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
}