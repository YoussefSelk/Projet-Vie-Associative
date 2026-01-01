<?php
/**
 * =============================================================================
 * CONTRÔLEUR DES INSCRIPTIONS AUX ÉVÉNEMENTS
 * =============================================================================
 * 
 * Gère les inscriptions et désinscriptions des utilisateurs aux événements :
 * - Inscription à un événement
 * - Désinscription d'un événement
 * - Récupération des inscriptions d'un utilisateur
 * 
 * @author Équipe de développement EILCO
 * @version 2.0
 */

class SubscriptionController {
    /** @var EventSubscription Modèle des inscriptions */
    private $subscriptionModel;
    
    /** @var Event Modèle des événements */
    private $eventModel;
    
    /** @var PDO Instance de connexion à la base de données */
    private $db;

    /**
     * Constructeur
     * @param PDO $database Instance de connexion PDO
     */
    public function __construct($database) {
        $this->db = $database;
        $this->subscriptionModel = new EventSubscription($database);
        $this->eventModel = new Event($database);
    }

    /**
     * Inscrit l'utilisateur connecté à un événement
     * Supporte les requêtes GET et POST
     * Redirige vers la page de l'événement après inscription
     */
    public function subscribe() {
        validateSession();
        
        // Supporter les requêtes GET et POST
        $event_id = $_GET['event_id'] ?? $_POST['event_id'] ?? null;
        if (!$event_id) {
            redirect('index.php');
        }

        // Vérifier que l'événement existe
        $event = $this->eventModel->getEventById($event_id);
        if (!$event) {
            redirect('index.php');
        }

        // Inscrire seulement si pas déjà inscrit
        if (!$this->subscriptionModel->isSubscribed($event_id, $_SESSION['id'])) {
            $this->subscriptionModel->subscribeToEvent($event_id, $_SESSION['id']);
        }

        // Redirection sécurisée vers le référent ou la page événement
        $referer = $_SERVER['HTTP_REFERER'] ?? null;
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if ($referer && strpos($referer, $host) !== false) {
            redirect($referer);
        }
        redirect('index.php?page=event-view&id=' . $event_id);
    }

    /**
     * Désinscrit l'utilisateur connecté d'un événement
     * Supporte les requêtes GET et POST
     * Redirige vers la page de l'événement après désinscription
     */
    public function unsubscribe() {
        validateSession();
        
        // Supporter les requêtes GET et POST
        $event_id = $_GET['event_id'] ?? $_POST['event_id'] ?? null;
        if (!$event_id) {
            redirect('index.php');
        }

        $this->subscriptionModel->unsubscribeFromEvent($event_id, $_SESSION['id']);
        
        // Redirection sécurisée vers le référent ou la page événement
        $referer = $_SERVER['HTTP_REFERER'] ?? null;
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if ($referer && strpos($referer, $host) !== false) {
            redirect($referer);
        }
        redirect('index.php?page=event-view&id=' . $event_id);
    }

    /**
     * Récupère toutes les inscriptions de l'utilisateur connecté
     * 
     * @return array Liste des inscriptions pour la vue
     */
    public function getUserSubscriptions() {
        validateSession();
        
        $subscriptions = $this->subscriptionModel->getUserSubscriptions($_SESSION['id']);
        
        return [
            'subscriptions' => $subscriptions
        ];
    }
}
