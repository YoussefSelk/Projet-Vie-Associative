<?php
declare(strict_types=1);
/**
 * =============================================================================
 * MODÈLE UTILISATEUR
 * =============================================================================
 * 
 * Gère toutes les opérations liées aux utilisateurs :
 * - Authentification (connexion, vérification mot de passe)
 * - Gestion des comptes (création, modification, suppression)
 * - Récupération des données utilisateur
 * 
 * @author Équipe de développement EILCO
 * @version 2.0
 */

class User {
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
     * Récupère un utilisateur par son ID
     * 
     * @param int $id Identifiant de l'utilisateur
     * @return array|false Données de l'utilisateur ou false si non trouvé
     */
    public function getUserById($id) {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un utilisateur par son adresse email
     * 
     * @param string $email Adresse email de l'utilisateur
     * @return array|false Données de l'utilisateur ou false si non trouvé
     */
    public function getUserByEmail($email) {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE mail = ?');
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère tous les utilisateurs
     * 
     * @return array Liste de tous les utilisateurs triés par nom
     */
    public function getAllUsers() {
        $stmt = $this->db->prepare('SELECT * FROM users ORDER BY nom ASC');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Authentifie un utilisateur avec email et mot de passe
     * 
     * @param string $email Adresse email
     * @param string $password Mot de passe en clair
     * @return array|null Données de l'utilisateur si authentifié, null sinon
     */
    public function authenticate($email, $password) {
        $user = $this->getUserByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return null;
    }

    /**
     * Met à jour le mot de passe d'un utilisateur
     * Le mot de passe est automatiquement haché avec bcrypt
     * 
     * @param string $email Adresse email de l'utilisateur
     * @param string $password Nouveau mot de passe en clair
     * @return bool Succès de l'opération
     */
    public function updatePassword($email, $password) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE mail = ?");
        return $stmt->execute([$hashedPassword, $email]);
    }

    /**
     * Met à jour les informations d'un utilisateur
     * Seuls les champs autorisés peuvent être modifiés
     * 
     * @param int $id Identifiant de l'utilisateur
     * @param array $data Données à mettre à jour
     * @return bool Succès de l'opération
     */
    public function updateUser($id, $data) {
        $allowed_fields = ['nom', 'prenom', 'mail', 'permission'];
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
        $stmt = $this->db->prepare("UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    /**
     * Crée un nouvel utilisateur
     * 
     * @param string $nom Nom de famille
     * @param string $prenom Prénom
     * @param string $mail Adresse email
     * @param string $password Mot de passe (haché ou non selon $isHashed)
     * @param string $promo Promotion (ex: CP1, ING1, etc.)
     * @param bool $isHashed Indique si le mot de passe est déjà haché
     * @param int|null $permission Niveau de permission (null = déduit du statut/promo)
     * @return bool Succès de la création
     */
    public function createUser($nom, $prenom, $mail, $password, $promo = 'etu', $isHashed = false, $permission = null, $ing2_type = null) {
        // Si le mot de passe n'est pas déjà haché, le hacher
        $finalPassword = $isHashed ? $password : password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        $finalPermission = $permission;
        if ($finalPermission === null) {
            $normalizedPromo = strtolower(trim((string)$promo));
            $studentPromos = ['cp1', 'cp2', 'ing1', 'ing2', 'ing3', 'etu'];
            $finalPermission = match (true) {
                in_array($normalizedPromo, $studentPromos, true) => 1,
                // Un "futur tuteur" doit être validé par un admin avant d'obtenir la permission 2.
                $normalizedPromo === 'tuteur' => 1,
                $normalizedPromo === 'bde' => 3,
                $normalizedPromo === 'admin' => 4,
                $normalizedPromo === 'personnel' => 4,
                default => 0
            };
        }

        // Le type ING2 (FISE/FISEA) n'a de sens que pour la promo ING2.
        $normalizedType = strtoupper(trim((string)$ing2_type));
        $ing2TypeValue = (strtolower(trim((string)$promo)) === 'ing2' && in_array($normalizedType, ['FISE', 'FISEA'], true))
            ? $normalizedType
            : null;

        // Tentative d'insertion avec la colonne ing2_type (ajoutée par migration).
        // Repli automatique si la colonne n'existe pas encore en base.
        try {
            $stmt = $this->db->prepare("INSERT INTO users (nom, prenom, mail, password, promo, permission, ing2_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
            return $stmt->execute([$nom, $prenom, $mail, $finalPassword, $promo, (int)$finalPermission, $ing2TypeValue]);
        } catch (\PDOException $e) {
            $stmt = $this->db->prepare("INSERT INTO users (nom, prenom, mail, password, promo, permission) VALUES (?, ?, ?, ?, ?, ?)");
            return $stmt->execute([$nom, $prenom, $mail, $finalPassword, $promo, (int)$finalPermission]);
        }
    }

    /**
     * Détermine si un utilisateur est autorisé à passer une soutenance.
     * Règle métier (retour client juin 2026) : seuls les ING2 FISE y sont éligibles.
     *
     * - promo doit être "ING2" (insensible à la casse) ;
     * - si le type ING2 est connu, il doit être "FISE" (les FISEA sont exclus) ;
     * - pour les comptes ING2 historiques sans type renseigné, on accorde le bénéfice
     *   du doute afin de ne pas bloquer les étudiants existants (à backfiller).
     *
     * @param array $user Données utilisateur (doit contenir au moins 'promo')
     * @return bool
     */
    public function isEligibleForSoutenance(array $user): bool {
        // En base, la promotion encode déjà la spécialité : "ING2FISE" / "ING2FISEA"
        // (et la colonne ing2_type est, historiquement, vide). On parse donc la promo,
        // avec un repli sur ing2_type si jamais elle est renseignée.
        $promo = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string)($user['promo'] ?? '')));

        // L'utilisateur doit être un ING2.
        if (strpos($promo, 'ING2') !== 0) {
            return false;
        }

        // Détection FISE / FISEA. ATTENTION : "ING2FISEA" contient "FISE", il faut
        // donc tester "FISEA" en premier.
        $type = '';
        if (strpos($promo, 'FISEA') !== false) {
            $type = 'FISEA';
        } elseif (strpos($promo, 'FISE') !== false) {
            $type = 'FISE';
        } elseif (array_key_exists('ing2_type', $user)) {
            $type = strtoupper(trim((string)($user['ing2_type'] ?? '')));
        }

        if ($type === 'FISE') {
            return true;   // ING2 FISE : éligible
        }
        if ($type === 'FISEA') {
            return false;  // ING2 FISEA : exclu
        }

        // ING2 sans spécialité identifiable (donnée historique) : bénéfice du doute.
        return true;
    }
}
