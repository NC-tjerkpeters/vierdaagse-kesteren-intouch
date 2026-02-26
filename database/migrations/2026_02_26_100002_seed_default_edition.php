<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $year = (int) date('Y');
        $prevYear = $year - 1;
        $name = "Editie {$year}";
        $startDate = "{$prevYear}-10-01";
        $endDate = "{$year}-09-30";

        $editionId = DB::table('editions')->insertGetId([
            'name' => $name,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('registrations')->update(['edition_id' => $editionId]);
        DB::table('sponsors')->update(['edition_id' => $editionId]);
        DB::table('event_days')->update(['edition_id' => $editionId]);
    }

    public function down(): void
    {
        DB::table('editions')->where('is_active', true)->delete();
    }
};
