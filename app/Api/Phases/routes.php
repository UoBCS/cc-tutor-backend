<?php

Route::post('/lexical-analysis', 'LexicalAnalysisController@run');
Route::prefix('syntax-analysis')->group(function () {
    Route::prefix('ll')->group(function() {
        Route::post('/init-parser', 'LLRunController@create');
        Route::post('/predict', 'LLRunController@predict');
        Route::post('/match', 'LLRunController@match');
    });
});
