<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User 1',
            'email' => 'test1@example.com',
            'password' => bcrypt(config('app.default_user_password')),
        ]);
        User::factory()->create([
            'name' => 'Test User 2',
            'email' => 'test2@example.com',
            'password' => bcrypt(config('app.default_user_password')),
        ]);
        User::factory()->create([
            'name' => 'Test User 3',
            'email' => 'test3@example.com',
            'password' => bcrypt(config('app.default_user_password')),
        ]);
    }
}
