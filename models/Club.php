<?php
/**
 * =============================================================================
 * MODÈLE CLUB
 * =============================================================================
 * 
 * Gère toutes les opérations liées aux clubs associatifs :
 * - Récupération des clubs (validés, par ID, par nom)
 * - Création et modification des fiches clubs
 * - Suppression des clubs
 * 
 * Table associée : fiche_club
 * 
 * @author Équipe de développement EILCO
 * @version 2.0
 */

class Club {
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
     * Récupère tous les clubs validés (ayant passé la validation finale)
     * 
     * @return array Liste des clubs validés triés par nom
     */
    public function getAllValidatedClubs() {
        $stmt = $this->db->prepare("SELECT * FROM fiche_club WHERE validation_finale = 1 ORDER BY nom_club ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un club par son identifiant
     * 
     * @param int $id Identifiant du club
     * @return array|false Données du club ou false si non trouvé
     */
    public function getClubById($id) {
        $stmt = $this->db->prepare("SELECT * FROM fiche_club WHERE club_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Recherche un club par son nom (insensible à la casse)
     * Évite les doublons avec des majuscules différentes
     * 
     * @param string $name Nom du club à rechercher
     * @return array|false Données du club ou false si non trouvé
     */
    public function getClubByName($name) {
        $stmt = $this->db->prepare("SELECT * FROM fiche_club WHERE LOWER(TRIM(nom_club)) = LOWER(TRIM(?))");
        $stmt->execute([$name]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Vérifie si un club avec ce nom existe déjà
     * Permet d'exclure un club spécifique (pour les modifications)
     * 
     * @param string $name Nom du club à vérifier
     * @param int|null $excludeId ID du club à exclure (optionnel)
     * @return bool True si le nom existe déjà
     */
    public function clubNameExists($name, $excludeId = null) {
        if ($excludeId) {
            $stmt = $this->db->prepare("SELECT club_id FROM fiche_club WHERE LOWER(TRIM(nom_club)) = LOWER(TRIM(?)) AND club_id != ?");
            $stmt->execute([$name, $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT club_id FROM fiche_club WHERE LOWER(TRIM(nom_club)) = LOWER(TRIM(?))");
            $stmt->execute([$name]);
        }
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    /**
     * Récupère tous les clubs (validés ou non)
     * 
     * @return array Liste de tous les clubs triés par nom
     */
    public function getAllClubs() {
        $stmt = $this->db->prepare("SELECT * FROM fiche_club ORDER BY nom_club ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère tous les clubs créés par un utilisateur (en tant que Président)
     * Jointe avec la table membres_club pour récupérer les clubs où l'utilisateur est Président
     * 
     * @param int $user_id Identifiant de l'utilisateur
     * @return array Liste des clubs créés par l'utilisateur
     */
    public function getClubsByUser($user_id) {
        $stmt = $this->db->prepare("
            SELECT fc.* FROM fiche_club fc
            INNER JOIN membres_club mc ON fc.club_id = mc.club_id
            WHERE mc.membre_id = ? AND mc.fonction = 'Président' AND mc.valide = 1
            ORDER BY fc.nom_club ASC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crée un nouveau club
     * Le club est créé avec validation_finale = 0 (en attente de validation)
     * 
     * @param array $data Données du club (nom_club, type_club, description, campus)
     * @return bool Succès de la création
     */
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

    /**
     * Met à jour les informations d'un club
     * Seuls les champs autorisés peuvent être modifiés
     * IMPORTANT : Si le club était rejeté (validation_admin = 0), réinitialise automatiquement
     * validation_admin et validation_finale à NULL pour que le club soit remis en attente d'examen
     * 
     * @param int $id Identifiant du club
     * @param array $data Données à mettre à jour
     * @param bool $resetValidation Paramètre hérité, non utilisé (la réinitialisation est automatique)
     * @return bool Succès de l'opération
     */
    public function updateClub($id, $data, $resetValidation = false) {
        $allowed_fields = ['nom_club', 'type_club', 'description', 'campus'];
        $fields = [];
        $values = [];

        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }

        // Vérifier si le club était en correction (validation_admin = 0)
        // Si oui, réinitialiser les statuts de validation pour qu'il soit réexaminé
        $check = $this->db->prepare("SELECT validation_admin FROM fiche_club WHERE club_id = ?");
        $check->execute([$id]);
        $club = $check->fetch(PDO::FETCH_ASSOC);
        
        if ($club && $club['validation_admin'] === 0) {
            // Le club était rejeté, le remettre en attente
            $fields[] = "validation_admin = NULL";
            $fields[] = "validation_finale = NULL";
            // Motif_refus reste pour traçabilité, mais peut être nettoyé si nécessaire
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $id;
        $stmt = $this->db->prepare("UPDATE fiche_club SET " . implode(", ", $fields) . " WHERE club_id = ?");
        return $stmt->execute($values);
    }

    /**
     * Supprime un club de la base de données
     * Attention : cette action est irréversible
     * 
     * @param int $id Identifiant du club à supprimer
     * @return bool Succès de la suppression
     */
    public function deleteClub($id) {
        $stmt = $this->db->prepare("DELETE FROM fiche_club WHERE club_id = ?");
        return $stmt->execute([$id]);
    }
}