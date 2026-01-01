<?php
/**
 * =============================================================================
 * MODÈLE RAPPORT D'ÉVÉNEMENT
 * =============================================================================
 * 
 * Gère les rapports post-événement :
 * - Création et modification des rapports
 * - Récupération des rapports par événement ou globalement
 * - Suppression des rapports
 * 
 * Table associée : rapport_event
 * Tables liées : fiche_event
 * 
 * @author Équipe de développement EILCO
 * @version 2.0
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
     * Récupère un rapport par son identifiant
     * 
     * @param int $id Identifiant du rapport
     * @return array|false Données du rapport ou false si non trouvé
     */
    public function getReportById($id) {
        $stmt = $this->db->prepare("SELECT * FROM rapport_event WHERE rapport_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère tous les rapports associés à un événement
     * 
     * @param int $event_id Identifiant de l'événement
     * @return array Liste des rapports triés par date de création décroissante
     */
    public function getReportsByEvent($event_id) {
        $stmt = $this->db->prepare("SELECT * FROM rapport_event WHERE event_id = ? ORDER BY date_creation DESC");
        $stmt->execute([$event_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère tous les rapports de la base de données
     * 
     * @return array Liste de tous les rapports triés par date de création décroissante
     */
    public function getAllReports() {
        $stmt = $this->db->prepare("SELECT * FROM rapport_event ORDER BY date_creation DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crée un nouveau rapport d'événement
     * La date de création est automatiquement définie à maintenant
     * 
     * @param array $data Données du rapport (event_id, contenu)
     * @return bool Succès de la création
     */
    public function createReport($data) {
        $stmt = $this->db->prepare("INSERT INTO rapport_event (event_id, contenu, date_creation) VALUES (?, ?, NOW())");
        return $stmt->execute([$data['event_id'], $data['contenu']]);
    }

    /**
     * Met à jour le contenu d'un rapport
     * 
     * @param int $id Identifiant du rapport
     * @param array $data Nouvelles données (contenu)
     * @return bool Succès de la mise à jour
     */
    public function updateReport($id, $data) {
        $stmt = $this->db->prepare("UPDATE rapport_event SET contenu = ? WHERE rapport_id = ?");
        return $stmt->execute([$data['contenu'], $id]);
    }

    /**
     * Supprime un rapport de la base de données
     * 
     * @param int $id Identifiant du rapport à supprimer
     * @return bool Succès de la suppression
     */
    public function deleteReport($id) {
        $stmt = $this->db->prepare("DELETE FROM rapport_event WHERE rapport_id = ?");
        return $stmt->execute([$id]);
    }
}