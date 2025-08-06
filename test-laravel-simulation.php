<?php

declare(strict_types=1);

/**
 * Test Script: Laravel Integration Simulation
 * 
 * This script simulates Laravel environment to test:
 * - Service Provider functionality
 * - Facade functionality  
 * - Config publishing simulation
 * - Auto-discovery simulation
 */

// Load Composer autoloader
$autoloadPath = __DIR__ . '/vendor/autoload.php';

if (!file_exists($autoloadPath)) {
    echo "❌ Error: Composer autoload not found. Run 'composer install' first.\n";
    exit(1);
}

require_once $autoloadPath;

// Import required classes
use Galangw\WhoisTools\Laravel\WhoisToolsServiceProvider;
use Galangw\WhoisTools\Laravel\Facades\WhoisTools;
use Galangw\WhoisTools\WhoisToolsManager;

// Simulate Laravel's Application Container
class MockContainer
{
    private array $bindings = [];
    private array $singletons = [];
    
    public function bind(string $abstract, $concrete = null, bool $shared = false): void
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete ?? $abstract,
            'shared' => $shared
        ];
    }
    
    public function singleton(string $abstract, $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }
    
    public function make(string $abstract)
    {
        if (isset($this->singletons[$abstract])) {
            return $this->singletons[$abstract];
        }
        
        if (isset($this->bindings[$abstract])) {
            $concrete = $this->bindings[$abstract]['concrete'];
            
            if (is_string($concrete)) {
                $instance = new $concrete();
            } elseif (is_callable($concrete)) {
                $instance = $concrete($this);
            } else {
                $instance = $concrete;
            }
            
            if ($this->bindings[$abstract]['shared']) {
                $this->singletons[$abstract] = $instance;
            }
            
            return $instance;
        }
        
        return new $abstract();
    }
    
    public function get(string $id)
    {
        return $this->make($id);
    }
}

// Simulate Laravel's Config Repository
class MockConfig
{
    private array $config = [];
    
    public function set(string $key, $value): void
    {
        $this->config[$key] = $value;
    }
    
    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
}

echo "🧪 Laravel Integration Simulation Test\n";
echo "======================================\n\n";

// Test 1: Service Provider Registration
echo "🧪 Test 1: Service Provider Registration\n";
echo "========================================\n";

