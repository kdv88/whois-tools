<?php

namespace Galangw\WhoisTools\Parsers;

/**
 * Minimal base parser to keep API compatible during migration.
 * Concrete TLD parsers can extend this class.
 */
class Parser
{
    public string $domain = '';
    public bool $registered = false;

    public ?string $registrar = null;
    public ?string $registrarURL = null;

    public ?string $creationDate = null;
    public ?string $creationDateISO8601 = null;

    public ?string $expirationDate = null;
    public ?string $expirationDateISO8601 = null;

    public ?string $updatedDate = null;
    public ?string $updatedDateISO8601 = null;

    public ?string $availableDate = null;
    public ?string $availableDateISO8601 = null;

    /** @var string[] */
    public array $status = [];

    /** @var string[] */
    public array $nameServers = [];

    public ?int $ageSeconds = null;
    public ?int $remainingSeconds = null;

    public ?string $age = null;
    public ?string $remaining = null;

    public bool $gracePeriod = false;
    public bool $redemptionPeriod = false;
    public bool $pendingDelete = false;

    public string $rdapData = '';
    public string $raw;

    /**
     * Menampung field yang tidak dikenali saat parsing WHOIS
     * untuk kompatibilitas proses merge.
     * @var array<string,bool>
     */
    public array $unknown = [];

    public function __construct(string $raw)
    {
        $this->raw = $raw;
        $this->parse($raw);
    }

    protected function parse(string $raw): void
    {
        // Intentionally noop for generic parser.
        // Concrete parsers should set $this->registered and fields based on WHOIS text.
    }

    /**
     * Return unknown fields list to keep merge behavior.
     *
     * @return array<string, bool>
     */
    public function getUnknown(): array
    {
        return $this->unknown;
    }
}
