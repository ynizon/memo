<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password'=>bcrypt("admin"),
        ]);

        DB::table('categories')->insert([
            'name' => "Health",
            'icon' => 'fa-notes-medical',
            'color' => 'e10a77',
            'forall'=>1,
            'user_id' => 1
        ]);

        DB::table('categories')->insert([
            'name' => "Car",
            'icon' => 'fa-car',
            'color' => 'c79710',
            'forall'=>1,
            'user_id' => 1
        ]);

        DB::table('categories')->insert([
            'name' => "Other",
            'icon' => 'fa-list',
            'color' => 'c02ae5',
            'forall'=>1,
            'user_id' => 1
        ]);

        DB::table('categories')->insert([
            'name' => "Animal",
            'icon' => 'fa-dog',
            'color' => '3474ab',
            'forall'=>1,
            'user_id' => 1
        ]);
    }
}
