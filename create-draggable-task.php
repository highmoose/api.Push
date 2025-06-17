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

// Create a task for TODAY with a specific time to test drag/resize
$today = date('Y-m-d');
$testTime = $today . ' 15:45:00'; // 3:45 PM today

$task = TaskModel::create([
    'user_id' => $user->id,
    'title' => 'DRAGGABLE TEST TASK',
    'description' => 'Try dragging and resizing this task!',
    'due_date' => $testTime,
    'priority' => 'high',
    'category' => 'general',
    'status' => 'pending',
    'reminder' => ['enabled' => true, 'time' => '30min']
]);

echo "Created draggable test task: " . $task->title . "\n";
echo "Due date: " . $task->due_date . "\n";
echo "This should appear at 3:45 PM today and be draggable/resizable!\n";

// Count all tasks for user
$totalTasks = TaskModel::where('user_id', $user->id)->count();
echo "Total tasks for user: " . $totalTasks . "\n";

echo "\nTest completed successfully!\n";
echo "Instructions:\n";
echo "1. Open the planner calendar\n";
echo "2. Find the red task at 3:45 PM today\n";
echo "3. Try clicking and dragging it to move it\n";
echo "4. Try clicking the top/bottom edges to resize it\n";
echo "5. The task should behave exactly like sessions!\n";
