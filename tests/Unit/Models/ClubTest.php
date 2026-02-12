<?php
declare(strict_types=1);

namespace Tests\Unit\Models;

use Club;
use PDO;
use PDOStatement;
use Tests\BaseTestCase;

/**
 * Unit tests for the Club model.
 * Tests CRUD operations, validation logic, and edge cases.
 */
class ClubTest extends BaseTestCase
{
    private Club $club;
    private PDO $pdo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = $this->createMockPdo();
        $this->club = new Club($this->pdo);
    }

    // =========================================================================
    // getAllValidatedClubs()
    // =========================================================================

    public function testGetAllValidatedClubsReturnsArray(): void
    {
        $expected = [
            ['club_id' => 1, 'nom_club' => 'Club A', 'validation_finale' => 1],
            ['club_id' => 2, 'nom_club' => 'Club B', 'validation_finale' => 1],
        ];

        $stmt = $this->createMockStatement(null, $expected);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->club->getAllValidatedClubs();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('Club A', $result[0]['nom_club']);
    }

    public function testGetAllValidatedClubsReturnsEmptyArrayWhenNone(): void
    {
        $stmt = $this->createMockStatement(null, []);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->club->getAllValidatedClubs();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // =========================================================================
    // getClubById()
    // =========================================================================

    public function testGetClubByIdReturnsClubData(): void
    {
        $expected = ['club_id' => 1, 'nom_club' => 'Test Club', 'campus' => 'Calais'];

        $stmt = $this->createMockStatement($expected);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->club->getClubById(1);

        $this->assertIsArray($result);
        $this->assertEquals('Test Club', $result['nom_club']);
    }

    public function testGetClubByIdReturnsFalseWhenNotFound(): void
    {
        $stmt = $this->createMockStatement(false);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->club->getClubById(9999);

        $this->assertFalse($result);
    }

    // =========================================================================
    // getClubByName()
    // =========================================================================

    public function testGetClubByNameFindsCaseInsensitive(): void
    {
        $expected = ['club_id' => 1, 'nom_club' => 'Test Club'];

        $stmt = $this->createMockStatement($expected);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->club->getClubByName('test club');

        $this->assertIsArray($result);
        $this->assertEquals(1, $result['club_id']);
    }

    public function testGetClubByNameReturnsFalseWhenNotFound(): void
    {
        $stmt = $this->createMockStatement(false);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->club->getClubByName('Nonexistent Club');

        $this->assertFalse($result);
    }

    // =========================================================================
    // clubNameExists()
    // =========================================================================

    public function testClubNameExistsReturnsTrue(): void
    {
        $stmt = $this->createMockStatement(['club_id' => 1]);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->club->clubNameExists('Existing Club');

        $this->assertTrue($result);
    }

    public function testClubNameExistsReturnsFalse(): void
    {
        $stmt = $this->createMockStatement(false);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->club->clubNameExists('New Club');

        $this->assertFalse($result);
    }

    public function testClubNameExistsWithExcludeId(): void
    {
        $stmt = $this->createMockStatement(false);
        $stmt->expects($this->once())
            ->method('execute')
            ->with(['Update Club', 5]);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->club->clubNameExists('Update Club', 5);

        $this->assertFalse($result);
    }

    // =========================================================================
    // createClub()
    // =========================================================================

    public function testCreateClubReturnsTrueOnSuccess(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with(['Mon Club', 'Sport', 'Description', 'Calais', 0])
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $data = [
            'nom_club' => 'Mon Club',
            'type_club' => 'Sport',
            'description' => 'Description',
            'campus' => 'Calais',
        ];

        $result = $this->club->createClub($data);

        $this->assertTrue($result);
    }

    // =========================================================================
    // updateClub()
    // =========================================================================

    public function testUpdateClubWithValidFields(): void
    {
        // Mock the check query for current club state
        $checkStmt = $this->createMockStatement([
            'validation_admin' => 1,
            'validation_tuteur' => 1,
            'validation_finale' => 1,
        ]);

        $updateStmt = $this->createMock(PDOStatement::class);
        $updateStmt->method('execute')->willReturn(true);

        $this->pdo->method('prepare')->willReturnOnConsecutiveCalls($checkStmt, $updateStmt);

        $result = $this->club->updateClub(1, [
            'nom_club' => 'Updated Name',
            'description' => 'Updated Desc',
        ]);

        $this->assertTrue($result);
    }

    public function testUpdateClubWithEmptyDataReturnsFalse(): void
    {
        $checkStmt = $this->createMockStatement([
            'validation_admin' => 1,
            'validation_tuteur' => 1,
            'validation_finale' => 1,
        ]);

        $this->pdo->method('prepare')->willReturn($checkStmt);

        $result = $this->club->updateClub(1, []);

        $this->assertFalse($result);
    }

    public function testUpdateClubResetsValidationWhenRejected(): void
    {
        // Club was rejected (validation_admin = 0)
        $checkStmt = $this->createMockStatement([
            'validation_admin' => 0,
            'validation_tuteur' => null,
            'validation_finale' => 0,
        ]);

        $updateStmt = $this->createMock(PDOStatement::class);
        $updateStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturnOnConsecutiveCalls($checkStmt, $updateStmt);

        $result = $this->club->updateClub(1, [
            'nom_club' => 'Fixed Club',
        ]);

        $this->assertTrue($result);
    }

    // =========================================================================
    // deleteClub()
    // =========================================================================

    public function testDeleteClubReturnsTrueOnSuccess(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with([1])
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->club->deleteClub(1);

        $this->assertTrue($result);
    }

    // =========================================================================
    // getClubsByUser()
    // =========================================================================

    public function testGetClubsByUserReturnsUserClubs(): void
    {
        $expected = [
            ['club_id' => 1, 'nom_club' => 'My Club', 'user_role' => 'Président'],
        ];

        $stmt = $this->createMockStatement(null, $expected);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->club->getClubsByUser(1);

        $this->assertCount(1, $result);
        $this->assertEquals('Président', $result[0]['user_role']);
    }

    public function testGetClubsByUserReturnsEmptyForUserWithNoClubs(): void
    {
        $stmt = $this->createMockStatement(null, []);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->club->getClubsByUser(9999);

        $this->assertEmpty($result);
    }
}
