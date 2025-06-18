<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'first_name' => 'John',
                'last_name' => 'Trainer',
                'email' => 'trainer@example.com',
                'phone' => '+1234567890',
                'location' => 'New York',
                'gym' => 'FitGym',
                'date_of_birth' => '1985-06-15',
                'password' => Hash::make('password123'),
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
                'password' => Hash::make('password123'),
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
                'password' => Hash::make('password123'),
                'role' => 'client',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Williams',
                'email' => 'client3@example.com',
                'phone' => '+1234567893',
                'location' => 'Miami',
                'gym' => 'HealthClub',
                'date_of_birth' => '1995-07-25',
                'password' => Hash::make('password123'),
                'role' => 'client',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
