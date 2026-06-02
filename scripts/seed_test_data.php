<?php
declare(strict_types=1);
/**
 * =============================================================================
 * SEED DE DONNÉES DE TEST  —  Vérification manuelle des correctifs (retour client)
 * =============================================================================
 *
 * Crée un jeu de données complet pour tester les 13 points sur la plateforme.
 * - Réutilise la connexion BD de l'application (.env) — aucun secret affiché.
 * - Ré-exécutable : supprime d'abord ses propres données (marqueurs "seed." / "[SEED]").
 * - N'utilise que des colonnes réellement manipulées par le code (import sûr).
 *
 * Usage :
 *     php scripts/seed_test_data.php
 *
 * ⚠️ À lancer sur une base de DEV/TEST. Ne touche QUE les données préfixées SEED.
 */

// --- Bootstrap minimal (constantes + .env + connexion) ----------------------
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
require_once CONFIG_PATH . '/Environment.php';
Environment::load();
require_once CONFIG_PATH . '/ErrorHandler.php';
require_once CONFIG_PATH . '/Database.php';

$db = (new Database())->connect();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$PASSWORD = 'Seed1234!';
$hash = password_hash($PASSWORD, PASSWORD_BCRYPT, ['cost' => 12]);
$today = new DateTimeImmutable('today');
$pastDate   = $today->modify('-2 days')->format('Y-m-d');
$futureDate = $today->modify('+15 days')->format('Y-m-d');
$logo = '/images/EILCO-LOGO-2022.png'; // image existante pour tester l'affiche (BUG 8)

