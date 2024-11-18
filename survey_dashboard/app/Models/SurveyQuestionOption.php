<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyQuestionOption extends Model
{
    protected $table = "survey_question_options";

    protected $fillable = [
        "survey_id",
        "question_id",
        "text",
        "type"
    ];

    public $timestamps = false;

    public function question(): BelongsTo
    {
        return $this->belongsTo(SurveyQuestion::class);
    }

    public function answers()
    {
        return $this->hasMany(SurveyAnswer::class, 'option_id', 'id');
    }

    public function matchingOptions()
    {
        return $this->hasMany(SurveyAnswer::class, 'second_question_id', 'id')
            ->whereColumn('id', 'survey_answers.id')
            ->whereColumn('question_id', 'survey_answers.question_id')->with('option');
    }
}
