<?php
declare(strict_types=1);
/**
 * =============================================================================
 * MODULE D'EXPORT CSV
 * =============================================================================
 *
 * Gère la génération et le téléchargement des exports CSV :
 *   - Liste des clubs validés
 *   - Membres d'un club / membres avec soutenance
 *   - Événements d'un club / par période / passés / à venir
 *
 * Contrôle d'accès :
 *   - Niveau 2 (tuteur)  : exports globaux autorisés + accès restreint aux clubs
 *                          dont il est le tuteur référent pour les exports ciblés.
 *   - Niveau 3 et supérieur (BDE, admin, super-admin) : accès complet.
 *
 * Toute génération de fichier est tracée dans le journal de sécurité.
 */

class ExportController
{
    /** @var PDO Connexion à la base de données */
    private $db;

    // -------------------------------------------------------------------------
    // Paramètres de mise en forme CSV
    // -------------------------------------------------------------------------

    /** Séparateur de colonnes : tabulation, indépendante de la locale Excel */
    private const DELIMITEUR    = "\t";
    /** Caractère d'encadrement des valeurs */
    private const GUILLEMET     = '"';
    /** Désactive le mode d'échappement non-standard de PHP (utilise le doublement RFC 4180) */
    private const ECHAPPEMENT   = "\0";
    /** Marque d'ordre d'octet UTF-16 LE, reconnue universellement par Excel */
    private const BOM           = "\xFF\xFE";

    // -------------------------------------------------------------------------
    // Paramètres de protection contre les abus
    // -------------------------------------------------------------------------

    /** Nombre maximal d'exports autorisés par fenêtre de temps */
    private const LIMITE_EXPORTS   = 30;
    /** Durée de la fenêtre de contrôle, en secondes */
    private const FENETRE_SECONDES = 60;
    /** Plage maximale autorisée pour l'export par période, en jours */
    private const PLAGE_MAX_JOURS  = 730;

    public function __construct($database)
    {
        $this->db = $database;
    }

    // =========================================================================
    // PAGE D'ACCUEIL DU MODULE
    // =========================================================================

    /**
     * Affiche la page principale du module d'export.
     * Charge la liste des clubs validés pour alimenter les menus déroulants.
     *
     * @return array Données transmises à la vue.
     */
    public function index(): array
    {
        checkPermission(2);

        $clubs = $this->db->query(
            "SELECT club_id, nom_club, campus
             FROM fiche_club
             WHERE validation_finale = 1
             ORDER BY campus ASC, nom_club ASC"
        )->fetchAll(PDO::FETCH_ASSOC);

        return ['clubs' => $clubs];
    }

    // =========================================================================
    // EXPORT — CLUBS
    // =========================================================================

    /**
     * Exporte la liste complète des clubs validés de la plateforme.
     * Accessible à tous les utilisateurs autorisés (tuteur inclus).
     */
    public function exportClubs(): void
    {
        checkPermission(2);
        $this->verifierRateLimit();

        $clubs = $this->fetchValidatedClubsForGlobalExport();
        $clubIds = array_map(static fn(array $club): int => (int)$club['club_id'], $clubs);

        $membersByClub = $this->fetchMembersGroupedByClub($clubIds);
        $eventsByClub = $this->fetchEventsGroupedByClub($clubIds);
        $participantsByClub = $this->fetchParticipantCountsByClub($clubIds);

        $headers = [
            'Nom du club',
            'Type de club',
            'Campus',
            'Description',
            'Tuteur',
            'Email du tuteur',
            'Validation administrateur',
            'Validation tuteur',
            'Validation BDE',
            'Validation finale',
            'Membres',
            'Rôles',
            'Soutenance',
            'Nombre total d\'événements',
            'Événements',
            'Nombre total de participants aux événements',
            'Documents des événements',
        ];

        // Retour client (juin 2026) : une LIGNE PAR MEMBRE (colonnes Membres / Rôles /
        // Soutenance séparées), au lieu d'un bloc « Membres et rôles » par club.
        // Les informations du club ne sont affichées que sur la première ligne du club.
        $rows = [];
        foreach ($clubs as $club) {
            $clubId = (int)$club['club_id'];
            $members = $membersByClub[$clubId] ?? [];
            $events = $eventsByClub[$clubId] ?? [];

            $clubLevel = [
                'Nom du club' => $this->formatText($club['nom_club'] ?? null),
                'Type de club' => $this->formatText($club['type_club'] ?? null),
                'Campus' => $this->formatText($club['campus'] ?? null),
                'Description' => $this->formatText($club['description'] ?? null),
                'Tuteur' => $this->formatPerson($club['tuteur_prenom'] ?? null, $club['tuteur_nom'] ?? null),
                'Email du tuteur' => $this->formatEmail($club['tuteur_mail'] ?? null),
                'Validation administrateur' => $this->formatValidation($club['validation_admin'] ?? null),
                'Validation tuteur' => $this->formatValidation($club['validation_tuteur'] ?? null),
                'Validation BDE' => $this->formatValidation($club['validation_bde'] ?? null),
                'Validation finale' => $this->formatValidation($club['validation_finale'] ?? null),
                'Nombre total d\'événements' => (string)count($events),
                'Événements' => $this->buildEventsSummary($events),
                'Nombre total de participants aux événements' => (string)($participantsByClub[$clubId] ?? 0),
                'Documents des événements' => $this->buildEventDocumentsSummary($events),
            ];
            // Cellules vides pour les lignes suivantes du même club (effet « fusionné »).
            $emptyClubLevel = array_map(static fn() => '', $clubLevel);

            if (empty($members)) {
                $rows[] = $clubLevel + [
                    'Membres' => 'Aucun membre',
                    'Rôles' => '',
                    'Soutenance' => '',
                ];
                continue;
            }

            $first = true;
            foreach ($members as $member) {
                $rows[] = ($first ? $clubLevel : $emptyClubLevel) + [
                    'Membres' => $this->formatPerson($member['prenom'] ?? null, $member['nom'] ?? null),
                    'Rôles' => $this->formatText($member['fonction'] ?? null, 'Membre'),
                    'Soutenance' => ((int)($member['soutenance'] ?? 0) === 1) ? 'Oui' : 'Non',
                ];
                $first = false;
            }
        }

        $this->journaliserExport('export_clubs_valides_global', null);
        $this->envoyerExcelXlsx(
            $rows,
            $this->sanitizeFilename('export_clubs_valides_' . date('Y-m-d') . '.xlsx'),
            $headers
        );
    }

