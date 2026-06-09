<?php
declare(strict_types=1);

namespace Tests\Feature;

use EventController;
use PDO;
use PDOStatement;
use Tests\BaseTestCase;

require_once dirname(__DIR__, 2) . '/controllers/EventController.php';

/**
 * Tests d'intégration des correctifs « retour client juin 2026 » du EventController :
 *
 *  A) Bloquer un événement dont l'heure de fin précède (ou égale) l'heure de début,
 *     en création (createEvent) comme en modification (updateEvent).
 *  B) Notifier les valideurs (BDE + Tuteur + Admin) lorsqu'un événement refusé est
 *     modifié puis re-soumis pour validation (updateEvent).
 *
 * Les fonctions globales notifyValidators* sont espionnées dans tests/bootstrap.php
 * et enregistrent leurs appels dans $GLOBALS['__notifications'].
 */
class EventControllerFixesTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['__notifications'] = [];
        $_SERVER['REQUEST_URI'] = '?page=create-event';
    }

    protected function tearDown(): void
    {
        $GLOBALS['__notifications'] = [];
        parent::tearDown();
    }

    /** Date valide (au moins 15 jours dans le futur) au format attendu. */
    private function futureDate(): string
    {
        return date('Y-m-d', strtotime('+20 days'));
    }

    /** PDO mock dont query() renvoie la liste des clubs et prepare() une ligne club. */
    private function pdoForCreate(): PDO
    {
        $pdo = $this->createMockPdo();

        $clubsStmt = $this->createMock(PDOStatement::class);
        $clubsStmt->method('execute')->willReturn(true);
        $clubsStmt->method('fetchAll')->willReturn([
            ['club_id' => 1, 'nom_club' => 'Club Photo'],
        ]);
        $pdo->method('query')->willReturn($clubsStmt);

        // prepare() : utilisé pour le lookup club (fetch) ET l'INSERT (execute)
        $genericStmt = $this->createMock(PDOStatement::class);
        $genericStmt->method('execute')->willReturn(true);
        $genericStmt->method('fetch')->willReturn(['nom_club' => 'Club Photo', 'tuteur' => 9]);
        $pdo->method('prepare')->willReturn($genericStmt);

        return $pdo;
    }

    // =========================================================================
    // A) createEvent — validation des horaires
    // =========================================================================

    public function testCreateEventRejectsEndTimeBeforeStartTime(): void
    {
        $this->loginAsUser(1, 3);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'create_event'  => '1',
            'type_event'    => 'activity',
            'nom_event'     => 'Sortie',
            'description'   => 'Une sortie',
            'date_ev'       => $this->futureDate(),
            'horaire_debut' => '14:00',
            'horaire_fin'   => '11:00', // fin AVANT début
            'campus'        => 'Calais',
            'lieu'          => 'Amphi A',
            'club_id'       => '1',
        ];

        $result = (new EventController($this->pdoForCreate()))->createEvent();

        $this->assertStringContainsString('postérieure', $result['error_msg']);
        // Aucun événement ne doit être créé → aucune notification de soumission.
        $this->assertSame([], $GLOBALS['__notifications']);
    }

    public function testCreateEventRejectsEqualStartAndEndTime(): void
    {
        $this->loginAsUser(1, 3);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'create_event'  => '1',
            'type_event'    => 'activity',
            'nom_event'     => 'Sortie',
            'description'   => 'Une sortie',
            'date_ev'       => $this->futureDate(),
            'horaire_debut' => '14:00',
            'horaire_fin'   => '14:00', // identique
            'campus'        => 'Calais',
            'lieu'          => 'Amphi A',
            'club_id'       => '1',
        ];

        $result = (new EventController($this->pdoForCreate()))->createEvent();

        $this->assertStringContainsString('postérieure', $result['error_msg']);
        $this->assertSame([], $GLOBALS['__notifications']);
    }

    public function testCreateEventAcceptsValidTimeRangeAndNotifies(): void
    {
        $this->loginAsUser(1, 3);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'create_event'  => '1',
            'type_event'    => 'activity', // activité → pas de dossier obligatoire
            'nom_event'     => 'Sortie',
            'description'   => 'Une sortie',
            'date_ev'       => $this->futureDate(),
            'horaire_debut' => '14:00',
            'horaire_fin'   => '18:00', // fin APRÈS début
            'campus'        => 'Calais',
            'lieu'          => 'Amphi A',
            'club_id'       => '1',
        ];

        $result = (new EventController($this->pdoForCreate()))->createEvent();

        $this->assertSame('', $result['error_msg']);
        // Création réussie → notification de nouvelle soumission envoyée aux valideurs.
        $this->assertCount(1, $GLOBALS['__notifications']);
        $this->assertSame('newSubmission', $GLOBALS['__notifications'][0]['fn']);
        $this->assertSame('evenement', $GLOBALS['__notifications'][0]['type']);
    }

    // =========================================================================
    // updateEvent — validation des horaires + notification de re-validation
    // =========================================================================

    /** Construit un PDO mock pour updateEvent ; la ligne event est renvoyée par fetch(). */
    private function pdoForUpdate(array $eventRow): PDO
    {
        $pdo = $this->createMockPdo();
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetch')->willReturn($eventRow);
        $pdo->method('prepare')->willReturn($stmt);
        return $pdo;
    }

    private function refusedEventRow(): array
    {
        // Événement précédemment refusé (validation_finale = -1) que l'étudiant modifie.
        return [
            'event_id'         => 1,
            'titre'            => 'Gala',
            'nom_event'        => 'Gala',
            'club_orga'        => 1,
            'club_id'          => 1,
            'nom_club'         => 'Club Photo',
            'club_tuteur'      => 9,
            'validation_finale' => -1,
            'doc_organisation' => '../uploads/docs_organisation/x.pdf',
            'horaire_debut'    => '14:00:00',
            'horaire_fin'      => '18:00:00',
        ];
    }

    public function testUpdateEventRejectsEndTimeBeforeStartTime(): void
    {
        $this->loginAsUser(1, 1);
        $_GET['id'] = 1;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'update_event'  => '1',
            'event_id'      => 1,
            'type_event'    => 'activity',
            'nom_event'     => 'Gala',
            'description'   => 'Soirée',
            'date_ev'       => $this->futureDate(),
            'horaire_debut' => '20:00',
            'horaire_fin'   => '19:00', // fin AVANT début
            'campus'        => 'Calais',
            'lieu'          => 'Salle B',
        ];

        $result = (new EventController($this->pdoForUpdate($this->refusedEventRow())))->updateEvent();

        $this->assertStringContainsString('postérieure', $result['error_msg']);
        // Modification refusée → pas de re-soumission notifiée.
        $this->assertSame([], $GLOBALS['__notifications']);
    }

    public function testUpdateEventValidNotifiesValidatorsForResubmission(): void
    {
        $this->loginAsUser(1, 1, 'Durand', 'Paul');
        $_GET['id'] = 1;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'update_event'  => '1',
            'event_id'      => 1,
            'type_event'    => 'activity',
            'nom_event'     => 'Gala',
            'description'   => 'Soirée',
            'date_ev'       => $this->futureDate(),
            'horaire_debut' => '20:00',
            'horaire_fin'   => '23:00', // fin APRÈS début
            'campus'        => 'Calais',
            'lieu'          => 'Salle B',
        ];

        $result = (new EventController($this->pdoForUpdate($this->refusedEventRow())))->updateEvent();

        $this->assertSame('', $result['error_msg']);
        $this->assertNotSame('', $result['success_msg']);

        // Le BDE + le tuteur doivent être notifiés que l'événement doit être revalidé.
        $this->assertCount(1, $GLOBALS['__notifications']);
        $notif = $GLOBALS['__notifications'][0];
        $this->assertSame('resubmission', $notif['fn']);
        $this->assertSame('Gala', $notif['item']);
        $this->assertSame('Paul Durand', $notif['editor']);
        $this->assertSame(9, $notif['tutorId']); // tuteur du club ciblé
    }

    public function testUpdateEventBlocksAlreadyApprovedEvent(): void
    {
        // Un événement déjà validé (validation_finale = 1) ne doit pas être modifiable :
        // la méthode redirige (stub no-op) sans atteindre la logique de mise à jour.
        $this->loginAsUser(1, 1);
        $_GET['id'] = 1;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['update_event' => '1', 'event_id' => 1];

        $row = $this->refusedEventRow();
        $row['validation_finale'] = 1;

        (new EventController($this->pdoForUpdate($row)))->updateEvent();

        $this->assertSame([], $GLOBALS['__notifications']);
    }
}
