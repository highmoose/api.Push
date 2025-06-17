<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\UserModel;
use App\Models\TaskModel;

// Get the test user
$user = UserModel::where('email', 'trainer@test.com')->first();

if (!$user) {
    echo "Test user not found!\n";
    exit(1);
}

echo "Found user: " . $user->first_name . " " . $user->last_name . "\n";

// Generate a token
$token = $user->createToken('test-token')->plainTextToken;
echo "Bearer Token: " . $token . "\n";

// Test task creation
$tasks = [    [
        'title' => 'Morning Equipment Check',
        'description' => 'Check all gym equipment before opening',
        'due_date' => '2025-06-17 08:30:00',
        'duration' => 60,
        'priority' => 'high',
        'category' => 'equipment',
        'status' => 'pending',
        'reminder' => ['enabled' => true, 'time' => '15min']
    ],
    [
        'title' => 'Client Progress Review',
        'description' => 'Review progress reports for all clients',
        'due_date' => '2025-06-17 14:00:00',
        'duration' => 45,
        'priority' => 'medium',
        'category' => 'client-related',
        'status' => 'in-progress',
        'reminder' => ['enabled' => true, 'time' => '30min']
    ],    [
        'title' => 'Nutrition Plan Updates',
        'description' => 'Update nutrition plans for 3 clients',
        'due_date' => '2025-06-18 10:15:00',
        'duration' => 90,
        'priority' => 'medium',
        'category' => 'client-related',
        'status' => 'pending',
        'reminder' => ['enabled' => false, 'time' => null]
    ],
    [
        'title' => 'Complete Monthly Reports',
        'description' => 'Finalize all monthly training reports',
        'due_date' => '2025-06-18 16:45:00',
        'duration' => 120,
        'priority' => 'high',
        'category' => 'administrative',
        'status' => 'pending',
        'reminder' => ['enabled' => true, 'time' => '1hour']
    ],
    [
        'title' => 'Gym Cleaning Task',
        'description' => 'Deep clean the gym floor and equipment',
        'due_date' => '2025-06-19 07:00:00',
        'duration' => 30,
        'priority' => 'low',
        'category' => 'general',
        'status' => 'completed',
        'reminder' => ['enabled' => false, 'time' => null]
    ],
];

foreach ($tasks as $taskData) {
    $task = TaskModel::create(array_merge($taskData, ['user_id' => $user->id]));
    echo "Created task: " . $task->title . " (ID: " . $task->id . ") - Due: " . $task->due_date . "\n";
}

// Count tasks
$taskCount = TaskModel::where('user_id', $user->id)->count();
echo "Total tasks for user: " . $taskCount . "\n";

echo "\nTest completed successfully!\n";
