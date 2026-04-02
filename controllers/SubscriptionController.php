<?php
declare(strict_types=1);
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
     * Supporte uniquement les requêtes POST
     * Redirige vers la page de l'événement après inscription
     */
    public function subscribe() {
        validateSession();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            ErrorHandler::renderHttpError(405, 'Méthode HTTP non autorisée.');
        }

        $event_id = $_POST['event_id'] ?? null;
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
        if ($referer && !empty($host) && parse_url($referer, PHP_URL_HOST) === $host) {
            redirect($referer);
        }
        redirect('index.php?page=event-view&id=' . (int)$event_id);
    }

    /**
     * Désinscrit l'utilisateur connecté d'un événement
     * Supporte uniquement les requêtes POST
     * Redirige vers la page de l'événement après désinscription
     */
    public function unsubscribe() {
        validateSession();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            ErrorHandler::renderHttpError(405, 'Méthode HTTP non autorisée.');
        }

        $event_id = $_POST['event_id'] ?? null;
        if (!$event_id) {
            redirect('index.php');
        }

        $this->subscriptionModel->unsubscribeFromEvent($event_id, $_SESSION['id']);
        
        // Redirection sécurisée vers le référent ou la page événement
        $referer = $_SERVER['HTTP_REFERER'] ?? null;
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if ($referer && !empty($host) && parse_url($referer, PHP_URL_HOST) === $host) {
            redirect($referer);
        }
        redirect('index.php?page=event-view&id=' . (int)$event_id);
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

    /**
     * Toggle abonnement via AJAX (retourne JSON)
     */
    public function toggleSubscriptionAjax() {
        header('Content-Type: application/json; charset=utf-8');
        validateSession();
        
        try {
            $event_id = $_POST['event_id'] ?? null;
            if (!$event_id) {
                echo json_encode(['success' => false, 'error' => 'ID événement manquant']);
                exit;
            }
            
            $event = $this->eventModel->getEventById($event_id);
            if (!$event) {
                echo json_encode(['success' => false, 'error' => 'Événement introuvable']);
                exit;
            }
            
            $user_id = $_SESSION['id'];
            $isSubscribed = $this->subscriptionModel->isSubscribed($event_id, $user_id);
            
            if ($isSubscribed) {
                $this->subscriptionModel->unsubscribeFromEvent($event_id, $user_id);
                $newState = false;
            } else {
                $this->subscriptionModel->subscribeToEvent($event_id, $user_id);
                $newState = true;
            }
            
            echo json_encode([
                'success' => true,
                'subscribed' => $newState,
                'event_id' => $event_id,
                'event_title' => $event['titre'] ?? ''
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => 'Erreur interne'], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }
}
