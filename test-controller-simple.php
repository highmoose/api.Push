<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing DietPlanController resolution...\n";

try {
    $controller = app()->make('App\Http\Controllers\DietPlanController');
    echo "✅ SUCCESS: DietPlanController resolved!\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

// Test class existence
echo "DietPlanController class exists: " . (class_exists('App\Http\Controllers\DietPlanController') ? 'YES' : 'NO') . "\n";
