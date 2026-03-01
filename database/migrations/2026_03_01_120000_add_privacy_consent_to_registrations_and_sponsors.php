<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->timestamp('privacy_consent_at')->nullable()->after('last_scan_at');
        });

        Schema::table('sponsors', function (Blueprint $table) {
            $table->timestamp('privacy_consent_at')->nullable()->after('betaling_id');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn('privacy_consent_at');
        });

        Schema::table('sponsors', function (Blueprint $table) {
            $table->dropColumn('privacy_consent_at');
        });
    }
};
