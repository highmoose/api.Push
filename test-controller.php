<?php

// Simple test script to verify the diet plan generate endpoint
require_once 'vendor/autoload.php';

echo "Testing DietPlanController generate method...\n";

// Test if the controller class exists
if (class_exists('App\Http\Controllers\DietPlanController')) {
    echo "✓ DietPlanController class exists\n";
    
    $controller = new App\Http\Controllers\DietPlanController();
    
    if (method_exists($controller, 'generate')) {
        echo "✓ generate method exists\n";
    } else {
        echo "✗ generate method does not exist\n";
    }
    
    if (method_exists($controller, 'index')) {
        echo "✓ index method exists\n";
    } else {
        echo "✗ index method does not exist\n";
    }
    
    if (method_exists($controller, 'show')) {
        echo "✓ show method exists\n";
    } else {
        echo "✗ show method does not exist\n";
    }
    
    if (method_exists($controller, 'update')) {
        echo "✓ update method exists\n";
    } else {
        echo "✗ update method does not exist\n";
    }
    
    if (method_exists($controller, 'destroy')) {
        echo "✓ destroy method exists\n";
    } else {
        echo "✗ destroy method does not exist\n";
    }
    
} else {
    echo "✗ DietPlanController class does not exist\n";
}

echo "\nTest completed!\n";
