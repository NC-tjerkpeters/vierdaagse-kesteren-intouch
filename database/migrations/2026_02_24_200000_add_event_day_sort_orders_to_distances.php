<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distances', function (Blueprint $table) {
            $table->json('event_day_sort_orders')->nullable()->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('distances', function (Blueprint $table) {
            $table->dropColumn('event_day_sort_orders');
        });
    }
};
