<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('participant_email_templates')->exists()) {
            return;
        }
        $templates = config('participant_communication.templates', []);
        $order = 0;
        foreach ($templates as $key => $tpl) {
            if (($tpl['custom'] ?? false) === true) {
                continue;
            }
            DB::table('participant_email_templates')->insert([
                'name' => $tpl['name'],
                'subject' => $tpl['subject'],
                'body' => $tpl['body'],
                'sort_order' => $order++,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('participant_email_templates')->truncate();
    }
};
