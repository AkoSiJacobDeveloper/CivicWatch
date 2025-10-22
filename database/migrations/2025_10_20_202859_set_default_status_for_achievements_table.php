<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('achievements', function (Blueprint $table) {
            // Change the status column to have a default value
            $table->enum('status', ['draft', 'published', 'archived'])
                  ->default('published')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('achievements', function (Blueprint $table) {
            $table->enum('status', ['draft', 'published', 'archived'])
                  ->default('draft')
                  ->change();
        });
    }
};
