<?php
declare(strict_types=1);

namespace Tests\Unit\Controllers;

use ClubController;
use PDOStatement;
use ReflectionMethod;
use Tests\BaseTestCase;

require_once dirname(__DIR__, 3) . '/controllers/ClubController.php';

/**
 * Tests unitaires de la règle « soutenance unique » du ClubController.
 *
 * Règle métier (retour client juin 2026) : un étudiant ne peut être enregistré
 * « avec soutenance » que dans un seul club. La détection est portée par la
 * méthode privée getAlreadySoutenanceElsewhereNames().
 */
class ClubControllerTest extends BaseTestCase
{
    /** Appelle la méthode privée du contrôleur via réflexion. */
    private function callHelper(ClubController $ctrl, string $method, mixed ...$args): mixed
    {
        $ref = new ReflectionMethod(ClubController::class, $method);
        $ref->setAccessible(true);
        return $ref->invoke($ctrl, ...$args);
    }

    /** Construit un PDOStatement mock capturant les paramètres d'execute(). */
    private function recordingStatement(array $fetchAllResult, ?array &$captureParams): PDOStatement
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturnCallback(function (array $params = []) use (&$captureParams): bool {
            $captureParams = $params;
            return true;
        });
        $stmt->method('fetchAll')->willReturn($fetchAllResult);
        return $stmt;
    }

    // =========================================================================
    // getAlreadySoutenanceElsewhereNames()
    // =========================================================================

    public function testReturnsEmptyForEmptyUserIdsWithoutQuery(): void
    {
        $pdo = $this->createMockPdo();
        // Aucune liste → aucune requête ne doit être préparée.
        $pdo->expects($this->never())->method('prepare');

        $ctrl = new ClubController($pdo);
        $result = $this->callHelper($ctrl, 'getAlreadySoutenanceElsewhereNames', []);

        $this->assertSame([], $result);
    }

    public function testReturnsNamesInPrenomNomForMembersAlreadyInSoutenance(): void
    {
        $rows = [
            ['id' => 2, 'nom' => 'Martin',  'prenom' => 'Alice'],
            ['id' => 3, 'nom' => 'Bernard', 'prenom' => 'Léa'],
        ];
        $params = null;
        $stmt = $this->recordingStatement($rows, $params);

        $pdo = $this->createMockPdo();
        $pdo->method('prepare')->willReturn($stmt);

        $ctrl = new ClubController($pdo);
        $result = $this->callHelper($ctrl, 'getAlreadySoutenanceElsewhereNames', [2, 3]);

        $this->assertSame(['Alice Martin', 'Léa Bernard'], $result);
    }

    public function testReturnsEmptyWhenNoOneIsAlreadyInSoutenance(): void
    {
        $params = null;
        $stmt = $this->recordingStatement([], $params);

        $pdo = $this->createMockPdo();
        $pdo->method('prepare')->willReturn($stmt);

        $ctrl = new ClubController($pdo);
        $result = $this->callHelper($ctrl, 'getAlreadySoutenanceElsewhereNames', [9, 10]);

        $this->assertSame([], $result);
    }

    public function testExcludeClubIdAddsClubFilterAndParam(): void
    {
        $captured = '';
        $params = null;
        $stmt = $this->recordingStatement([], $params);

        $pdo = $this->createMockPdo();
        $pdo->method('prepare')->willReturnCallback(function (string $sql) use (&$captured, $stmt): PDOStatement {
            $captured = $sql;
            return $stmt;
        });

        $ctrl = new ClubController($pdo);
        $this->callHelper($ctrl, 'getAlreadySoutenanceElsewhereNames', [2, 3], 7);

        // En édition : on exclut le club courant de la recherche.
        $this->assertStringContainsString('mc.club_id != ?', $captured);
        $this->assertStringContainsString('mc.soutenance = 1', $captured);
        // Les paramètres = identifiants membres + club exclu.
        $this->assertSame([2, 3, 7], $params);
    }

    public function testNoExcludeClubIdOmitsClubFilter(): void
    {
        $captured = '';
        $params = null;
        $stmt = $this->recordingStatement([], $params);

        $pdo = $this->createMockPdo();
        $pdo->method('prepare')->willReturnCallback(function (string $sql) use (&$captured, $stmt): PDOStatement {
            $captured = $sql;
            return $stmt;
        });

        $ctrl = new ClubController($pdo);
        $this->callHelper($ctrl, 'getAlreadySoutenanceElsewhereNames', [2, 3]);

        // En création : aucun club à exclure.
        $this->assertStringNotContainsString('club_id != ?', $captured);
        $this->assertSame([2, 3], $params);
    }

    public function testDeduplicatesAndFiltersInvalidIds(): void
    {
        $captured = '';
        $params = null;
        $stmt = $this->recordingStatement([], $params);

        $pdo = $this->createMockPdo();
        $pdo->method('prepare')->willReturnCallback(function (string $sql) use (&$captured, $stmt): PDOStatement {
            $captured = $sql;
            return $stmt;
        });

        $ctrl = new ClubController($pdo);
        // Doublons (2,2) et identifiant vide (0) doivent être nettoyés.
        $this->callHelper($ctrl, 'getAlreadySoutenanceElsewhereNames', [2, 2, 0, 5]);

        $this->assertSame([2, 5], $params);
    }

    public function testFallbackNameWhenNomAndPrenomEmpty(): void
    {
        $rows = [['id' => 4, 'nom' => '', 'prenom' => '']];
        $params = null;
        $stmt = $this->recordingStatement($rows, $params);

        $pdo = $this->createMockPdo();
        $pdo->method('prepare')->willReturn($stmt);

        $ctrl = new ClubController($pdo);
        $result = $this->callHelper($ctrl, 'getAlreadySoutenanceElsewhereNames', [4]);

        $this->assertSame(['Utilisateur #4'], $result);
    }
}
