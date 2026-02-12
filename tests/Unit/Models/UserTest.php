<?php
declare(strict_types=1);

namespace Tests\Unit\Models;

use User;
use PDO;
use PDOStatement;
use Tests\BaseTestCase;

/**
 * Unit tests for the User model.
 * Tests authentication, CRUD operations, password hashing.
 */
class UserTest extends BaseTestCase
{
    private User $user;
    private PDO $pdo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = $this->createMockPdo();
        $this->user = new User($this->pdo);
    }

    // =========================================================================
    // getUserById()
    // =========================================================================

    public function testGetUserByIdReturnsUserData(): void
    {
        $expected = [
            'id' => 1,
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'mail' => 'jean@eilco.fr',
            'permission' => 0,
        ];

        $stmt = $this->createMockStatement($expected);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->user->getUserById(1);

        $this->assertIsArray($result);
        $this->assertEquals('Dupont', $result['nom']);
        $this->assertEquals(1, $result['id']);
    }

    public function testGetUserByIdReturnsFalseForInvalidId(): void
    {
        $stmt = $this->createMockStatement(false);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->user->getUserById(0);

        $this->assertFalse($result);
    }

    // =========================================================================
    // getUserByEmail()
    // =========================================================================

    public function testGetUserByEmailReturnsUser(): void
    {
        $expected = ['id' => 1, 'mail' => 'test@eilco.fr'];

        $stmt = $this->createMockStatement($expected);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->user->getUserByEmail('test@eilco.fr');

        $this->assertEquals('test@eilco.fr', $result['mail']);
    }

    public function testGetUserByEmailReturnsFalseForNonexistent(): void
    {
        $stmt = $this->createMockStatement(false);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->user->getUserByEmail('nonexistent@eilco.fr');

        $this->assertFalse($result);
    }

    // =========================================================================
    // authenticate()
    // =========================================================================

    public function testAuthenticateSucceedsWithValidCredentials(): void
    {
        $hashedPassword = password_hash('SecurePass!123', PASSWORD_BCRYPT, ['cost' => 12]);
        $expected = [
            'id' => 1,
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'mail' => 'jean@eilco.fr',
            'password' => $hashedPassword,
            'permission' => 0,
        ];

        $stmt = $this->createMockStatement($expected);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->user->authenticate('jean@eilco.fr', 'SecurePass!123');

        $this->assertNotNull($result);
        $this->assertEquals(1, $result['id']);
        $this->assertEquals('Dupont', $result['nom']);
    }

    public function testAuthenticateReturnsNullWithWrongPassword(): void
    {
        $hashedPassword = password_hash('CorrectPassword!', PASSWORD_BCRYPT, ['cost' => 12]);
        $expected = [
            'id' => 1,
            'mail' => 'jean@eilco.fr',
            'password' => $hashedPassword,
        ];

        $stmt = $this->createMockStatement($expected);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->user->authenticate('jean@eilco.fr', 'WrongPassword!');

        $this->assertNull($result);
    }

    public function testAuthenticateReturnsNullForNonexistentEmail(): void
    {
        $stmt = $this->createMockStatement(false);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->user->authenticate('nobody@eilco.fr', 'anything');

        $this->assertNull($result);
    }

    // =========================================================================
    // createUser()
    // =========================================================================

    public function testCreateUserHashesPasswordByDefault(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function (array $params): bool {
                // Check that the password (index 3) is properly hashed
                return $params[0] === 'Dupont'
                    && $params[1] === 'Jean'
                    && $params[2] === 'jean@eilco.fr'
                    && password_verify('MyPass!123', $params[3])
                    && $params[4] === 'ING1';
            }))
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->user->createUser('Dupont', 'Jean', 'jean@eilco.fr', 'MyPass!123', 'ING1');

        $this->assertTrue($result);
    }

    public function testCreateUserAcceptsPreHashedPassword(): void
    {
        $preHashed = password_hash('AlreadyHashed!', PASSWORD_BCRYPT, ['cost' => 12]);

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function (array $params) use ($preHashed): bool {
                // When isHashed=true, password should be passed as-is
                return $params[3] === $preHashed;
            }))
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->user->createUser('Test', 'User', 'test@eilco.fr', $preHashed, 'CP1', true);

        $this->assertTrue($result);
    }

    // =========================================================================
    // updateUser()
    // =========================================================================

    public function testUpdateUserWithValidFields(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->user->updateUser(1, [
            'nom' => 'NewName',
            'prenom' => 'NewFirst',
        ]);

        $this->assertTrue($result);
    }

    public function testUpdateUserIgnoresDisallowedFields(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function (array $params): bool {
                // Only allowed fields should be present + the ID
                // 'nom' + id = 2 params
                return count($params) === 2 && $params[0] === 'Test' && $params[1] === 1;
            }))
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->user->updateUser(1, [
            'nom' => 'Test',
            'password' => 'should_not_be_updated',  // Not in allowed_fields
            'role' => 'admin',  // Not in allowed_fields
        ]);

        $this->assertTrue($result);
    }

    public function testUpdateUserWithEmptyDataReturnsFalse(): void
    {
        $result = $this->user->updateUser(1, []);

        $this->assertFalse($result);
    }

    // =========================================================================
    // updatePassword()
    // =========================================================================

    public function testUpdatePasswordHashesNewPassword(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function (array $params): bool {
                return password_verify('NewSecure!Pass', $params[0])
                    && $params[1] === 'user@eilco.fr';
            }))
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->user->updatePassword('user@eilco.fr', 'NewSecure!Pass');

        $this->assertTrue($result);
    }

    // =========================================================================
    // getAllUsers()
    // =========================================================================

    public function testGetAllUsersReturnsArray(): void
    {
        $expected = [
            ['id' => 1, 'nom' => 'A'],
            ['id' => 2, 'nom' => 'B'],
        ];

        $stmt = $this->createMockStatement(null, $expected);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->user->getAllUsers();

        $this->assertCount(2, $result);
    }
}
