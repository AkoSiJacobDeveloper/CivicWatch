<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Barangay;
use App\Models\Sitio;

class PopulateLocations extends Command
{
    protected $signature = 'locations:populate {--fresh : Delete all existing data first} {--update : Update existing records}';
    protected $description = 'Populate barangays and sitios with real data';

    public function handle()
    {
        $fresh = $this->option('fresh');
        $update = $this->option('update');

        if ($fresh) {
            if ($this->confirm('This will delete ALL existing location data. Are you sure?')) {
                Sitio::truncate();
                Barangay::truncate();
                $this->info('Existing data cleared.');
            } else {
                return;
            }
        }

        $this->info('Populating locations...');

        // Your real location data with 10 sitios each
        $locationData = [
            'BagongBanwa Island' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Banlasan' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Batasan Island' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'BilangBilangan Island' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Bosongon' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Buenos Aires' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Bunacan' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Cabulijan' => [
                'is_available' => true, // PILOT BARANGAY
                'description' => 'Pilot barangay - Currently accepting reports',
                'sitios' => [
                    'Purok 1 - Sitio Ligua',
                    'Purok 2 - Sitio Centro',
                    'Purok 3 - Sitio Red Land',
                    'Purok 4 - Sitio Sto.Nino',
                    'Purok 5 - Sitio Sambag 1',
                    'Purok 6 - Sitio Sambag 2'
                ]
            ],
            'Cahayag' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Cawayanan' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Centro' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Genonocan' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Guiwanon' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Ilijan Norte' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Ilijan Sur' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Libertad' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Macaas' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Matabao' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Mocaboc Island' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Panadtaran' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Panaytayon' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Pandan' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Pangapasan Island' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Pinayagan Sur' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Pinayagan Norte' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Pooc Occidental' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Pooc Oriental' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Potohan' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Talenceras' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Tan-awan' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Tinangnan' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Ubojan' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Ubay Island' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ],
            'Villanueva' => [
                'is_available' => false,
                'description' => 'Coming soon',
                'sitios' => [
                    'Purok 1 - Sitio 1',
                    'Purok 2 - Sitio 2',
                    'Purok 3 - Sitio 3',
                    'Purok 4 - Sitio 4',
                    'Purok 5 - Sitio 5',
                    'Purok 6 - Sitio 6',
                    'Purok 7 - Sitio 7',
                    'Purok 8 - Sitio 8',
                    'Purok 9 - Sitio 9',
                    'Purok 10 - Sitio 10'
                ]
            ]
        ];

        foreach ($locationData as $barangayName => $data) {
            $this->line("Processing barangay: {$barangayName}");
            
            if ($update) {
                $barangay = Barangay::updateOrCreate(
                    ['name' => $barangayName],
                    [
                        'is_available' => $data['is_available'],
                        'description' => $data['description']
                    ]
                );
            } else {
                $barangay = Barangay::firstOrCreate(
                    ['name' => $barangayName],
                    [
                        'is_available' => $data['is_available'],
                        'description' => $data['description']
                    ]
                );
            }

            foreach ($data['sitios'] as $sitioName) {
                $this->line("  - Processing sitio: {$sitioName}");
                
                Sitio::updateOrCreate(
                    [
                        'name' => $sitioName,
                        'barangay_id' => $barangay->id
                    ],
                    [
                        'is_available' => $data['is_available']
                    ]
                );
            }
        }

        $this->info('✅ Locations populated successfully!');
        
        $availableCount = Barangay::where('is_available', true)->count();
        $totalCount = Barangay::count();
        $sitioCount = Sitio::count();
        
        $this->info("📊 Available barangays: {$availableCount}/{$totalCount}");
        $this->info("📍 Total sitios: {$sitioCount}");
        $this->comment("🚀 Only Cabulijan is currently available for pilot testing.");
    }
}

// php artisan locations:populate - To populate without deleting existing data:
// php artisan locations:populate --fresh - To delete all existing barangays and sitios first and repopulate:
// php artisan locations:populate --update - To update existing records if they already exist:
