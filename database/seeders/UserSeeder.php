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
                'role' => 'admin',
            ],
            [
                'name' => 'מוטי פקטור',
                'email' => 'moti@gmail.com',
                'personal_id' => '213466154',
                'phone' => '0502345678',
                'password' => Hash::make('123456'),
                'role' => 'moderator',
            ],
            [
                'name' => 'מנדי בויגל',
                'email' => 'mebdy@gmail.com',
                'personal_id' => '123456789',
                'phone' => '0503456789',
                'password' => Hash::make('123456'),
                'role' => 'moderator',
            ],
            [
                'name' => 'חיים שפיר',
                'email' => 'haim@gmail.com',
                'personal_id' => '987654324',
                'phone' => '0528224072',
                'password' => Hash::make('123456'),
                'role' => 'user',
            ],
            [
                'name' => 'אופיר נווה',
                'email' => 'ofir@gmail.com',
                'personal_id' => '987654314',
                'phone' => '0504204491',
                'password' => Hash::make('123456'),
                'role' => 'user',
            ],
            [
                'name' => 'מתן דוגוט',
                'email' => 'matan@gmail.com',
                'personal_id' => '9876543412',
                'phone' => '0532154478',
                'password' => Hash::make('123456'),
                'role' => 'user',
            ],
        ];

          foreach ($users as $userData) {

            $role = $userData['role'];
            unset($userData['role']);
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            // assign role
            $user->syncRoles([$role]);
        }
    }
}