    // =========================================================================
    // EXPORT — MEMBRES
    // =========================================================================
    /**
     * Exporte les clubs rattachés à chaque utilisateur, sans identifiants internes.
     */
    public function exportUserClubs(): void
    {
        checkPermission(2);
        $this->verifierRateLimit();

        $rowsByUser = [];
        $stmt = $this->db->prepare("
            SELECT u.nom,
                   u.prenom,
                   u.mail,
                   u.promo,
                   fc.nom_club,
                   fc.type_club,
                   fc.campus,
                   mc.fonction
            FROM membres_club mc
            INNER JOIN users u ON u.id = mc.membre_id
            INNER JOIN fiche_club fc ON fc.club_id = mc.club_id
                                    AND fc.validation_finale = 1
            ORDER BY u.nom ASC, u.prenom ASC, fc.nom_club ASC
        ");
        $stmt->execute();

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $userKey = $this->formatEmail($row['mail'] ?? null)
                . '|'
                . $this->formatPerson($row['prenom'] ?? null, $row['nom'] ?? null);

            if (!isset($rowsByUser[$userKey])) {
                $rowsByUser[$userKey] = [
                    'Utilisateur' => $this->formatPerson($row['prenom'] ?? null, $row['nom'] ?? null),
                    'Email' => $this->formatEmail($row['mail'] ?? null),
                    'Promo' => $this->formatPromo($row['promo'] ?? null),
                    'clubs' => [],
                ];
            }

            $rowsByUser[$userKey]['clubs'][] =
                '<strong>' . htmlspecialchars($this->formatText($row['nom_club'] ?? null), ENT_QUOTES, 'UTF-8') . '</strong>'
                . ' - Type : ' . htmlspecialchars($this->formatText($row['type_club'] ?? null), ENT_QUOTES, 'UTF-8')
                . ' - Campus : ' . htmlspecialchars($this->formatText($row['campus'] ?? null), ENT_QUOTES, 'UTF-8')
                . ' - Rôle : ' . htmlspecialchars($this->formatText($row['fonction'] ?? null, 'Rôle non renseigné'), ENT_QUOTES, 'UTF-8');
        }

        $headers = [
            'Utilisateur',
            'Email',
            'Promo',
            'Nombre de clubs',
            'Clubs et rôles',
        ];

        $rows = [];
        foreach ($rowsByUser as $userRow) {
            $clubs = $userRow['clubs'];
            $rows[] = [
                'Utilisateur' => $userRow['Utilisateur'],
                'Email' => $userRow['Email'],
                'Promo' => $userRow['Promo'],
                'Nombre de clubs' => (string)count($clubs),
                'Clubs et rôles' => empty($clubs) ? 'Aucun club' : implode('<br>', $clubs),
            ];
        }

        $this->journaliserExport('export_clubs_par_utilisateur', null);
        $this->envoyerExcelXlsx(
            $rows,
            $this->sanitizeFilename('export_clubs_par_utilisateur_' . date('Y-m-d') . '.xlsx'),
            $headers
        );
    }

    private function fetchValidatedClubsForGlobalExport(): array
    {
        $stmt = $this->db->prepare("
            SELECT fc.*,
                   tuteur.nom AS tuteur_nom,
                   tuteur.prenom AS tuteur_prenom,
                   tuteur.promo AS tuteur_promo,
                   tuteur.mail AS tuteur_mail
            FROM fiche_club fc
            LEFT JOIN users tuteur ON CAST(fc.tuteur AS UNSIGNED) = tuteur.id
            WHERE fc.validation_finale = 1
            ORDER BY fc.nom_club ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function fetchMembersGroupedByClub(array $clubIds): array
    {
        if (empty($clubIds)) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT mc.club_id,
                   mc.fonction,
                   mc.soutenance,
                   mc.valide,
                   u.nom,
                   u.prenom,
                   u.promo,
                   u.mail,
                   u.permission
            FROM membres_club mc
            INNER JOIN users u ON u.id = mc.membre_id
            INNER JOIN fiche_club fc ON fc.club_id = mc.club_id AND fc.validation_finale = 1
            ORDER BY mc.club_id ASC, mc.fonction ASC, u.nom ASC, u.prenom ASC
        ");
        $stmt->execute();

        $grouped = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $member) {
            $clubId = (int)($member['club_id'] ?? 0);
            if ($clubId > 0) {
                $grouped[$clubId][] = $member;
            }
        }

        return $grouped;
    }

    private function fetchEventsGroupedByClub(array $clubIds): array
    {
        if (empty($clubIds)) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT fe.*,
                   responsable.nom AS responsable_nom,
                   responsable.prenom AS responsable_prenom,
                   responsable.mail AS responsable_mail
            FROM fiche_event fe
            INNER JOIN fiche_club fc ON fc.club_id = CAST(fe.club_orga AS UNSIGNED)
                                    AND fc.validation_finale = 1
            LEFT JOIN users responsable ON responsable.id = fe.id_responsable
            ORDER BY CAST(fe.club_orga AS UNSIGNED) ASC, fe.date_ev DESC, fe.horaire_debut ASC
        ");
        $stmt->execute();

        $grouped = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $event) {
            $clubId = (int)($event['club_orga'] ?? 0);
            if ($clubId > 0) {
                $grouped[$clubId][] = $event;
            }
        }

        return $grouped;
    }

    private function fetchParticipantCountsByClub(array $clubIds): array
    {
        if (empty($clubIds)) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT CAST(fe.club_orga AS UNSIGNED) AS club_id,
                   COUNT(*) AS total_participants
            FROM fiche_event fe
            INNER JOIN fiche_club fc ON fc.club_id = CAST(fe.club_orga AS UNSIGNED)
                                    AND fc.validation_finale = 1
            INNER JOIN abonnements ab ON ab.event_id = fe.event_id
            GROUP BY CAST(fe.club_orga AS UNSIGNED)
        ");
        $stmt->execute();

        $counts = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $counts[(int)$row['club_id']] = (int)$row['total_participants'];
        }

