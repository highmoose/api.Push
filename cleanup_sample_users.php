<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Current users in database:\n";
$users = DB::table('users')->select('id', 'first_name', 'last_name', 'email', 'role')->get();
foreach ($users as $user) {
    echo "ID: {$user->id}, Name: {$user->first_name} {$user->last_name}, Email: {$user->email}, Role: {$user->role}\n";
}

echo "\nRemoving sample users with @example.com emails...\n";

// Clean up any related data first (diet plans, etc.)
$sampleUserIds = DB::table('users')->where('email', 'like', '%@example.com')->pluck('id');
if ($sampleUserIds->count() > 0) {
    echo "Found " . $sampleUserIds->count() . " sample users to remove.\n";
    
    // Clean up related diet plan data
    $dietPlansDeleted = DB::table('diet_plans')->whereIn('client_id', $sampleUserIds)->delete();
    echo "Deleted {$dietPlansDeleted} diet plans linked to sample users.\n";
    
    $dietPlanItemsDeleted = DB::table('diet_plan_items')
        ->whereIn('diet_plan_id', function($query) use ($sampleUserIds) {
            $query->select('id')->from('diet_plans')->whereIn('client_id', $sampleUserIds);
        })->delete();
    echo "Deleted {$dietPlanItemsDeleted} diet plan items linked to sample users.\n";
}

// Remove users with sample email domains
$deletedCount = DB::table('users')->where('email', 'like', '%@example.com')->delete();
echo "Deleted {$deletedCount} sample users.\n";

echo "\nRemaining users:\n";
$remainingUsers = DB::table('users')->select('id', 'first_name', 'last_name', 'email', 'role')->get();
foreach ($remainingUsers as $user) {
    echo "ID: {$user->id}, Name: {$user->first_name} {$user->last_name}, Email: {$user->email}, Role: {$user->role}\n";
}

echo "\nCleanup completed!\n";
?>
