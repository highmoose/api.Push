<?php
// Simple script to insert sample users

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

require_once 'vendor/autoload.php';

// Set up minimal Laravel environment
$app = new Illuminate\Foundation\Application(
    realpath(__DIR__)
);

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

// Bootstrap
$app->bootstrapWith([
    \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
    \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
    \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
    \Illuminate\Foundation\Bootstrap\RegisterFacades::class,
    \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
    \Illuminate\Foundation\Bootstrap\BootProviders::class,
]);

try {
    // Check current user count
    $currentCount = DB::table('users')->count();
    echo "Current users in database: $currentCount\n";

    if ($currentCount == 0) {
        echo "Inserting sample users...\n";
        
        DB::table('users')->insert([
            [
                'first_name' => 'John',
                'last_name' => 'Trainer',
                'email' => 'trainer@example.com',
                'phone' => '+1234567890',
                'location' => 'New York',
                'gym' => 'FitGym',
                'date_of_birth' => '1985-06-15',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'role' => 'trainer',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Client',
                'email' => 'client1@example.com',
                'phone' => '+1234567891',
                'location' => 'Los Angeles',
                'gym' => 'FitGym',
                'date_of_birth' => '1990-03-20',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'role' => 'client',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'first_name' => 'Mike',
                'last_name' => 'Johnson',
                'email' => 'client2@example.com',
                'phone' => '+1234567892',
                'location' => 'Chicago',
                'gym' => 'PowerGym',
                'date_of_birth' => '1988-11-10',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'role' => 'client',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
        
        echo "Sample users inserted successfully!\n";
    }

    // Show users
    $users = DB::table('users')->select('id', 'first_name', 'last_name', 'role', 'date_of_birth')->get();
    echo "\nUsers in database:\n";
    foreach ($users as $user) {
        $age = $user->date_of_birth ? Carbon::parse($user->date_of_birth)->age : 'N/A';
        echo "ID: {$user->id}, Name: {$user->first_name} {$user->last_name}, Role: {$user->role}, Age: $age\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
