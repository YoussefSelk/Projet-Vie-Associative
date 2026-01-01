<?php
/**
 * Database Utility Class
 * Provides helpers for database operations, migrations, backups
 */

class DatabaseUtil {
    private $db;
    
    // Liste blanche des tables autorisées pour prévenir l'injection SQL
    // Note: rapport_event n'est pas une table, c'est une colonne de fiche_event
    // Note: abonnements = table d'inscriptions aux événements (id=user_id, event_id, date_abonnement)
    private static $allowedTables = [
        'users', 'fiche_club', 'fiche_event', 
        'membres_club', 'mails', 'abonnements',
        'config', 'ville'
    ];

    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Valide qu'un nom de table est autorisé (prévention injection SQL)
     * @param string $table Nom de la table à valider
     * @return bool True si la table est autorisée
     */
    private function isTableAllowed($table) {
        return in_array($table, self::$allowedTables, true);
    }

    /**
     * Récupère la structure d'une table
     * @param string $table_name Nom de la table
     * @return array Structure de la table ou tableau vide si table non autorisée
     */
    public function getTableStructure($table_name) {
        // Validation pour prévenir l'injection SQL
        if (!$this->isTableAllowed($table_name)) {
            ErrorHandler::logSecurity(
                "Tentative d'accès à une table non autorisée: $table_name",
                'WARN',
                ['table' => $table_name, 'action' => 'getTableStructure']
            );
            return [];
        }
        $stmt = $this->db->prepare("DESCRIBE `$table_name`");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère toutes les tables de la base de données
     * @return array Liste des noms de tables
     */
    public function getAllTables() {
        $stmt = $this->db->prepare("SHOW TABLES");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Compte le nombre d'enregistrements dans une table
     * @param string $table Nom de la table
     * @return int|string Nombre d'enregistrements ou 'N/A' si table non autorisée
     */
    public function countRecords($table) {
        // Validation pour prévenir l'injection SQL
        if (!$this->isTableAllowed($table)) {
            ErrorHandler::logSecurity(
                "Tentative de comptage sur table non autorisée: $table",
                'WARN',
                ['table' => $table, 'action' => 'countRecords']
            );
            return 'N/A';
        }
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM `$table`");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    /**
     * Verify database structure
     */
    public function verifyStructure() {
        $required_tables = [
            'users', 'fiche_club', 'fiche_event', 
            'membres_club', 'abonnements', 'config', 'mails', 'ville'
        ];

        $existing_tables = $this->getAllTables();
        $missing_tables = array_diff($required_tables, $existing_tables);

        return [
            'all_present' => empty($missing_tables),
            'missing_tables' => $missing_tables,
            'existing_tables' => $existing_tables
        ];
    }

    /**
     * Get database statistics
     */
    public function getDatabaseStats() {
        $tables = $this->getAllTables();
        $stats = [];

        foreach ($tables as $table) {
            $stats[$table] = [
                'record_count' => $this->countRecords($table),
                'structure' => $this->getTableStructure($table)
            ];
        }

        return $stats;
    }

    /**
     * Backup database to SQL file
     */
    public function backupDatabase($filename = null) {
        if (!$filename) {
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        }

        $tables = $this->getAllTables();
        $backup = '';

        foreach ($tables as $table) {
            $stmt = $this->db->prepare("SHOW CREATE TABLE $table");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $backup .= "\n\n" . $result['Create Table'] . ";\n\n";

            $stmt = $this->db->prepare("SELECT * FROM $table");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $values = array_map(function($val) {
                    return $this->db->quote($val);
                }, array_values($row));

                $columns = implode(',', array_keys($row));
                $backup .= "INSERT INTO $table ($columns) VALUES (" . implode(',', $values) . ");\n";
            }
        }

        return $backup;
    }

    /**
     * Test database connection
     */
    public function testConnection() {
        try {
            $this->db->query("SELECT 1");
            return [
                'success' => true,
                'message' => 'Database connection successful'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}

