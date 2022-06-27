<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name'              => 'Admin',
            'email'             => 'admin@delfiniti.com',
            'email_verified_at' => now(),
            'password'          => '$2y$10$35tSUN6kgVhu3arf00b0DO4nAE8PU8qerbsg5hHagB9iHNLCQ6H12',
            'remember_token'    => Str::random(10)
        ])->assignRole('Administrador');
    }
}
