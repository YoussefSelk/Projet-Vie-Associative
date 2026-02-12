<?php
declare(strict_types=1);

namespace Tests\Unit\Models;

use ClubMember;
use PDO;
use PDOStatement;
use Tests\BaseTestCase;

/**
 * Unit tests for the ClubMember model.
 * Tests member CRUD and validation operations.
 */
class ClubMemberTest extends BaseTestCase
{
    private ClubMember $member;
    private PDO $pdo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = $this->createMockPdo();
        $this->member = new ClubMember($this->pdo);
    }

    public function testGetClubMembersReturnsArray(): void
    {
        $expected = [
            ['membre_id' => 1, 'club_id' => 1, 'nom' => 'Dupont', 'prenom' => 'Jean', 'fonction' => 'Président'],
        ];

        $stmt = $this->createMockStatement(null, $expected);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->member->getClubMembers(1);

        $this->assertCount(1, $result);
        $this->assertEquals('Président', $result[0]['fonction']);
    }

    public function testGetUserClubsReturnsClubs(): void
    {
        $expected = [
            ['club_id' => 1, 'nom_club' => 'Club A'],
        ];

        $stmt = $this->createMockStatement(null, $expected);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->member->getUserClubs(1);

        $this->assertCount(1, $result);
    }

    public function testAddMemberInserts(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with([1, 2, 'Trésorier'])
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->member->addMember(1, 2, 'Trésorier');

        $this->assertTrue($result);
    }

    public function testAddMemberDefaultsToMembre(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with([1, 3, 'Membre'])
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->member->addMember(1, 3);

        $this->assertTrue($result);
    }

    public function testRemoveMemberDeletes(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with([1, 2])
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->member->removeMember(1, 2);

        $this->assertTrue($result);
    }

    public function testValidateMemberUpdates(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with([1, 2])
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->member->validateMember(1, 2);

        $this->assertTrue($result);
    }
}
