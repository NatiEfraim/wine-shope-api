<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Role::create(['name' => 'admin','guard_name'=>'passport']);
        Role::create(['name' => 'moderator','guard_name' =>'passport']);
        Role::create(['name' => 'user','guard_name'=>'passport']);
    }
}
