<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    public function testSanitizeStripsScriptTag(): void
    {
        $this->assertSame('alert(1)', sanitize('<script>alert(1)</script>'));
    }

    public function testSanitizeTrimsWhitespace(): void
    {
        $this->assertSame('hello', sanitize('  hello  '));
    }

    public function testSanitizeEncodesSingleQuote(): void
    {
        $this->assertSame('O&#039;Brien', sanitize("O'Brien"));
    }

    public function testSanitizeStripsBoldTag(): void
    {
        $this->assertSame('bold', sanitize('<b>bold</b>'));
    }

    public function testSanitizeHandlesEmptyString(): void
    {
        $this->assertSame('', sanitize(''));
    }

    public function testSanitizeEncodesDoubleQuote(): void
    {
        $this->assertSame('say &quot;hi&quot;', sanitize('say "hi"'));
    }

    public function testIsLoggedInReturnsFalseWithoutSession(): void
    {
        unset($_SESSION['user_id']);
        $this->assertFalse(isLoggedIn());
    }

    public function testIsLoggedInReturnsTrueWithSession(): void
    {
        $_SESSION['user_id'] = 1;
        $this->assertTrue(isLoggedIn());
        unset($_SESSION['user_id']);
    }

    public function testAppUrlConstantIsDefined(): void
    {
        $this->assertTrue(defined('APP_URL'));
    }

    public function testAppNameIsDonaTu(): void
    {
        $this->assertSame('DonaTu', APP_NAME);
    }
}
