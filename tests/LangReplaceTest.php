<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LangReplaceTest extends TestCase
{
    public function testReplaceDoesNotCorruptTotalPlaceholder(): void
    {
        $all = ['pagination.showing' => ':from–:to de :total'];
        $ref = new ReflectionClass(Lang::class);
        $prop = $ref->getProperty('strings');
        $prop->setAccessible(true);
        $prop->setValue(null, $all);

        $text = Lang::get('pagination.showing', ['from' => 1, 'to' => 3, 'total' => 3]);
        $this->assertSame('1–3 de 3', $text);

        $prop->setValue(null, null);
    }
}
