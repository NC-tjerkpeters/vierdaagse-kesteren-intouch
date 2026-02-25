<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public const MIN_MINUTES_BETWEEN_SCANS = 5;

    public function up(): void
    {
        if (! Schema::hasTable('scan_points')) {
            Schema::create('scan_points', function (Blueprint $table) {
                $table->id();
                $table->unsignedTinyInteger('point_number')->unique();
                $table->string('name');
                $table->unsignedTinyInteger('sort_order')->default(0);
                $table->timestamps();
            });

        $points = [
            1 => 'Inschrijving',
            2 => 'Start 1',
            3 => 'Post 1',
            4 => 'Finish 1',
            5 => 'Start 2',
            6 => 'Post 2',
            7 => 'Finish 2',
            8 => 'Start 3',
            9 => 'Post 3',
            10 => 'Finish 3',
            11 => 'Start 4',
            12 => 'Post 4',
            13 => 'Finish 4',
        ];
        foreach ($points as $num => $name) {
            DB::table('scan_points')->insert([
                'point_number' => $num,
                'name' => $name,
                'sort_order' => $num,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        } else {
            if (DB::table('scan_points')->count() === 0) {
                $points = [1 => 'Inschrijving', 2 => 'Start 1', 3 => 'Post 1', 4 => 'Finish 1', 5 => 'Start 2', 6 => 'Post 2', 7 => 'Finish 2', 8 => 'Start 3', 9 => 'Post 3', 10 => 'Finish 3', 11 => 'Start 4', 12 => 'Post 4', 13 => 'Finish 4'];
                foreach ($points as $num => $name) {
                    DB::table('scan_points')->insert(['point_number' => $num, 'name' => $name, 'sort_order' => $num, 'created_at' => now(), 'updated_at' => now()]);
                }
            }
        }

        if (! Schema::hasColumn('scans', 'point_number')) {
            Schema::table('scans', function (Blueprint $table) {
                $table->unsignedTinyInteger('point_number')->nullable()->after('event_day_id');
            });

            DB::table('scans')->where('scan_point', 'start')->update(['point_number' => 2]);
            DB::table('scans')->where('scan_point', 'post')->update(['point_number' => 7]);
            DB::table('scans')->where('scan_point', 'finish')->update(['point_number' => 13]);
        }

        try {
            Schema::table('scans', function (Blueprint $table) {
                $table->unique(['registration_id', 'event_day_id', 'point_number'], 'scans_reg_event_point_unique');
            });
        } catch (\Throwable $e) {
            // Unique already exists from previous partial run
        }

        try {
            Schema::table('scans', function (Blueprint $table) {
                $table->dropUnique(['registration_id', 'event_day_id', 'scan_point']);
            });
        } catch (\Throwable $e) {
            // Leave old unique if MySQL needs it for FK
        }
    }

    public function down(): void
    {
        Schema::table('scans', function (Blueprint $table) {
            $table->dropUnique(['registration_id', 'event_day_id', 'point_number']);
        });
        Schema::table('scans', function (Blueprint $table) {
            $table->dropColumn('point_number');
        });
        Schema::dropIfExists('scan_points');
    }
};
