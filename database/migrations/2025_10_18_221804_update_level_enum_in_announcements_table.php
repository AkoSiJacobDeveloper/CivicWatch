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
        // Step 1: First, change the column to a VARCHAR temporarily
        DB::statement("ALTER TABLE announcements MODIFY COLUMN level VARCHAR(20) NOT NULL");
        
        // Step 2: Update existing records to match new values
        DB::table('announcements')->where('level', 'high')->update(['level' => 'urgent']);
        DB::table('announcements')->where('level', 'medium')->update(['level' => 'important']);
        DB::table('announcements')->where('level', 'low')->update(['level' => 'general']);
        
        // Step 3: Now change it to the new ENUM with the new values
        DB::statement("ALTER TABLE announcements MODIFY COLUMN level ENUM('urgent', 'important', 'general') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse: VARCHAR first, update data, then back to old ENUM
        DB::statement("ALTER TABLE announcements MODIFY COLUMN level VARCHAR(20) NOT NULL");
        
        DB::table('announcements')->where('level', 'urgent')->update(['level' => 'high']);
        DB::table('announcements')->where('level', 'important')->update(['level' => 'medium']);
        DB::table('announcements')->where('level', 'general')->update(['level' => 'low']);
        
        DB::statement("ALTER TABLE announcements MODIFY COLUMN level ENUM('high', 'medium', 'low') NOT NULL");
    }
};