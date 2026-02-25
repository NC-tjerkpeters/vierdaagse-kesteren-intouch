<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_days', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Dag 1, Dag 2, ...
            $table->boolean('is_current')->default(false);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->date('event_date')->nullable();
            $table->timestamps();
        });

        Schema::create('scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_day_id')->constrained()->cascadeOnDelete();
            $table->string('scan_point', 20); // start, post, finish
            $table->timestamp('scanned_at');
            $table->foreignId('scanned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['registration_id', 'event_day_id', 'scan_point']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scans');
        Schema::dropIfExists('event_days');
    }
};
