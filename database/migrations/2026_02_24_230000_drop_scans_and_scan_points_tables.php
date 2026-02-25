<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('scans');
        Schema::dropIfExists('scan_points');
    }

    public function down(): void
    {
        // Tables are not recreated; restore from backup if needed.
    }
};
