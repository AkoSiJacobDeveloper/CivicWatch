<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            [
                'name' => 'All Purok - Entire Barangay',
                'purok_number' => '0',
                'sitio_name' => 'Cabulijan'
            ],
            [
                'name' => 'Purok 1 - Sitio Ligua',
                'purok_number' => '1',
                'sitio_name' => 'Sitio Ligua'
            ],
            [
                'name' => 'Purok 2 - Sitio Centro',
                'purok_number' => '2', 
                'sitio_name' => 'Sitio Centro'
            ],
            [
                'name' => 'Purok 3 - Sitio Red Land',
                'purok_number' => '3',
                'sitio_name' => 'Sitio Red Land'
            ],
            [
                'name' => 'Purok 4 - Sitio Sto.Nino',
                'purok_number' => '4',
                'sitio_name' => 'Sitio Sto.Nino'
            ],
            [
                'name' => 'Purok 5 - Sitio Sambag 1',
                'purok_number' => '5',
                'sitio_name' => 'Sitio Sambag 1'
            ],
            [
                'name' => 'Purok 6 - Sitio Sambag 2',
                'purok_number' => '6',
                'sitio_name' => 'Sitio Sambag 2'
            ],
        ];

        foreach ($locations as $location) {
            Location::updateOrCreate(
                ['purok_number' => $location['purok_number']],
                $location 
            );
        }
    }
}
