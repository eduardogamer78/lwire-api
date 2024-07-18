<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Seed the application's database. */
    public function run(): void
    {
        User::factory(20)->create();

        // User::factory()->create([
        //     'name' => 'Eduardo Gamer',
        //     'email' => 'eduardo@gamer.com',
        // ]);

        // User::factory()->create([
        //     'name' => 'Jess Archer',
        //     'email' => 'archer@jess.com',
        // ]);
    }
}
