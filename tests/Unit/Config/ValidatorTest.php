<?php
declare(strict_types=1);

namespace Tests\Unit\Config;

use Validator;
use Tests\BaseTestCase;

/**
 * Unit tests for the Validator class.
 * Tests sanitization, validation rules, and error collection.
 */
class ValidatorTest extends BaseTestCase
{
    private Validator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new Validator();
    }

    // =========================================================================
    // Sanitization
    // =========================================================================

    public function testSanitizeStringRemovesXss(): void
    {
        $input = '<script>alert("XSS")</script>';
        $result = $this->validator->sanitizeString($input);

        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringContainsString('&lt;script&gt;', $result);
    }

    public function testSanitizeStringTrimsWhitespace(): void
    {
        $result = $this->validator->sanitizeString('  hello  ');

        $this->assertEquals('hello', $result);
    }

    public function testSanitizeStringHandlesNull(): void
    {
        $result = $this->validator->sanitizeString(null);

        $this->assertEquals('', $result);
    }

    public function testSanitizeStringPreservesNewlines(): void
    {
        $result = $this->validator->sanitizeString("line1\nline2", true);

        $this->assertStringContainsString("\n", $result);
    }

    public function testSanitizeEmailLowercases(): void
    {
        $result = $this->validator->sanitizeEmail('Jean@EILCO.FR');

        $this->assertEquals('jean@eilco.fr', $result);
    }

    public function testSanitizeIntReturnsInteger(): void
    {
        $result = $this->validator->sanitizeInt('42');

        $this->assertSame(42, $result);
    }

    public function testSanitizeIntReturnsDefault(): void
    {
        $result = $this->validator->sanitizeInt('not-a-number', 10);

        $this->assertSame(10, $result);
    }

    public function testSanitizeArrayRecursive(): void
    {
        $result = $this->validator->sanitizeArray([
            '<b>bold</b>',
            ['<i>italic</i>'],
        ]);

        $this->assertStringNotContainsString('<b>', $result[0]);
        $this->assertStringNotContainsString('<i>', $result[1][0]);
    }

    // =========================================================================
    // Validation rules
    // =========================================================================

    public function testValidateRequiredFailsOnEmpty(): void
    {
        $this->validator->validateRequired('', 'name');

        $this->assertTrue($this->validator->hasErrors());
    }

    public function testValidateRequiredPassesOnValue(): void
    {
        $this->validator->validateRequired('Jean', 'name');

        $this->assertFalse($this->validator->hasErrors());
    }

    public function testValidateEmailAcceptsValid(): void
    {
        $this->validator->validateEmail('jean@eilco.fr', 'email');

        $this->assertFalse($this->validator->hasErrors());
    }

    public function testValidateEmailRejectsInvalid(): void
    {
        $this->validator->validateEmail('not-an-email', 'email');

        $this->assertTrue($this->validator->hasErrors());
    }

    public function testValidateLengthTooShort(): void
    {
        $this->validator->validateLength('ab', 3, 50, 'name');

        $this->assertTrue($this->validator->hasErrors());
    }

    public function testValidateLengthTooLong(): void
    {
        $this->validator->validateLength(str_repeat('x', 51), 3, 50, 'name');

        $this->assertTrue($this->validator->hasErrors());
    }

    public function testValidateLengthAcceptsValid(): void
    {
        $this->validator->validateLength('Hello World', 3, 50, 'name');

        $this->assertFalse($this->validator->hasErrors());
    }

    // =========================================================================
    // Error collection
    // =========================================================================

    public function testResetClearsErrors(): void
    {
        $this->validator->validateRequired('', 'field');
        $this->assertTrue($this->validator->hasErrors());

        $this->validator->reset();
        $this->assertFalse($this->validator->hasErrors());
    }

    public function testGetErrorsReturnsAllErrors(): void
    {
        $this->validator->validateRequired('', 'field1');
        $this->validator->validateEmail('invalid', 'field2');

        $errors = $this->validator->getErrors();

        $this->assertCount(2, $errors);
    }

    public function testGetErrorReturnsSpecificFieldError(): void
    {
        $this->validator->validateRequired('', 'nom');

        $error = $this->validator->getFirstError('nom');

        $this->assertNotNull($error);
        $this->assertIsString($error);
    }

    // =========================================================================
    // Edge cases
    // =========================================================================

    public function testValidateInListAcceptsValidOption(): void
    {
        $this->validator->validateInList('Calais', ['Calais', 'Longuenesse', 'Dunkerque', 'Boulogne'], 'campus');

        $this->assertFalse($this->validator->hasErrors());
    }

    public function testValidateInListRejectsInvalidOption(): void
    {
        $this->validator->validateInList('Paris', ['Calais', 'Longuenesse', 'Dunkerque', 'Boulogne'], 'campus');

        $this->assertTrue($this->validator->hasErrors());
    }
}
