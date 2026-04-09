<?php

declare(strict_types=1);

namespace Galangw\WhoisTools;

if (!function_exists(__NAMESPACE__ . '\config')) {
    function config(string $key): mixed
    {
        return $GLOBALS['whois_tools_test_config'][$key] ?? null;
    }
}

namespace Galangw\WhoisTools\Tests;

use Galangw\WhoisTools\Lookup;
use Galangw\WhoisTools\RDAP;
use Galangw\WhoisTools\WHOIS;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class DataPathOverridesTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($GLOBALS['whois_tools_test_config']);
    }

    public function testLookupUsesConfiguredPublicSuffixListPath(): void
    {
        $pslPath = sys_get_temp_dir() . '/whois-tools-psl-' . uniqid('', true) . '.dat';
        copy(dirname(__DIR__) . '/resources/data/public-suffix-list.dat', $pslPath);

        $GLOBALS['whois_tools_test_config'] = [
            'whois-tools.paths.public_suffix_list' => $pslPath,
        ];

        $lookup = new Lookup('example.com', ['rdap']);

        $this->assertSame('example.com', strtolower($lookup->domain));
        $this->assertSame('com', $lookup->extension);
    }

    public function testRdapUsesConfiguredServerLists(): void
    {
        $baseDir = sys_get_temp_dir() . '/whois-tools-test-' . uniqid('', true);
        mkdir($baseDir, 0777, true);

        $ianaPath = $baseDir . '/rdap-iana.json';
        $extraPath = $baseDir . '/rdap-extra.json';

        file_put_contents($ianaPath, json_encode([
            'services' => [
                [['example'], ['https://rdap.iana.example']],
            ],
        ], JSON_PRETTY_PRINT));
        file_put_contents($extraPath, json_encode([
            'example' => 'https://rdap.extra.example',
        ], JSON_PRETTY_PRINT));

        $GLOBALS['whois_tools_test_config'] = [
            'whois-tools.paths.rdap_servers_iana' => $ianaPath,
            'whois-tools.paths.rdap_servers_extra' => $extraPath,
        ];

        $rdap = new RDAP('domain.example', 'example');

        $reflection = new ReflectionClass($rdap);
        $serverProperty = $reflection->getProperty('server');
        $serverProperty->setAccessible(true);

        $this->assertSame('https://rdap.extra.example/', $serverProperty->getValue($rdap));
    }

    public function testWhoisUsesConfiguredServerLists(): void
    {
        $baseDir = sys_get_temp_dir() . '/whois-tools-test-' . uniqid('', true);
        mkdir($baseDir, 0777, true);

        $ianaPath = $baseDir . '/whois-iana.json';
        $extraPath = $baseDir . '/whois-extra.json';

        file_put_contents($ianaPath, json_encode([
            'example' => 'whois.iana.example',
        ], JSON_PRETTY_PRINT));
        file_put_contents($extraPath, json_encode([
            'example' => 'whois.extra.example',
        ], JSON_PRETTY_PRINT));

        $GLOBALS['whois_tools_test_config'] = [
            'whois-tools.paths.whois_servers_iana' => $ianaPath,
            'whois-tools.paths.whois_servers_extra' => $extraPath,
        ];

        $whois = new WHOIS('domain.example', 'example');

        $reflection = new ReflectionClass($whois);
        $serverProperty = $reflection->getProperty('server');
        $serverProperty->setAccessible(true);

        $this->assertSame('whois.extra.example', $serverProperty->getValue($whois));
    }
}
