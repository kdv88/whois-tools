<?php

declare(strict_types=1);

/**
 * Test Runner: Complete Package Validation
 * 
 * Runs all tests to ensure the package follows best practices:
 * 1. PSR-4 autoloading works correctly
 * 2. No manual require_once needed
 * 3. Native PHP usage works
 * 4. Laravel integration works
 */

echo "🚀 galangw/whois-tools - Complete Test Suite\n";
echo "============================================\n";
echo "Version: 1.1.0\n";
echo "Testing PSR-4 autoloading structure fixes\n\n";

// Check if we're in the correct directory
$expectedFiles = ['composer.json', 'src/Lookup.php', 'src/Laravel/WhoisToolsServiceProvider.php'];
foreach ($expectedFiles as $file) {
    if (!file_exists(__DIR__ . '/' . $file)) {
        echo "❌ Error: Expected file not found: {$file}\n";
        echo "Please run this script from the whois-tools package root directory.\n";
        exit(1);
    }
}

// Check if composer install has been run
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "❌ Error: Composer dependencies not installed.\n";
    echo "Please run 'composer install' first.\n";
    exit(1);
}

echo "✅ Pre-flight checks passed\n\n";

// Test 1: Native PHP Usage Test
echo "📦 Running Test 1: Native PHP Usage\n";
echo "===================================\n";

$nativeTestOutput = [];
$nativeTestReturn = 0;
exec('php test-native-usage.php 2>&1', $nativeTestOutput, $nativeTestReturn);

if ($nativeTestReturn === 0) {
    echo "✅ Native PHP test passed\n";
    
    // Show key results
    foreach ($nativeTestOutput as $line) {
        if (strpos($line, '✅') === 0 || strpos($line, '🎉') === 0) {
            echo "  {$line}\n";
        }
    }
} else {
    echo "❌ Native PHP test failed\n";
    foreach ($nativeTestOutput as $line) {
        echo "  {$line}\n";
    }
}

echo "\n";

// Test 2: Laravel Integration Test
echo "🔧 Running Test 2: Laravel Integration\n";
echo "======================================\n";

$laravelTestOutput = [];
$laravelTestReturn = 0;
exec('php test-laravel-simulation.php 2>&1', $laravelTestOutput, $laravelTestReturn);

if ($laravelTestReturn === 0) {
    echo "✅ Laravel integration test passed\n";
    
    // Show key results
    foreach ($laravelTestOutput as $line) {
        if (strpos($line, '✅') === 0 || strpos($line, '🎉') === 0) {
            echo "  {$line}\n";
        }
    }
} else {
    echo "❌ Laravel integration test failed\n";
    foreach ($laravelTestOutput as $line) {
        echo "  {$line}\n";
    }
}

echo "\n";

// Test 3: PSR-4 Structure Validation
echo "🏗️  Running Test 3: PSR-4 Structure Validation\n";
echo "===============================================\n";

// Load the autoloader for this validation
require_once __DIR__ . '/vendor/autoload.php';

$structureValid = true;

// Check that files are in correct PSR-4 locations
$expectedPsr4Structure = [
    'src/Lookup.php' => 'Galangw\WhoisTools\Lookup',
    'src/WHOIS.php' => 'Galangw\WhoisTools\WHOIS', 
    'src/RDAP.php' => 'Galangw\WhoisTools\RDAP',
    'src/WhoisToolsManager.php' => 'Galangw\WhoisTools\WhoisToolsManager',
    'src/Parsers/Parser.php' => 'Galangw\WhoisTools\Parsers\Parser',
    'src/Parsers/ParserFactory.php' => 'Galangw\WhoisTools\Parsers\ParserFactory',
    'src/Parsers/ParserRDAP.php' => 'Galangw\WhoisTools\Parsers\ParserRDAP',
    'src/Laravel/WhoisToolsServiceProvider.php' => 'Galangw\WhoisTools\Laravel\WhoisToolsServiceProvider',
    'src/Laravel/Facades/WhoisTools.php' => 'Galangw\WhoisTools\Laravel\Facades\WhoisTools'
];

