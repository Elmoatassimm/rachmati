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
        Schema::create('subscription_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('designer_id')->constrained('designers')->onDelete('cascade');
            $table->foreignId('pricing_plan_id')->constrained('pricing_plans')->onDelete('cascade');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable(); // Designer notes/comments
            $table->string('payment_proof_path')->nullable(); // Path to uploaded payment proof
            $table->string('payment_proof_original_name')->nullable(); // Original filename
            $table->unsignedBigInteger('payment_proof_size')->nullable(); // File size in bytes
            $table->string('payment_proof_mime_type')->nullable(); // MIME type
            $table->text('admin_notes')->nullable(); // Admin review notes
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null'); // Admin who reviewed
            $table->timestamp('reviewed_at')->nullable(); // When it was reviewed
            $table->decimal('subscription_price', 10, 2); // Price at time of request
            $table->date('requested_start_date'); // When designer wants subscription to start
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index(['designer_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_requests');
    }
};
