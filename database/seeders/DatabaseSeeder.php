<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the demo users for the security lab.
     *
     * All passwords are "password". User id 1 is the attacker's own account;
     * the victim accounts (id 2, 3) are what the IDOR demo targets.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Attacker', 'email' => 'attacker@example.com'],
            ['name' => 'Victim One', 'email' => 'victim1@example.com'],
            ['name' => 'Victim Two', 'email' => 'victim2@example.com'],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => 'password',
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
