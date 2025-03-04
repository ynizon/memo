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
            'premium' => true,
            'token' => 'admin',
            'admin' => true,
            'password'=>bcrypt("admin"),
        ]);

        DB::table('categories')->insert([
            'name' => "Health",
            'icon' => 'fa-notes-medical',
            'color' => '#e10a77',
            'user_id' => 1,
            'month' => 12,
            'archive' => false,
        ]);

        DB::table('categories')->insert([
            'name' => "Car",
            'icon' => 'fa-car',
            'color' => '#c79710',
            'user_id' => 1,
            'month' => 12,
            'archive' => false,
        ]);

        DB::table('categories')->insert([
            'name' => "Other",
            'icon' => 'fa-list',
            'color' => '#c02ae5',
            'user_id' => 1,
            'month' => 12,
            'archive' => false,
        ]);

        DB::table('categories')->insert([
            'name' => "Animal",
            'icon' => 'fa-dog',
            'color' => '#3474ab',
            'user_id' => 1,
            'month' => 12,
            'archive' => false,
        ]);

        DB::table('groups')->insert([
            'name' => "Famille",
            'user_id' => 1
        ]);

        DB::table('user_group')->insert([
            'user_id' => 1,
            'group_id' => 1,
        ]);

        $numDay = 1;
        for ($k = 1; $k < 10; $k++){
            if ($numDay > 28) {$numDay = 1;}
            $j = ($numDay < 10) ? '0'.$numDay : $k;
            DB::table('tasks')->insert([
                'name' => "Stuff " .$k,
                'information'=>'informations additionnelles',
                'category_id'=>rand(1,4),
                'user_id' => 1,
                'created_at' => '2024-01-'.$j,
                'price' => rand(10,350),
                'reminder' => false,
                'reminder_date' => null,
            ]);
            if ($k < 3) {
                DB::table('task_group')->insert([
                    'task_id' => $k,
                    'group_id' => 1,
                ]);
            }
            $numDay++;
        }
    }
}
