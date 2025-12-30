<?php
/**
 * Database Utility Class
 * Provides helpers for database operations, migrations, backups
 */

class DatabaseUtil {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Get table structure
     */
    public function getTableStructure($table_name) {
        $stmt = $this->db->prepare("DESCRIBE $table_name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all tables in database
     */
    public function getAllTables() {
        $stmt = $this->db->prepare("SHOW TABLES");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Count records in table
     */
    public function countRecords($table) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM $table");
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
            'subscribe_event', 'membres_club', 'rapport_event'
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