foreach ($expectedPsr4Structure as $file => $expectedClass) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "✅ {$file} - exists\n";
        
        // Verify class can be loaded
        if (class_exists($expectedClass)) {
            echo "  ✅ {$expectedClass} - autoloadable\n";
        } else {
            echo "  ❌ {$expectedClass} - not autoloadable\n";
            $structureValid = false;
        }
    } else {
        echo "❌ {$file} - missing\n";
        $structureValid = false;
    }
}

// Check that old nested structure is gone
$oldPaths = [
    'src/Galangw/',
    'src/Galangw/WhoisTools/'
];

foreach ($oldPaths as $path) {
    if (is_dir(__DIR__ . '/' . $path)) {
        echo "❌ Old nested directory still exists: {$path}\n";
        $structureValid = false;
    } else {
        echo "✅ Old nested directory removed: {$path}\n";
    }
}

if ($structureValid) {
    echo "✅ PSR-4 structure validation passed\n";
} else {
    echo "❌ PSR-4 structure validation failed\n";
}

echo "\n";

// Test 4: Composer.json Validation
echo "📋 Running Test 4: Composer.json Validation\n";
echo "============================================\n";

$composerContent = file_get_contents(__DIR__ . '/composer.json');
$composerData = json_decode($composerContent, true);

if ($composerData === null) {
    echo "❌ Invalid composer.json file\n";
} else {
    echo "✅ composer.json is valid JSON\n";
    
    // Check PSR-4 autoload configuration
    if (isset($composerData['autoload']['psr-4']['Galangw\\WhoisTools\\']) && 
        $composerData['autoload']['psr-4']['Galangw\\WhoisTools\\'] === 'src/') {
        echo "✅ PSR-4 autoload correctly configured: Galangw\\WhoisTools\\ => src/\n";
    } else {
        echo "❌ PSR-4 autoload misconfigured\n";
    }
    
    // Check version
    if (isset($composerData['version'])) {
        echo "✅ Version specified: {$composerData['version']}\n";
    } else {
        echo "⚠️  No version specified in composer.json\n";
    }
    
    // Check Laravel auto-discovery
    if (isset($composerData['extra']['laravel'])) {
        echo "✅ Laravel auto-discovery configured\n";
    } else {
        echo "❌ Laravel auto-discovery not configured\n";
    }
}

echo "\n";

// Final Summary
echo "📊 Final Test Results Summary\n";
echo "=============================\n";

$allTestsPassed = ($nativeTestReturn === 0) && ($laravelTestReturn === 0) && $structureValid;

if ($allTestsPassed) {
    echo "🎉 ALL TESTS PASSED! 🎉\n";
    echo "\n";
    echo "✅ PSR-4 autoloading structure is correct\n";
    echo "✅ No manual require_once statements needed\n";  
    echo "✅ Package works in native PHP projects\n";
    echo "✅ Laravel integration works correctly\n";
    echo "✅ Best practices are followed\n";
    echo "\n";
    echo "🚀 Package is ready for production use!\n";
    echo "\n";
    echo "📦 Installation instructions:\n";
    echo "composer require galangw/whois-tools\n";
    echo "\n";
    echo "💡 Usage examples:\n";
    echo "Native PHP:\n";
    echo "  use Galangw\\WhoisTools\\Lookup;\n";
    echo "  \$lookup = new Lookup('example.com');\n";
    echo "\n";
    echo "Laravel:\n";
    echo "  use Galangw\\WhoisTools\\Laravel\\Facades\\WhoisTools;\n";
    echo "  \$parser = WhoisTools::lookup('example.com');\n";
    
} else {
    echo "❌ SOME TESTS FAILED\n";
    echo "\n";
    echo "Please review the test output above and fix any issues.\n";
    
    if ($nativeTestReturn !== 0) {
        echo "- Native PHP usage test failed\n";
    }
    if ($laravelTestReturn !== 0) {
        echo "- Laravel integration test failed\n";
    }
    if (!$structureValid) {
        echo "- PSR-4 structure validation failed\n";
    }
}

echo "\n";
echo "Test completed at: " . date('Y-m-d H:i:s') . "\n";