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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('rachma_id')->constrained('rachmat')->onDelete('cascade');
            $table->decimal('price', 8, 2); // Price of rachma at time of order
            $table->timestamps();

            // Allow multiple entries of same rachma_id per order (for quantity)
            // No unique constraint - clients can add same rachma multiple times
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
