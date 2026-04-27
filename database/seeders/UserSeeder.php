<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
          $users = [
[
        'name' => 'נתנאל אפרים',
        'email' => 'nati@gmail.com',
        'personal_id' => '315552810',
        'phone' => '0501234567',
        'password' => Hash::make('123456'),
    ],
    [
        'name' => 'מוטי פקטור',
        'email' => 'moti@gmail.com',
        'personal_id' => '213466154',
        'phone' => '0502345678',
        'password' => Hash::make('123456'),
    ],
    [
        'name' => 'דוד כהן',
        'email' => 'david@example.com',
        'personal_id' => '123456789',
        'phone' => '0503456789',
        'password' => Hash::make('123456'),
    ],
    [
        'name' => 'רות לוי',
        'email' => 'ruth@example.com',
        'personal_id' => '987654321',
        'phone' => '0504567890',
        'password' => Hash::make('123456'),
    ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']], 
                $userData
            );
        }
    }
}
