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
        
        $event_id = $_POST['event_id'] ?? null;
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

        redirect('index.php?page=event-view&id=' . $event_id);
    }

    public function unsubscribe() {
        validateSession();
        
        $event_id = $_POST['event_id'] ?? null;
        if (!$event_id) {
            redirect('index.php');
        }

        $this->subscriptionModel->unsubscribeFromEvent($event_id, $_SESSION['id']);
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
