<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            if (Schema::hasColumn('reports', 'barangay_id')) {
                $table->dropForeign(['barangay_id']);
                $table->dropColumn('barangay_id');
            }

            if (Schema::hasColumn('reports', 'sitio_id')) {
                $table->dropForeign(['sitio_id']);
                $table->dropColumn('sitio_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            if (!Schema::hasColumn('reports', 'barangay_id')) {
                $table->foreignId('barangay_id')
                    ->nullable()
                    ->constrained()
                    ->onDelete('set null')
                    ->after('image');
            }

            if (!Schema::hasColumn('reports', 'sitio_id')) {
                $table->foreignId('sitio_id')
                    ->nullable()
                    ->constrained()
                    ->onDelete('set null')
                    ->after('barangay_id');
            }
        });
    }
};
