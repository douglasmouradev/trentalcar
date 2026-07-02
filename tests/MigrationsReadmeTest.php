<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MigrationsReadmeTest extends TestCase
{
    public function testReadmeDocumentsDuplicatePrefixes(): void
    {
        $path = BASE_PATH . '/database/migrations/README.md';
        $this->assertFileExists($path);
        $content = file_get_contents($path);
        $this->assertIsString($content);
        foreach (['006_', '007_', '008_', '009_', '017_leads_modern_schema', '018_'] as $needle) {
            $this->assertStringContainsString($needle, $content);
        }
    }
}
