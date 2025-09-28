<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Report;
use App\Models\Barangay;
use App\Models\Sitio;

class ReportLocationBackfillSeeder extends Seeder
{
    public function run(): void
    {
        Report::with(['barangay', 'sitio'])
            ->whereNull('barangay_name')
            ->orWhereNull('sitio_name')
            ->chunk(100, function ($reports) {
                foreach ($reports as $report) {
                    $report->update([
                        'barangay_name' => $report->barangay?->name ?? 'Unknown Barangay',
                        'sitio_name'    => $report->sitio?->name ?? 'Unknown Sitio',
                    ]);
                }
            });
    }
}
