<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
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
            DB::table('scan_points')->where('point_number', $num)->update(['name' => $name]);
        }
    }

    public function down(): void
    {
        $points = [
            1 => 'Inschrijving',
            2 => 'Start',
            3 => 'Post 1',
            4 => 'Post 2',
            5 => 'Post 3',
            6 => 'Post 4',
            7 => 'Post 5',
            8 => 'Post 6',
            9 => 'Post 7',
            10 => 'Post 8',
            11 => 'Post 9',
            12 => 'Post 10',
            13 => 'Finish',
        ];
        foreach ($points as $num => $name) {
            DB::table('scan_points')->where('point_number', $num)->update(['name' => $name]);
        }
    }
};
