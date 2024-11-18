<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('survey_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('surveys');
            $table->string('section')->nullable();
            $table->string('question_text');
            $table->boolean('is_required')->default(false);
            $table->enum('type', ['entry_question', 'single_choice', 'multiple_choice', 'open_ended', 'multiple_choice_table'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_questions');
    }
};
