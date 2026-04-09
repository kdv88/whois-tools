<?php

namespace Galangw\WhoisTools;

use RuntimeException;

class WHOIS
{
    private const SERVERS_IANA = '/resources/data/whois-servers-iana.json';
    private const SERVERS_EXTRA = '/resources/data/whois-servers-extra.json';
    private const TIMEOUT = 10;

    public string $domain;
    public string $extension;

    private array $servers = [];
    private string|array $server = '';

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
            $this->server = $overrideServer;
        } else {
            $this->server = $this->getServer();
        }
    }

    private function getServers(): array
    {
        $servers = [];

        if (
            ($json = file_get_contents($this->resolvePath('whois_servers_iana', self::SERVERS_IANA))) !== false
        ) {
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                $servers = array_merge($servers, $decoded);
            }
        }

        if (
            ($json = file_get_contents($this->resolvePath('whois_servers_extra', self::SERVERS_EXTRA))) !== false
        ) {
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                $servers = array_merge($servers, $decoded);
            }
        }

        return $servers;
    }

    private function getServer(): string|array
    {
        if ($this->extension === "iana") {
            return "whois.iana.org";
        }

        $server = $this->servers[idn_to_ascii($this->extension)] ?? "";

        if (empty($server)) {
            throw new RuntimeException("No WHOIS server found for '$this->domain'");
        }

        return $server;
    }

    public function getData(): string
    {
        $domain = idn_to_ascii($this->domain);

        $host = $this->server;
        $query = "$domain\r\n";

        if (is_array($this->server)) {
            $host = $this->server["host"];
            $query = str_replace("{domain}", $domain, $this->server["query"]);
        }

        $socket = @stream_socket_client("tcp://$host:43", $errno, $errstr, self::TIMEOUT);

        if (!$socket) {
            throw new RuntimeException($errstr);
        }

        stream_set_timeout($socket, self::TIMEOUT);

        fwrite($socket, $query);

        $data = stream_get_contents($socket);

        $encoding = mb_detect_encoding($data, ["UTF-8", "ISO-8859-1"], true);
        if ($encoding && $encoding !== "UTF-8") {
            $data = mb_convert_encoding($data, "UTF-8", $encoding);
        }

        $metaData = stream_get_meta_data($socket);
        if ($metaData["timed_out"]) {
            fclose($socket);
            throw new RuntimeException("Operation timed out");
        }

        fclose($socket);

        return $data;
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
