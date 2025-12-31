<?php

/**
 * HomeController - Public home page functionality
 * Admin functions have been moved to AdminController
 */
class HomeController {
    private $eventModel;
    private $clubModel;
    private $db;

    public function __construct($database) {
        $this->db = $database;
        $this->eventModel = new Event($database);
        $this->clubModel = new Club($database);
    }

    /**
     * Public home page - shows events and clubs for logged-in users
     */
    public function index() {
        if (isset($_SESSION['id'])) {
            $events = $this->eventModel->getAllValidatedEvents();
            $clubs = $this->clubModel->getAllValidatedClubs();
        } else {
            $events = [];
            $clubs = [];
        }

        return [
            'events' => $events,
            'clubs' => $clubs
        ];
    }
}
