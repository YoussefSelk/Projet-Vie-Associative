<?php

class SubscriptionController {
    private $subscriptionModel;
    private $eventModel;
    private $db;

    public function __construct($database) {
        $this->db = $database;
        $this->subscriptionModel = new EventSubscription($database);
        $this->eventModel = new Event($database);
    }

    public function subscribe() {
        validateSession();
        
        // Support both GET and POST requests
        $event_id = $_GET['event_id'] ?? $_POST['event_id'] ?? null;
        if (!$event_id) {
            redirect('index.php');
        }

        $event = $this->eventModel->getEventById($event_id);
        if (!$event) {
            redirect('index.php');
        }

        if (!$this->subscriptionModel->isSubscribed($event_id, $_SESSION['id'])) {
            $this->subscriptionModel->subscribeToEvent($event_id, $_SESSION['id']);
        }

        // Redirect back to referrer or event page
        $referer = $_SERVER['HTTP_REFERER'] ?? null;
        if ($referer && strpos($referer, 'localhost') !== false) {
            redirect($referer);
        }
        redirect('index.php?page=event-view&id=' . $event_id);
    }

    public function unsubscribe() {
        validateSession();
        
        // Support both GET and POST requests
        $event_id = $_GET['event_id'] ?? $_POST['event_id'] ?? null;
        if (!$event_id) {
            redirect('index.php');
        }

        $this->subscriptionModel->unsubscribeFromEvent($event_id, $_SESSION['id']);
        
        // Redirect back to referrer or event page
        $referer = $_SERVER['HTTP_REFERER'] ?? null;
        if ($referer && strpos($referer, 'localhost') !== false) {
            redirect($referer);
        }
        redirect('index.php?page=event-view&id=' . $event_id);
    }

    public function getUserSubscriptions() {
        validateSession();
        
        $subscriptions = $this->subscriptionModel->getUserSubscriptions($_SESSION['id']);
        
        return [
            'subscriptions' => $subscriptions
        ];
    }
}
