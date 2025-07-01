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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('target_id'); // ID of rachma or designer
            $table->enum('target_type', ['rachma', 'store']); // What is being rated
            $table->integer('rating')->unsigned()->default(1); // 1-5 stars
            $table->timestamps();
            
            // Prevent duplicate ratings from same user for same target
            $table->unique(['user_id', 'target_id', 'target_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
