<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Survey;
use Illuminate\Support\Str;
use App\Models\SurveyAnswer;
use Illuminate\Http\Request;
use App\Models\SurveyQuestion;
use App\Models\SurveyQuestionOption;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class SurveyController extends Controller
{
    public function statics()  {
        $trashCount = Survey::onlyTrashed()->count();
        return view('backend.survey.index', compact('trashCount'));
    }

    public function surveys(Request $request) {
        $surveys = Survey::orderBy('id', 'desc');

        return DataTables::of($surveys)
            ->addColumn('answered_user_count', function ($survey) {
                return SurveyAnswer::whereIn('question_id', SurveyQuestion::where('survey_id', $survey->id)->pluck('id'))->distinct('session_key')->count('session_key');
            })
            ->addColumn('question_count', function ($survey) {
                return SurveyQuestion::where('survey_id', $survey->id)->whereNot('type', 'entry_data')->count();
            })
            ->addIndexColumn()
            ->escapeColumns([])
            ->make(true);
    }

    public function storeSurvey(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'description' => 'nullable|max:4000000000',
            'sectionHasQuestions' => 'required'
        ], [
            'name.required' => 'Title is required',
            'name.min' => 'The survey title must be at least :min characters',
            'description.max' => 'The survey description can be up to :max characters',
            'sectionHasQuestions.required' => 'At least one section must be added',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withInput()
                ->withErrors($validator);
        }

        $survey = Survey::create([
            "title" => $request->name,
            "description" => $request->description ?? null,
            "status" => $request->status ?? 'draft',
            "share_link" => Str::random(10),
            "created_by" => auth()->id(),
        ]);

        foreach(json_decode($request->sectionHasQuestions) as $sectionName => $questions){
            foreach ($questions as $question){
                if(blank($question->question_text) || blank($question->type)){
                    continue;
                }

                $createdQuestion = SurveyQuestion::create([
                    "survey_id" => $survey->id,
                    "section" => $sectionName != "null" ? $sectionName : null,
                    "type" => $question->type,
                    "question_text" => $question->question_text,
                    "is_required" => $question->is_required,
                ]);

                if(property_exists($question, 'options') && filled($question->options)){
                    foreach ($question->options as $option){
                        if(blank($option->text)){
                            continue;
                        }
                        SurveyQuestionOption::create([
                            "survey_id" => $survey->id,
                            "question_id" => $createdQuestion->id,
                            "text" => $option->text,
                            "type" => $option->type ?? null,
                        ]);
                    }
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Survey created successfully',
            'redirect_url' => route('dashboard.list'),
        ]);
    }

    public function updateSurvey(Request $request, $surveyId) {
        $survey = Survey::findOrFail($surveyId);
        $survey->title = $request->name;
        $survey->description = $request->description;
        $survey->status = $request->status ?? 'draft';
        $survey->save();

        $questionIds = [];
        $optionsIds = [];

        foreach(json_decode($request->sectionHasQuestions) as $sectionName => $questions){
            foreach ($questions as $question){
                $questionId = null;
                if(is_int($question->id) && SurveyQuestion::where('id', $question->id)->exists()){
                    $createdQuestion = SurveyQuestion::where('id', $question->id)->update([
                        "section" => $sectionName != "null" ? $sectionName : null,
                        "type" => $question->type,
                        "question_text" => $question->question_text,
                        "is_required" => $question->is_required,
                    ]);
                    $questionId = $question->id;
                }else{
                    $createdQuestion = SurveyQuestion::create([
                        "survey_id" => $surveyId,
                        "section" => $sectionName != "null" ? $sectionName : null,
                        "type" => $question->type,
                        "question_text" => $question->question_text,
                        "is_required" => $question->is_required,
                    ]);
                    $questionId = $createdQuestion->id;
                }
                $questionIds[] = $questionId;

                if(property_exists($question, 'options') && filled($question->options)){
                    if(isset($question->options)){
                        foreach ($question->options as $option){
                            if(property_exists($option, 'id')){
                                $optionsIds[] = $option->id;
                                SurveyQuestionOption::where('id', $option->id)->update([
                                    "question_id" => $questionId,
                                    "text" => $option->text,
                                    "type" => $option->type ?? null,
                                ]);
                            }else{
                                $option = SurveyQuestionOption::create([
                                    "survey_id" => $surveyId,
                                    "question_id" => $questionId,
                                    "text" => $option->text,
                                    "type" => $option->type ?? null,
                                ]);
                                $optionsIds[] = $option->id;
                            }
                        }
                    }
                }
            }
        }

        SurveyQuestion::where('survey_id', $surveyId)->whereNot('type', 'entry_data')->whereNotIn('id', $questionIds)->delete();
        SurveyQuestionOption::where('survey_id', $surveyId)->whereNotIn('id', $optionsIds)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Survey updated successfully',
            'redirect_url' => route('dashboard.list'),
        ]);
    }

}
