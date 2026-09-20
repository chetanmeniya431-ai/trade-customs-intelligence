<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $clientCompany = Client::where('name', 'Arjun Textiles Pvt Ltd')->first();

        $users = [
            ['name' => 'Meridian Admin', 'email' => 'admin@tradecustoms.local', 'role' => 'Customs Broker'],
            ['name' => 'Coordinator User', 'email' => 'coordinator@tradecustoms.local', 'role' => 'Import/Export Coordinator'],
            ['name' => 'Compliance User', 'email' => 'compliance@tradecustoms.local', 'role' => 'Compliance Manager'],
            ['name' => 'Finance User', 'email' => 'finance@tradecustoms.local', 'role' => 'Finance'],
            ['name' => 'Arjun Textiles Client', 'email' => 'client@tradecustoms.local', 'role' => 'Client', 'client_id' => $clientCompany?->id],
        ];

        foreach ($users as $u) {
            $user = User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'client_id' => $u['client_id'] ?? null,
                ]
            );

            if (! $user->hasRole($u['role'])) {
                $user->assignRole($u['role']);
            }
        }
    }
}
