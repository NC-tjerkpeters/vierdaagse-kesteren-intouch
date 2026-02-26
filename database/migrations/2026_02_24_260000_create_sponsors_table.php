<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sponsors', function (Blueprint $table) {
            $table->id();
            $table->string('bedrijfsnaam')->nullable();
            $table->string('voornaam');
            $table->string('achternaam');
            $table->string('email');
            $table->decimal('bedrag', 10, 2);
            $table->string('betaalstatus', 20)->default('open');
            $table->string('invoice_id')->nullable();
            $table->string('betaling_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsors');
    }
};
