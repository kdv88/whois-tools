<?php

namespace Galangw\WhoisTools\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Galangw\WhoisTools\Parsers\Parser lookup(string $domain, array $options = [])
 *
 * @see \Galangw\WhoisTools\WhoisToolsManager
 */
class WhoisTools extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'whois-tools';
    }
}
