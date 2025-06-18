<?php

require 'vendor/autoload.php';

echo "Base directory check:\n";
$vendorDir = dirname(dirname(__FILE__) . '/vendor');
$baseDir = dirname($vendorDir . '/vendor');
echo "VendorDir: $vendorDir\n";  
echo "BaseDir: $baseDir\n";
echo "App path should be: $baseDir/app\n";
echo "App directory exists: " . (is_dir($baseDir . '/app') ? 'YES' : 'NO') . "\n";
echo "Services directory exists: " . (is_dir($baseDir . '/app/Services') ? 'YES' : 'NO') . "\n";
echo "OpenAIService.php exists: " . (file_exists($baseDir . '/app/Services/OpenAIService.php') ? 'YES' : 'NO') . "\n";

// Check autoload info
echo "\nAutoload PSR-4 info:\n";
$psr4 = require 'vendor/composer/autoload_psr4.php';
if (isset($psr4['App\\'])) {
    echo "App namespace mapped to: " . implode(', ', $psr4['App\\']) . "\n";
} else {
    echo "App namespace NOT FOUND in PSR-4!\n";
}

// Try to manually load the class
echo "\nTrying to manually include the file:\n";
$servicePath = __DIR__ . '/app/Services/OpenAIService.php';
echo "Service path: $servicePath\n";
echo "File exists: " . (file_exists($servicePath) ? 'YES' : 'NO') . "\n";

if (file_exists($servicePath)) {
    require_once $servicePath;
    echo "File included successfully\n";
    echo "Class exists after include: " . (class_exists('App\Services\OpenAIService', false) ? 'YES' : 'NO') . "\n";
}
