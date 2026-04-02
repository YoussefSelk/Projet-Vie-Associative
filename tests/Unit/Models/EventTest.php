<?php
declare(strict_types=1);

namespace Tests\Unit\Models;

use Event;
use PDO;
use PDOStatement;
use Tests\BaseTestCase;

/**
 * Unit tests for the Event model.
 * Tests CRUD operations, date parsing, and field mapping.
 */
class EventTest extends BaseTestCase
{
    private Event $event;
    private PDO $pdo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = $this->createMockPdo();
        $this->event = new Event($this->pdo);
    }

    // =========================================================================
    // getAllValidatedEvents()
    // =========================================================================

    public function testGetAllValidatedEventsReturnsArray(): void
    {
        $expected = [
            ['event_id' => 1, 'titre' => 'Hackathon', 'validation_finale' => 1, 'nom_club' => 'Club Info'],
            ['event_id' => 2, 'titre' => 'Soirée BDE', 'validation_finale' => 1, 'nom_club' => 'BDE'],
        ];

        $stmt = $this->createMockStatement(null, $expected);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->event->getAllValidatedEvents();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('Hackathon', $result[0]['titre']);
    }

    // =========================================================================
    // getEventById()
    // =========================================================================

    public function testGetEventByIdReturnsEvent(): void
    {
        $expected = ['event_id' => 1, 'titre' => 'Test Event', 'campus' => 'Calais'];

        $stmt = $this->createMockStatement($expected);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->event->getEventById(1);

        $this->assertIsArray($result);
        $this->assertEquals('Test Event', $result['titre']);
    }

    public function testGetEventByIdReturnsFalseWhenNotFound(): void
    {
        $stmt = $this->createMockStatement(false);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->event->getEventById(9999);

        $this->assertFalse($result);
    }

    // =========================================================================
    // createEvent()
    // =========================================================================

    public function testCreateEventWithDateEvFormat(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function (array $params): bool {
                return $params[0] === 'Test Event'           // titre
                    && $params[1] === 'event'                 // type_event
                    && $params[2] === 'Description'           // description
                    && $params[3] === '2026-03-15'            // date_ev
                    && $params[4] === '14:00:00'              // horaire_debut
                    && $params[5] === '18:00:00'              // horaire_fin
                    && $params[6] === 1                       // club_orga
                    && $params[7] === 'Calais'                // campus
                    && $params[8] === 'Amphi A'               // lieu
                    && $params[9] === 1                       // id_responsable
                    && $params[10] === 0                      // financement_bde
                    && $params[11] === 0                      // montant
                    && $params[12] === null                   // fiche_sanitaire
                    && $params[13] === null                   // affiche
                    && $params[14] === null;                  // doc_organisation
            }))
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $data = [
            'nom_event' => 'Test Event',
            'description' => 'Description',
            'date_ev' => '2026-03-15',
            'horaire_debut' => '14:00:00',
            'horaire_fin' => '18:00:00',
            'club_id' => 1,
            'campus' => 'Calais',
            'lieu' => 'Amphi A',
            'user_id' => 1,
        ];

        $result = $this->event->createEvent($data);

        $this->assertTrue($result);
    }

    public function testCreateEventWithDatetimeLocalFormat(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function (array $params): bool {
                return $params[3] === '2026-06-20'        // date parsed
                    && $params[4] === '15:30:00'          // horaire_debut parsed
                    && str_contains($params[5], ':');     // horaire_fin set (2h later)
            }))
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $data = [
            'date_event' => '2026-06-20T15:30',
            'nom_event' => 'Datetime Event',
            'description' => 'Test',
            'club_id' => 1,
            'campus' => 'Calais',
            'lieu' => 'Salle B',
            'user_id' => 1,
        ];

        $result = $this->event->createEvent($data);

        $this->assertTrue($result);
    }

    // =========================================================================
    // getEventsByUser()
    // =========================================================================

    public function testGetEventsByUserReturnsUserEvents(): void
    {
        $expected = [
            ['event_id' => 1, 'titre' => 'Club Event'],
        ];

        $stmt = $this->createMockStatement(null, $expected);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->event->getEventsByUser(1);

        $this->assertCount(1, $result);
    }

    // =========================================================================
    // getSubscribedEvents()
    // =========================================================================

    public function testGetSubscribedEventsReturnsEmptyOnException(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willThrowException(new \PDOException('Table not found'));

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->event->getSubscribedEvents(1);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // =========================================================================
    // deleteEvent()
    // =========================================================================

    public function testDeleteEventReturnsTrueOnSuccess(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with([1])
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->event->deleteEvent(1);

        $this->assertTrue($result);
    }

    // =========================================================================
    // updateEvent()
    // =========================================================================

    public function testUpdateEventWithValidData(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->event->updateEvent(1, [
            'titre' => 'Updated Title',
            'description' => 'Updated Description',
        ]);

        $this->assertTrue($result);
    }

    public function testUpdateEventWithEmptyDataReturnsFalse(): void
    {
        $result = $this->event->updateEvent(1, []);

        $this->assertFalse($result);
    }

    public function testUpdateEventMapsFieldNames(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function (array $params): bool {
                return $params[0] === '2026-06-20'
                    && $params[1] === '14:00:00'
                    && $params[count($params) - 1] === 1;
            }))
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->event->updateEvent(1, [
            'date_ev' => '2026-06-20',
            'horaire_debut' => '14:00:00',
        ]);

        $this->assertTrue($result);
    }
}
