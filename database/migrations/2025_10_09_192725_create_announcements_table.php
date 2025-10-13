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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('title');
            $table->foreignId('category_id')->constrained('announcement_categories')->onDelete('cascade');
            $table->enum('level', ['high', 'medium', 'low']);
            $table->boolean('is_pinned')->default(false);
            $table->text('content');
            $table->string('image')->nullable();
            $table->datetime('publish_at');
            $table->datetime('event_date')->nullable();
            $table->datetime('expiry_date');
            $table->string('contact_person');
            $table->string('contact_number');
            $table->text('instructions')->nullable();
            $table->integer('counts')->default(0);
            $table->date('reg_deadline')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
