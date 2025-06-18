<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->boot();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Check users in database
$usersCount = DB::table('users')->count();
echo "Total users in database: $usersCount\n";

if ($usersCount > 0) {
    $users = DB::table('users')->select('id', 'first_name', 'last_name', 'role', 'date_of_birth')->take(5)->get();
    echo "\nFirst 5 users:\n";
    foreach ($users as $user) {
        echo "ID: {$user->id}, Name: {$user->first_name} {$user->last_name}, Role: {$user->role}";        if ($user->date_of_birth) {
            $age = Carbon::parse($user->date_of_birth)->age;
            echo ", Age: $age";
        }
        echo "\n";
    }
} else {
    echo "No users found. You may need to run seeders or add users manually.\n";
}
?>