        return $counts;
    }

    public function exportClubMembers(): void
    {
        checkPermission(2);
        $this->verifierRateLimit();

        if (($_GET['club_id'] ?? '') === 'all') {
            $this->exportAllClubMembers();
        }

        $clubId = $this->lireClubId();
        $club   = $this->getClubOrAbort($clubId);
        $this->verifierAccesClub($clubId);

        $stmt = $this->db->prepare("
            SELECT
                u.nom                                                AS 'Nom',
                u.prenom                                             AS 'Prénom',
                u.mail                                               AS 'Email',
                u.promo                                              AS 'Promotion',
                COALESCE(NULLIF(TRIM(mc.fonction), ''), 'Membre')    AS 'Rôle',
                CASE mc.soutenance
                    WHEN 1 THEN 'Oui'
                    ELSE 'Non'
                END                                                  AS 'Soutenance',
                COALESCE(
                    CONCAT(t.prenom, ' ', t.nom),
                    'Non renseigné'
                )                                                    AS 'Tuteur du club'
            FROM membres_club mc
            JOIN users u       ON u.id       = mc.membre_id
            JOIN fiche_club fc ON fc.club_id  = mc.club_id
            LEFT JOIN users t  ON t.id        = CAST(fc.tuteur AS UNSIGNED)
            WHERE mc.club_id = :club_id
              AND mc.valide  = 1
            ORDER BY u.nom ASC, u.prenom ASC
        ");
        $stmt->execute([':club_id' => $clubId]);
        $lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->journaliserExport('export_membres', $clubId);
        $this->envoyerCsv(
            $lignes,
            'membres_' . $this->slug($club['nom_club']) . '_' . date('Y-m-d') . '.csv'
        );
    }

    private function exportAllClubMembers(): void
    {
        // Retour client (juin 2026) : le rôle affiché doit être le rôle DANS LE CLUB
        // (membres_club.fonction) et non le rôle/permission du site. Une ligne par
        // adhésion (un membre présent dans plusieurs clubs apparaît plusieurs fois).
        $stmt = $this->db->prepare("
            SELECT
                u.nom                                                AS 'Nom',
                u.prenom                                             AS 'Prénom',
                u.mail                                               AS 'Email',
                u.promo                                              AS 'Promotion',
                fc.campus                                            AS 'Site',
                fc.nom_club                                          AS 'Club',
                COALESCE(NULLIF(TRIM(mc.fonction), ''), 'Membre')    AS 'Rôle dans le club'
            FROM membres_club mc
            JOIN users u       ON u.id       = mc.membre_id
            JOIN fiche_club fc ON fc.club_id  = mc.club_id
            WHERE fc.validation_finale = 1
              AND mc.valide = 1
            ORDER BY u.nom ASC, u.prenom ASC, fc.nom_club ASC
        ");
        $stmt->execute();
        $lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->journaliserExport('export_membres_tous_clubs', null);
        $this->envoyerCsv(
            $lignes,
            'membres_tous_clubs_' . date('Y-m-d') . '.csv'
        );
    }

    /**
     * Exporte les membres marqués « soutenance » d'un club.
     *
     * Un tuteur (niveau 2) ne peut exporter que les membres du club
     * dont il est le tuteur référent.
     */
    public function exportSoutenanceMembers(): void
    {
        checkPermission(2);
        $this->verifierRateLimit();

        // Retour client (juin 2026) : l'extraction des soutenances doit être une
        // liste GLOBALE (tous clubs) et non club par club. Le mode par club reste
        // disponible si un identifiant explicite est transmis.
        $clubIdParam = $_GET['club_id'] ?? '';
        if ($clubIdParam === '' || $clubIdParam === 'all') {
            $this->exportAllSoutenanceMembers();
            return;
        }

        $clubId = $this->lireClubId();
        $club   = $this->getClubOrAbort($clubId);
        $this->verifierAccesClub($clubId);

        $stmt = $this->db->prepare("
            SELECT
                u.nom                                                AS 'Nom',
                u.prenom                                             AS 'Prénom',
                u.mail                                               AS 'Email',
                u.promo                                              AS 'Promotion',
                COALESCE(NULLIF(TRIM(mc.fonction), ''), 'Membre')    AS 'Rôle',
                COALESCE(
                    CONCAT(t.prenom, ' ', t.nom),
                    'Non renseigné'
                )                                                    AS 'Tuteur du club'
            FROM membres_club mc
            JOIN users u       ON u.id       = mc.membre_id
            JOIN fiche_club fc ON fc.club_id  = mc.club_id
            LEFT JOIN users t  ON t.id        = CAST(fc.tuteur AS UNSIGNED)
            WHERE mc.club_id   = :club_id
              AND mc.valide     = 1
              AND mc.soutenance = 1
            ORDER BY u.nom ASC, u.prenom ASC
        ");
        $stmt->execute([':club_id' => $clubId]);
        $lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->journaliserExport('export_soutenance', $clubId);
        $this->envoyerCsv(
            $lignes,
            'soutenance_' . $this->slug($club['nom_club']) . '_' . date('Y-m-d') . '.csv'
        );
    }

    /**
     * Exporte la liste GLOBALE des membres marqués « soutenance », tous clubs
     * confondus. (Retour client juin 2026)
     *
     * Colonnes : Nom, Prénom, Site, Club, Rôle dans le club.
     * Le rôle est le rôle DANS LE CLUB (membres_club.fonction), pas le rôle du site.
     *
     * Portée : permission >= 3 (BDE/admin) voit tous les clubs ; un tuteur (niveau 2)
     * ne voit que les clubs dont il est le tuteur référent.
     */
    private function exportAllSoutenanceMembers(): void
    {
        $params = [];
        $scopeCondition = '';
        if ((int)($_SESSION['permission'] ?? 0) < 3) {
            $scopeCondition = ' AND fc.tuteur = :tuteur ';
            $params[':tuteur'] = (string)($_SESSION['id'] ?? '');
        }

        $stmt = $this->db->prepare("
            SELECT
                u.nom                                                AS 'Nom',
                u.prenom                                             AS 'Prénom',
                fc.campus                                            AS 'Site',
                fc.nom_club                                          AS 'Club',
                COALESCE(NULLIF(TRIM(mc.fonction), ''), 'Membre')    AS 'Rôle dans le club'
            FROM membres_club mc
            JOIN users u       ON u.id      = mc.membre_id
            JOIN fiche_club fc ON fc.club_id = mc.club_id
            WHERE fc.validation_finale = 1
              AND mc.valide     = 1
              AND mc.soutenance = 1
              {$scopeCondition}
            ORDER BY u.nom ASC, u.prenom ASC, fc.nom_club ASC
        ");
        $stmt->execute($params);
        $lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->journaliserExport('export_soutenance_global', null);
        $this->envoyerCsv(
            $lignes,
            'soutenances_global_' . date('Y-m-d') . '.csv'
        );
    }

    // =========================================================================
    // EXPORT — ÉVÉNEMENTS
    // =========================================================================

    /**
     * Exporte les événements validés d'un club.
     *
     * Un tuteur (niveau 2) ne peut exporter que les événements du club
     * dont il est le tuteur référent.
     */
    public function exportClubEvents(): void
    {
        checkPermission(2);
        $this->verifierRateLimit();

        $clubId = $this->lireClubId();
        $club   = $this->getClubOrAbort($clubId);
        $this->verifierAccesClub($clubId);

        $stmt = $this->db->prepare("
            SELECT
                fe.titre                                             AS 'Titre',
                DATE_FORMAT(fe.date_ev, '%d/%m/%Y')                 AS 'Date',
                fe.horaire_debut                                     AS 'Heure de début',
                fe.horaire_fin                                       AS 'Heure de fin',
                fe.campus                                            AS 'Campus',
                fe.lieu                                              AS 'Lieu',
                COALESCE(
                    CONCAT(u.prenom, ' ', u.nom),
                    'Non renseigné'
                )                                                    AS 'Responsable',
                CASE fe.financement_bde
                    WHEN 1 THEN 'Oui'
                    ELSE 'Non'
                END                                                  AS 'Financement BDE',
                CASE
                    WHEN fe.financement_bde = 1
                        THEN CONCAT(fe.montant, ' EUR')
                    ELSE '-'
                END                                                  AS 'Montant',
                CASE WHEN fe.rapport_event IS NOT NULL
                    THEN 'Oui'
                    ELSE 'Non'
                END                                                  AS 'Rapport déposé',
                CASE fe.validation_soutenance
                    WHEN 1 THEN 'Oui'
                    ELSE 'Non'
                END                                                  AS 'Soutenance'
            FROM fiche_event fe
            LEFT JOIN users u ON u.id = fe.id_responsable
            WHERE fe.club_orga        = :club_id
              AND fe.validation_finale = 1
            ORDER BY fe.date_ev DESC
        ");
        $stmt->execute([':club_id' => (string)$clubId]);
        $lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->journaliserExport('export_evenements_club', $clubId);
        $this->envoyerCsv(
            $lignes,
            'evenements_' . $this->slug($club['nom_club']) . '_' . date('Y-m-d') . '.csv'
        );
    }

    /**
     * Exporte les événements validés compris dans une plage de dates.
     * Les paramètres date_debut et date_fin sont lus dans la requête GET.
     * La plage est limitée à deux ans maximum.
     */
    public function exportEventsByPeriod(): void
    {
        checkPermission(2);
        $this->verifierRateLimit();

        $dateDebut = filter_var($_GET['date_debut'] ?? '', FILTER_DEFAULT);
        $dateFin   = filter_var($_GET['date_fin']   ?? '', FILTER_DEFAULT);

        if (!$this->estDateValide($dateDebut) || !$this->estDateValide($dateFin)) {
            $this->abort('Les dates renseignées sont invalides.');
        }

        if ($dateDebut > $dateFin) {
            $this->abort('La date de début doit être antérieure ou égale à la date de fin.');
        }

        $debut = new \DateTime($dateDebut);
        $fin   = new \DateTime($dateFin);
        if ($debut->diff($fin)->days > self::PLAGE_MAX_JOURS) {
            $this->abort(
                'La plage sélectionnée dépasse la limite autorisée de '
                . self::PLAGE_MAX_JOURS . ' jours.'
            );
        }

        $stmt = $this->db->prepare("
            SELECT
                fe.titre                                             AS 'Titre',
                DATE_FORMAT(fe.date_ev, '%d/%m/%Y')                 AS 'Date',
                fe.horaire_debut                                     AS 'Heure de début',
                fe.horaire_fin                                       AS 'Heure de fin',
                fc.nom_club                                          AS 'Club organisateur',
                fe.campus                                            AS 'Campus',
                fe.lieu                                              AS 'Lieu',
                COALESCE(
                    CONCAT(u.prenom, ' ', u.nom),
                    'Non renseigné'
                )                                                    AS 'Responsable',
                CASE fe.financement_bde
                    WHEN 1 THEN 'Oui'
                    ELSE 'Non'
                END                                                  AS 'Financement BDE',
                CASE fe.validation_soutenance
                    WHEN 1 THEN 'Oui'
                    ELSE 'Non'
                END                                                  AS 'Soutenance'
            FROM fiche_event fe
            LEFT JOIN fiche_club fc ON fc.club_id = CAST(fe.club_orga AS UNSIGNED)
            LEFT JOIN users      u  ON u.id       = fe.id_responsable
            WHERE fe.validation_finale = 1
              AND fe.date_ev BETWEEN :date_debut AND :date_fin
            ORDER BY fe.date_ev ASC
        ");
        $stmt->execute([':date_debut' => $dateDebut, ':date_fin' => $dateFin]);
        $lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->journaliserExport('export_evenements_periode', null);
        $this->envoyerCsv(
            $lignes,
            'evenements_periode_'
            . str_replace('-', '', $dateDebut) . '_'
            . str_replace('-', '', $dateFin)   . '.csv'
        );
    }

    /**
     * Exporte tous les événements validés dont la date est antérieure à aujourd'hui.
     */
    public function exportPastEvents(): void
    {
        checkPermission(2);
        $this->verifierRateLimit();

        $stmt = $this->db->query("
            SELECT
                fe.titre                                             AS 'Titre',
                DATE_FORMAT(fe.date_ev, '%d/%m/%Y')                 AS 'Date',
                fe.horaire_debut                                     AS 'Heure de début',
                fe.horaire_fin                                       AS 'Heure de fin',
                fc.nom_club                                          AS 'Club organisateur',
                fe.campus                                            AS 'Campus',
                fe.lieu                                              AS 'Lieu',
                COALESCE(
                    CONCAT(u.prenom, ' ', u.nom),
                    'Non renseigné'
                )                                                    AS 'Responsable',
                CASE WHEN fe.rapport_event IS NOT NULL
                    THEN 'Oui'
                    ELSE 'Non'
                END                                                  AS 'Rapport déposé'
            FROM fiche_event fe
            LEFT JOIN fiche_club fc ON fc.club_id = CAST(fe.club_orga AS UNSIGNED)
            LEFT JOIN users      u  ON u.id       = fe.id_responsable
            WHERE fe.validation_finale = 1
              AND fe.date_ev < CURDATE()
            ORDER BY fe.date_ev DESC
        ");
        $lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->journaliserExport('export_evenements_passes', null);
        $this->envoyerCsv($lignes, 'evenements_passes_' . date('Y-m-d') . '.csv');
    }

    /**
     * Exporte tous les événements validés dont la date est égale ou postérieure à aujourd'hui.
     */
    public function exportUpcomingEvents(): void
    {
        checkPermission(2);
        $this->verifierRateLimit();

        $stmt = $this->db->query("
            SELECT
                fe.titre                                             AS 'Titre',
                DATE_FORMAT(fe.date_ev, '%d/%m/%Y')                 AS 'Date',
                fe.horaire_debut                                     AS 'Heure de début',
                fe.horaire_fin                                       AS 'Heure de fin',
                fc.nom_club                                          AS 'Club organisateur',
                fe.campus                                            AS 'Campus',
                fe.lieu                                              AS 'Lieu',
                COALESCE(
                    CONCAT(u.prenom, ' ', u.nom),
                    'Non renseigné'
                )                                                    AS 'Responsable',
                CASE fe.financement_bde
                    WHEN 1 THEN 'Oui'
                    ELSE 'Non'
                END                                                  AS 'Financement BDE'
            FROM fiche_event fe
            LEFT JOIN fiche_club fc ON fc.club_id = CAST(fe.club_orga AS UNSIGNED)
            LEFT JOIN users      u  ON u.id       = fe.id_responsable
            WHERE fe.validation_finale = 1
              AND fe.date_ev >= CURDATE()
            ORDER BY fe.date_ev ASC
        ");
        $lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->journaliserExport('export_evenements_a_venir', null);
        $this->envoyerCsv($lignes, 'evenements_a_venir_' . date('Y-m-d') . '.csv');
    }

    // =========================================================================
    // MÉTHODES PRIVÉES — SÉCURITÉ
    // =========================================================================

    /**
     * Lit et valide l'identifiant de club transmis en GET.
     *
     * @return int Identifiant du club
     */
    private function lireClubId(): int
    {
        $clubId = filter_var($_GET['club_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$clubId || $clubId <= 0) {
            $this->abort('Identifiant de club manquant ou invalide.');
        }

        return (int)$clubId;
    }

    /**
     * Vérifie que l'utilisateur courant a le droit d'accéder aux données d'un club.
     *
     * Les utilisateurs de niveau inférieur à 3 (BDE) ne peuvent consulter que
     * le club dont ils sont le tuteur référent.
     *
     * @param int $clubId Identifiant du club à vérifier
     */
    private function verifierAccesClub(int $clubId): void
    {
        if ($_SESSION['permission'] >= 3) {
            return;
        }

        $stmt = $this->db->prepare(
            "SELECT club_id FROM fiche_club WHERE club_id = ? AND tuteur = ?"
        );
        $stmt->execute([$clubId, (string)$_SESSION['id']]);

        if (!$stmt->fetch()) {
            ErrorHandler::logSecurity(
                'Tentative d\'accès non autorisé à l\'export du club #' . $clubId,
                'WARN',
                ['user_id' => $_SESSION['id'], 'club_id' => $clubId]
            );
            $this->abort('Vous n\'êtes pas autorisé à exporter les données de ce club.');
        }
    }

    /**
     * Applique une limitation du nombre d'exports par utilisateur et par fenêtre de temps.
     *
     * Déclenche un abandon si la limite est atteinte afin de prévenir
     * toute utilisation abusive du module.
     */
    private function verifierRateLimit(): void
    {
        $maintenant  = time();
        $historique  = $_SESSION['export_timestamps'] ?? [];

        // Ne conserver que les horodatages dans la fenêtre courante
        $historique = array_filter(
            $historique,
            static fn($ts) => is_int($ts) && ($maintenant - $ts) < self::FENETRE_SECONDES
        );

        if (count($historique) >= self::LIMITE_EXPORTS) {
            ErrorHandler::logSecurity(
                'Limite d\'exports atteinte',
                'WARN',
                ['user_id' => $_SESSION['id'], 'count' => count($historique)]
            );
            $this->abort(
                'Vous avez atteint la limite d\'exports autorisée. '
                . 'Veuillez patienter avant de relancer un export.'
            );
        }

        $historique[]                     = $maintenant;
        $_SESSION['export_timestamps']    = array_values($historique);
    }

    /**
     * Enregistre un événement d'export dans le journal de sécurité.
     *
     * @param string   $typeExport Identifiant du type d'export (ex. 'export_clubs')
     * @param int|null $clubId     Identifiant du club concerné, ou null pour les exports globaux
     */
    private function journaliserExport(string $typeExport, ?int $clubId): void
    {
        $contexte = [
            'user_id'     => $_SESSION['id']    ?? null,
            'user_email'  => $_SESSION['email'] ?? null,
            'type_export' => $typeExport,
            'club_id'     => $clubId,
        ];

        ErrorHandler::logSecurity('Export CSV déclenché', 'INFO', $contexte);
    }

    // =========================================================================
    // MÉTHODES PRIVÉES — UTILITAIRES
    // =========================================================================

    /**
     * Envoie un tableau de données sous forme de fichier CSV téléchargeable.
     *
     * L'encodage utilisé assure la compatibilité avec Microsoft Excel
     * sur toutes les versions et configurations régionales.
     *
     * @param array  $lignes     Tableau associatif ; les clés du premier élément forment l'en-tête.
     * @param string $nomFichier Nom du fichier proposé au téléchargement.
     */
    private function envoyerExcelXlsx(array $lignes, string $nomFichier, array $headers): void
    {
        $body = $this->buildXlsxWorkbook($lignes, $headers);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . addslashes($nomFichier) . '"');
        header('Content-Length: ' . strlen($body));
        header('Pragma: no-cache');
        header('Expires: 0');
        header('X-Content-Type-Options: nosniff');

        echo $body;
        exit;
    }

    private function buildXlsxWorkbook(array $lignes, array $headers): string
    {
        $rowsXml = [];
        $hyperlinks = [];
        $rowsXml[] = $this->buildXlsxRow($headers, 1, true, $hyperlinks);

        $rowIndex = 2;
        if (empty($lignes)) {
            $rowsXml[] = $this->buildXlsxRow(['Aucune donnée disponible pour cet export.'], $rowIndex, false, $hyperlinks);
        } else {
            foreach ($lignes as $ligne) {
                $row = [];
                foreach ($headers as $header) {
                    $row[] = $ligne[$header] ?? 'Non renseigné';
                }
                $rowsXml[] = $this->buildXlsxRow($row, $rowIndex, false, $hyperlinks);
                $rowIndex++;
            }
        }

        $lastColumn = $this->excelColumnName(max(1, count($headers)));
        $lastRow = max(1, $rowIndex - 1);

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<dimension ref="A1:' . $lastColumn . $lastRow . '"/>'
            . '<sheetViews><sheetView workbookViewId="0"/></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="18"/>'
            . '<cols>' . $this->buildXlsxColumns(count($headers)) . '</cols>'
            . '<sheetData>' . implode('', $rowsXml) . '</sheetData>'
            . $this->buildXlsxHyperlinksXml($hyperlinks)
            . '</worksheet>';

        $files = [
            '[Content_Types].xml' => $this->xlsxContentTypesXml(),
            '_rels/.rels' => $this->xlsxRootRelsXml(),
            'xl/workbook.xml' => $this->xlsxWorkbookXml(),
            'xl/_rels/workbook.xml.rels' => $this->xlsxWorkbookRelsXml(),
            'xl/styles.xml' => $this->xlsxStylesXml(),
            'xl/worksheets/sheet1.xml' => $sheetXml,
            'docProps/core.xml' => $this->xlsxCoreXml(),
            'docProps/app.xml' => $this->xlsxAppXml(),
        ];

        if (!empty($hyperlinks)) {
            $files['xl/worksheets/_rels/sheet1.xml.rels'] = $this->xlsxSheetRelsXml($hyperlinks);
        }

        return $this->buildStoredZip($files);
    }

    private function buildXlsxRow(array $values, int $rowIndex, bool $header = false, array &$hyperlinks = []): string
    {
        $cells = [];
        $maxLines = 1;
        foreach (array_values($values) as $index => $value) {
            $column = $this->excelColumnName($index + 1);
            $cellRef = $column . $rowIndex;
            $isHyperlink = is_array($value) && !empty($value['hyperlink']);
            $style = $header ? 1 : ($isHyperlink ? 3 : 2);
            $rawText = $this->xlsxCellText($value);
            $maxLines = max($maxLines, substr_count($rawText, "\n") + 1);
            $text = htmlspecialchars($rawText, ENT_XML1 | ENT_COMPAT, 'UTF-8');

            if ($isHyperlink) {
                $hyperlinks[] = [
                    'ref' => $cellRef,
                    'url' => (string)$value['hyperlink'],
                ];
            }

            $cells[] = '<c r="' . $cellRef . '" t="inlineStr" s="' . $style . '"><is><t xml:space="preserve">' . $text . '</t></is></c>';
        }

        $height = $header ? 22 : min(220, max(18, $maxLines * 17));
        return '<row r="' . $rowIndex . '" ht="' . $height . '" customHeight="1">' . implode('', $cells) . '</row>';
    }

    private function buildXlsxColumns(int $count): string
    {
        $xml = '';
        for ($i = 1; $i <= max(1, $count); $i++) {
            $width = $i >= 5 ? 48 : 24;
            $xml .= '<col min="' . $i . '" max="' . $i . '" width="' . $width . '" customWidth="1"/>';
        }
        return $xml;
    }

    private function buildXlsxHyperlinksXml(array $hyperlinks): string
    {
        if (empty($hyperlinks)) {
            return '';
        }

        $xml = '<hyperlinks>';
        foreach (array_values($hyperlinks) as $index => $hyperlink) {
            $xml .= '<hyperlink ref="'
                . htmlspecialchars($hyperlink['ref'], ENT_XML1 | ENT_COMPAT, 'UTF-8')
                . '" r:id="rId'
                . ($index + 1)
                . '"/>';
        }
        return $xml . '</hyperlinks>';
    }

    private function xlsxCellText($value): string
    {
        if (is_array($value)) {
            $value = $value['text'] ?? $value['label'] ?? $value['hyperlink'] ?? 'Non renseigné';
        }

        $text = (string)$value;
        $text = preg_replace_callback(
            '#<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)</a>#is',
            static function (array $matches): string {
                $label = trim(strip_tags($matches[2]));
                $url = html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
                return ($label !== '' ? $label . ' : ' : '') . $url;
            },
            $text
        ) ?? $text;
        $text = preg_replace('#<br\s*/?>#i', "\n", $text) ?? $text;
        $text = preg_replace('#</p>|</div>|</li>#i', "\n", $text) ?? $text;
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace("/[ \t]+\n/", "\n", $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;
        $text = trim($text);

        return $text === '' ? 'Non renseigné' : $text;
    }

    private function excelColumnName(int $index): string
    {
        $name = '';
        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)) . $name;
            $index = intdiv($index, 26);
        }
        return $name;
    }

    private function buildStoredZip(array $files): string
    {
        $localParts = [];
        $centralParts = [];
        $offset = 0;

        foreach ($files as $name => $content) {
            $name = str_replace('\\', '/', $name);
            $nameBytes = $name;
            $content = (string)$content;
            $crc = $this->unsignedCrc32($content);
            $size = strlen($content);

            $localHeader = pack('VvvvvvVVVvv', 0x04034b50, 20, 0, 0, 0, 0, $crc, $size, $size, strlen($nameBytes), 0)
                . $nameBytes;
            $localParts[] = $localHeader . $content;

            $centralParts[] = pack('VvvvvvvVVVvvvvvVV', 0x02014b50, 20, 20, 0, 0, 0, 0, $crc, $size, $size, strlen($nameBytes), 0, 0, 0, 0, 0, $offset)
                . $nameBytes;

            $offset += strlen($localHeader) + $size;
        }

        $centralDirectory = implode('', $centralParts);
        $centralOffset = $offset;
        $centralSize = strlen($centralDirectory);
        $count = count($files);
        $end = pack('VvvvvVVv', 0x06054b50, 0, 0, $count, $count, $centralSize, $centralOffset, 0);

        return implode('', $localParts) . $centralDirectory . $end;
    }

    private function unsignedCrc32(string $content): int
    {
        return (int)sprintf('%u', crc32($content));
    }

    private function xlsxContentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            . '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            . '</Types>';
    }

    private function xlsxRootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            . '</Relationships>';
    }

    private function xlsxWorkbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Export" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private function xlsxWorkbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    private function xlsxSheetRelsXml(array $hyperlinks): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';

        foreach (array_values($hyperlinks) as $index => $hyperlink) {
            $xml .= '<Relationship Id="rId'
                . ($index + 1)
                . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/hyperlink" Target="'
                . htmlspecialchars($hyperlink['url'], ENT_XML1 | ENT_COMPAT, 'UTF-8')
                . '" TargetMode="External"/>';
        }

        return $xml . '</Relationships>';
    }

    private function xlsxStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="3"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font><font><u/><color rgb="FF0563C1"/><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="1"><fill><patternFill patternType="none"/></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="4">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"><alignment wrapText="1"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"><alignment wrapText="1" vertical="top"/></xf>'
            . '<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"><alignment wrapText="1" vertical="top"/></xf>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    private function xlsxCoreXml(): string
    {
        $now = gmdate('Y-m-d\TH:i:s\Z');
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" '
            . 'xmlns:dc="http://purl.org/dc/elements/1.1/" '
            . 'xmlns:dcterms="http://purl.org/dc/terms/" '
            . 'xmlns:dcmitype="http://purl.org/dc/dcmitype/" '
            . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:creator>Vie Etudiante EILCO</dc:creator>'
            . '<cp:lastModifiedBy>Vie Etudiante EILCO</cp:lastModifiedBy>'
            . '<dcterms:created xsi:type="dcterms:W3CDTF">' . $now . '</dcterms:created>'
            . '<dcterms:modified xsi:type="dcterms:W3CDTF">' . $now . '</dcterms:modified>'
            . '</cp:coreProperties>';
    }

    private function xlsxAppXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" '
            . 'xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            . '<Application>Vie Etudiante EILCO</Application>'
            . '</Properties>';
    }

    private function formatValidation($value): string
    {
        if ($value === null || trim((string)$value) === '') {
            return 'Non renseigné';
        }

        return match (trim((string)$value)) {
            '1' => 'Validé',
            '0' => 'En attente',
            '-1' => 'Refusé',
            default => 'Statut inconnu',
        };
    }

    private function formatText($value, string $fallback = 'Non renseigné'): string
    {
        if ($value === null) {
            return $fallback;
        }

        $text = trim((string)$value);
        return $text === '' ? $fallback : $this->sanitizeCsvCell($text);
    }

    private function formatDateFr($value): string
    {
        $text = trim((string)($value ?? ''));
        if ($text === '' || $text === '0000-00-00') {
            return 'Date non renseignée';
        }

        $date = DateTime::createFromFormat('!Y-m-d', substr($text, 0, 10));
        $errors = DateTime::getLastErrors();
        if (!$date || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            return 'Date non renseignée';
        }

        return $date->format('d/m/Y');
    }

    private function formatTimeFr($value): string
    {
        $text = trim((string)($value ?? ''));
        if ($text === '') {
            return 'Horaire non renseigné';
        }

        if (!preg_match('/^([01]\d|2[0-3]):([0-5]\d)(?::[0-5]\d)?$/', $text, $matches)) {
            return 'Horaire non renseigné';
        }

        return $matches[1] . ':' . $matches[2];
    }

    private function formatPerson($prenom, $nom): string
    {
        $parts = [];
        foreach ([$prenom, $nom] as $part) {
            $part = trim((string)($part ?? ''));
            if ($part !== '') {
                $parts[] = $part;
            }
        }

        return empty($parts) ? 'Personne non renseignée' : $this->sanitizeCsvCell(implode(' ', $parts));
    }

    private function formatEmail($value): string
    {
        return $this->formatText($value, 'Email non renseigné');
    }

    private function formatPromo($value): string
    {
        return $this->formatText($value, 'Promo non renseignée');
    }

    private function formatDocument($value, string $fallback = 'Aucun document'): string
    {
        return $this->formatText($value, $fallback);
    }

    private function sanitizeFilename($value): string
    {
        $filename = preg_replace('/[^A-Za-z0-9._-]+/', '_', (string)$value) ?? '';
        $filename = trim($filename, '._-');
        return $filename !== '' ? $filename : 'export.csv';
    }

    private function sanitizeCsvCell($value): string
    {
        $cell = trim((string)$value);
        $cell = preg_replace('/[\r\n\t]+/', ' ', $cell) ?? '';
        $cell = preg_replace('/\s{2,}/', ' ', $cell) ?? '';

        if ($cell === '') {
            return 'Non renseigné';
        }

        if (preg_match('/^[=+\-@]/', $cell)) {
            $cell = "'" . $cell;
        }

        return $cell;
    }

    private function buildMembersSummary(array $members): string
    {
        if (empty($members)) {
            return 'Aucun membre';
        }

        $items = [];
        foreach ($members as $member) {
            $items[] = $this->formatPerson($member['prenom'] ?? null, $member['nom'] ?? null)
                . ' - '
                . $this->formatText($member['fonction'] ?? null, 'Rôle non renseigné');
        }

        return $this->sanitizeCsvCell(implode(' | ', $items));
    }

    private function buildEventsSummary(array $events): string
    {
        if (empty($events)) {
            return 'Aucun événement';
        }

        $items = [];
        foreach (array_values($events) as $index => $event) {
            $items[] = '<strong>' . ($index + 1) . '. ' . htmlspecialchars($this->formatText($event['titre'] ?? null), ENT_QUOTES, 'UTF-8') . '</strong>'
                . '<br>Date : ' . htmlspecialchars($this->formatDateFr($event['date_ev'] ?? null), ENT_QUOTES, 'UTF-8')
                . '<br>Lieu : ' . htmlspecialchars($this->formatText($event['lieu'] ?? null), ENT_QUOTES, 'UTF-8')
                . '<br>Responsable : ' . htmlspecialchars($this->formatPerson($event['responsable_prenom'] ?? null, $event['responsable_nom'] ?? null), ENT_QUOTES, 'UTF-8')
                . '<br>Statut : ' . htmlspecialchars($this->getEventPeriodLabel($event['date_ev'] ?? null), ENT_QUOTES, 'UTF-8');
        }

        return implode('<br><br>', $items);
    }

    private function buildEventDocumentsSummary(array $events)
    {
        $links = [];
        $documentFields = [
            'fiche_sanitaire' => 'Fiche sanitaire',
            'affiche' => 'Affiche',
            'doc_organisation' => 'Document organisation',
            'rapport_event' => 'Rapport événement',
            'images_event' => 'Images événement',
        ];

        foreach ($events as $event) {
            $eventTitle = $this->formatText($event['titre'] ?? null);
            foreach ($documentFields as $field => $label) {
                foreach ($this->extractDocumentValues($event[$field] ?? null) as $index => $documentPath) {
                    $url = $this->buildAbsoluteResourceUrl($documentPath);
                    if ($url === null) {
                        continue;
                    }

                    $suffix = $index > 0 ? ' ' . ($index + 1) : '';
                    $links[] = [
                        'text' => $eventTitle . ' - ' . $label . $suffix,
                        'hyperlink' => $url,
                    ];
                }
            }
        }

        if (empty($links)) {
            return 'Aucun document';
        }

        $lines = [];
        foreach (array_values($links) as $index => $link) {
            $lines[] = ($index + 1) . '. ' . $link['text'] . "\nLien : " . $link['hyperlink'];
        }

        return [
            'text' => implode("\n\n", $lines),
            'hyperlink' => $links[0]['hyperlink'],
        ];
    }

    private function extractDocumentValues($value): array
    {
        $text = trim((string)($value ?? ''));
        if ($text === '') {
            return [];
        }

        $parts = array_map('trim', explode(',', $text));
        return array_values(array_filter($parts, static fn(string $part): bool => $part !== ''));
    }

    private function buildAbsoluteResourceUrl(string $path): ?string
    {
        $path = trim($path);
        if ($path === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $normalized = str_replace('\\', '/', $path);
        $normalized = preg_replace('#^(\.\./)+#', '', $normalized) ?? $normalized;
        $normalized = ltrim($normalized, '/');

        if ($normalized === '') {
            return null;
        }

        $segments = array_map('rawurlencode', explode('/', $normalized));
        $encodedPath = implode('/', $segments);
        $baseUrl = defined('BASE_URL') ? BASE_URL : Environment::getBaseUrl();

        return rtrim((string)$baseUrl, '/') . '/' . $encodedPath;
    }

    private function countMembersByStatus(array $members): array
    {
        $counts = ['valides' => 0, 'en_attente' => 0, 'refuses' => 0];
        foreach ($members as $member) {
            $status = trim((string)($member['valide'] ?? ''));
            if ($status === '1') {
                $counts['valides']++;
            } elseif ($status === '-1') {
                $counts['refuses']++;
            } else {
                $counts['en_attente']++;
            }
        }

        return $counts;
    }

    private function countEventsByPeriod(array $events): array
    {
        $counts = ['passes' => 0, 'a_venir' => 0];
        $today = new DateTime('today');

        foreach ($events as $event) {
            $date = $this->parseDate($event['date_ev'] ?? null);
            if (!$date) {
                continue;
            }

            if ($date < $today) {
                $counts['passes']++;
            } else {
                $counts['a_venir']++;
            }
        }

        return $counts;
    }

    private function getEventPeriodLabel($dateValue): string
    {
        $date = $this->parseDate($dateValue);
        if (!$date) {
            return 'Date non renseignée';
        }

        return $date < new DateTime('today') ? 'Passé' : 'À venir';
    }

    private function parseDate($value): ?DateTime
    {
        $text = trim((string)($value ?? ''));
        if ($text === '' || $text === '0000-00-00') {
            return null;
        }

        $date = DateTime::createFromFormat('!Y-m-d', substr($text, 0, 10));
        $errors = DateTime::getLastErrors();
        if (!$date || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            return null;
        }

        return $date;
    }

    private function envoyerCsv(array $lignes, string $nomFichier): void
    {
        // Construction du corps en mémoire avant l'envoi des en-têtes
        // (indispensable pour calculer Content-Length avec précision)
        $tmp = fopen('php://temp', 'r+b');

        if (empty($lignes)) {
            fputcsv($tmp, ['Aucune donnée disponible pour cet export.'], self::DELIMITEUR, self::GUILLEMET, self::ECHAPPEMENT);
        } else {
            fputcsv($tmp, array_keys($lignes[0]), self::DELIMITEUR, self::GUILLEMET, self::ECHAPPEMENT);

            foreach ($lignes as $ligne) {
                $nettoyee = array_map(static function ($val) {
                    if ($val === null) {
                        return '';
                    }
                    return str_replace(["\r\n", "\r", "\n"], ' ', trim((string)$val));
                }, $ligne);

                fputcsv($tmp, $nettoyee, self::DELIMITEUR, self::GUILLEMET, self::ECHAPPEMENT);
            }
        }

        rewind($tmp);
        $csv = stream_get_contents($tmp);
        fclose($tmp);

        // Corps final : BOM UTF-16 LE + contenu converti
        $corps = self::BOM . mb_convert_encoding($csv, 'UTF-16LE', 'UTF-8');

        // Vider tous les tampons de sortie avant d'envoyer les en-têtes
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=UTF-16LE');
        header('Content-Disposition: attachment; filename="' . addslashes($nomFichier) . '"');
        // Content-Length précis — évite le transfert fragmenté (chunked) et active
        // les barres de progression côté navigateur / gestionnaire de téléchargement
        header('Content-Length: ' . strlen($corps));
        // private : empêche la mise en cache par les proxys partagés
        header('Cache-Control: private, no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('X-Content-Type-Options: nosniff');

        echo $corps;
        exit;
    }

    /**
     * Récupère un club validé par son identifiant ou interrompt la requête.
     *
     * @param  int   $clubId Identifiant du club
     * @return array Données du club
     */
    private function getClubOrAbort(int $clubId): array
    {
        $stmt = $this->db->prepare(
            "SELECT club_id, nom_club FROM fiche_club WHERE club_id = ? AND validation_finale = 1"
        );
        $stmt->execute([$clubId]);
        $club = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$club) {
            $this->abort('Club introuvable ou non validé.');
        }

        return $club;
    }

    /**
     * Vérifie qu'une chaîne respecte le format de date YYYY-MM-DD et représente une date valide.
     *
     * @param  string $date Chaîne à valider
     * @return bool
     */
    private function estDateValide(string $date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }
        $parties = explode('-', $date);
        return checkdate((int)$parties[1], (int)$parties[2], (int)$parties[0]);
    }

    /**
     * Génère un slug composé uniquement de caractères ASCII pour les noms de fichiers.
     *
     * @param  string $str Chaîne source (accepte les caractères accentués)
     * @return string Slug normalisé
     */
    private function slug(string $str): string
    {
        $str = mb_strtolower($str, 'UTF-8');

        $accents = [
            'à'=>'a','â'=>'a','ä'=>'a','á'=>'a','å'=>'a','ã'=>'a',
            'è'=>'e','é'=>'e','ê'=>'e','ë'=>'e',
            'ì'=>'i','í'=>'i','î'=>'i','ï'=>'i',
            'ò'=>'o','ó'=>'o','ô'=>'o','õ'=>'o','ö'=>'o','ø'=>'o',
            'ù'=>'u','ú'=>'u','û'=>'u','ü'=>'u',
            'ý'=>'y','ÿ'=>'y',
            'ç'=>'c','ñ'=>'n','ß'=>'ss',
            'œ'=>'oe','æ'=>'ae',
        ];
        $str = str_replace(array_keys($accents), array_values($accents), $str);

        $resultat = preg_replace('/[^a-z0-9]+/', '_', $str) ?? '';
        return trim($resultat, '_') ?: 'export';
    }

    /**
     * Interrompt la requête en cours avec un message d'erreur.
     *
     * Retourne une réponse JSON 422 pour les appels AJAX,
     * ou redirige vers la page d'export pour les requêtes navigateur.
     *
     * @param string $message Message d'erreur à transmettre
     */
    private function abort(string $message): void
    {
        if (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            header('Content-Type: application/json; charset=UTF-8');
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => $message]);
            exit;
        }

        $_SESSION['export_error'] = $message;
        header('Location: index.php?page=export');
        exit;
    }
}
