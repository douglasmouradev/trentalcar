<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SiteDeprecatedTest extends TestCase
{
    public function testLegacySiteFolderWasRemoved(): void
    {
        $this->assertDirectoryDoesNotExist(BASE_PATH . '/site');
    }
}
