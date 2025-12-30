<?php

class User {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function getUserById($id) {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserByEmail($email) {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE mail = ?');
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllUsers() {
        $stmt = $this->db->prepare('SELECT * FROM users ORDER BY nom ASC');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function authenticate($email, $password) {
        $user = $this->getUserByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return null;
    }

    public function updatePassword($email, $password) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE mail = ?");
        return $stmt->execute([$hashedPassword, $email]);
    }

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

    public function createUser($nom, $prenom, $mail, $password, $promo = 'etu') {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->db->prepare("INSERT INTO users (nom, prenom, mail, password, promo, permission) VALUES (?, ?, ?, ?, ?, 0)");
        return $stmt->execute([$nom, $prenom, $mail, $hashedPassword, $promo]);
    }
}