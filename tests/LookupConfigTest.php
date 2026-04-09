<?php

declare(strict_types=1);

namespace Galangw\WhoisTools\Tests;

use Galangw\WhoisTools\Lookup;
use PHPUnit\Framework\TestCase;

final class LookupConfigTest extends TestCase
{
    public function testLookupUsesBundledPublicSuffixListByDefault(): void
    {
        $lookup = new Lookup('example.com', ['rdap']);

        $this->assertSame('example.com', strtolower($lookup->domain));
        $this->assertSame('com', $lookup->extension);
    }
}
