<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::name('dashboard.')->controller(\App\Http\Controllers\HomeController::class)->middleware('auth')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/survey/{id?}', 'survey')->name('survey');
    Route::get('/list', 'list')->name('list');
});

Route::name('api.')->controller(\App\Http\Controllers\SurveyController::class)->middleware('auth')->group(function () {
    Route::post('/save', 'storeSurvey')->name('save');
    Route::post('/update/{id?}', 'updateSurvey')->name('update');
    Route::post('/surveys', 'surveys')->name('surveys');
});

Route::get('/surveys/{id}', function ($id) {
    return response()->file(public_path('index.html'));
});

Route::get('logout', function () {
    Auth::logout();

    return redirect()->route('login');
});

Auth::routes();
