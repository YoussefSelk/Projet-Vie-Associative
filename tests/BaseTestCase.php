<?php
declare(strict_types=1);

namespace Tests;

use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

/**
 * Base test case providing common mock utilities.
 * All test classes should extend this instead of PHPUnit\Framework\TestCase.
 */
abstract class BaseTestCase extends TestCase
{
    /**
     * Create a mock PDO instance.
     *
     * @return PDO&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createMockPdo(): PDO
    {
        $pdo = $this->createMock(PDO::class);
        return $pdo;
    }

    /**
     * Create a mock PDOStatement that returns specified data.
     *
     * @param mixed $fetchResult Result for fetch()
     * @param array|null $fetchAllResult Result for fetchAll()
     * @return PDOStatement&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createMockStatement(mixed $fetchResult = false, ?array $fetchAllResult = null): PDOStatement
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetch')->willReturn($fetchResult);

        if ($fetchAllResult !== null) {
            $stmt->method('fetchAll')->willReturn($fetchAllResult);
        }

        return $stmt;
    }

    /**
     * Configure a PDO mock to return a specific statement on prepare().
     *
     * @param PDO&\PHPUnit\Framework\MockObject\MockObject $pdo
     * @param PDOStatement&\PHPUnit\Framework\MockObject\MockObject $stmt
     */
    protected function expectPrepare(PDO $pdo, PDOStatement $stmt): void
    {
        $pdo->method('prepare')->willReturn($stmt);
    }

    /**
     * Set up session variables for a logged-in user.
     */
    protected function loginAsUser(int $id = 1, int $permission = 0, string $nom = 'Dupont', string $prenom = 'Jean'): void
    {
        $_SESSION['id'] = $id;
        $_SESSION['permission'] = $permission;
        $_SESSION['nom'] = $nom;
        $_SESSION['prenom'] = $prenom;
    }

    /**
     * Clear session data.
     */
    protected function logout(): void
    {
        $_SESSION = [];
    }

    /**
     * Reset superglobals to clean state.
     */
    protected function resetSuperglobals(): void
    {
        $_POST = [];
        $_GET = [];
        $_FILES = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SESSION = [];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetSuperglobals();
    }
}
