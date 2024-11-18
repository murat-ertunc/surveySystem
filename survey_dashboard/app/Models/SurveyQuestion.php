<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyQuestion extends Model
{
    protected $table = "survey_questions";

    protected $fillable = [
        "survey_id",
        "section",
        "type",
        "question_text",
        "is_required",
        "answer_length",
    ];

    public $timestamps = false;

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function options()
    {
        return $this->hasMany(SurveyQuestionOption::class, 'question_id', 'id')
            ->orderBy('id', 'ASC')->with(['answers', 'matchingOptions']);
    }

    public function answers()
    {
        return $this->hasMany(SurveyAnswer::class, 'question_id', 'id');
    }
}
