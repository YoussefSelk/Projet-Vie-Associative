<?php
/**
 * =============================================================================
 * MODÈLE ÉVÉNEMENT
 * =============================================================================
 * 
 * Gère toutes les opérations liées aux événements :
 * - Récupération des événements (validés, par ID, par utilisateur)
 * - Création et modification des fiches événements
 * - Gestion des inscriptions aux événements
 * - Suppression des événements
 * 
 * Table associée : fiche_event
 * Tables liées : abonnements, membres_club
 * 
 * @author Équipe de développement EILCO
 * @version 2.1
 */

class Event {
    /** @var PDO Instance de connexion à la base de données */
    private $db;

    /**
     * Constructeur
     * @param PDO $database Instance de connexion PDO
     */
    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Récupère tous les événements validés
     * 
     * @return array Liste des événements validés triés par date décroissante
     */
    public function getAllValidatedEvents() {
        $stmt = $this->db->prepare("SELECT * FROM fiche_event WHERE validation_finale = 1 ORDER BY date_ev DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un événement par son identifiant
     * 
     * @param int $id Identifiant de l'événement
     * @return array|false Données de l'événement ou false si non trouvé
     */
    public function getEventById($id) {
        $stmt = $this->db->prepare("SELECT * FROM fiche_event WHERE event_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les événements des clubs dont l'utilisateur est membre
     * 
     * @param int $user_id Identifiant de l'utilisateur
     * @return array Liste des événements associés aux clubs de l'utilisateur
     */
    public function getEventsByUser($user_id) {
        $stmt = $this->db->prepare("
            SELECT DISTINCT fe.* FROM fiche_event fe
            INNER JOIN membres_club mc ON fe.club_orga = mc.club_id
            WHERE mc.membre_id = ? AND mc.valide = 1
            ORDER BY fe.date_ev DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les événements auxquels l'utilisateur est inscrit
     * 
     * @param int $user_id Identifiant de l'utilisateur
     * @return array Liste des événements avec inscription validée
     */
    public function getSubscribedEvents($user_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT fe.* FROM fiche_event fe
                INNER JOIN abonnements a ON fe.event_id = a.event_id
                WHERE a.id = ? AND fe.validation_finale = 1
                ORDER BY fe.date_ev DESC
            ");
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // La table abonnements peut être vide
            return [];
        }
    }

    /**
     * Récupère tous les événements (validés ou non)
     * 
     * @return array Liste de tous les événements triés par date décroissante
     */
    public function getAllEvents() {
        $stmt = $this->db->prepare("SELECT * FROM fiche_event ORDER BY date_ev DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crée un nouvel événement
     * L'événement est créé avec toutes les validations à NULL (en attente)
     * 
     * Structure BD fiche_event:
     * event_id, date_depot, validation_admin, validation_bde, validation_tuteur, validation_soutenance,
     * titre, club_orga, campus, date_ev (DATE), horaire_debut (TIME), horaire_fin (TIME),
     * lieu, id_responsable, description, financement_bde, montant, fiche_sanitaire, affiche,
     * rapport_event, motif_refus, validation_finale, commentaire_validation
     * 
     * @param array $data Données de l'événement
     * @return bool Succès de la création
     */
    public function createEvent($data) {
        // Parse date_event (datetime-local format: "2025-12-15T14:30") into date and time parts
        $date_ev = null;
        $horaire_debut = null;
        $horaire_fin = null;
        
        if (!empty($data['date_event'])) {
            $datetime = new DateTime($data['date_event']);
            $date_ev = $datetime->format('Y-m-d');
            $horaire_debut = $datetime->format('H:i:s');
            // Default end time: 2 hours after start
            $datetime->modify('+2 hours');
            $horaire_fin = $datetime->format('H:i:s');
        } elseif (!empty($data['date_ev'])) {
            $date_ev = $data['date_ev'];
            $horaire_debut = $data['horaire_debut'] ?? '13:00:00';
            $horaire_fin = $data['horaire_fin'] ?? '17:00:00';
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO fiche_event (
                titre, description, date_ev, horaire_debut, horaire_fin, 
                club_orga, campus, lieu, id_responsable,
                financement_bde, montant,
                validation_admin, validation_bde, validation_tuteur, validation_finale
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL, NULL, NULL)
        ");
        return $stmt->execute([
            $data['nom_event'] ?? $data['titre'] ?? '',
            $data['description'] ?? '',
            $date_ev,
            $horaire_debut,
            $horaire_fin,
            $data['club_id'] ?? $data['club_orga'] ?? null,
            $data['campus'] ?? '',
            $data['lieu'] ?? '',
            $data['user_id'] ?? $data['id_responsable'] ?? null,
            isset($data['financement_bde']) ? 1 : 0,
            intval($data['montant'] ?? $data['budget'] ?? 0)
        ]);
    }

    /**
     * Met à jour les informations d'un événement
     * Gère le mapping entre les noms de champs courants et les colonnes réelles
     * 
     * Structure BD: titre, description, date_ev, horaire_debut, horaire_fin, campus, lieu
     * 
     * @param int $id Identifiant de l'événement
     * @param array $data Données à mettre à jour
     * @return bool Succès de l'opération
     */
    public function updateEvent($id, $data) {
        // Mapping des noms de champs vers les colonnes de la base de données
        $field_mapping = [
            'nom_event' => 'titre',
            'date_event' => 'date_ev', // Will be processed specially
            'club_id' => 'club_orga'
        ];
        
        $allowed_fields = ['titre', 'description', 'date_ev', 'horaire_debut', 'horaire_fin', 'campus', 'lieu'];
        $fields = [];
        $values = [];

        // Handle date_event specially - parse datetime into date + time
        if (!empty($data['date_event'])) {
            $datetime = new DateTime($data['date_event']);
            $fields[] = "date_ev = ?";
            $values[] = $datetime->format('Y-m-d');
            $fields[] = "horaire_debut = ?";
            $values[] = $datetime->format('H:i:s');
            unset($data['date_event']);
        }

        foreach ($data as $key => $value) {
            // Mapper le nom du champ si nécessaire
            $db_field = $field_mapping[$key] ?? $key;
            
            if (in_array($db_field, $allowed_fields) && !in_array("$db_field = ?", $fields)) {
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

    /**
     * Supprime un événement de la base de données
     * Attention : cette action est irréversible
     * 
     * @param int $id Identifiant de l'événement à supprimer
     * @return bool Succès de la suppression
     */
    public function deleteEvent($id) {
        $stmt = $this->db->prepare("DELETE FROM fiche_event WHERE event_id = ?");
        return $stmt->execute([$id]);
    }
}