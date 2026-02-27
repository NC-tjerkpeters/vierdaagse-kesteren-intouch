<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distance_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('word_path')->nullable();
            $table->string('pdf_path')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('route_template_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_template_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('walk_routes', function (Blueprint $table) {
            $table->foreignId('route_template_id')->nullable()->after('edition_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('walk_routes', function (Blueprint $table) {
            $table->dropForeign(['route_template_id']);
        });
        Schema::dropIfExists('route_template_points');
        Schema::dropIfExists('route_templates');
    }
};
