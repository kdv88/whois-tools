<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Timeout Settings
    |--------------------------------------------------------------------------
    |
    | Define the timeout in seconds for WHOIS and RDAP queries.
    |
    */
    'timeout_whois' => 10,
    'timeout_rdap' => 10,

    /*
    |--------------------------------------------------------------------------
    | Default Data Sources
    |--------------------------------------------------------------------------
    |
    | Specify the default data sources to use for lookups.
    | Can be ['whois'], ['rdap'], or ['whois', 'rdap'].
    |
    */
    'default_sources' => ['whois', 'rdap'],

    /*
    |--------------------------------------------------------------------------
    | Data Paths
    |--------------------------------------------------------------------------
    |
    | Allows overriding the default paths for data files. By default,
    | it uses the files within the package. You can publish and
    | customize these files if needed.
    |
    */
    'paths' => [
        'public_suffix_list' => null, // realpath(__DIR__ . '/../storage/data/public-suffix-list.dat'),
        'rdap_servers_iana' => null,
        'rdap_servers_extra' => null,
        'whois_servers_iana' => null,
        'whois_servers_extra' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Web WHOIS Extensions
    |--------------------------------------------------------------------------
    |
    | A list of TLDs that should be queried via a web-based WHOIS
    | endpoint instead of a direct socket connection.
    |
    */
    'enable_web_whois_extensions' => [
        // 'sg', 'com.sg', ...
    ],
];
