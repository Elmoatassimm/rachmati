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
        Schema::create('admin_payment_infos', function (Blueprint $table) {
            $table->id();
            $table->string('ccp_number')->nullable()->comment('CCP account number');
            $table->string('ccp_key')->nullable()->comment('CCP key');
            $table->string('nom')->nullable()->comment('Account holder name');
            $table->text('adress')->nullable()->comment('Address');
            $table->string('baridimob')->nullable()->comment('BaridiMob number');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_payment_infos');
    }
};
