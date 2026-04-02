<?php
declare(strict_types=1);

namespace Tests\Feature;

use AuthController;
use PDO;
use ReflectionClass;
use Security;
use Tests\BaseTestCase;

require_once ROOT_PATH . '/controllers/AuthController.php';

final class AuthControllerSecurityTest extends BaseTestCase
{
    private AuthController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $pdo = $this->createMock(PDO::class);
        $this->controller = new AuthController($pdo);

        // Inject a lightweight stubbed user model so tests don't need a real DB.
        $stubUserModel = new class {
            public bool $authSuccess = false;
            public bool $emailExists = false;

            public function authenticate(string $mail, string $password)
            {
                if ($this->authSuccess) {
                    return [
                        'id' => 7,
                        'nom' => 'Doe',
                        'prenom' => 'Jane',
                        'permission' => 1,
                    ];
                }
                return false;
            }

            public function getUserByEmail(string $mail)
            {
                return $this->emailExists ? ['mail' => $mail] : false;
            }

            public function updatePassword(string $mail, string $password): bool
            {
                return true;
            }

            public function createUser(...$args): bool
            {
                return true;
            }
        };

        $ref = new ReflectionClass($this->controller);
        $prop = $ref->getProperty('userModel');
        $prop->setAccessible(true);
        $prop->setValue($this->controller, $stubUserModel);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REMOTE_ADDR'] = '203.0.113.10';
        $_SESSION['reset_step'] = 0;
    }

    public function testLoginRateLimitBlocksOnSixthFailure(): void
    {
        $_POST = [
            'formsend' => '1',
            'mail' => 'student@eilco.fr',
            'password' => 'wrong-pass'
        ];

        $lastError = '';
        for ($i = 0; $i < 6; $i++) {
            $result = $this->controller->login();
            $lastError = (string)($result['error_message'] ?? '');
        }

        $this->assertStringContainsString('Trop de tentatives', $lastError);
    }

    public function testLoginRateLimitUsesEmailAndIpPair(): void
    {
        unset($_SERVER['HTTP_CF_CONNECTING_IP'], $_SERVER['HTTP_X_REAL_IP']);
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.10';
        $_POST = [
            'formsend' => '1',
            'mail' => 'student@eilco.fr',
            'password' => 'wrong-pass'
        ];

        // Consume quota on IP A
        for ($i = 0; $i < 6; $i++) {
            $this->controller->login();
        }

        // Switch to another IP: should not be blocked immediately
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.11';
        $_SERVER['REMOTE_ADDR'] = '203.0.113.11';
        $result = $this->controller->login();

        $this->assertSame('Identifiants invalides', $result['error_message']);
    }

    public function testResetTokenStoredHashedWithExpiry(): void
    {
        $this->setStubEmailExists(true);

        $_POST = [
            'send_reset_code' => '1',
            'mail' => 'student@eilco.fr'
        ];

        $this->controller->login();

        $this->assertArrayHasKey('reset_token_hash', $_SESSION);
        $this->assertArrayHasKey('reset_token_expires_at', $_SESSION);
        $this->assertEquals(64, strlen((string)$_SESSION['reset_token_hash']));
        $this->assertGreaterThan(time(), (int)$_SESSION['reset_token_expires_at']);
        $this->assertArrayNotHasKey('reset_code', $_SESSION);
    }

    public function testExpiredResetTokenForcesStepBack(): void
    {
        $_SESSION['reset_step'] = 2;
        $_SESSION['reset_mail'] = 'student@eilco.fr';
        $_SESSION['reset_token_hash'] = hash('sha256', 'known-token');
        $_SESSION['reset_token_expires_at'] = time() - 5;

        $_POST = [
            'verify_reset_code' => '1',
            'reset_code' => 'known-token'
        ];

        $result = $this->controller->login();

        $this->assertSame(1, $_SESSION['reset_step']);
        $this->assertStringContainsString('expir', (string)$result['error_message']);
    }

    public function testSecurityGetClientIpFallsBackSafely(): void
    {
        unset(
            $_SERVER['HTTP_CF_CONNECTING_IP'],
            $_SERVER['HTTP_X_FORWARDED_FOR'],
            $_SERVER['HTTP_X_REAL_IP'],
            $_SERVER['REMOTE_ADDR']
        );

        $this->assertSame('0.0.0.0', Security::getClientIp());
    }

    public function testSecurityGetClientIpUsesForwardedChainFirstIp(): void
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.9, 10.0.0.5';
        $_SERVER['REMOTE_ADDR'] = '203.0.113.40';

        $this->assertSame('198.51.100.9', Security::getClientIp());
    }

    private function setStubEmailExists(bool $exists): void
    {
        $ref = new ReflectionClass($this->controller);
        $prop = $ref->getProperty('userModel');
        $prop->setAccessible(true);
        $stub = $prop->getValue($this->controller);
        $stub->emailExists = $exists;
        $prop->setValue($this->controller, $stub);
    }
}
