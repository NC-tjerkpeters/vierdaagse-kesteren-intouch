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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('postal_code', 10);
            $table->string('house_number', 10);
            $table->string('phone_number', 20);
            $table->string('email');

            $table->foreignId('distance_id')->constrained('distances');

            $table->boolean('wants_medal')->default(false);
            $table->unsignedInteger('medal_number')->nullable();

            $table->string('mollie_payment_id')->nullable();
            $table->string('mollie_payment_status')->default('open');

            $table->string('qr_code')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
