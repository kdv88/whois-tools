# galangw/whois-tools

A reusable PHP package for WHOIS and RDAP domain lookups with strong TLD compatibility.

## Features

- **Standalone PHP API** - Works in any PHP project
- **Laravel Integration** - Service Provider + Facade included
- **Multiple Data Sources** - Support for both WHOIS and RDAP protocols
- **PSR-4 Autoloading** - Proper namespace structure
- **Comprehensive Data Parsing** - Extract registrar, dates, nameservers, and more

## Requirements

- PHP >= 8.1
- ext-mbstring (recommended for encoding conversion)
- Network access for WHOIS (port 43) and RDAP (HTTPS)

## Installation

Install via Composer:

```bash
composer require galangw/whois-tools
```

For Laravel projects, the service provider is auto-discovered. For standalone usage, just include Composer's autoloader.

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

After installation, the service provider and facade are auto-discovered.

#### Publishing Config

```bash
php artisan vendor:publish --provider="Galangw\WhoisTools\Laravel\WhoisToolsServiceProvider" --tag=config
```

#### Using the Facade

```php
<?php
use Galangw\WhoisTools\Laravel\Facades\WhoisTools;

// Using default sources from config (whois+rdap)
$parser = WhoisTools::lookup('example.com');

// Using custom sources
$parser = WhoisTools::lookup('example.com', ['sources' => ['rdap']]);

// Access parsed data
echo "Domain: " . ($parser->domain ?? 'n/a') . PHP_EOL;
echo "Registered: " . ($parser->registered ? 'yes' : 'no') . PHP_EOL;
echo "Registrar: " . ($parser->registrar ?? 'n/a') . PHP_EOL;
```

#### Configuration Options

The published config file `config/whois-tools.php` includes:

- `timeout_whois` - Timeout for WHOIS queries (seconds)
- `timeout_rdap` - Timeout for RDAP queries (seconds) 
- `default_sources` - Default data sources `['whois', 'rdap']`
- `paths` - Override paths for PSL and server lists (optional)

## API Reference

### Lookup Class

```php
$lookup = new Lookup(string $domain, array $sources = ['whois', 'rdap']);

// Properties
$lookup->parser;      // Parsed domain data object
$lookup->whoisData;   // Raw WHOIS response string
$lookup->rdapData;    // Raw RDAP response JSON string
```

### Parser Object Properties

- `domain` - Domain name
- `registered` - Boolean registration status
- `registrar` - Registrar name
- `registrarURL` - Registrar URL
- `creationDate` - Creation date
- `expirationDate` - Expiration date
- `updatedDate` - Last updated date
- `availableDate` - Available date (if applicable)
- `status` - Array of domain status codes
- `nameServers` - Array of name servers
- `age` - Domain age (if calculable)
- `remaining` - Time remaining until expiration (if calculable)

## Version History

### v1.1.0
- **Fixed PSR-4 autoloading structure** - Moved classes from `src/Galangw/WhoisTools/` to `src/` for proper namespace mapping
- Improved documentation and examples
- Cleaned up unused files

### v1.0.x
- Initial release with WHOIS and RDAP support
- Laravel integration with auto-discovery

## License

MIT License. See the LICENSE file for details.

## Credits

- RDAP/WHOIS data sources derived from IANA and curated extras
- Inspired by [reg233/whois-domain-lookup](https://github.com/reg233/whois-domain-lookup)
- Inspired by [monovm/whois-php](https://github.com/monovm/whois-php)
