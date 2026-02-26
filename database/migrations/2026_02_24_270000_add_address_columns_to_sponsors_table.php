<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sponsors', function (Blueprint $table) {
            $table->string('postcode', 20)->nullable()->after('achternaam');
            $table->string('huisnummer', 20)->nullable()->after('postcode');
            $table->string('telefoonnummer', 30)->nullable()->after('huisnummer');
        });
    }

    public function down(): void
    {
        Schema::table('sponsors', function (Blueprint $table) {
            $table->dropColumn(['postcode', 'huisnummer', 'telefoonnummer']);
        });
    }
};
