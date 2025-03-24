<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ApplicationDefaultsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
         * Generate CRUD permissions of Model
         */
        foreach (config('xepare.models') as $model) {
            foreach (['create', 'read', 'update', 'delete'] as $crud)
                Permission::create([
                    'name' => sprintf('%s.%s', Str::snake(class_basename($model), '.'), $crud)
                ]);
        }

        /**
         * Register custom permissions
         */
        foreach (config('xepare.permissions') as $value) {
            Permission::create([
                'name' => $value
            ]);
        }

        /**
         * Register roles & attach permissions
         */
        foreach (config('xepare.roles') as $name => $models) {
            if (is_null($name)) continue;

            /** @var Role $role */
            $role = Role::create(['name' => $name]);
            // Assign permissions to role
            collect($models)->map(fn($permissions, $model) => collect($permissions)->values()->map(
                fn($type) => sprintf('%s.%s', Str::snake(class_basename($model), '.'), $type)
            ))->each(fn($data) => $data->values()->each(fn($permission) => $role->givePermissionTo($permission)));
        }
    }
}
