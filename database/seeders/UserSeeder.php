<?php

namespace Database\Seeders;

use App\Models\User;
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
                'email' => 'nati1@example.com',
                'personal_id' => '315552810',
            ],
            [
                'name' => 'שני פורייס',
                'email' => 'shani@example.com',
                'personal_id' => '213466154',
            ],
            [
                'name' => 'דוד כהן',
                'email' => 'david@example.com',
                'personal_id' => '123456789',
            ],
            [
                'name' => 'רות לוי',
                'email' => 'ruth@example.com',
                'personal_id' => '987654321',
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
