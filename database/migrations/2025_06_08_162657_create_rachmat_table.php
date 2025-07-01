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
            $table->foreignId('designer_id')->constrained()->onDelete('cascade');
            
            // Multilingual title support
            $table->string('title_ar'); // Arabic title
            $table->string('title_fr'); // French title
            
            // Multilingual description support
            $table->text('description_ar')->nullable(); // Arabic description
            $table->text('description_fr')->nullable(); // French description
            
            // File support - both single and multiple
            $table->json('files')->nullable(); // JSON column to store multiple file paths with metadata
            
            $table->json('preview_images'); // Array of preview image paths
            
            // Size fields - both original and split dimensions
            $table->decimal('width', 8, 2)->nullable(); // Width dimension
            $table->decimal('height', 8, 2)->nullable(); // Height dimension
            
            $table->integer('gharazat'); // Number of stitches
            $table->json('color_numbers'); // Array of color codes/numbers
            $table->json('rachma_parts')->nullable(); // Details of each part (length x height, stitches per part)
            $table->decimal('price', 8, 2);
            $table->decimal('original_price', 8, 2); // For tracking price decreases after sales
            $table->integer('sales_count')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('ratings_count')->default(0);
            $table->boolean('is_active')->default(true);
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
