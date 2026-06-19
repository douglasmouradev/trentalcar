<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TotpRecoveryTest extends TestCase
{
    public function testNormalizeCode(): void
    {
        $ref = new ReflectionClass(TotpRecovery::class);
        $m = $ref->getMethod('normalize');
        $m->setAccessible(true);
        $this->assertSame('ABCD-1234', $m->invoke(null, 'abcd1234'));
        $this->assertSame('ABCD-1234', $m->invoke(null, 'ABCD-1234'));
        $this->assertSame('', $m->invoke(null, 'invalid'));
    }
}
