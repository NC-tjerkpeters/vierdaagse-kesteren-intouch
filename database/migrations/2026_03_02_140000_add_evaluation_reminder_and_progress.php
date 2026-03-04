<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->unsignedTinyInteger('reminder_days')->nullable()->after('sent_at')
                ->comment('Na dit aantal dagen herinnering sturen naar niet-respondenten; null = geen herinnering');
            $table->unsignedInteger('invitations_sent_count')->default(0)->after('sent_at');
            $table->unsignedInteger('invitations_total')->nullable()->after('invitations_sent_count');
        });
    }

    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropColumn(['reminder_days', 'invitations_sent_count', 'invitations_total']);
        });
    }
};
