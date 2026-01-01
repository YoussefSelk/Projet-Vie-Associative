<?php
/**
 * =============================================================================
 * CONTRÔLEUR PAGE D'ACCUEIL
 * =============================================================================
 * 
 * Gère la page d'accueil publique de l'application :
 * - Affichage des événements validés
 * - Affichage des clubs validés
 * 
 * Note : Les fonctions d'administration ont été déplacées vers AdminController
 * 
 * @author Équipe de développement EILCO
 * @version 2.0
 */

class HomeController {
    /** @var Event Modèle pour les événements */
    private $eventModel;
    
    /** @var Club Modèle pour les clubs */
    private $clubModel;
    
    /** @var PDO Instance de connexion à la base de données */
    private $db;

    /**
     * Constructeur
     * Initialise les modèles nécessaires
     * 
     * @param PDO $database Instance de connexion PDO
     */
    public function __construct($database) {
        $this->db = $database;
        $this->eventModel = new Event($database);
        $this->clubModel = new Club($database);
    }

    /**
     * Page d'accueil publique
     * Affiche les événements et clubs validés pour les utilisateurs connectés
     * Les visiteurs non connectés voient une page vide
     * 
     * @return array Données pour la vue [events, clubs]
     */
    public function index() {
        if (isset($_SESSION['id'])) {
            // Utilisateur connecté : afficher le contenu
            $events = $this->eventModel->getAllValidatedEvents();
            $clubs = $this->clubModel->getAllValidatedClubs();
        } else {
            // Visiteur non connecté : listes vides
            $events = [];
            $clubs = [];
        }

        return [
            'events' => $events,
            'clubs' => $clubs
        ];
    }
}
