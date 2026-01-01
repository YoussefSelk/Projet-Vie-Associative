<?php
/**
 * =============================================================================
 * MODÈLE VALIDATION
 * =============================================================================
 * 
 * Gère le processus de validation des clubs et événements :
 * - Récupération des éléments en attente de validation
 * - Validation par les différents acteurs (admin, tuteur, BDE)
 * - Rejet avec remarques explicatives
 * - Suppression des éléments rejetés
 * 
 * Workflow de validation :
 * - validation_finale = 0 : En attente
 * - validation_finale = 1 : Validé
 * - validation_finale = -1 : Rejeté
 * 
 * Tables associées : fiche_club, fiche_event
 * 
 * @author Équipe de développement EILCO
 * @version 2.0
 */

class Validation {
    /** @var PDO Instance de connexion à la base de données */
    private $db;

    /**
     * Constructeur
     * @param PDO $database Instance de connexion PDO
     */
    public function __construct($database) {
        $this->db = $database;
    }

    // =========================================================================
    // VALIDATION DES CLUBS
    // =========================================================================

    /**
     * Récupère tous les clubs en attente de validation
     * 
     * @return array Liste des clubs non validés
     */
    public function getPendingClubs() {
        $stmt = $this->db->prepare("SELECT * FROM fiche_club WHERE validation_finale IS NULL OR validation_finale = 0");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère tous les clubs qui ont été rejetés
     * 
     * @return array Liste des clubs rejetés
     */
    public function getRejectedClubs() {
        $stmt = $this->db->prepare("SELECT * FROM fiche_club WHERE validation_finale = -1");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Met à jour les statuts de validation d'un club
     * Permet de valider partiellement ou totalement un club
     * 
     * @param int $club_id Identifiant du club
     * @param int|null $admin_validation Validation par l'admin (0 ou 1)
     * @param int|null $tuteur_validation Validation par le tuteur (0 ou 1)
     * @param int|null $final_validation Validation finale (0, 1 ou -1)
     * @param string|null $remarques Commentaires sur la validation
     * @return bool Succès de l'opération
     */
    public function validateClub($club_id, $admin_validation = null, $tuteur_validation = null, $final_validation = null, $remarques = null) {
        $updates = [];
        $values = [];

        if ($admin_validation !== null) {
            $updates[] = "validation_admin = ?";
            $values[] = $admin_validation;
        }
        if ($tuteur_validation !== null) {
            $updates[] = "validation_tuteur = ?";
            $values[] = $tuteur_validation;
        }
        if ($final_validation !== null) {
            $updates[] = "validation_finale = ?";
            $values[] = $final_validation;
        }
        if ($remarques !== null) {
            $updates[] = "remarques = ?";
            $values[] = $remarques;
        }

        if (empty($updates)) return false;

        $values[] = $club_id;
        $stmt = $this->db->prepare("UPDATE fiche_club SET " . implode(", ", $updates) . " WHERE club_id = ?");
        return $stmt->execute($values);
    }
    
    /**
     * Rejette un club (validation_finale = -1)
     * 
     * @param int $club_id Identifiant du club à rejeter
     * @param string $remarques_refus Motif du refus (optionnel)
     * @return bool Succès de l'opération
     */
    public function rejectClub($club_id, $remarques_refus = '') {
        $stmt = $this->db->prepare("UPDATE fiche_club SET validation_finale = -1 WHERE club_id = ?");
        return $stmt->execute([$club_id]);
    }

    // =========================================================================
    // VALIDATION DES ÉVÉNEMENTS
    // =========================================================================

    /**
     * Récupère tous les événements en attente de validation
     * 
     * @return array Liste des événements non validés
     */
    public function getPendingEvents() {
        $stmt = $this->db->prepare("SELECT * FROM fiche_event WHERE validation_finale IS NULL OR validation_finale = 0");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère tous les événements qui ont été rejetés
     * 
     * @return array Liste des événements rejetés
     */
    public function getRejectedEvents() {
        $stmt = $this->db->prepare("SELECT * FROM fiche_event WHERE validation_finale = -1");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Met à jour les statuts de validation d'un événement
     * Permet de valider partiellement ou totalement un événement
     * 
     * @param int $event_id Identifiant de l'événement
     * @param int|null $bde_validation Validation par le BDE (0 ou 1)
     * @param int|null $tuteur_validation Validation par le tuteur (0 ou 1)
     * @param int|null $final_validation Validation finale (0, 1 ou -1)
     * @param string|null $remarques Commentaires sur la validation
     * @return bool Succès de l'opération
     */
    public function validateEvent($event_id, $bde_validation = null, $tuteur_validation = null, $final_validation = null, $remarques = null) {
        $updates = [];
        $values = [];

        if ($bde_validation !== null) {
            $updates[] = "validation_bde = ?";
            $values[] = $bde_validation;
        }
        if ($tuteur_validation !== null) {
            $updates[] = "validation_tuteur = ?";
            $values[] = $tuteur_validation;
        }
        if ($final_validation !== null) {
            $updates[] = "validation_finale = ?";
            $values[] = $final_validation;
        }
        if ($remarques !== null) {
            $updates[] = "remarques = ?";
            $values[] = $remarques;
        }

        if (empty($updates)) return false;

        $values[] = $event_id;
        $stmt = $this->db->prepare("UPDATE fiche_event SET " . implode(", ", $updates) . " WHERE event_id = ?");
        return $stmt->execute($values);
    }
    
    /**
     * Rejette un événement avec remarques explicatives
     * 
     * @param int $event_id Identifiant de l'événement à rejeter
     * @param string $remarques_refus Motif du refus
     * @return bool Succès de l'opération
     */
    public function rejectEvent($event_id, $remarques_refus = '') {
        $stmt = $this->db->prepare("UPDATE fiche_event SET validation_finale = -1, remarques = ? WHERE event_id = ?");
        return $stmt->execute([$remarques_refus, $event_id]);
    }
    
    // =========================================================================
    // SUPPRESSION DES ÉLÉMENTS REJETÉS
    // =========================================================================
    
    /**
     * Supprime définitivement un club rejeté
     * Supprime d'abord les membres associés pour respecter les contraintes
     * 
     * @param int $club_id Identifiant du club à supprimer
     * @return bool Succès de la suppression
     */
    public function deleteRejectedClub($club_id) {
        // Supprimer d'abord les membres associés
        $this->db->prepare("DELETE FROM membres_club WHERE club_id = ?")->execute([$club_id]);
        // Puis supprimer le club (seulement s'il est rejeté)
        $stmt = $this->db->prepare("DELETE FROM fiche_club WHERE club_id = ? AND validation_finale = -1");
        return $stmt->execute([$club_id]);
    }
    
    /**
     * Supprime définitivement un événement rejeté
     * 
     * @param int $event_id Identifiant de l'événement à supprimer
     * @return bool Succès de la suppression
     */
    public function deleteRejectedEvent($event_id) {
        $stmt = $this->db->prepare("DELETE FROM fiche_event WHERE event_id = ? AND validation_finale = -1");
        return $stmt->execute([$event_id]);
    }
}