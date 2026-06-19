<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LangParityTest extends TestCase
{
    /** @return array<string, string> */
    private static function loadLang(string $locale): array
    {
        $file = BASE_PATH . '/lang/' . $locale . '.php';
        return require $file;
    }

    public function testPtAndEnShareCoreKeys(): void
    {
        $pt = self::loadLang('pt-BR');
        $en = self::loadLang('en-US');

        $corePrefixes = [
            'app.', 'nav.', 'auth.', 'landing.hero_', 'landing.notice_bar',
            'customer.', 'reservation.', 'account.recovery_', 'contact.',
        ];

        $missingInEn = [];
        foreach ($pt as $key => $_) {
            foreach ($corePrefixes as $prefix) {
                if (str_starts_with($key, $prefix) && !array_key_exists($key, $en)) {
                    $missingInEn[] = $key;
                }
            }
        }

        $this->assertSame([], $missingInEn, 'Chaves pt-BR ausentes em en-US: ' . implode(', ', $missingInEn));
    }
}
