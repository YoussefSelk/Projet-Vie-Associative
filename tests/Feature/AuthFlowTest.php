<?php
declare(strict_types=1);

namespace Tests\Feature;

use Security;
use Tests\BaseTestCase;

/**
 * Integration tests for Authentication & Authorization flows.
 * Tests session management, CSRF, permissions, and rate limiting.
 */
class AuthFlowTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        unset(
            $_SESSION['csrf_token'],
            $_SESSION['csrf_token_time'],
            $_SESSION['id'],
            $_SESSION['permission']
        );
    }

    // =========================================================================
    // Session + CSRF Integration
    // =========================================================================

    public function testCsrfTokenRoundTrip(): void
    {
        // Generate
        $token = Security::generateCsrfToken();
        $this->assertNotEmpty($token);

        // Validate same token
        $this->assertTrue(Security::validateCsrfToken($token));

        // Wrong token fails
        $this->assertFalse(Security::validateCsrfToken('wrong-token'));
    }

    public function testCsrfFieldContainsValidToken(): void
    {
        $field = Security::csrfField();

        // Extract token from the hidden field
        preg_match('/value="([^"]+)"/', $field, $matches);
        $this->assertNotEmpty($matches[1]);

        // The extracted token should validate
        $this->assertTrue(Security::validateCsrfToken($matches[1]));
    }

    // =========================================================================
    // Permission levels
    // =========================================================================

    public function testUnauthenticatedUserHasNoSession(): void
    {
        $this->assertArrayNotHasKey('id', $_SESSION);
        $this->assertArrayNotHasKey('permission', $_SESSION);
    }

    public function testLoginSetsSessionVariables(): void
    {
        $this->loginAsUser(1, 3, 'Admin', 'Test');

        $this->assertEquals(1, $_SESSION['id']);
        $this->assertEquals(3, $_SESSION['permission']);
        $this->assertEquals('Admin', $_SESSION['nom']);
        $this->assertEquals('Test', $_SESSION['prenom']);
    }

    public function testLogoutClearsSession(): void
    {
        $this->loginAsUser(1, 5);
        $this->logout();

        $this->assertArrayNotHasKey('id', $_SESSION);
        $this->assertArrayNotHasKey('permission', $_SESSION);
    }

    // =========================================================================
    // Rate Limiting integration
    // =========================================================================

    public function testLoginRateLimitBlocksAfterMaxAttempts(): void
    {
        $key = 'login_test@eilco.fr';

        for ($i = 0; $i < 5; $i++) {
            $this->assertTrue(Security::checkRateLimit($key, 5, 5));
        }

        // 6th attempt should be blocked
        $this->assertFalse(Security::checkRateLimit($key, 5, 5));
    }

    public function testRateLimitResetsAfterSuccessfulLogin(): void
    {
        $key = 'login_user@eilco.fr';

        // Simulate failed attempts
        for ($i = 0; $i < 3; $i++) {
            Security::checkRateLimit($key, 5, 5);
        }

        // Reset on successful login
        Security::resetRateLimit($key);

        // Should allow attempts again
        $this->assertTrue(Security::checkRateLimit($key, 5, 5));
    }

    // =========================================================================
    // Input sanitization in auth context
    // =========================================================================

    public function testSanitizeInputPreventsXssInLoginForm(): void
    {
        $maliciousEmail = '<script>document.cookie</script>@evil.com';
        $sanitized = Security::sanitizeInput($maliciousEmail);

        $this->assertStringNotContainsString('<script>', $sanitized);
    }

    public function testSanitizeInputPreservesValidEmail(): void
    {
        $email = 'jean.dupont@eilco.univ-littoral.fr';
        $sanitized = Security::sanitizeInput($email);

        $this->assertEquals($email, $sanitized);
    }

    // =========================================================================
    // Edge cases
    // =========================================================================

    public function testEmptyPasswordRejected(): void
    {
        $this->assertFalse(password_verify('', '$2y$12$invalidhash'));
    }

    public function testPasswordHashingConsistency(): void
    {
        $password = 'SecurePass!2026';
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        $this->assertTrue(password_verify($password, $hash));
        $this->assertFalse(password_verify('WrongPass!', $hash));
    }
}
