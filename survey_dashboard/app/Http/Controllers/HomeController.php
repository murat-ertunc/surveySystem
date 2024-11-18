<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Survey;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('pages.index');
    }

    /**
     * Show the create - edit page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function survey($surveyId = null)
    {
        $survey = null;
        $sections = [];
        if(filled($surveyId)){
            $survey = Survey::where('id', $surveyId)->with('questions', function($query){
                $query->whereNot('type', 'entry_data')->orderBy('id', 'ASC');
            })->firstOrFail();
            $survey->end_time = $survey->end_time ? Carbon::parse($survey->end_time)->format('d-m-Y H:i') : null;
            $sections = array_keys($survey->questions->groupBy('section')->toArray());
        }

        return view('pages.survey', compact('surveyId', 'survey', 'sections'));
    }

    /**
     * Show the list page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function list()
    {
        return view('pages.list');
    }
}
