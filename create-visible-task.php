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

// Create a task for TODAY at a specific time to test visibility
$today = date('Y-m-d');
$testTime = $today . ' 12:30:00'; // 12:30 PM today

$task = TaskModel::create([
    'user_id' => $user->id,
    'title' => 'CALENDAR TEST TASK',
    'description' => 'This task should be visible at 12:30 PM today',
    'due_date' => $testTime,
    'priority' => 'high',
    'category' => 'general',
    'status' => 'pending',
    'reminder' => ['enabled' => true, 'time' => '15min']
]);

echo "Created visible test task: " . $task->title . "\n";
echo "Due date: " . $task->due_date . "\n";
echo "This should appear in the calendar at 12:30 PM on " . date('M d, Y') . "\n";

// Count all tasks for user
$totalTasks = TaskModel::where('user_id', $user->id)->count();
echo "Total tasks for user: " . $totalTasks . "\n";

echo "\nTest completed successfully!\n";
