<?php

namespace Galangw\WhoisTools\Parsers;

class ParserFactory
{
    public static function create(string $extension, string $data)
    {
        $class = __NAMESPACE__ . '\\Parser' . strtoupper(str_replace(['.', '-'], '', $extension));
        if (class_exists($class)) {
            return new $class($data);
        }

        // Fallback to generic parser if exists; otherwise return base Parser.
        $generic = __NAMESPACE__ . '\\Parser';
        if (class_exists($generic)) {
            return new $generic($data);
        }

        // Minimal placeholder to avoid fatal if none present in initial migration step.
        return new class($data) {
            public string $domain = '';
            public bool $registered = false;
            public function __construct(public string $raw) {}
            public function getUnknown(): array
            {
                return [];
            }
        };
    }
}
