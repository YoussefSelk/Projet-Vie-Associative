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
    public function createUser($nom, $prenom, $mail, $password, $promo = 'etu', $isHashed = false, $permission = null) {
        // Si le mot de passe n'est pas déjà haché, le hacher
        $finalPassword = $isHashed ? $password : password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        $finalPermission = $permission;
        if ($finalPermission === null) {
            $normalizedPromo = strtolower(trim((string)$promo));
            // Préfixes étudiants. On teste par préfixe car la promo ING2 encode la
            // spécialité ("ing2fise" / "ing2fisea") — il ne faut pas la confondre
            // avec un statut non étudiant.
            $studentPrefixes = ['cp1', 'cp2', 'ing1', 'ing2', 'ing3', 'etu'];
            $isStudent = false;
            foreach ($studentPrefixes as $prefix) {
                if (str_starts_with($normalizedPromo, $prefix)) {
                    $isStudent = true;
                    break;
                }
            }
            $finalPermission = match (true) {
                $isStudent => 1,
                // Un "futur tuteur" doit être validé par un admin avant d'obtenir la permission 2.
                $normalizedPromo === 'tuteur' => 1,
                $normalizedPromo === 'bde' => 3,
                $normalizedPromo === 'admin' => 4,
                $normalizedPromo === 'personnel' => 4,
                default => 0
            };
        }

        // La spécialité ING2 (FISE/FISEA) est portée directement par `promo`
        // ("ING2FISE" / "ING2FISEA"). Aucune colonne ing2_type n'est utilisée.
        $stmt = $this->db->prepare("INSERT INTO users (nom, prenom, mail, password, promo, permission) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$nom, $prenom, $mail, $finalPassword, $promo, (int)$finalPermission]);
    }

    /**
     * Détermine si un utilisateur est autorisé à passer une soutenance.
     * Règle métier (retour client juin 2026) : seuls les ING2 FISE y sont éligibles.
     *
     * La spécialité est lue UNIQUEMENT depuis `promo`, qui encode déjà la valeur
     * en base : "ING2FISE" (éligible) / "ING2FISEA" (exclu). Aucune autre colonne
     * n'est utilisée.
     *
     * @param array $user Données utilisateur (doit contenir au moins 'promo')
     * @return bool
     */
    public function isEligibleForSoutenance(array $user): bool {
        $promo = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string)($user['promo'] ?? '')));

        // L'utilisateur doit être un ING2.
        if (strpos($promo, 'ING2') !== 0) {
            return false;
        }

        // ATTENTION : "ING2FISEA" contient "FISE" — tester "FISEA" en premier.
        if (strpos($promo, 'FISEA') !== false) {
            return false;  // ING2 FISEA : exclu
        }
        if (strpos($promo, 'FISE') !== false) {
            return true;   // ING2 FISE : éligible
        }

        // ING2 sans spécialité identifiable (donnée historique) : bénéfice du doute.
        return true;
    }
}
