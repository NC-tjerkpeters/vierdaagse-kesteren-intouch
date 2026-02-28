<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('volunteer_route_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('volunteer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('walk_route_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['volunteer_id', 'walk_route_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('volunteer_route_assignments');
    }
};
