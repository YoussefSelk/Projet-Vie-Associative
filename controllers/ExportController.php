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

        $stmt = $this->db->query("
            SELECT
                fc.nom_club                                          AS 'Nom du club',
                fc.type_club                                         AS 'Type',
                fc.campus                                            AS 'Campus',
                COALESCE(
                    CONCAT(t.prenom, ' ', t.nom),
                    'Non renseigné'
                )                                                    AS 'Tuteur référent',
                CASE
                    WHEN fc.validation_finale = 1 THEN 'Validé'
                    WHEN fc.validation_finale = 0 THEN 'Refusé'
                    ELSE 'En attente'
                END                                                  AS 'Statut'
            FROM fiche_club fc
            LEFT JOIN users t ON t.id = CAST(fc.tuteur AS UNSIGNED)
            WHERE fc.validation_finale = 1
            ORDER BY fc.campus ASC, fc.nom_club ASC
        ");
        $lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->journaliserExport('export_clubs', null);
        $this->envoyerCsv($lignes, 'clubs_' . date('Y-m-d') . '.csv');
    }

    // =========================================================================
    // EXPORT — MEMBRES
    // =========================================================================

    /**
     * Exporte les membres validés d'un club.
     *
     * Un tuteur (niveau 2) ne peut exporter que les membres du club
     * dont il est le tuteur référent. BDE et admins ont accès à tous les clubs.
     */
    public function exportClubMembers(): void
    {
        checkPermission(2);
        $this->verifierRateLimit();

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
    private function envoyerCsv(array $lignes, string $nomFichier): void
    {
        // Construction du corps en m\u00e9moire avant l'envoi des en-t\u00eates
        // (indispensable pour calculer Content-Length avec pr\u00e9cision)
        $tmp = fopen('php://temp', 'r+b');

        if (empty($lignes)) {
            fputcsv($tmp, ['Aucune donn\u00e9e disponible pour cet export.'], self::DELIMITEUR, self::GUILLEMET, self::ECHAPPEMENT);
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

        // Vider tous les tampons de sortie avant d'envoyer les en-t\u00eates
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=UTF-16LE');
        header('Content-Disposition: attachment; filename="' . addslashes($nomFichier) . '"');
        // Content-Length pr\u00e9cis \u2014 \u00e9vite le transfert fragment\u00e9 (chunked) et active
        // les barres de progression c\u00f4t\u00e9 navigateur / gestionnaire de t\u00e9l\u00e9chargement
        header('Content-Length: ' . strlen($corps));
        // private : emp\u00eache la mise en cache par les proxys partag\u00e9s
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
