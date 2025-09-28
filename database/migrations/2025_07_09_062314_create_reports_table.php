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
            Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_code')->unique();
            $table->string('title');
            $table->string('issue_type');
            $table->text('description');
            $table->string('image')->nullable();
            $table->foreignId('barangay_id')->constrained('barangays')->onDelete('cascade');
            $table->foreignId('sitio_id')->constrained('sitios')->onDelete('cascade');
            $table->string('sender_name')->nullable();
            $table->string('contact_number')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status',['pending', 'in_progress', 'rejected', 'resolved'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
