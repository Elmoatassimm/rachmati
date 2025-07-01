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
        Schema::create('rachma_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rachma_id')->constrained('rachmat')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->timestamps();

            // Ensure unique combinations
            $table->unique(['rachma_id', 'category_id']);

            // Add indexes for better performance
            $table->index('rachma_id');
            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rachma_categories');
    }
};
