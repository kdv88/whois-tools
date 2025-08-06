<?php

declare(strict_types=1);

namespace Galangw\WhoisTools\Tests;

use Galangw\WhoisTools\Parsers\ParserRDAP;
use PHPUnit\Framework\TestCase;

final class ParserRdapTest extends TestCase
{
    public function testParsesMinimalRdapJson(): void
    {
        $json = [
            'ldhName' => 'example.com',
            'status' => ['active'],
            'events' => [
                ['eventAction' => 'registration', 'eventDate' => '1995-08-14T04:00:00Z'],
                ['eventAction' => 'expiration', 'eventDate' => '2030-08-13T04:00:00Z'],
            ],
            'nameservers' => [
                ['ldhName' => 'a.iana-servers.net'],
                ['ldhName' => 'b.iana-servers.net'],
            ],
        ];

        $raw = json_encode($json, JSON_UNESCAPED_SLASHES);
        $parser = new ParserRDAP(200, $raw ?: '', $json);

        $this->assertTrue($parser->registered, 'Should be registered for 200 code');
        $this->assertSame('example.com', $parser->domain);
        $this->assertContains('active', $parser->status);
        $this->assertNotNull($parser->creationDateISO8601);
        $this->assertNotNull($parser->expirationDateISO8601);
        $this->assertGreaterThan(0, $parser->ageSeconds ?? 0);
        $this->assertGreaterThanOrEqual(0, $parser->remainingSeconds ?? 0);
        $this->assertCount(2, $parser->nameServers);
    }

    public function testHandlesErrorCodeGracefully(): void
    {
        $parser = new ParserRDAP(404, '{"error":"not found"}', null);
        $this->assertFalse($parser->registered);
        $this->assertSame('', $parser->domain);
        $this->assertSame([], $parser->status);
    }
}
