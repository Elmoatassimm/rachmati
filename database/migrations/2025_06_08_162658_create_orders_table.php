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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('rachma_id')->constrained('rachmat')->onDelete('cascade');
            $table->decimal('amount', 8, 2);
            $table->enum('payment_method', ['ccp', 'baridi_mob', 'dahabiya']);
            $table->string('payment_proof_path');
            
            // Simplified status system (3 statuses instead of 5)
            $table->enum('status', ['pending', 'completed', 'rejected'])->default('pending');
            
            // Legacy timestamp fields for backward compatibility
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('file_sent_at')->nullable();
            
            $table->text('admin_notes')->nullable();
            
            // Rejection and completion fields
            $table->text('rejection_reason')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
