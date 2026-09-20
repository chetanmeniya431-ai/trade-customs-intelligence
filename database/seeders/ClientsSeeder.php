<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientsSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            ['name' => 'Arjun Textiles Pvt Ltd', 'contact_name' => 'Arjun Mehta', 'contact_email' => 'arjun@arjuntextiles.example', 'country' => 'India'],
            ['name' => 'Nexgen Electronics', 'contact_name' => 'Priya Rao', 'contact_email' => 'priya@nexgenelec.example', 'country' => 'India'],
            ['name' => 'Pure Harvest Foods', 'contact_name' => 'Karan Shah', 'contact_email' => 'karan@pureharvest.example', 'country' => 'India'],
            ['name' => 'Medica Devices India', 'contact_name' => 'Sunita Iyer', 'contact_email' => 'sunita@medicadevices.example', 'country' => 'India'],
            ['name' => 'Sunbeam Chemicals', 'contact_name' => 'Rohit Verma', 'contact_email' => 'rohit@sunbeamchem.example', 'country' => 'India'],
        ];

        foreach ($clients as $client) {
            Client::firstOrCreate(['name' => $client['name']], $client);
        }
    }
}
