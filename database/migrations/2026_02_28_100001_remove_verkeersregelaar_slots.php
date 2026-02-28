<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('volunteer_slots')->where('role', 'verkeersregelaar')->delete();
    }

    public function down(): void
    {
        // Niet terugzetten
    }
};
