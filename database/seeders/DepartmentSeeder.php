<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['category' => 'Primary', 'name' => 'Office of the Barangay Captain'],
            ['category' => 'Primary', 'name' => "Barangay Secretary's Office"],
            ['category' => 'Primary', 'name' => "Barangay Treasurer's Office"],
            ['category' => 'Primary', 'name' => 'Health Center'],
            ['category' => 'Primary', 'name' => 'Social Welfare & Development'],
            ['category' => 'Primary', 'name' => 'Peace & Order Committee'],
            ['category' => 'Primary', 'name' => 'Disaster Risk Reduction Management'],
            ['category' => 'Primary', 'name' => 'Sangguniang Kabataan (SK) Office'],

            ['category' => 'Secondary', 'name' => 'Education & Culture Committee'],
            ['category' => 'Secondary', 'name' => 'Infrastructure & Public Works'],
            ['category' => 'Secondary', 'name' => 'Environmental Protection Committee'],
            ['category' => 'Secondary', 'name' => 'Senior Citizens Affairs'],
            ['category' => 'Secondary', 'name' => 'PWD Affairs Office'],
            ['category' => 'Secondary', 'name' => 'Business Permit & Licensing'],
            ['category' => 'Secondary', 'name' => 'Barangay Tanod Office'],
            ['category' => 'Secondary', 'name' => 'General Services'],

            ['category' => 'General', 'name' => 'Barangay Hall - Main Office'],
            ['category' => 'General', 'name' => 'Public Assistance Desk'],
            ['category' => 'General', 'name' => 'Other Department'],
        ];

        foreach ($departments as $department) {
            DB::table('departments')->updateOrInsert(
                ['name' => $department['name']], 
                ['category' => $department['category']]
            );
        }
    }
}
