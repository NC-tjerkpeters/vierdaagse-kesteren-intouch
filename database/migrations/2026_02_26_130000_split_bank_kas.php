<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('editions', function (Blueprint $table) {
            $table->decimal('opening_balance_bank', 12, 2)->default(0)->after('opening_balance');
            $table->decimal('opening_balance_cash', 12, 2)->default(0)->after('opening_balance_bank');
        });

        \DB::table('editions')->update([
            'opening_balance_bank' => \DB::raw('opening_balance'),
            'opening_balance_cash' => 0,
        ]);

        Schema::table('editions', function (Blueprint $table) {
            $table->dropColumn('opening_balance');
        });

        Schema::table('cost_entries', function (Blueprint $table) {
            $table->string('payment_method', 10)->default('bank')->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('editions', function (Blueprint $table) {
            $table->decimal('opening_balance', 12, 2)->default(0)->after('is_active');
        });

        \DB::table('editions')->update([
            'opening_balance' => \DB::raw('opening_balance_bank + opening_balance_cash'),
        ]);

        Schema::table('editions', function (Blueprint $table) {
            $table->dropColumn(['opening_balance_bank', 'opening_balance_cash']);
        });

        Schema::table('cost_entries', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }
};
