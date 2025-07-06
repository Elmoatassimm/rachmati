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
        Schema::create('rachmat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('designer_id')->constrained('designers')->onDelete('cascade');
            
            // Multilingual title support
            $table->string('title_ar'); // Arabic title
            $table->string('title_fr')->nullable(); // French title
            
            // Multilingual description support
            $table->text('description_ar')->nullable(); // Arabic description
            $table->text('description_fr')->nullable(); // French description
            
            // File support - both single and multiple
            $table->json('files')->nullable(); // JSON column to store multiple file paths with metadata
            
            $table->json('preview_images')->nullable(); // Array of preview image paths
            
            $table->json('color_numbers'); // Array of color codes/numbers
            $table->json('rachma_parts')->nullable(); // Details of each part (length x height, stitches per part)
            $table->decimal('price', 10, 2);
            $table->integer('sales_count')->default(0);
            $table->decimal('average_rating', 3, 2)->nullable();
            $table->integer('ratings_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rachmat');
    }
};
