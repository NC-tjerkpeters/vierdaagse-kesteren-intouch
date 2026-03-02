<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edition_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('target', 30)->default('all_paid');
            $table->text('intro_text')->nullable();
            $table->text('thank_you_text')->nullable();
            $table->datetime('closes_at')->nullable();
            $table->datetime('sent_at')->nullable();
            $table->string('mail_subject');
            $table->text('mail_body')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('evaluation_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->string('question_text');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(true);
            $table->timestamps();
        });

        Schema::create('evaluation_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->datetime('submitted_at');
            $table->timestamps();
            $table->unique(['evaluation_id', 'registration_id']);
        });

        Schema::create('evaluation_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_id')->constrained('evaluation_responses')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('evaluation_questions')->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->timestamps();
            $table->unique(['response_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_answers');
        Schema::dropIfExists('evaluation_responses');
        Schema::dropIfExists('evaluation_questions');
        Schema::dropIfExists('evaluations');
    }
};
