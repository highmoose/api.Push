<?php

require 'vendor/autoload.php';

// Bootstrap Laravel
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing class existence:\n";
echo "OpenAIService exists: " . (class_exists('App\Services\OpenAIService') ? 'YES' : 'NO') . "\n";
echo "DietPlanController exists: " . (class_exists('App\Http\Controllers\DietPlanController') ? 'YES' : 'NO') . "\n";

// Try to resolve OpenAIService through Laravel container
try {
    $openai = app()->make('App\Services\OpenAIService');
    echo "OpenAIService can be resolved: YES\n";
} catch (Exception $e) {
    echo "OpenAIService resolution error: " . $e->getMessage() . "\n";
}

// Try to resolve DietPlanController through Laravel container
try {
    $controller = app()->make('App\Http\Controllers\DietPlanController');
    echo "DietPlanController can be resolved: YES\n";
} catch (Exception $e) {
    echo "DietPlanController resolution error: " . $e->getMessage() . "\n";
}
