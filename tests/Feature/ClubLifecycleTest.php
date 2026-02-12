<?php
declare(strict_types=1);

namespace Tests\Feature;

use Club;
use Validation;
use PDO;
use PDOStatement;
use Tests\BaseTestCase;

/**
 * Integration tests for the Club lifecycle:
 * Create → Validate → Modify → Re-validate
 */
class ClubLifecycleTest extends BaseTestCase
{
    private Club $clubModel;
    private Validation $validationModel;
    private PDO $pdo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = $this->createMockPdo();
        $this->clubModel = new Club($this->pdo);
        $this->validationModel = new Validation($this->pdo);
    }

    /**
     * Test the full club creation → pending state flow
     */
    public function testClubCreationStartsAsPending(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function (array $params): bool {
                // validation_finale should be null (pending)
                return $params[4] === null;
            }))
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->clubModel->createClub([
            'nom_club' => 'New Club',
            'type_club' => 'Sport',
            'description' => 'A new sports club',
            'campus' => 'Calais',
        ]);

        $this->assertTrue($result);
    }

    /**
     * Test admin approval + tutor approval = final validation
     */
    public function testFullApprovalWorkflow(): void
    {
        // Step 1: Admin validates
        $adminStmt = $this->createMock(PDOStatement::class);
        $adminStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($adminStmt);

        $result1 = $this->validationModel->validateClub(1, 1);
        $this->assertTrue($result1);

        // Step 2: Tutor validates → should trigger validation_finale = 1
        $tutorPdo = $this->createMockPdo();
        $tutorValidation = new Validation($tutorPdo);

        $tutorStmt = $this->createMock(PDOStatement::class);
        $tutorStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $tutorPdo->method('prepare')->willReturn($tutorStmt);

        $result2 = $tutorValidation->validateClub(1, null, 1, 1);
        $this->assertTrue($result2);
    }

    /**
     * Test rejection adds motif_refus
     */
    public function testClubRejectionSetsMotif(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with(['Description incomplète', 1])
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->validationModel->rejectClub(1, 'Description incomplète');

        $this->assertTrue($result);
    }

    /**
     * Test modify rejected club resets validation flags
     */
    public function testModifyRejectedClubResetsValidation(): void
    {
        // Mock check query returning a rejected club
        $checkStmt = $this->createMockStatement([
            'validation_admin' => 0,
            'validation_tuteur' => null,
            'validation_finale' => 0,
        ]);

        $updateStmt = $this->createMock(PDOStatement::class);
        $updateStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function (array $params): bool {
                // The update should include the new name value + the club id
                return $params[0] === 'Fixed Club Name'
                    && end($params) === 1; // club_id
            }))
            ->willReturn(true);

        $this->pdo->method('prepare')
            ->willReturnOnConsecutiveCalls($checkStmt, $updateStmt);

        $result = $this->clubModel->updateClub(1, [
            'nom_club' => 'Fixed Club Name',
        ]);

        $this->assertTrue($result);
    }

    /**
     * Test duplicate club name prevention
     */
    public function testDuplicateClubNameDetection(): void
    {
        $stmt = $this->createMockStatement(['club_id' => 5]);
        $this->expectPrepare($this->pdo, $stmt);

        $exists = $this->clubModel->clubNameExists('Existing Club');

        $this->assertTrue($exists);
    }

    /**
     * Test delete rejected club cleans up members first
     */
    public function testDeleteRejectedClubCleansMembers(): void
    {
        $memberStmt = $this->createMock(PDOStatement::class);
        $memberStmt->expects($this->once())
            ->method('execute')
            ->with([1])
            ->willReturn(true);

        $clubStmt = $this->createMock(PDOStatement::class);
        $clubStmt->expects($this->once())
            ->method('execute')
            ->with([1])
            ->willReturn(true);

        $this->pdo->method('prepare')
            ->willReturnOnConsecutiveCalls($memberStmt, $clubStmt);

        $result = $this->validationModel->deleteRejectedClub(1);

        $this->assertTrue($result);
    }
}
