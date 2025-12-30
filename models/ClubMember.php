<?php

class ClubMember {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function getClubMembers($club_id) {
        $stmt = $this->db->prepare("SELECT mc.*, u.nom, u.prenom FROM membres_club mc 
                                    JOIN users u ON mc.membre_id = u.id 
                                    WHERE mc.club_id = ? AND mc.valide = 1");
        $stmt->execute([$club_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserClubs($user_id) {
        $stmt = $this->db->prepare("SELECT fc.* FROM fiche_club fc 
                                    JOIN membres_club mc ON fc.club_id = mc.club_id 
                                    WHERE mc.membre_id = ? AND mc.valide = 1");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addMember($club_id, $user_id) {
        $stmt = $this->db->prepare("INSERT INTO membres_club (club_id, membre_id, valide) VALUES (?, ?, 0)");
        return $stmt->execute([$club_id, $user_id]);
    }

    public function removeMember($club_id, $user_id) {
        $stmt = $this->db->prepare("DELETE FROM membres_club WHERE club_id = ? AND membre_id = ?");
        return $stmt->execute([$club_id, $user_id]);
    }

    public function validateMember($club_id, $user_id) {
        $stmt = $this->db->prepare("UPDATE membres_club SET valide = 1 WHERE club_id = ? AND membre_id = ?");
        return $stmt->execute([$club_id, $user_id]);
    }
}