echo "=== SEED — nettoyage des anciennes données SEED ===\n";
$db->beginTransaction();
try {
    // Suppression dépendances -> parents (uniquement les données SEED)
    $db->exec("DELETE ab FROM abonnements ab JOIN fiche_event fe ON fe.event_id = ab.event_id WHERE fe.titre LIKE '[SEED]%'");
    $db->exec("DELETE ab FROM abonnements ab JOIN users u ON u.id = ab.id WHERE u.mail LIKE 'seed.%'");
    $db->exec("DELETE mc FROM membres_club mc JOIN fiche_club fc ON fc.club_id = mc.club_id WHERE fc.nom_club LIKE '[SEED]%'");
    $db->exec("DELETE mc FROM membres_club mc JOIN users u ON u.id = mc.membre_id WHERE u.mail LIKE 'seed.%'");
    $db->exec("DELETE FROM fiche_event WHERE titre LIKE '[SEED]%'");
    $db->exec("DELETE FROM fiche_club  WHERE nom_club LIKE '[SEED]%'");
    $db->exec("DELETE FROM users WHERE mail LIKE 'seed.%'");

    // --- 1. UTILISATEURS -----------------------------------------------------
    $insUser = $db->prepare("INSERT INTO users (nom, prenom, mail, password, promo, permission) VALUES (?, ?, ?, ?, ?, ?)");
    $mkUser = function (string $nom, string $prenom, string $mail, string $promo, int $perm) use ($insUser, $db, $hash): int {
        $insUser->execute([$nom, $prenom, $mail, $hash, $promo, $perm]);
        return (int)$db->lastInsertId();
    };

    $superAdmin = $mkUser('Admin', 'Seed', 'seed.superadmin@eilco.univ-littoral.fr', 'admin',     5);
    $bde        = $mkUser('BDE',   'Seed', 'seed.bde@eilco.univ-littoral.fr',        'bde',       3);
    $tuteur     = $mkUser('Tuteur','Seed', 'seed.tuteur@eilco.univ-littoral.fr',     'tuteur',    2);
    $fise       = $mkUser('Fise',  'Alice','seed.fise@etu.eilco.univ-littoral.fr',   'ING2FISE',  1);
    $fise2      = $mkUser('Fise',  'Bob',  'seed.fise2@etu.eilco.univ-littoral.fr',  'ING2FISE',  1);
    $fise3      = $mkUser('Fise',  'Chloe','seed.fise3@etu.eilco.univ-littoral.fr',  'ING2FISE',  1);
    $fisea      = $mkUser('Fisea', 'Driss','seed.fisea@etu.eilco.univ-littoral.fr',  'ING2FISEA', 1);
    $ing1       = $mkUser('Ing1',  'Emma', 'seed.ing1@etu.eilco.univ-littoral.fr',   'ING1',      1);

    // --- 2. CLUBS ------------------------------------------------------------
    $insClub = $db->prepare("INSERT INTO fiche_club (nom_club, type_club, description, campus, tuteur, logo_club) VALUES (?, ?, ?, ?, ?, ?)");
    $setClubValidation = $db->prepare("UPDATE fiche_club SET validation_bde=?, validation_admin=?, validation_tuteur=?, validation_finale=?, motif_forcage=? WHERE club_id=?");
    $mkClub = function (string $nom, string $type, string $desc, string $campus, int $tuteurId) use ($insClub, $db, $logo): int {
        $insClub->execute([$nom, $type, $desc, $campus, (string)$tuteurId, $logo]);
        return (int)$db->lastInsertId();
    };
    $addMember = $db->prepare("INSERT INTO membres_club (club_id, membre_id, fonction, soutenance, valide) VALUES (?, ?, ?, ?, 1)");

    // Club 1 : VALIDÉ, 3 membres en soutenance (FISE) + 1 membre non éligible
    $club1 = $mkClub('[SEED] Club Validé', 'culture', 'Club validé pour tester exports & soutenance.', 'Calais', $tuteur);
    $setClubValidation->execute([1, 1, 1, 1, null, $club1]);
    $addMember->execute([$club1, $fise,  'Président',  1]);
    $addMember->execute([$club1, $fise2, 'Trésorier',  1]);
    $addMember->execute([$club1, $fise3, 'Secrétaire', 1]);
    $addMember->execute([$club1, $ing1,  'Membre',     0]);

    // Club 2 : EN ATTENTE (toutes validations NULL) -> tests validation BDE/tuteur/admin, refus, commentaire
    $club2 = $mkClub('[SEED] Club À Valider', 'sport', 'Club en attente de validation.', 'Longuenesse', $tuteur);
    // validations laissées à NULL par défaut
    $addMember->execute([$club2, $fise,  'Président',  1]);
    $addMember->execute([$club2, $fise2, 'Trésorier',  1]);
    $addMember->execute([$club2, $fise3, 'Secrétaire', 1]);

    // Club 3 : VALIDÉ PAR FORÇAGE (motif_forcage) -> test BUG 7 (visible membres/tuteur/admin seulement)
    $club3 = $mkClub('[SEED] Club Forcé', 'tech', 'Club validé par forçage admin.', 'Dunkerque', $tuteur);
    $setClubValidation->execute([1, 1, 1, 1, "Validation forcée : dérogation exceptionnelle accordée par l'administration.", $club3]);
    $addMember->execute([$club3, $fise2, 'Président', 1]); // fise2 est membre ; ing1/fisea ne le sont PAS

    // --- 3. ÉVÉNEMENTS (organisés par Club 1, dont fise est membre) ----------
    $insEvent = $db->prepare("
        INSERT INTO fiche_event
            (titre, type_event, description, date_ev, horaire_debut, horaire_fin,
             club_orga, campus, lieu, id_responsable, financement_bde, montant,
             fiche_sanitaire, affiche, doc_organisation,
             validation_admin, validation_bde, validation_tuteur, validation_finale, motif_forcage)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    // Event 1 : VALIDÉ, PASSÉ, sans rapport -> BUG 11 (pop-up rapport) + BUG 9 (photo 2 Mo)
    $insEvent->execute([
        '[SEED] Event passé sans rapport', 'event', 'Événement passé : déposez un rapport pour tester la pop-up et la photo 2 Mo.',
        $pastDate, '14:00', '17:00', $club1, 'Calais', 'MDE', $fise, 0, 0,
        null, null, null, 1, 1, 1, 1, null,
    ]);

    // Event 2 : EN ATTENTE -> BUG 6 (commentaire admin), BUG 4 (refus notifié), validation
    $insEvent->execute([
        '[SEED] Event à valider', 'event', 'Événement en attente : validez-le avec un commentaire (admin) ou refusez-le.',
        $futureDate, '18:00', '22:00', $club1, 'Calais', 'Amphi A', $fise, 1, 200,
        null, null, null, null, null, null, null, null,
    ]);

    // Event 3 : VALIDÉ PAR FORÇAGE + AFFICHE -> BUG 8 (affiche publique) + BUG 7 (motif restreint)
    $insEvent->execute([
        '[SEED] Event forcé avec affiche', 'event', 'Événement validé par forçage, avec une affiche visible par tous.',
        $futureDate, '10:00', '12:00', $club1, 'Calais', 'Hall', $fise, 0, 0,
        null, $logo, null, 1, 1, 1, 1, "Validation forcée : créneau exceptionnel validé malgré le délai.",
    ]);
    $event3 = (int)$db->lastInsertId();

    // Une inscription pour étoffer l'agenda / la liste des inscrits
    $db->prepare("INSERT INTO abonnements (id, event_id, date_abonnement) VALUES (?, ?, NOW())")
       ->execute([$fise2, $event3]);

    $db->commit();
} catch (\Throwable $e) {
    if ($db->inTransaction()) { $db->rollBack(); }
    fwrite(STDERR, "ERREUR SEED : " . $e->getMessage() . "\n");
    exit(1);
}

// --- Récapitulatif -----------------------------------------------------------
echo "\n=== SEED TERMINÉ ✅  (mot de passe commun : {$PASSWORD}) ===\n\n";
echo "COMPTES :\n";
printf("  %-44s %-10s %s\n", 'Email', 'Promo', 'Rôle');
$accounts = [
    ['seed.superadmin@eilco.univ-littoral.fr', 'admin',     'Admin (perm 5) — validation, exports, PURGE'],
    ['seed.bde@eilco.univ-littoral.fr',        'bde',       'BDE (perm 3) — validation événements'],
    ['seed.tuteur@eilco.univ-littoral.fr',     'tuteur',    'Tuteur (perm 2) — validation de SES clubs'],
    ['seed.fise@etu.eilco.univ-littoral.fr',   'ING2FISE',  'Étudiant éligible soutenance — président Club 1/2'],
    ['seed.fise2@etu.eilco.univ-littoral.fr',  'ING2FISE',  'Étudiant éligible — membre + président Club 3'],
    ['seed.fise3@etu.eilco.univ-littoral.fr',  'ING2FISE',  'Étudiant éligible — membre'],
    ['seed.fisea@etu.eilco.univ-littoral.fr',  'ING2FISEA', 'Étudiant NON éligible (FISEA) — pour tester BUG 1'],
    ['seed.ing1@etu.eilco.univ-littoral.fr',   'ING1',      'Étudiant NON éligible (ING1) — non membre Club 3'],
];
foreach ($accounts as $a) { printf("  %-44s %-10s %s\n", $a[0], $a[1], $a[2]); }

echo "\nQUOI TESTER OÙ :\n";
$map = [
    'BUG 1  Soutenance = ING2 FISE'      => "Créer un club (login fise) et tenter d'ajouter 'Fisea Driss' en soutenance -> refusé ; 'Ing1 Emma' -> refusé.",
    'BUG 2  Soutenance 3 à 5'            => "Créer un club avec 2 membres en soutenance -> erreur ; 3 à 5 -> OK.",
    'BUG 3  Notifs création/rapport'     => "Créer club/événement ou déposer rapport (login fise) -> mails BDE/tuteur/admin (vérifier logs si SMTP HS).",
    'BUG 4  Refus notifié'               => "Login bde/tuteur/admin -> refuser '[SEED] Club À Valider' ou '[SEED] Event à valider' -> autres valideurs notifiés.",
    'BUG 5  Commentaire tuteur'          => "Login tuteur -> ?page=tutoring -> approuver '[SEED] Club À Valider' avec un commentaire -> visible sur la fiche club.",
    'BUG 6  Commentaire admin (event)'   => "Login admin -> ?page=pending-events -> approuver '[SEED] Event à valider' avec commentaire -> visible sur la fiche.",
    'BUG 7  Motif forçage restreint'     => "Ouvrir '[SEED] Club Forcé' / '[SEED] Event forcé' : motif visible pour fise2/tuteur/admin, INVISIBLE pour ing1 et déconnecté.",
    'BUG 8  Affiche publique'            => "Se DÉCONNECTER puis ouvrir '[SEED] Event forcé avec affiche' -> l'affiche (logo) s'affiche.",
    'BUG 9  Photo 2 Mo'                  => "Login fise -> ?page=event-report -> '[SEED] Event passé' -> photo ~1,5-2 Mo acceptée.",
    'BUG 10 Purge'                       => "Login superadmin -> outils base de données -> Purger -> taper PURGER.",
    'BUG 11 Pop-up rapport'              => "Même que BUG 9 : après envoi -> pop-up 'Rapport déposé'.",
    'BUG 12 Export soutenance global'    => "Login admin -> ?page=export -> 'Exporter les soutenances (global)' -> 5 colonnes, 3 lignes (Club 1).",
    'BUG 13 Rôle = rôle club'            => "Login admin -> export 'Membres d'un club' = Tous les clubs -> colonne 'Rôle dans le club'.",
];
foreach ($map as $k => $v) { echo "  • {$k}\n      {$v}\n"; }
echo "\nPour tout effacer : relancer ce script (il nettoie les données SEED) ou supprimer les lignes 'seed.%' / '[SEED]%'.\n";
