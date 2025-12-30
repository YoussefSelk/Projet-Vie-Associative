<?php

class Club {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function getAllValidatedClubs() {
        $stmt = $this->db->prepare("SELECT * FROM fiche_club WHERE validation_finale = 1 ORDER BY nom_club ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getClubById($id) {
        $stmt = $this->db->prepare("SELECT * FROM fiche_club WHERE club_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getClubByName($name) {
        // Case-insensitive search to prevent duplicates with different capitalization
        $stmt = $this->db->prepare("SELECT * FROM fiche_club WHERE LOWER(TRIM(nom_club)) = LOWER(TRIM(?))");
        $stmt->execute([$name]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function clubNameExists($name, $excludeId = null) {
        // Check if a club with this name exists (case-insensitive)
        if ($excludeId) {
            $stmt = $this->db->prepare("SELECT club_id FROM fiche_club WHERE LOWER(TRIM(nom_club)) = LOWER(TRIM(?)) AND club_id != ?");
            $stmt->execute([$name, $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT club_id FROM fiche_club WHERE LOWER(TRIM(nom_club)) = LOWER(TRIM(?))");
            $stmt->execute([$name]);
        }
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function getAllClubs() {
        $stmt = $this->db->prepare("SELECT * FROM fiche_club ORDER BY nom_club ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createClub($data) {
        $stmt = $this->db->prepare("INSERT INTO fiche_club (nom_club, type_club, description, campus, validation_finale) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['nom_club'],
            $data['type_club'],
            $data['description'],
            $data['campus'],
            0
        ]);
    }

    public function updateClub($id, $data) {
        $allowed_fields = ['nom_club', 'type_club', 'description', 'campus'];
        $fields = [];
        $values = [];

        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $id;
        $stmt = $this->db->prepare("UPDATE fiche_club SET " . implode(", ", $fields) . " WHERE club_id = ?");
        return $stmt->execute($values);
    }

    public function deleteClub($id) {
        $stmt = $this->db->prepare("DELETE FROM fiche_club WHERE club_id = ?");
        return $stmt->execute([$id]);
    }
}