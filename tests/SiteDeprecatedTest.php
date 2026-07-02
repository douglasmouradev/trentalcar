<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SiteDeprecatedTest extends TestCase
{
    public function testLegacySiteFolderIsMarkedDeprecated(): void
    {
        $readme = BASE_PATH . '/site/README.md';
        $marker = BASE_PATH . '/site/.deprecated';
        $this->assertFileExists($readme);
        $this->assertFileExists($marker);
        $content = file_get_contents($readme);
        $this->assertIsString($content);
        $this->assertStringContainsString('DEPRECATED', $content);
        $this->assertStringContainsString('public/', $content);
    }
}
