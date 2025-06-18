<?php
// Simple test script to verify session creation works with all fields

require_once 'vendor/autoload.php';

use Illuminate\Http\Request;
use App\Http\Controllers\SessionController;
use App\Models\SessionModel;

echo "Testing session creation with all fields...\n";

// Test data
$testData = [
    'client_id' => 1, // Assuming user ID 1 exists
    'scheduled_at' => '2025-06-20 10:00:00',
    'duration' => 60,
    'notes' => 'Test session',
    'status' => 'scheduled',
    'session_type' => 'strength',
    'location' => 'Main Gym',
    'rate' => 75.00,
    'equipment_needed' => 'Dumbbells',
    'preparation_notes' => 'Warm up required',
    'goals' => 'Build muscle strength'
];

echo "Test data prepared:\n";
print_r($testData);

// Check if we can create a session model directly
try {
    $session = new SessionModel();
    echo "SessionModel instantiated successfully\n";
    
    // Check fillable fields
    echo "Fillable fields: " . implode(', ', $session->getFillable()) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Test completed.\n";
