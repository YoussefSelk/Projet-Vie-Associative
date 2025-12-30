<?php

class Event {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function getAllValidatedEvents() {
        $stmt = $this->db->prepare("SELECT * FROM fiche_event WHERE validation_finale = 1 ORDER BY date_ev DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventById($id) {
        $stmt = $this->db->prepare("SELECT * FROM fiche_event WHERE event_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getEventsByUser($user_id) {
        // Get events from clubs the user is a member of
        $stmt = $this->db->prepare("
            SELECT DISTINCT fe.* FROM fiche_event fe
            INNER JOIN membres_club mc ON fe.club_orga = mc.club_id
            WHERE mc.membre_id = ? AND mc.valide = 1
            ORDER BY fe.date_ev DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSubscribedEvents($user_id) {
        // Get events the user has subscribed to
        try {
            $stmt = $this->db->prepare("
                SELECT fe.* FROM fiche_event fe
                INNER JOIN subscribe_event se ON fe.event_id = se.event_id
                WHERE se.user_id = ? AND fe.validation_finale = 1
                ORDER BY fe.date_ev DESC
            ");
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // subscribe_event table might not exist yet
            return [];
        }
    }

    public function getAllEvents() {
        $stmt = $this->db->prepare("SELECT * FROM fiche_event ORDER BY date_ev DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createEvent($data) {
        $stmt = $this->db->prepare("INSERT INTO fiche_event (titre, description, date_ev, club_orga, campus, validation_finale, validation_tuteur, validation_bde) VALUES (?, ?, ?, ?, ?, 0, 0, 0)");
        return $stmt->execute([
            $data['nom_event'] ?? $data['titre'],
            $data['description'],
            $data['date_event'] ?? $data['date_ev'],
            $data['club_id'] ?? $data['club_orga'] ?? null,
            $data['campus']
        ]);
    }

    public function updateEvent($id, $data) {
        // Map common field names to actual database column names
        $field_mapping = [
            'nom_event' => 'titre',
            'date_event' => 'date_ev',
            'club_id' => 'club_orga'
        ];
        
        $allowed_fields = ['titre', 'description', 'date_ev', 'campus'];
        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            // Map field name if needed
            $db_field = $field_mapping[$key] ?? $key;
            
            if (in_array($db_field, $allowed_fields)) {
                $fields[] = "$db_field = ?";
                $values[] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $id;
        $stmt = $this->db->prepare("UPDATE fiche_event SET " . implode(", ", $fields) . " WHERE event_id = ?");
        return $stmt->execute($values);
    }

    public function deleteEvent($id) {
        $stmt = $this->db->prepare("DELETE FROM fiche_event WHERE event_id = ?");
        return $stmt->execute([$id]);
    }
}