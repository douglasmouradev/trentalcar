<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/helpers/Lang.php';

final class LangPlaceholderTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = ['lang' => 'pt-BR'];
        $ref = new ReflectionClass(Lang::class);
        $prop = $ref->getProperty('strings');
        $prop->setAccessible(true);
        $prop->setValue(null, null);
    }

    public function testPlaceholderReplacement(): void
    {
        $msg = Lang::get('flash.reservation_saved', ['code' => 'TR-001']);
        $this->assertStringContainsString('TR-001', $msg);
    }
}
