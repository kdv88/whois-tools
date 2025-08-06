<?php

declare(strict_types=1);

namespace Galangw\WhoisTools\Tests;

use Galangw\WhoisTools\Lookup;
use PHPUnit\Framework\TestCase;

/**
 * Network test using real RDAP for google.com.
 *
 * Notes:
 * - This test performs a live HTTP call to an RDAP server.
 * - It may be flaky if there is no internet connectivity or the RDAP endpoint rate-limits.
 * - We use RDAP only (no WHOIS) to avoid socket restrictions in some environments.
 */
final class LookupGoogleTest extends TestCase
{
    public function testRdapLookupForGoogleDotCom(): void
    {
        // Use RDAP only for more stable networking in CI and local environments.
        $lookup = new Lookup('google.com', ['rdap']);

        $parser = $lookup->parser;

        // Basic sanity checks
        $this->assertNotEmpty($parser->domain, 'Parsed domain should not be empty');
        $this->assertSame('google.com', strtolower($parser->domain), 'Domain should be google.com (case-insensitive)');

        // Registered domains should normally be flagged as registered
        $this->assertTrue($parser->registered, 'google.com should be registered');

        // Common fields should be available or null (vary by RDAP provider)
        $this->assertIsArray($parser->status);
        $this->assertIsArray($parser->nameServers);

        // Age/remaining are optional depending on RDAP data
        if ($parser->ageSeconds !== null) {
            $this->assertGreaterThan(0, $parser->ageSeconds, 'Age seconds should be positive if provided');
        }
        if ($parser->remainingSeconds !== null) {
            $this->assertGreaterThanOrEqual(0, $parser->remainingSeconds, 'Remaining seconds should be non-negative if provided');
        }
    }
}