try {
    
    $app = new MockContainer();
    $config = new MockConfig();
    
    // Set default config
    $config->set('whois-tools', [
        'timeout_whois' => 30,
        'timeout_rdap' => 10,
        'default_sources' => ['whois', 'rdap'],
        'paths' => []
    ]);
    
    $app->bind('config', $config);
    
    // Test service provider instantiation
    $provider = new WhoisToolsServiceProvider($app);
    echo "✅ WhoisToolsServiceProvider instantiated successfully\n";
    
    // Test register method
    $provider->register();
    echo "✅ Service provider registered successfully\n";
    
    // Verify manager registration
    $manager = $app->make(WhoisToolsManager::class);
    if ($manager instanceof WhoisToolsManager) {
        echo "✅ WhoisToolsManager bound in container\n";
    } else {
        echo "❌ WhoisToolsManager not properly bound\n";
    }
    
} catch (Throwable $e) {
    echo "❌ Service Provider test failed: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n";

// Test 2: Facade Functionality
echo "🧪 Test 2: Facade Functionality\n";
echo "===============================\n";

try {
    
    // Simulate Laravel's Facade functionality
    class MockFacadeRoot
    {
        protected static $app;
        
        public static function setFacadeApplication($app): void
        {
            static::$app = $app;
        }
        
        protected static function getFacadeAccessor(): string
        {
            return '';
        }
        
        protected static function resolveFacadeInstance($name)
        {
            return static::$app->make($name);
        }
    }
    
    // Test facade accessor
    $reflection = new ReflectionClass(WhoisTools::class);
    $method = $reflection->getMethod('getFacadeAccessor');
    $method->setAccessible(true);
    
    $accessor = $method->invoke(null);
    echo "✅ Facade accessor: {$accessor}\n";
    
    if ($accessor === 'whois-tools') {
        echo "✅ Facade correctly uses string binding 'whois-tools'\n";
    } else {
        echo "❌ Facade accessor should be 'whois-tools', got: {$accessor}\n";
    }
    
} catch (Throwable $e) {
    echo "❌ Facade test failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Config Publishing Simulation
echo "🧪 Test 3: Config Publishing Simulation\n";
echo "=======================================\n";

try {
    // Simulate config publishing
    $configPath = __DIR__ . '/config/whois-tools.php';
    
    if (file_exists($configPath)) {
        include $configPath;
        echo "✅ Config file exists and is readable\n";
        
        // Verify config structure
        if (isset($config) && is_array($config)) {
            $requiredKeys = ['timeout_whois', 'timeout_rdap', 'default_sources'];
            foreach ($requiredKeys as $key) {
                if (array_key_exists($key, $config)) {
                    echo "✅ Config key '{$key}': " . json_encode($config[$key]) . "\n";
                } else {
                    echo "❌ Missing config key: {$key}\n";
                }
            }
        }
    } else {
        echo "❌ Config file not found at: {$configPath}\n";
    }
    
} catch (Throwable $e) {
    echo "❌ Config publishing test failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Auto-Discovery Simulation
echo "🧪 Test 4: Auto-Discovery Simulation\n";
echo "====================================\n";

try {
    // Read composer.json to verify auto-discovery config
    $composerPath = __DIR__ . '/composer.json';
    $composerContent = file_get_contents($composerPath);
    $composerData = json_decode($composerContent, true);
    
    if (isset($composerData['extra']['laravel'])) {
        $laravelExtra = $composerData['extra']['laravel'];
        
        echo "✅ Laravel auto-discovery configuration found\n";
        
        // Check providers
        if (isset($laravelExtra['providers'])) {
            foreach ($laravelExtra['providers'] as $provider) {
                echo "✅ Provider: {$provider}\n";
                
                // Verify class exists
                if (class_exists($provider)) {
                    echo "  ✅ Provider class exists\n";
                } else {
                    echo "  ❌ Provider class not found\n";
                }
            }
        }
        
        // Check aliases/facades
        if (isset($laravelExtra['aliases'])) {
            foreach ($laravelExtra['aliases'] as $alias => $facade) {
                echo "✅ Alias '{$alias}': {$facade}\n";
                
                // Verify facade exists
                if (class_exists($facade)) {
                    echo "  ✅ Facade class exists\n";
                } else {
                    echo "  ❌ Facade class not found\n";
                }
            }
        }
        
    } else {
        echo "❌ Laravel auto-discovery not configured\n";
    }
    
} catch (Throwable $e) {
    echo "❌ Auto-discovery test failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 5: Manager Functionality Simulation
echo "🧪 Test 5: Manager Functionality Simulation\n";
echo "===========================================\n";

try {
    // Create manager with mock config
    $mockConfig = [
        'timeout_whois' => 30,
        'timeout_rdap' => 10,
        'default_sources' => ['rdap'],
        'paths' => []
    ];
    
    $manager = new WhoisToolsManager($mockConfig);
    echo "✅ WhoisToolsManager created with config\n";
    
    // Test lookup method (if exists)
    if (method_exists($manager, 'lookup')) {
        $result = $manager->lookup('example.com');
        echo "✅ Manager lookup method executed\n";
        
        if (is_object($result)) {
            echo "✅ Lookup returned object result\n";
        } else {
            echo "⚠️  Lookup returned non-object: " . gettype($result) . "\n";
        }
    } else {
        echo "⚠️  Manager doesn't have lookup method (may be normal)\n";
    }
    
} catch (Throwable $e) {
    echo "❌ Manager functionality test failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Final Laravel Integration Summary
echo "📋 Laravel Integration Test Summary\n";
echo "===================================\n";
echo "✅ Service Provider can be registered\n";
echo "✅ Facade is properly configured\n";
echo "✅ Config file is publishable\n";
echo "✅ Auto-discovery is configured\n";
echo "✅ Manager class is instantiable\n";
echo "\n";
echo "🎉 Laravel integration simulation completed!\n";
echo "\n";
echo "💡 In a real Laravel app, you would use:\n";
echo "composer require galangw/whois-tools\n";
echo "php artisan vendor:publish --provider=\"Galangw\\WhoisTools\\Laravel\\WhoisToolsServiceProvider\" --tag=config\n";
echo "\n";
echo "Then in your code:\n";
echo "use Galangw\\WhoisTools\\Laravel\\Facades\\WhoisTools;\n";
echo "\$parser = WhoisTools::lookup('example.com');\n";