<?php
declare(strict_types=1);

namespace Tests\Unit\Config;

use Security;
use Tests\BaseTestCase;

/**
 * Unit tests for the Security class.
 * Tests CSRF token generation/validation, input sanitization, and rate limiting.
 */
class SecurityTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Clear CSRF tokens from session
        unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
    }

    // =========================================================================
    // CSRF Token
    // =========================================================================

    public function testGenerateCsrfTokenCreatesToken(): void
    {
        $token = Security::generateCsrfToken();

        $this->assertNotEmpty($token);
        $this->assertEquals(64, strlen($token)); // 32 bytes = 64 hex chars
        $this->assertEquals($token, $_SESSION['csrf_token']);
    }

    public function testGenerateCsrfTokenReturnsSameWithinLifetime(): void
    {
        $token1 = Security::generateCsrfToken();
        $token2 = Security::generateCsrfToken();

        $this->assertEquals($token1, $token2);
    }

    public function testValidateCsrfTokenAcceptsValid(): void
    {
        $token = Security::generateCsrfToken();

        $this->assertTrue(Security::validateCsrfToken($token));
    }

    public function testValidateCsrfTokenRejectsInvalid(): void
    {
        Security::generateCsrfToken();

        $this->assertFalse(Security::validateCsrfToken('invalid-token'));
    }

    public function testValidateCsrfTokenRejectsEmpty(): void
    {
        $this->assertFalse(Security::validateCsrfToken(''));
    }

    public function testValidateCsrfTokenRejectsWhenNoTokenInSession(): void
    {
        $this->assertFalse(Security::validateCsrfToken('some-token'));
    }

    public function testCsrfFieldReturnsHiddenInput(): void
    {
        $field = Security::csrfField();

        $this->assertStringContainsString('type="hidden"', $field);
        $this->assertStringContainsString('name="csrf_token"', $field);
        $this->assertStringContainsString('value="', $field);
    }

    // =========================================================================
    // Input Sanitization
    // =========================================================================

    public function testSanitizeInputEncodesHtml(): void
    {
        $result = Security::sanitizeInput('<script>alert("xss")</script>');

        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringContainsString('&lt;script&gt;', $result);
    }

    public function testSanitizeInputTrims(): void
    {
        $result = Security::sanitizeInput('  test  ');

        $this->assertEquals('test', $result);
    }

    public function testSanitizeInputHandlesArray(): void
    {
        $result = Security::sanitizeInput(['<b>bold</b>', '  trim  ']);

        $this->assertIsArray($result);
        $this->assertStringNotContainsString('<b>', $result[0]);
        $this->assertEquals('trim', $result[1]);
    }

    public function testSanitizeEmailRemovesInvalidChars(): void
    {
        $result = Security::sanitizeEmail('  test@eilco.fr  ');

        $this->assertEquals('test@eilco.fr', $result);
    }

    public function testValidateEmailAcceptsValid(): void
    {
        $this->assertTrue(Security::validateEmail('user@eilco.fr'));
    }

    public function testValidateEmailRejectsInvalid(): void
    {
        $this->assertFalse(Security::validateEmail('not-an-email'));
    }

    // =========================================================================
    // Rate Limiting
    // =========================================================================

    public function testCheckRateLimitAllowsFirstAttempt(): void
    {
        $this->assertTrue(Security::checkRateLimit('test_action'));
    }

    public function testCheckRateLimitBlocksAfterMaxAttempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            Security::checkRateLimit('brute_force_test', 5, 5);
        }

        $this->assertFalse(Security::checkRateLimit('brute_force_test', 5, 5));
    }

    public function testResetRateLimitClearsCounter(): void
    {
        for ($i = 0; $i < 5; $i++) {
            Security::checkRateLimit('reset_test', 5, 5);
        }

        Security::resetRateLimit('reset_test');

        $this->assertTrue(Security::checkRateLimit('reset_test', 5, 5));
    }

    // =========================================================================
    // HTTPS Detection
    // =========================================================================

    public function testIsHttpsReturnsFalseByDefault(): void
    {
        unset($_SERVER['HTTPS'], $_SERVER['HTTP_X_FORWARDED_PROTO']);

        $this->assertFalse(Security::isHttps());
    }

    public function testIsHttpsReturnsTrueWhenHttpsOn(): void
    {
        $_SERVER['HTTPS'] = 'on';

        $this->assertTrue(Security::isHttps());

        unset($_SERVER['HTTPS']);
    }

    public function testIsHttpsReturnsTrueBehindProxy(): void
    {
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';

        $this->assertTrue(Security::isHttps());

        unset($_SERVER['HTTP_X_FORWARDED_PROTO']);
    }
}
