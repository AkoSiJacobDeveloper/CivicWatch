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
        Schema::table('reports', function (Blueprint $table) {
            // Add the column that points to the primary report
            $table->foreignId('duplicate_of_report_id')
                ->nullable()
                ->constrained('reports') // This creates a foreign key to the reports table itself
                ->onDelete('set null');   // If the primary report is deleted, this field is set to NULL, preserving the duplicate record.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            // Drop the foreign key first, then the column
            $table->dropForeign(['duplicate_of_report_id']);
            $table->dropColumn('duplicate_of_report_id');
        });
    }
};
