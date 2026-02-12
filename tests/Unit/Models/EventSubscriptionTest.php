<?php
declare(strict_types=1);

namespace Tests\Unit\Models;

use EventSubscription;
use PDO;
use PDOStatement;
use Tests\BaseTestCase;

/**
 * Unit tests for the EventSubscription model.
 * Tests subscribe/unsubscribe, duplicate prevention, and counting.
 */
class EventSubscriptionTest extends BaseTestCase
{
    private EventSubscription $subscription;
    private PDO $pdo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = $this->createMockPdo();
        $this->subscription = new EventSubscription($this->pdo);
    }

    // =========================================================================
    // isSubscribed()
    // =========================================================================

    public function testIsSubscribedReturnsTrueWhenSubscribed(): void
    {
        $stmt = $this->createMockStatement(['count' => 1]);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->subscription->isSubscribed(1, 1);

        $this->assertTrue($result);
    }

    public function testIsSubscribedReturnsFalseWhenNotSubscribed(): void
    {
        $stmt = $this->createMockStatement(['count' => 0]);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->subscription->isSubscribed(1, 1);

        $this->assertFalse($result);
    }

    // =========================================================================
    // subscribeToEvent()
    // =========================================================================

    public function testSubscribeToEventPreventsDuplicate(): void
    {
        // First call to isSubscribed returns true (already subscribed)
        $checkStmt = $this->createMockStatement(['count' => 1]);

        $this->pdo->method('prepare')->willReturn($checkStmt);

        $result = $this->subscription->subscribeToEvent(1, 1);

        $this->assertTrue($result); // Should return true without inserting
    }

    public function testSubscribeToEventInsertsWhenNew(): void
    {
        // isSubscribed check returns 0, then INSERT succeeds
        $checkStmt = $this->createMockStatement(['count' => 0]);
        $insertStmt = $this->createMock(PDOStatement::class);
        $insertStmt->method('execute')->willReturn(true);

        $this->pdo->method('prepare')
            ->willReturnOnConsecutiveCalls($checkStmt, $insertStmt);

        $result = $this->subscription->subscribeToEvent(1, 1);

        $this->assertTrue($result);
    }

    // =========================================================================
    // unsubscribeFromEvent()
    // =========================================================================

    public function testUnsubscribeFromEventExecutesDelete(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with([1, 5])
            ->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->subscription->unsubscribeFromEvent(5, 1);

        $this->assertTrue($result);
    }

    // =========================================================================
    // getSubscriptionCount()
    // =========================================================================

    public function testGetSubscriptionCountReturnsCorrectCount(): void
    {
        $stmt = $this->createMockStatement(['count' => 42]);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->subscription->getSubscriptionCount(1);

        $this->assertEquals(42, $result);
    }

    public function testGetSubscriptionCountReturnsZeroWhenEmpty(): void
    {
        $stmt = $this->createMockStatement(['count' => 0]);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->subscription->getSubscriptionCount(999);

        $this->assertEquals(0, $result);
    }

    // =========================================================================
    // getEventSubscribers()
    // =========================================================================

    public function testGetEventSubscribersReturnsUserData(): void
    {
        $expected = [
            ['user_id' => 1, 'event_id' => 1, 'nom' => 'Dupont', 'prenom' => 'Jean'],
        ];

        $stmt = $this->createMockStatement(null, $expected);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->subscription->getEventSubscribers(1);

        $this->assertCount(1, $result);
        $this->assertArrayHasKey('nom', $result[0]);
    }

    // =========================================================================
    // getUserSubscriptions()
    // =========================================================================

    public function testGetUserSubscriptionsReturnsEventsArray(): void
    {
        $expected = [
            ['event_id' => 1, 'titre' => 'Event 1'],
            ['event_id' => 2, 'titre' => 'Event 2'],
        ];

        $stmt = $this->createMockStatement(null, $expected);
        $this->expectPrepare($this->pdo, $stmt);

        $result = $this->subscription->getUserSubscriptions(1);

        $this->assertCount(2, $result);
    }
}
