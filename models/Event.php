<?php
declare(strict_types=1);
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
    /**
 * Récupère tous les événements validés avec le nom de leur club
 */
public function getAllValidatedEvents() {
    // Jointure entre fiche_event (fe) et fiche_club (fc)
    $stmt = $this->db->prepare("
        SELECT fe.*, fc.nom_club 
        FROM fiche_event fe 
        INNER JOIN fiche_club fc ON fe.club_orga = fc.club_id 
        WHERE fe.validation_finale = 1 
        ORDER BY fe.date_ev DESC
    ");
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
    /**
     * Crée un nouvel événement ou activité
     * * @param array $data Données de l'événement
     * @return bool Succès de la création
     */
    public function createEvent($data) {
        $date_ev = null;
        $horaire_debut = null;
        $horaire_fin = null;
        
        if (!empty($data['date_event'])) {
            $datetime = new DateTime($data['date_event']);
            $date_ev = $datetime->format('Y-m-d');
            $horaire_debut = $datetime->format('H:i:s');
            $datetime->modify('+2 hours');
            $horaire_fin = $datetime->format('H:i:s');
        } elseif (!empty($data['date_ev'])) {
            $date_ev = $data['date_ev'];
            $horaire_debut = $data['horaire_debut'] ?? '13:00:00';
            $horaire_fin = $data['horaire_fin'] ?? '17:00:00';
        }
        
        // Ajout de type_event et doc_organisation dans la requête
        $stmt = $this->db->prepare("
            INSERT INTO fiche_event (
                titre, type_event, description, date_ev, horaire_debut, horaire_fin, 
                club_orga, campus, lieu, id_responsable,
                financement_bde, montant, fiche_sanitaire, affiche, doc_organisation,
                validation_admin, validation_bde, validation_tuteur, validation_finale
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL, NULL, NULL)
        ");

        return $stmt->execute([
            $data['nom_event'] ?? $data['titre'] ?? '',
            $data['type_event'] ?? 'event', // Nouvelle valeur
            $data['description'] ?? '',
            $date_ev,
            $horaire_debut,
            $horaire_fin,
            $data['club_id'] ?? $data['club_orga'] ?? null,
            $data['campus'] ?? '',
            $data['lieu'] ?? '',
            $data['user_id'] ?? $data['id_responsable'] ?? null,
            isset($data['financement_bde']) && $data['financement_bde'] == 1 ? 1 : 0,
            intval($data['montant'] ?? $data['budget'] ?? 0),
            $data['fiche_sanitaire'] ?? null,
            $data['affiche'] ?? null,
            $data['doc_organisation'] ?? null // Nouvelle valeur
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
    /**
     * Met à jour les informations d'un événement et réinitialise les validations
     * * @param int $id Identifiant de l'événement
     * @param array $data Données à mettre à jour
     * @return bool Succès de l'opération
     */
    public function updateEvent($id, $data) {
        // 1. Définition des colonnes autorisées pour la mise à jour dynamique
        $field_mapping = [
            'nom_event'        => 'titre',
            'type_event'       => 'type_event',
            'description'      => 'description',
            'date_ev'          => 'date_ev',
            'horaire_debut'    => 'horaire_debut',
            'horaire_fin'      => 'horaire_fin',
            'campus'           => 'campus',
            'lieu'             => 'lieu',
            'financement_bde'  => 'financement_bde',
            'montant'          => 'montant',
            'affiche'          => 'affiche',
            'doc_organisation' => 'doc_organisation',
            'fiche_sanitaire'  => 'fiche_sanitaire'
        ];
        
        $fields = [];
        $values = [];

        // 2. Construction dynamique de la requête pour les champs modifiables
        foreach ($data as $key => $value) {
            if (isset($field_mapping[$key])) {
                // PROTECTION : Si c'est un champ de fichier et qu'il est vide/null, 
                // on ne l'ajoute pas à la requête pour garder l'ancien chemin en BDD.
                if (in_array($key, ['affiche', 'doc_organisation', 'fiche_sanitaire']) && empty($value)) {
                    continue;
                }
                $fields[] = $field_mapping[$key] . " = ?";
                $values[] = $value;
            }
        }

        if (empty($fields)) {
            // No editable fields changed; still reset validations to restart the workflow.
            $fields = [];
        }

        // AJOUT FORCÉ : Réinitialisation des validations
        // Ces colonnes repassent à NULL systématiquement lors d'une modification
        $fields[] = "validation_bde = NULL";
        $fields[] = "validation_tuteur = NULL";
        $fields[] = "validation_admin = NULL";
        $fields[] = "validation_finale = NULL";
        $fields[] = "motif_refus = NULL";

        $values[] = $id;
        
        $sql = "UPDATE fiche_event SET " . implode(", ", $fields) . " WHERE event_id = ?";
        $stmt = $this->db->prepare($sql);
        
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