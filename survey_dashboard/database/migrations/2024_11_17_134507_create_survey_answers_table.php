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
        Schema::create('survey_answers', function (Blueprint $table) {
            $table->id();
            $table->string('session_key');
            $table->foreignId('question_id')->constrained('survey_questions');
            $table->foreignId('second_question_id')->nullable()->constrained('survey_questions');
            $table->foreignId('option_id')->nullable()->constrained('survey_question_options');
            $table->text('answer')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_answers');
    }
};
