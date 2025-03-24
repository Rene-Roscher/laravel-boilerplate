<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ApplicationDefaultsSeeder::class,
        ]);

        tap(User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => '123admin456',
        ]), function (User $user) {
            $user->assignRole(RoleEnum::SUPER_ADMIN->name);
        });

    }
}
