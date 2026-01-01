<?php
/**
 * =============================================================================
 * MODÈLE MEMBRE DE CLUB
 * =============================================================================
 * 
 * Gère les relations entre les utilisateurs et les clubs :
 * - Adhésion aux clubs (demande, validation)
 * - Récupération des membres d'un club
 * - Récupération des clubs d'un utilisateur
 * - Suppression des adhésions
 * 
 * Table associée : membres_club
 * Tables liées : users, fiche_club
 * 
 * @author Équipe de développement EILCO
 * @version 2.0
 */

class ClubMember {
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
     * Récupère tous les membres validés d'un club
     * Inclut les informations utilisateur (nom, prénom)
     * 
     * @param int $club_id Identifiant du club
     * @return array Liste des membres avec leurs informations
     */
    public function getClubMembers($club_id) {
        $stmt = $this->db->prepare("SELECT mc.*, u.nom, u.prenom FROM membres_club mc 
                                    JOIN users u ON mc.membre_id = u.id 
                                    WHERE mc.club_id = ? AND mc.valide = 1");
        $stmt->execute([$club_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère tous les clubs dont l'utilisateur est membre validé
     * 
     * @param int $user_id Identifiant de l'utilisateur
     * @return array Liste des clubs avec leurs informations
     */
    public function getUserClubs($user_id) {
        $stmt = $this->db->prepare("SELECT fc.* FROM fiche_club fc 
                                    JOIN membres_club mc ON fc.club_id = mc.club_id 
                                    WHERE mc.membre_id = ? AND mc.valide = 1");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ajoute un utilisateur comme membre d'un club
     * Le membre est créé avec valide = 0 (en attente de validation)
     * 
     * @param int $club_id Identifiant du club
     * @param int $user_id Identifiant de l'utilisateur
     * @return bool Succès de l'opération
     */
    public function addMember($club_id, $user_id) {
        $stmt = $this->db->prepare("INSERT INTO membres_club (club_id, membre_id, valide) VALUES (?, ?, 0)");
        return $stmt->execute([$club_id, $user_id]);
    }

    /**
     * Supprime un membre d'un club
     * 
     * @param int $club_id Identifiant du club
     * @param int $user_id Identifiant de l'utilisateur à retirer
     * @return bool Succès de la suppression
     */
    public function removeMember($club_id, $user_id) {
        $stmt = $this->db->prepare("DELETE FROM membres_club WHERE club_id = ? AND membre_id = ?");
        return $stmt->execute([$club_id, $user_id]);
    }

    /**
     * Valide l'adhésion d'un membre à un club
     * Passe le statut valide de 0 à 1
     * 
     * @param int $club_id Identifiant du club
     * @param int $user_id Identifiant de l'utilisateur à valider
     * @return bool Succès de la validation
     */
    public function validateMember($club_id, $user_id) {
        $stmt = $this->db->prepare("UPDATE membres_club SET valide = 1 WHERE club_id = ? AND membre_id = ?");
        return $stmt->execute([$club_id, $user_id]);
    }
}