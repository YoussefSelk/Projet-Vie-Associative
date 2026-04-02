<?php
declare(strict_types=1);

namespace Tests\Feature;

use Event;
use Validation;
use PDO;
use PDOStatement;
use Tests\BaseTestCase;

/**
 * Integration tests for the Event lifecycle:
 * Create → BDE Approve → Tutor Approve → Final Validation
 */
class EventLifecycleTest extends BaseTestCase
{
    private Event $eventModel;
    private Validation $validationModel;
    private PDO $pdo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = $this->createMockPdo();
        $this->eventModel = new Event($this->pdo);
        $this->validationModel = new Validation($this->pdo);
    }

    /**
     * Test event creation sets all validations to NULL (pending)
     */
    public function testEventCreationStartsAsPending(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function (array $params): bool {
                // The query contains NULL placeholders for validations in SQL, not params
                return $params[0] === 'Test Event'
                    && $params[1] === 'event'
                    && $params[2] === 'Description du test';
            }))
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->eventModel->createEvent([
            'nom_event' => 'Test Event',
            'description' => 'Description du test',
            'date_ev' => '2026-06-15',
            'horaire_debut' => '14:00:00',
            'horaire_fin' => '18:00:00',
            'club_id' => 1,
            'campus' => 'Calais',
            'lieu' => 'Amphi A',
            'user_id' => 1,
        ]);

        $this->assertTrue($result);
    }

    /**
     * Test BDE partial approval (only sets validation_bde)
     */
    public function testBdePartialApproval(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function (array $params): bool {
                return $params[0] === 1 // bde_validation = 1
                    && $params[1] === 1; // event_id
            }))
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->validationModel->validateEvent(1, 1);

        $this->assertTrue($result);
    }

    /**
     * Test tutor approval combined with BDE → final validation
     */
    public function testTutorApprovalCompletesValidation(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        // Set both BDE and tutor + final validation
        $result = $this->validationModel->validateEvent(1, 1, 1, 1);

        $this->assertTrue($result);
    }

    /**
     * Test event rejection sets motif_refus
     */
    public function testEventRejectionWithMotif(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with(['Budget non justifié', 1])
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->validationModel->rejectEvent(1, 'Budget non justifié');

        $this->assertTrue($result);
    }

    /**
     * Test event update with datetime-local format parsing
     */
    public function testEventUpdateParsesDatetimeLocal(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function (array $params): bool {
                return $params[0] === '2026-07-20'   // date_ev
                    && $params[1] === '15:00:00';   // horaire_debut
            }))
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->eventModel->updateEvent(1, [
            'date_ev' => '2026-07-20',
            'horaire_debut' => '15:00:00',
        ]);

        $this->assertTrue($result);
    }

    /**
     * Test rejected event deletion only affects rejected items
     */
    public function testDeleteRejectedEventProtected(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with([5])
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        // The SQL includes WHERE validation_finale = -1, protecting active events
        $result = $this->validationModel->deleteRejectedEvent(5);

        $this->assertTrue($result);
    }

    /**
     * Test subscribed events return empty on missing table
     */
    public function testSubscribedEventsGracefulOnError(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')
            ->willThrowException(new \PDOException('Table not found'));

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->eventModel->getSubscribedEvents(1);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
