<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->foreignId('edition_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('sponsors', function (Blueprint $table) {
            $table->foreignId('edition_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('event_days', function (Blueprint $table) {
            $table->foreignId('edition_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropForeign(['edition_id']);
        });
        Schema::table('sponsors', function (Blueprint $table) {
            $table->dropForeign(['edition_id']);
        });
        Schema::table('event_days', function (Blueprint $table) {
            $table->dropForeign(['edition_id']);
        });
    }
};
