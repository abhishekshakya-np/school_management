<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

         \App\Models\User::factory()->create([
             'name' => 'xabickx botter',
             'email' => 'xabickx5@gmail.com',
             'password' => bcrypt('Password'),
         ]);

         Student::factory(10)
         ->has(Guardian::factory()->count(3))
        ->create();

         $this->call(StandardSeeder::class);
    }
}
