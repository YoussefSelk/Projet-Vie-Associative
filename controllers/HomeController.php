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

    /**
     * API JSON pour le calendrier AJAX
     * Renvoie les événements d'un mois donné avec les infos d'abonnement
     * 
     * @return void Envoie une réponse JSON et termine l'exécution
     */
    public function calendarData() {
        header('Content-Type: application/json; charset=utf-8');
        
        $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
        $year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
        
        // Corriger les dépassements
        if ($month < 1) { $month = 12; $year--; }
        elseif ($month > 12) { $month = 1; $year++; }
        
        // Récupérer les événements du mois
        $query = $this->db->prepare("
            SELECT e.event_id, e.titre, e.date_ev, e.campus, e.horaire_debut, e.horaire_fin, e.lieu, e.club_orga, c.nom_club
            FROM fiche_event e
            LEFT JOIN fiche_club c ON e.club_orga = c.club_id
            WHERE MONTH(e.date_ev) = :mois
            AND YEAR(e.date_ev) = :annee
            AND e.validation_finale = 1
        ");
        $query->execute(['mois' => $month, 'annee' => $year]);
        $events = $query->fetchAll(PDO::FETCH_ASSOC);
        
        // Abonnements de l'utilisateur
        $subscriptions = [];
        $user_id = $_SESSION['id'] ?? null;
        if ($user_id) {
            foreach ($events as $event) {
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM abonnements WHERE id = :uid AND event_id = :eid");
                $stmt->execute(['uid' => $user_id, 'eid' => $event['event_id']]);
                $subscriptions[$event['event_id']] = (int)$stmt->fetchColumn() > 0;
            }
        }
        
        // Rappels (événements dans les 7 prochains jours)
        $reminders = [];
        if ($user_id) {
            $future_limit = (new DateTime())->modify('+7 days')->format('Y-m-d');
            $stmt = $this->db->prepare("
                SELECT e.event_id, e.titre, e.date_ev
                FROM fiche_event e
                JOIN abonnements a ON e.event_id = a.event_id
                WHERE a.id = :user_id
                  AND e.validation_finale = 1
                  AND e.date_ev BETWEEN CURDATE() AND :limit_date
            ");
            $stmt->execute(['user_id' => $user_id, 'limit_date' => $future_limit]);
            $reminders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Infos du calendrier
        $nb_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $first_day = date('w', strtotime("$year-$month-01"));
        $first_day = ($first_day == 0) ? 6 : $first_day - 1;
        
        $mois_francais = [
            1 => "Janvier", 2 => "Février", 3 => "Mars", 4 => "Avril",
            5 => "Mai", 6 => "Juin", 7 => "Juillet", 8 => "Août",
            9 => "Septembre", 10 => "Octobre", 11 => "Novembre", 12 => "Décembre"
        ];
        
        // Organiser par jour
        $event_by_day = [];
        foreach ($events as $event) {
            $day = (int)date('j', strtotime($event['date_ev']));
            $event['subscribed'] = $subscriptions[$event['event_id']] ?? false;
            $event_by_day[$day][] = $event;
        }
        
        echo json_encode([
            'month' => $month,
            'year' => $year,
            'month_name' => $mois_francais[$month],
            'nb_days' => $nb_days,
            'first_day_offset' => $first_day,
            'events_by_day' => $event_by_day,
            'reminders' => $reminders,
            'is_logged_in' => $user_id !== null,
            'today' => date('Y-m-d'),
            'prev' => ['month' => $month == 1 ? 12 : $month - 1, 'year' => $month == 1 ? $year - 1 : $year],
            'next' => ['month' => $month == 12 ? 1 : $month + 1, 'year' => $month == 12 ? $year + 1 : $year],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
