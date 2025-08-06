# galangw/whois-tools

Reusable PHP package for WHOIS and RDAP domain lookups with strong TLD compatibility  
Includes:

- Standalone API (pure PHP)
- Laravel Service Provider + Facade (soon)

## Requirements

- PHP >= 8.1
- ext-mbstring (recommended for encoding conversion)
- Network access for WHOIS (port 43) and RDAP (HTTPS)

## Installation

Install via Composer in your project:

```bash
composer require galangw/whois-tools
```

If using Laravel, the service provider is auto-discovered. For standalone usage, just include Composer autoload and use the classes.

## Usage

### 1) Standalone PHP

```php
<?php
require 'vendor/autoload.php';

use Galangw\WhoisTools\Lookup;

$lookup = new Lookup('example.com', ['whois', 'rdap']); // choose sources: 'whois', 'rdap', or both
$parser = $lookup->parser;

echo "Domain: " . ($parser->domain ?? 'n/a') . PHP_EOL;
echo "Registered: " . ($parser->registered ? 'yes' : 'no') . PHP_EOL;
echo "Registrar: " . ($parser->registrar ?? 'n/a') . PHP_EOL;

if (!empty($lookup->whoisData)) {
    echo PHP_EOL . "=== Raw WHOIS ===" . PHP_EOL;
    echo $lookup->whoisData . PHP_EOL;
}

if (!empty($lookup->rdapData)) {
    echo PHP_EOL . "=== Raw RDAP ===" . PHP_EOL;
    echo $lookup->rdapData . PHP_EOL;
}
```

### 2) Laravel Integration

Auto-discovery registers the service provider and facade.

Config (publishable):

```bash
php artisan vendor:publish --provider="Galangw\WhoisTools\Laravel\WhoisToolsServiceProvider" --tag=config
```

Global facade example:

```php
<?php
use Galangw\WhoisTools\Laravel\Facades\WhoisTools;

// Default sources from config (whois+rdap)
$parser = WhoisTools::lookup('example.com');

// Custom sources
$parser = WhoisTools::lookup('example.com', ['sources' => ['rdap']]);
```

Config file `config/whois-tools.php` (publishable):

- timeout_whois, timeout_rdap
- default_sources: ['whois', 'rdap']
- paths override (PSL and server lists, optional)
- web whois extensions (optional, if you later add WHOISWeb support)

## License

MIT. Lihat file LICENSE (disarankan menambahkan file lisensi MIT lengkap di root paket).

## Credits

- RDAP/WHOIS data sources derived from IANA and curated extras
- https://github.com/reg233/whois-domain-lookup
- https://github.com/monovm/whois-php
