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
        // Modify the status column to include 'duplicate'
        DB::statement("ALTER TABLE reports MODIFY COLUMN status ENUM('pending', 'in_progress', 'resolved', 'rejected', 'duplicate') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'duplicate' from the enum if rolling back
        DB::statement("ALTER TABLE reports MODIFY COLUMN status ENUM('pending', 'in_progress', 'resolved', 'rejected') NOT NULL DEFAULT 'pending'");
    }
};
