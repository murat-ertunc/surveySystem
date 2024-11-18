<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Survey extends Model
{
    protected $table = "surveys";

    protected $fillable = [
        "title",
        "description",
        "end_time",
        "status",
        "share_link",
        "quota",
        "created_by",
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('created_by', function ($builder) {
            if(auth()->check()){
                $builder->where('created_by', auth()->id());
            }
        });
    }

    public function status(): string
    {
        return match($this->status){
            "draft" => '<span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">Draft</span>',
            "published" => '<span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Published</span>',
            "completed" => '<span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">Completed</span>',
            default => "-"
        };
    }

    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class)->with('options');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class);
    }


    public static function canEnterSurvey($userId, $surveyId){
        $survey = Survey::select('id','status', 'end_time', 'quota', 'share_link', 'anonymous')->find($surveyId);

        if(blank($userId) && $survey->anonymous !== true){
            return [
                'status' => false,
                'message' => __('surveys.only_designated_users_can_participate_in_this_survey')
            ];
        }

        //Survey status check
        $isNotPublished = $survey->status != 'published';

        //Survey end time check
        $isExpired = $survey->end_time && Carbon::parse($survey->end_time)->isPast();

        //Survey quota check
        $hasReachedQuota = filled($survey->quota) &&
            SurveyAnswer::whereIn('question_id', SurveyQuestion::where('survey_id', $survey->id)->pluck('id'))
            ->distinct('session_key')
            ->count('session_key') >= $survey->quota;

        //Survey share link check
        $hasInvalidShareLink = false;
        if($survey->anonymous === false){
            $hasInvalidShareLink = !auth()->check();
        }elseif($survey->anonymous === null){
            $hasInvalidShareLink = !SurveyUser::where('survey_id', $survey->id)->where('user_id', auth()->id())->exists() && !Survey::inSurveyAssignments($userId, $surveyId);
        }

        //Survey answered check
        $answered = false;
        if(auth()->check() && SurveyUser::where('survey_id', $survey->id)->where('user_id', auth()->id())->whereNotNull('answered_at')->exists()){
            $answered = true;
        }

        if ($isNotPublished) {
            return [
                'status' => false,
                'message' => __('surveys.survey_is_not_published')
            ];
        }

        if ($isExpired) {
            return [
                'status' => false,
                'message' => __('surveys.survey_is_expired')
            ];
        }

        if ($hasInvalidShareLink) {
            return [
                'status' => false,
                'message' => __('surveys.only_designated_users_can_participate_in_this_survey')
            ];
        }

        if ($answered) {
            return [
                'status' => false,
                'message' => __('surveys.you_already_answered_this_survey')
            ];
        }

        return true;
    }
}
