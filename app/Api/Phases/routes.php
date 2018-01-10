<?php

Route::get('/', 'PhaseController@index');

Route::post('/lexical-analysis', 'PhaseController@lexicalAnalysis');
Route::prefix('syntax-analysis')->group(function () {
    //Route::
});
