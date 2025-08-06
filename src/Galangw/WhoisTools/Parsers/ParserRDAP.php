<?php

namespace Galangw\WhoisTools\Parsers;

class ParserRDAP
{
    public bool $registered = false;
    public string $domain = '';
    public string $registrar = '';
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

    public function __construct(
        public int $code,
        public string $raw,
        public ?array $json
    ) {
        $this->rdapData = $raw;
        $this->parse();
    }

    private function parse(): void
    {
        if (!$this->json || $this->code >= 400) {
            return;
        }

        $j = $this->json;

        $this->domain = (string)($j['ldhName'] ?? $j['unicodeName'] ?? '');
        $this->status = array_values(array_filter(array_map('strval', $j['status'] ?? [])));

        // entities -> registrar
        if (!empty($j['entities']) && is_array($j['entities'])) {
            foreach ($j['entities'] as $ent) {
                $roles = $ent['roles'] ?? [];
                if (in_array('registrar', $roles, true)) {
                    $this->registrar = (string)($ent['vcardArray'][1][1][3] ?? $ent['fn']['value'] ?? $ent['handle'] ?? '');
                    // search for URL in links
                    if (!empty($ent['links'])) {
                        foreach ($ent['links'] as $link) {
                            if (!empty($link['href']) && is_string($link['href'])) {
                                $this->registrarURL = $link['href'];
                                break;
                            }
                        }
                    }
                    break;
                }
            }
        }

        // nameservers
        if (!empty($j['nameservers']) && is_array($j['nameservers'])) {
            foreach ($j['nameservers'] as $ns) {
                if (!empty($ns['ldhName'])) {
                    $this->nameServers[] = $ns['ldhName'];
                }
            }
        }

        // events
        $events = $j['events'] ?? [];
        $byAction = [];
        foreach ($events as $ev) {
            if (!empty($ev['eventAction']) && !empty($ev['eventDate'])) {
                $byAction[$ev['eventAction']] = $ev['eventDate'];
            }
        }

        $this->creationDateISO8601 = $byAction['registration'] ?? ($byAction['created'] ?? null);
        $this->updatedDateISO8601   = $byAction['last changed'] ?? ($byAction['last update of RDAP database'] ?? null);
        $this->expirationDateISO8601 = $byAction['expiration'] ?? null;

        // keep original formatting too
        $this->creationDate = $this->creationDateISO8601;
        $this->updatedDate  = $this->updatedDateISO8601;
        $this->expirationDate = $this->expirationDateISO8601;

        // availability
        $this->registered = $this->code === 200;

        // durations
        $now = time();
        if ($this->creationDateISO8601) {
            $c = strtotime($this->creationDateISO8601);
            if ($c) {
                $this->ageSeconds = max(0, $now - $c);
                $this->age = $this->formatDuration($this->ageSeconds);
            }
        }
        if ($this->expirationDateISO8601) {
            $e = strtotime($this->expirationDateISO8601);
            if ($e) {
                $this->remainingSeconds = max(0, $e - $now);
                $this->remaining = $this->formatDuration($this->remainingSeconds);
            }
        }

        // detect periods by status keywords
        foreach ($this->status as $st) {
            $ls = strtolower($st);
            $this->gracePeriod      = $this->gracePeriod || str_contains($ls, 'grace');
            $this->redemptionPeriod = $this->redemptionPeriod || str_contains($ls, 'redemption');
            $this->pendingDelete    = $this->pendingDelete || str_contains($ls, 'pending delete');
        }
    }

    private function formatDuration(int $seconds): string
    {
        $days = intdiv($seconds, 86400);
        $hours = intdiv($seconds % 86400, 3600);
        $mins = intdiv($seconds % 3600, 60);

        if ($days > 0) {
            return sprintf('%dd %dh', $days, $hours);
        }
        if ($hours > 0) {
            return sprintf('%dh %dm', $hours, $mins);
        }
        return sprintf('%dm', $mins);
    }

    public function getUnknown(): array
    {
        // RDAP is structured, but still expose a compatible API with WHOIS parser merge step
        return [];
    }
}
