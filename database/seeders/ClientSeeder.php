<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Client::updateOrCreate(
            ['email' => 'parbat@gmail.com'],
            [
                'name' => 'Parbat',
                'company_name' => 'Parbat Inc.',
                'phone' => '123-456-7890',
                'address' => '123 Main St, Cityville',
            ]
        );

        Client::updateOrCreate(
            ['email' => 'bibek@gmail.com'],
            [
                'name' => 'Bibek',
                'company_name' => 'Bibek Solutions',
                'phone' => '987-654-3210',
                'address' => '456 Elm St, Townsville',
            ]
        );

        Client::updateOrCreate(
            ['email' => 'bhadrabista@gmail.com'],
            [
                'name' => 'Bhadrabista',
                'company_name' => 'Bhadrabista Co.',
                'phone' => '555-123-4567',
                'address' => '789 Oak St, Villagetown',
            ]
        );

        Client::updateOrCreate(
            ['email' => 'khatrisanjay804@gmail.com'],
            [
                'name' => 'Sanjay Khatri',
                'company_name' => 'Khatri Enterprises',
                'phone' => '321-654-0987',
                'address' => '321 Pine St, Hamletville',
            ]
        );
    }
}
