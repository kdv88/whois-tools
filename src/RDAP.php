<?php

namespace Galangw\WhoisTools;

use RuntimeException;

class RDAP
{
    private const SERVERS_IANA = '/resources/data/rdap-servers-iana.json';
    private const SERVERS_EXTRA = '/resources/data/rdap-servers-extra.json';
    private const TIMEOUT = 10;

    public string $domain;
    public string $extension;

    private array $servers = [];
    private string $server = '';

    private ?string $extensionTop;

    public function __construct(string $domain, string $extension, ?string $extensionTop = null, ?string $overrideServer = null)
    {
        $this->domain = $domain;
        $this->extension = $extension;
        $this->extensionTop = $extensionTop;

        $this->servers = $this->getServers();

        if (!empty($extensionTop) && !array_key_exists($extension, $this->servers)) {
            $this->extension = $extensionTop;
        }

        if ($overrideServer) {
            $this->server = rtrim($overrideServer, '/') . '/';
        } else {
            $this->server = $this->getServer();
        }
    }

    private function getServers(): array
    {
        $servers = [];

        if (
            ($json = file_get_contents($this->resolvePath('rdap_servers_iana', self::SERVERS_IANA))) !== false
        ) {
            $decoded = json_decode($json, true);
            if (is_array($decoded) && isset($decoded['services'])) {
                foreach ($decoded["services"] as $service) {
                    $tlds = $service[0];
                    $server = rtrim($service[1][0] ?? '', '/') . '/';

                    foreach ($tlds as $tld) {
                        $servers[$tld] = $server;
                    }
                }
            }
        }

        if (
            ($json = file_get_contents($this->resolvePath('rdap_servers_extra', self::SERVERS_EXTRA))) !== false
        ) {
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                foreach ($decoded as $tld => $url) {
                    $servers[$tld] = rtrim($url, '/') . '/';
                }
            }
        }

        return $servers;
    }

    private function getServer(): string
    {
        if ($this->extension === "iana") {
            return "https://rdap.iana.org/";
        }

        $server = $this->servers[idn_to_ascii($this->extension)] ?? "";

        if (empty($server)) {
            throw new RuntimeException("No RDAP server found for '$this->domain'");
        }

        return $server;
    }

    /**
     * @return array{0:int,1:string}
     */
    public function getData(): array
    {
        $curl = curl_init("{$this->server}domain/{$this->domain}");

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => self::TIMEOUT,
        ]);

        $response = curl_exec($curl);
        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new RuntimeException($error);
        }

        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);

        curl_close($curl);

        if (!preg_match("/^application\/(rdap\+)?json/i", (string) $contentType)) {
            $response = "";
        }

        return [$code, $response];
    }

    private function resolvePath(string $key, string $defaultRelativePath): string
    {
        $configKey = 'whois-tools.paths.' . $key;
        $namespacedConfig = __NAMESPACE__ . '\config';
        $path = null;

        if (function_exists($namespacedConfig)) {
            $path = $namespacedConfig($configKey);
        } else if (function_exists('config')) {
            $path = config($configKey);
        }

        if (!is_string($path) || $path === '') {
            $path = dirname(__DIR__) . $defaultRelativePath;
        }

        if (!is_readable($path)) {
            throw new RuntimeException("Configured path for '$key' is not readable");
        }

        return $path;
    }
}
