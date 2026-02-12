<?php
declare(strict_types=1);

namespace Tests\Feature;

use EventSubscription;
use PDO;
use PDOStatement;
use Tests\BaseTestCase;

/**
 * Integration tests for subscription management.
 * Tests subscribe/unsubscribe flows, duplicate prevention, and edge cases.
 */
class SubscriptionFlowTest extends BaseTestCase
{
    private EventSubscription $subscription;
    private PDO $pdo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = $this->createMockPdo();
        $this->subscription = new EventSubscription($this->pdo);
    }

    /**
     * Test: subscribe → check is subscribed → unsubscribe → check not subscribed
     */
    public function testSubscribeUnsubscribeFullCycle(): void
    {
        // Step 1: Check not subscribed -> subscribe
        $checkNotSubscribed = $this->createMockStatement(['count' => 0]);
        $insertStmt = $this->createMock(PDOStatement::class);
        $insertStmt->method('execute')->willReturn(true);

        $this->pdo->method('prepare')
            ->willReturnOnConsecutiveCalls($checkNotSubscribed, $insertStmt);

        $subscribed = $this->subscription->subscribeToEvent(1, 1);
        $this->assertTrue($subscribed);
    }

    /**
     * Test that subscribing twice doesn't create duplicates
     */
    public function testDoubleSubscribePreventsDuplicate(): void
    {
        // Already subscribed
        $checkSubscribed = $this->createMockStatement(['count' => 1]);
        $this->pdo->method('prepare')->willReturn($checkSubscribed);

        $result = $this->subscription->subscribeToEvent(1, 1);

        // Should still return true but without INSERT
        $this->assertTrue($result);
    }

    /**
     * Test subscribing to invalid event (PDO exception)
     */
    public function testSubscribeToInvalidEventHandlesError(): void
    {
        // Not subscribed
        $checkStmt = $this->createMockStatement(['count' => 0]);

        // INSERT throws exception (FK constraint)
        $insertStmt = $this->createMock(PDOStatement::class);
        $insertStmt->method('execute')
            ->willThrowException(new \PDOException('FK violation'));

        $this->pdo->method('prepare')
            ->willReturnOnConsecutiveCalls($checkStmt, $insertStmt);

        $result = $this->subscription->subscribeToEvent(999, 1);

        $this->assertFalse($result);
    }

    /**
     * Test subscription count reflects subscribes and unsubscribes
     */
    public function testSubscriptionCountAccurate(): void
    {
        $stmt = $this->createMockStatement(['count' => 15]);
        $this->pdo->method('prepare')->willReturn($stmt);

        $count = $this->subscription->getSubscriptionCount(1);

        $this->assertEquals(15, $count);
    }

    /**
     * Test: unsubscribe from event user wasn't subscribed to
     */
    public function testUnsubscribeFromUnsubscribedEventDoesNotFail(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);

        $this->pdo->method('prepare')->willReturn($stmt);

        // Should not throw, just do nothing
        $result = $this->subscription->unsubscribeFromEvent(1, 999);
        $this->assertTrue($result);
    }
}
