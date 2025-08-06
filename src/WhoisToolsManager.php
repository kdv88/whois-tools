<?php

namespace Galangw\WhoisTools;

class WhoisToolsManager
{
    public function __construct(
        protected array $config = []
    ) {}

    /**
     * Perform a domain lookup using configured/default sources.
     *
     * @param string $domain
     * @param array{sources?:array<string>} $options
     * @return mixed Parser instance (merged WHOIS/RDAP parser shape)
     */
    public function lookup(string $domain, array $options = [])
    {
        $sources = $options['sources']
            ?? ($this->config['default_sources'] ?? ['whois', 'rdap']);

        $lookup = new Lookup($domain, $sources);
        return $lookup->parser;
    }
}
