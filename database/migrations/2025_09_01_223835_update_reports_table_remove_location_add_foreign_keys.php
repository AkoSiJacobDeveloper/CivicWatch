<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            if (!Schema::hasColumn('reports', 'sitio_id')) {
                $table->foreignId('sitio_id')->constrained()->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            // Reverse the changes
            $table->string('location');
            
            // Remove foreign keys and columns
            $table->dropForeign(['barangay_id']);
            $table->dropForeign(['sitio_id']);
            $table->dropColumn(['barangay_id', 'sitio_id']);
            
            // Remove tracking_code
            if (Schema::hasColumn('reports', 'tracking_code')) {
                $table->dropColumn('tracking_code');
            }
        });
    }
};