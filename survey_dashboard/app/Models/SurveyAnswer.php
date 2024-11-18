<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyAnswer extends Model
{
    protected $table = "survey_answers";

    protected $fillable = [
        "session_key",
        "question_id",
        "second_question_id",
        "option_id",
        "answer",
    ];

    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    public function option() {
        return $this->belongsTo(SurveyQuestionOption::class, 'option_id', 'id');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function question() {
        return $this->belongsTo(SurveyQuestion::class, 'question_id', 'id');
    }
}
