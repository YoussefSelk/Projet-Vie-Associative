<?php
/**
 * =============================================================================
 * CLASSE DE CONNEXION À LA BASE DE DONNÉES
 * =============================================================================
 * 
 * Gère la connexion PDO à la base de données MySQL.
 * Les paramètres sont chargés depuis les variables d'environnement (.env).
 * 
 * @author Équipe de développement EILCO
 * @version 2.0
 */

class Database {
    /** @var string Hôte de la base de données */
    private $host;
    
    /** @var string Nom de la base de données */
    private $db_name;
    
    /** @var string Nom d'utilisateur */
    private $user;
    
    /** @var string Mot de passe */
    private $pass;
    
    /** @var PDO|null Instance de connexion PDO */
    private $db;

    /**
     * Constructeur - charge les paramètres depuis l'environnement
     */
    public function __construct() {
        $this->host = Environment::get('DB_HOST', 'localhost');
        $this->db_name = Environment::get('DB_NAME', 'test_projet_tech');
        $this->user = Environment::get('DB_USER', 'root');
        $this->pass = Environment::get('DB_PASS', '');
    }

    /**
     * Établit la connexion à la base de données
     * 
     * @return PDO Instance de connexion PDO
     * @throws PDOException En cas d'erreur de connexion
     */
    public function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            $this->db = new PDO($dsn, $this->user, $this->pass, $options);
            
        } catch (PDOException $e) {
            // En production, journaliser l'erreur sans exposer les détails
            if (Environment::isProduction()) {
                ErrorHandler::logError("Erreur de connexion BDD: " . $e->getMessage(), 'CRITICAL', [
                    'host' => $this->host,
                    'database' => $this->db_name
                ]);
                die("Échec de la connexion à la base de données. Veuillez contacter l'administrateur.");
            } else {
                die("Erreur de connexion BDD: " . $e->getMessage());
            }
        }
        return $this->db;
    }

    /**
     * Récupère l'instance de connexion existante ou en crée une nouvelle
     * 
     * @return PDO Instance de connexion PDO
     */
    public function getConnection() {
        return $this->db ?? $this->connect();
    }
}
