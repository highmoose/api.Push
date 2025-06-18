<?php

// Simple test without full Laravel bootstrap
require 'vendor/autoload.php';

echo "Testing class loading...\n";

// Try to load classes directly
try {
    $openai = new App\Services\OpenAIService();
    echo "✅ OpenAIService instantiated directly!\n";
} catch (Exception $e) {
    echo "❌ OpenAIService error: " . $e->getMessage() . "\n";
}

try {
    // This will fail because it needs OpenAIService dependency
    $controller = new App\Http\Controllers\DietPlanController(new App\Services\OpenAIService());
    echo "✅ DietPlanController instantiated directly!\n";
} catch (Exception $e) {
    echo "❌ DietPlanController error: " . $e->getMessage() . "\n";
}

// Test class existence
echo "OpenAIService class exists: " . (class_exists('App\Services\OpenAIService') ? 'YES' : 'NO') . "\n";
echo "DietPlanController class exists: " . (class_exists('App\Http\Controllers\DietPlanController') ? 'YES' : 'NO') . "\n";
