<?php
declare(strict_types=1);

namespace Tests\Unit\Models;

use Tests\BaseTestCase;
use EventReport;
use PDO;
use PDOStatement;

/**
 * Unit tests for the EventReport model.
 * Tests report retrieval, update, deletion, and existence check.
 */
class EventReportTest extends BaseTestCase
{
    private EventReport $report;
    private PDO $pdo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = $this->createMockPdo();
        $this->report = new EventReport($this->pdo);
    }

    // =========================================================================
    // getEventWithReport
    // =========================================================================

    public function testGetEventWithReportReturnsData(): void
    {
        $expected = ['event_id' => 1, 'nom_event' => 'Gala', 'rapport_event' => 'rapports/gala.pdf', 'nom_club' => 'BDE'];
        $stmt = $this->createMockStatement($expected);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->report->getEventWithReport(1);

        $this->assertIsArray($result);
        $this->assertEquals('Gala', $result['nom_event']);
    }

    public function testGetEventWithReportReturnsFalseWhenNotFound(): void
    {
        $stmt = $this->createMockStatement(false);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->report->getEventWithReport(999);

        $this->assertFalse($result);
    }

    // =========================================================================
    // getEventsWithReports
    // =========================================================================

    public function testGetEventsWithReportsReturnsList(): void
    {
        $rows = [
            ['event_id' => 1, 'nom_event' => 'Gala', 'rapport_event' => 'rapports/gala.pdf'],
            ['event_id' => 2, 'nom_event' => 'Soirée', 'rapport_event' => 'rapports/soiree.pdf'],
        ];
        $stmt = $this->createMockStatement(false, $rows);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->report->getEventsWithReports();

        $this->assertCount(2, $result);
    }

    public function testGetEventsWithReportsReturnsEmptyWhenNone(): void
    {
        $stmt = $this->createMockStatement(false, []);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->report->getEventsWithReports();

        $this->assertEmpty($result);
    }

    // =========================================================================
    // getEventsWithoutReports
    // =========================================================================

    public function testGetEventsWithoutReportsReturnsPastEventsWithNoReport(): void
    {
        $rows = [
            ['event_id' => 3, 'nom_event' => 'Tournoi', 'rapport_event' => null],
        ];
        $stmt = $this->createMockStatement(false, $rows);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->report->getEventsWithoutReports();

        $this->assertCount(1, $result);
        $this->assertNull($result[0]['rapport_event']);
    }

    // =========================================================================
    // updateReportWithImages
    // =========================================================================

    public function testUpdateReportWithImagesExecutesCorrectly(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with(['rapports/gala.pdf', 'images/gala.zip', 1])
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->report->updateReportWithImages(1, 'rapports/gala.pdf', 'images/gala.zip');

        $this->assertTrue($result);
    }

    // =========================================================================
    // deleteReport
    // =========================================================================

    public function testDeleteReportNullsColumn(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with([1])
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->report->deleteReport(1);

        $this->assertTrue($result);
    }

    // =========================================================================
    // hasReport
    // =========================================================================

    public function testHasReportReturnsTrueWhenReportExists(): void
    {
        $stmt = $this->createMockStatement(['rapport_event' => 'rapports/gala.pdf']);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->report->hasReport(1);

        $this->assertTrue($result);
    }

    public function testHasReportReturnsFalseWhenNoReport(): void
    {
        $stmt = $this->createMockStatement(false);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->report->hasReport(999);

        $this->assertFalse($result);
    }
}
