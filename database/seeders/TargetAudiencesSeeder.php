<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class TargetAudiencesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $audiences = [
            'All Residents',
            'Senior Citizens',
            'Persons with Disabilities (PWD)',
            'Youth/Students',
            'Women',
            'Solo Parents',
            'Business Owners',
            'Job Seekers',
            'Indigenous People',
            'Farmers/Fisherfolk',
            'Urban Poor',
            'Homeowners',
            'Renters/Tenants',
            'Market Vendors',
            'Overseas Workers (OFWs)',
            'Out-of-School Youth'
        ];

        foreach ($audiences as $audience) {
            DB::table('audiences')->updateOrInsert(
                ['name' => $audience],
                []
            );
        }
    }
}
