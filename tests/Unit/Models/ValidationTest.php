<?php
declare(strict_types=1);

namespace Tests\Unit\Models;

use Validation;
use PDO;
use PDOStatement;
use Tests\BaseTestCase;

/**
 * Unit tests for the Validation model.
 * Tests club/event validation workflow, rejection, and deletion.
 */
class ValidationTest extends BaseTestCase
{
    private Validation $validation;
    private PDO $pdo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = $this->createMockPdo();
        $this->validation = new Validation($this->pdo);
    }

    // =========================================================================
    // getPendingClubs()
    // =========================================================================

    public function testGetPendingClubsReturnsArray(): void
    {
        $expected = [
            ['club_id' => 1, 'nom_club' => 'Pending Club', 'validation_finale' => null],
        ];

        $stmt = $this->createMockStatement(null, $expected);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->validation->getPendingClubs();

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    public function testGetPendingClubsReturnsEmptyWhenNoneExist(): void
    {
        $stmt = $this->createMockStatement(null, []);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->validation->getPendingClubs();

        $this->assertEmpty($result);
    }

    // =========================================================================
    // getRejectedClubs()
    // =========================================================================

    public function testGetRejectedClubsFiltersCorrectly(): void
    {
        $expected = [
            ['club_id' => 3, 'nom_club' => 'Rejected', 'validation_finale' => -1],
        ];

        $stmt = $this->createMockStatement(null, $expected);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->validation->getRejectedClubs();

        $this->assertCount(1, $result);
        $this->assertEquals(-1, $result[0]['validation_finale']);
    }

    // =========================================================================
    // validateClub()
    // =========================================================================

    public function testValidateClubAdminApproval(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function (array $params): bool {
                return $params[0] === 1 && $params[1] === 1; // admin=1, club_id=1
            }))
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->validation->validateClub(1, 1);

        $this->assertTrue($result);
    }

    public function testValidateClubWithRemarks(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->validation->validateClub(
            1,
            null,
            null,
            -1,
            'Description insuffisante'
        );

        $this->assertTrue($result);
    }

    public function testValidateClubWithNoUpdatesReturnsFalse(): void
    {
        $result = $this->validation->validateClub(1);

        $this->assertFalse($result);
    }

    // =========================================================================
    // rejectClub()
    // =========================================================================

    public function testRejectClubSetsCorrectValues(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with(['Motif du refus', 1])
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->validation->rejectClub(1, 'Motif du refus');

        $this->assertTrue($result);
    }

    // =========================================================================
    // getPendingEvents()
    // =========================================================================

    public function testGetPendingEventsIncludesJoinedData(): void
    {
        $expected = [
            [
                'event_id' => 1,
                'titre' => 'Pending Event',
                'nom_club' => 'Club A',
                'responsable_prenom' => 'Jean',
                'responsable_nom' => 'Dupont',
            ],
        ];

        $stmt = $this->createMockStatement(null, $expected);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->validation->getPendingEvents();

        $this->assertCount(1, $result);
        $this->assertArrayHasKey('nom_club', $result[0]);
        $this->assertArrayHasKey('responsable_prenom', $result[0]);
    }

    // =========================================================================
    // validateEvent()
    // =========================================================================

    public function testValidateEventBdeApproval(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->validation->validateEvent(1, 1);

        $this->assertTrue($result);
    }

    public function testValidateEventWithNoUpdatesReturnsFalse(): void
    {
        $result = $this->validation->validateEvent(1);

        $this->assertFalse($result);
    }

    // =========================================================================
    // rejectEvent()
    // =========================================================================

    public function testRejectEventSetsMotif(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with(['Budget non justifié', 1])
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->validation->rejectEvent(1, 'Budget non justifié');

        $this->assertTrue($result);
    }

    // =========================================================================
    // deleteRejectedClub()
    // =========================================================================

    public function testDeleteRejectedClubDeletesMembersFirst(): void
    {
        $memberStmt = $this->createMock(PDOStatement::class);
        $memberStmt->expects($this->once())->method('execute')->with([1])->willReturn(true);

        $clubStmt = $this->createMock(PDOStatement::class);
        $clubStmt->expects($this->once())->method('execute')->with([1])->willReturn(true);

        $this->pdo->method('prepare')->willReturnOnConsecutiveCalls($memberStmt, $clubStmt);

        $result = $this->validation->deleteRejectedClub(1);

        $this->assertTrue($result);
    }

    // =========================================================================
    // deleteRejectedEvent()
    // =========================================================================

    public function testDeleteRejectedEventOnlyDeletesRejected(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with([5])
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->validation->deleteRejectedEvent(5);

        $this->assertTrue($result);
    }
}
