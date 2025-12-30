<?php

class EventReport {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function getReportById($id) {
        $stmt = $this->db->prepare("SELECT * FROM rapport_event WHERE rapport_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getReportsByEvent($event_id) {
        $stmt = $this->db->prepare("SELECT * FROM rapport_event WHERE event_id = ? ORDER BY date_creation DESC");
        $stmt->execute([$event_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllReports() {
        $stmt = $this->db->prepare("SELECT * FROM rapport_event ORDER BY date_creation DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createReport($data) {
        $stmt = $this->db->prepare("INSERT INTO rapport_event (event_id, contenu, date_creation) VALUES (?, ?, NOW())");
        return $stmt->execute([$data['event_id'], $data['contenu']]);
    }

    public function updateReport($id, $data) {
        $stmt = $this->db->prepare("UPDATE rapport_event SET contenu = ? WHERE rapport_id = ?");
        return $stmt->execute([$data['contenu'], $id]);
    }

    public function deleteReport($id) {
        $stmt = $this->db->prepare("DELETE FROM rapport_event WHERE rapport_id = ?");
        return $stmt->execute([$id]);
    }
}