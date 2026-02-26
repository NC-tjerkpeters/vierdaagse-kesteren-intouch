<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edition_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->string('category', 50)->default('overig'); // mollie, medailles, overig
            $table->date('cost_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_entries');
    }
};
