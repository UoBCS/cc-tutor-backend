<?php

Route::post('/lexical-analysis', 'LexicalAnalysisController@run');
Route::prefix('syntax-analysis')->group(function () {
    Route::prefix('ll')->group(function() {
        Route::post('/init-parser', 'LLRunController@create');
        Route::post('/predict', 'LLRunController@predict');
        Route::post('/match', 'LLRunController@match');
        Route::delete('/{id}', 'LLRunController@delete');
    });

    Route::prefix('ll1')->group(function () {
        Route::post('/parse', 'LL1Controller@parse');
        Route::post('/first', 'LL1Controller@first');
        Route::post('/follow', 'LL1Controller@first');
    });
});
