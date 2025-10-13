<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documents = [
            // Common
            ['document_type' => 'Common', 'name' => 'Barangay ID'],
            ['document_type' => 'Common', 'name' => 'Birth Certificate (PSA)'],
            ['document_type' => 'Common', 'name' => 'Proof of Residence'],
            ['document_type' => 'Common', 'name' => 'Certificate of Indigency'],
            ['document_type' => 'Common', 'name' => '2x2 ID Pictures'],
            ['document_type' => 'Common', 'name' => 'Proof of Family Income'],
            ['document_type' => 'Common', 'name' => 'Marriage Certificate'],
            ['document_type' => 'Common', 'name' => 'Medical Certificate'],
            ['document_type' => 'Common', 'name' => 'School Records/ID'],
            ['document_type' => 'Common', 'name' => 'Government-Issued ID'],

            // Specialized
            ['document_type' => 'Specialized', 'name' => 'Business Permit/Registration'],
            ['document_type' => 'Specialized', 'name' => 'Land Title/Tax Declaration'],
            ['document_type' => 'Specialized', 'name' => 'Senior Citizen ID'],
            ['document_type' => 'Specialized', 'name' => 'PWD ID'],
            ['document_type' => 'Specialized', 'name' => 'PhilHealth ID'],
            ['document_type' => 'Specialized', 'name' => 'Police Clearance'],
            ['document_type' => 'Specialized', 'name' => 'NBI Clearance'],
            ['document_type' => 'Specialized', 'name' => 'Tax Return (ITR)'],
        ];

        foreach ($documents as $document) {
            DB::table('documents')->updateOrInsert(
                ['name' => $document['name']], 
                ['document_type' => $document['document_type']]
            );
        }
    }
}
