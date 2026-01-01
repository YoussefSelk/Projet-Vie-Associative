<?php
/**
 * =============================================================================
 * MODÈLE RAPPORT D'ÉVÉNEMENT
 * =============================================================================
 * 
 * Gère les rapports post-événement :
 * - Récupération des événements avec leurs rapports
 * - Mise à jour du rapport d'un événement
 * 
 * Le rapport est stocké dans la colonne rapport_event de fiche_event (VARCHAR 255 = chemin fichier)
 * Tables liées : fiche_event, fiche_club
 * 
 * @author Équipe de développement EILCO
 * @version 2.1
 */

class EventReport {
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
     * Récupère un événement avec son rapport par son identifiant
     * 
     * @param int $event_id Identifiant de l'événement
     * @return array|false Données de l'événement avec rapport ou false si non trouvé
     */
    public function getEventWithReport($event_id) {
        $stmt = $this->db->prepare("
            SELECT fe.*, fc.nom_club 
            FROM fiche_event fe
            LEFT JOIN fiche_club fc ON fe.club_orga = fc.club_id
            WHERE fe.event_id = ?
        ");
        $stmt->execute([$event_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère tous les événements validés avec un rapport soumis
     * 
     * @return array Liste des événements avec rapport, triés par date décroissante
     */
    public function getEventsWithReports() {
        $stmt = $this->db->prepare("
            SELECT fe.*, fc.nom_club 
            FROM fiche_event fe
            LEFT JOIN fiche_club fc ON fe.club_orga = fc.club_id
            WHERE fe.validation_finale = 1 
              AND fe.rapport_event IS NOT NULL 
              AND fe.rapport_event != ''
            ORDER BY fe.date_ev DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère tous les événements passés sans rapport
     * 
     * @return array Liste des événements passés sans rapport soumis
     */
    public function getEventsWithoutReports() {
        $stmt = $this->db->prepare("
            SELECT fe.*, fc.nom_club 
            FROM fiche_event fe
            LEFT JOIN fiche_club fc ON fe.club_orga = fc.club_id
            WHERE fe.validation_finale = 1 
              AND fe.date_ev < CURDATE()
              AND (fe.rapport_event IS NULL OR fe.rapport_event = '')
            ORDER BY fe.date_ev DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Met à jour le rapport d'un événement
     * 
     * @param int $event_id Identifiant de l'événement
     * @param string $rapport_path Chemin du fichier rapport
     * @return bool Succès de la mise à jour
     */
    public function updateReport($event_id, $rapport_path) {
        $stmt = $this->db->prepare("UPDATE fiche_event SET rapport_event = ? WHERE event_id = ?");
        return $stmt->execute([$rapport_path, $event_id]);
    }

    /**
     * Supprime le rapport d'un événement
     * 
     * @param int $event_id Identifiant de l'événement
     * @return bool Succès de la suppression
     */
    public function deleteReport($event_id) {
        $stmt = $this->db->prepare("UPDATE fiche_event SET rapport_event = NULL WHERE event_id = ?");
        return $stmt->execute([$event_id]);
    }

    /**
     * Vérifie si un événement a un rapport
     * 
     * @param int $event_id Identifiant de l'événement
     * @return bool True si un rapport existe
     */
    public function hasReport($event_id) {
        $stmt = $this->db->prepare("
            SELECT rapport_event FROM fiche_event 
            WHERE event_id = ? AND rapport_event IS NOT NULL AND rapport_event != ''
        ");
        $stmt->execute([$event_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }
}