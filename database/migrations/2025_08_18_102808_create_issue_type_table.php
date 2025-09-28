<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('issue_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        $issueTypes = [
            'Potholes',
            'Cracked or uneven pavement',
            'Blocked or obstructed sidewalks',
            'Damaged speed bumps or humps',
            'Broken or non-functioning streetlights',
            'Exposed electric wires',
            'Flickering or dim streetlights',
            'Leaning/damaged electric poles',
            'Uncollected garbage',
            'Illegal dumping',
            'Overflowing public trash bins',
            'Animal carcasses not removed',
            'Clogged canals or drainages',
            'Stagnant water (risk of dengue)',
            'Broken drainage covers',
            'Damaged benches or waiting sheds',
            'Broken barangay signage',
            'Vandalized public walls/facilities',
            'Overgrown weeds or vegetation on roads',
            'Fallen trees blocking paths',
            'Untended public spaces (plazas, mini-parks)',
            'Stray dog or animal complaints (non-aggressive)',
            'Animal waste in public areas',
            'Road obstructions due to informal construction',
            'Unsecured construction materials in public areas',
            'Loud sounds from establishments during restricted hours',
            'Public karaoke complaints (non-urgent)',
        ];

        foreach ($issueTypes as $type) {
            DB::table('issue_types')->insert([
                'name' => $type,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issue_types');
    }
};
