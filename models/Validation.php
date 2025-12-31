<?php

class Validation {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    // Club Validation
    public function getPendingClubs() {
        $stmt = $this->db->prepare("SELECT * FROM fiche_club WHERE validation_finale IS NULL OR validation_finale = 0");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getRejectedClubs() {
        $stmt = $this->db->prepare("SELECT * FROM fiche_club WHERE validation_finale = -1");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

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
    
    public function rejectClub($club_id, $remarques_refus = '') {
        // Note: remarques stored separately if needed, just mark as rejected
        $stmt = $this->db->prepare("UPDATE fiche_club SET validation_finale = -1 WHERE club_id = ?");
        return $stmt->execute([$club_id]);
    }

    // Event Validation
    public function getPendingEvents() {
        $stmt = $this->db->prepare("SELECT * FROM fiche_event WHERE validation_finale IS NULL OR validation_finale = 0");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getRejectedEvents() {
        $stmt = $this->db->prepare("SELECT * FROM fiche_event WHERE validation_finale = -1");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

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
    
    public function rejectEvent($event_id, $remarques_refus = '') {
        $stmt = $this->db->prepare("UPDATE fiche_event SET validation_finale = -1, remarques = ? WHERE event_id = ?");
        return $stmt->execute([$remarques_refus, $event_id]);
    }
    
    /**
     * Delete a rejected item (club or event)
     */
    public function deleteRejectedClub($club_id) {
        // First delete associated members
        $this->db->prepare("DELETE FROM membres_club WHERE club_id = ?")->execute([$club_id]);
        // Then delete the club
        $stmt = $this->db->prepare("DELETE FROM fiche_club WHERE club_id = ? AND validation_finale = -1");
        return $stmt->execute([$club_id]);
    }
    
    public function deleteRejectedEvent($event_id) {
        $stmt = $this->db->prepare("DELETE FROM fiche_event WHERE event_id = ? AND validation_finale = -1");
        return $stmt->execute([$event_id]);
    }
}