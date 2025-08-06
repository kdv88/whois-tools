<?php

namespace Galangw\WhoisTools\Laravel;

use Galangw\WhoisTools\WhoisToolsManager;
use Illuminate\Support\ServiceProvider;

class WhoisToolsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Use application API to avoid helper warnings in static analysis.
        $configPublishPath = method_exists($this->app, 'configPath')
            ? $this->app->configPath('whois-tools.php')
            : (defined('BASE_PATH') ? BASE_PATH . '/config/whois-tools.php' : __DIR__ . '/../../../../config/whois-tools.php');

        $this->publishes([
            __DIR__ . '/../../config/whois-tools.php' => $configPublishPath,
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/whois-tools.php',
            'whois-tools'
        );

        $this->app->singleton('whois-tools', function ($app) {
            $cfg = (array) ($app['config']['whois-tools'] ?? []);
            return new WhoisToolsManager($cfg);
        });
    }
}
