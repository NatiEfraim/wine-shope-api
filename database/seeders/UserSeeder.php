<?php

namespace Database\Seeders;

use App\Enum\RoleEnum;
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
        $faker = \Faker\Factory::create();
        $users = [
            [
                'name' => 'נתנאל אפרים',
                'email' => 'nati@gmail.com',
                'personal_id' => '315552810',
                'phone' => '0501234567',
                'password' => Hash::make('123456'),
                'role' => RoleEnum::ADMIN->value,
            ],
            [
                'name' => 'מוטי פקטר',
                'email' => 'moti@gmail.com',
                'personal_id' => '213466154',
                'phone' => '0502345678',
                'password' => Hash::make('123456'),
                'role' => RoleEnum::MODERATOR->value,
            ],
            [
                'name' => 'מני בויגל',
                'email' => 'meny@gmail.com',
                'personal_id' => '123456789',
                'phone' => '0503456789',
                'password' => Hash::make('123456'),
                'role' => RoleEnum::MODERATOR->value,
            ],
            [
                'name' => 'חיים שפיר',
                'email' => 'haim@gmail.com',
                'personal_id' => '987654324',
                'phone' => '0528224072',
                'password' => Hash::make('123456'),
                'role' => RoleEnum::USER->value,
            ],
            [
                'name' => 'אופיר נווה',
                'email' => 'ofir@gmail.com',
                'personal_id' => '987654314',
                'phone' => '0504204491',
                'password' => Hash::make('123456'),
                'role' => RoleEnum::USER->value,
            ],
            [
                'name' => 'מתן דוגוט',
                'email' => 'matan@gmail.com',
                'personal_id' => '9876543412',
                'phone' => '0532154478',
                'password' => Hash::make('123456'),
                'role' => RoleEnum::USER->value,
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);
            $user = User::updateOrCreate(['email' => $userData['email']], $userData);

            // assign role
            $user->syncRoles([$role]);
        }

        //-------------Generate 50 random user

        // for ($i = 1; $i <= 50; $i++) {
        //     $role = RoleEnum::USER->value;

        //     if ($i % 15 == 0) {
        //         $role = RoleEnum::MODERATOR->value;
        //     }

        //     $users[] = [
        //         'name' => $faker->name(),
        //         'email' => $faker->unique()->safeEmail(),
        //         'personal_id' => '900000' . str_pad($i, 3, '0', STR_PAD_LEFT),
        //         'phone' => '050' . rand(1000000, 9999999),
        //         'password' => Hash::make('123456'),
        //         'role' => $role,
        //     ];
        // }

        // foreach ($users as $userData) {
        //     $role = $userData['role'];

        //     unset($userData['role']);

        //     $user = User::updateOrCreate(['email' => $userData['email']], $userData);

        //     $user->syncRoles([$role]);
        // }
    }
}
