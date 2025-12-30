<?php

class Database {
    private $host;
    private $db_name;
    private $user;
    private $pass;
    private $db;

    public function __construct() {
        // Load from environment variables
        $this->host = Environment::get('DB_HOST', 'localhost');
        $this->db_name = Environment::get('DB_NAME', 'test_projet_tech');
        $this->user = Environment::get('DB_USER', 'root');
        $this->pass = Environment::get('DB_PASS', '');
    }

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
            // In production, log the error but don't expose details
            if (Environment::isProduction()) {
                error_log("Database Connection Error: " . $e->getMessage());
                die("Database connection failed. Please contact the administrator.");
            } else {
                die("Database Connection Error: " . $e->getMessage());
            }
        }
        return $this->db;
    }

    public function getConnection() {
        return $this->db ?? $this->connect();
    }
}
