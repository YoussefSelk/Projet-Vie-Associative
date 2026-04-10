<?php
declare(strict_types=1);
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

    /** @var bool Evite de verifier les colonnes de rappel a chaque appel */
    private bool $reminderColumnsEnsured = false;

    /**
     * Constructeur
     * @param PDO $database Instance de connexion PDO
     */
    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Garantit la presence des colonnes de suivi d'envoi des rappels.
     */
    private function ensureReminderColumns(): void {
        if ($this->reminderColumnsEnsured) {
            return;
        }

        $existingColumns = [];
        $stmt = $this->db->query("SHOW COLUMNS FROM abonnements");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $column) {
            $name = (string)($column['Field'] ?? '');
            if ($name !== '') {
                $existingColumns[$name] = true;
            }
        }

        if (!isset($existingColumns['reminder_48h_sent_at'])) {
            $this->db->exec("ALTER TABLE abonnements ADD COLUMN reminder_48h_sent_at DATETIME NULL DEFAULT NULL");
        }

        if (!isset($existingColumns['reminder_24h_sent_at'])) {
            $this->db->exec("ALTER TABLE abonnements ADD COLUMN reminder_24h_sent_at DATETIME NULL DEFAULT NULL");
        }

        $this->reminderColumnsEnsured = true;
    }

    /**
     * Verifie et retourne un nom de colonne de rappel autorise.
     */
    private function normalizeReminderColumn(string $column): string {
        $allowed = ['reminder_48h_sent_at', 'reminder_24h_sent_at'];
        if (!in_array($column, $allowed, true)) {
            throw new InvalidArgumentException('Unsupported reminder column');
        }

        return $column;
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

    /**
     * Retourne les inscriptions a notifier dans une fenetre temporelle.
     *
     * @param int $hoursBefore Nombre d'heures avant debut evenement (24 ou 48)
     * @param string $reminderColumn Colonne de suivi d'envoi
     * @param int $windowMinutes Taille de la fenetre de recherche
     * @return array<int, array<string, mixed>>
     */
    public function getDueEventReminders(int $hoursBefore, string $reminderColumn, int $windowMinutes = 60): array {
        $this->ensureReminderColumns();
        $column = $this->normalizeReminderColumn($reminderColumn);
        $windowMinutes = max(5, $windowMinutes);

        $sql = "
            SELECT
                a.id AS user_id,
                a.event_id,
                u.mail,
                u.prenom,
                u.nom,
                fe.titre,
                fe.campus,
                fe.lieu,
                fe.date_ev,
                fe.horaire_debut,
                TIMESTAMP(fe.date_ev, COALESCE(fe.horaire_debut, '00:00:00')) AS event_datetime
            FROM abonnements a
            INNER JOIN users u ON u.id = a.id
            INNER JOIN fiche_event fe ON fe.event_id = a.event_id
            WHERE fe.validation_finale = 1
              AND u.mail IS NOT NULL
              AND u.mail <> ''
              AND a.$column IS NULL
              AND TIMESTAMP(fe.date_ev, COALESCE(fe.horaire_debut, '00:00:00')) >= DATE_ADD(NOW(), INTERVAL :hours_before_start HOUR)
              AND TIMESTAMP(fe.date_ev, COALESCE(fe.horaire_debut, '00:00:00')) < DATE_ADD(DATE_ADD(NOW(), INTERVAL :hours_before_end HOUR), INTERVAL :window_minutes MINUTE)
        ";

        $stmt = $this->db->prepare($sql);
          $stmt->bindValue(':hours_before_start', $hoursBefore, PDO::PARAM_INT);
          $stmt->bindValue(':hours_before_end', $hoursBefore, PDO::PARAM_INT);
        $stmt->bindValue(':window_minutes', $windowMinutes, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Marque un rappel comme envoye pour eviter les doublons.
     */
    public function markReminderSent(int $userId, int $eventId, string $reminderColumn): bool {
        $this->ensureReminderColumns();
        $column = $this->normalizeReminderColumn($reminderColumn);

        $stmt = $this->db->prepare("UPDATE abonnements SET $column = NOW() WHERE id = ? AND event_id = ? AND $column IS NULL");
        return $stmt->execute([$userId, $eventId]);
    }
}