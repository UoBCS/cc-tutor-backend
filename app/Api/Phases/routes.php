<?php

Route::post('/lexical-analysis', 'LexicalAnalysisController@run');
Route::prefix('syntax-analysis')->group(function () {
    Route::prefix('ll')->group(function() {
        Route::post('/init-parser', 'NonDetParserRunController@create')->name('ll_parsing');
        Route::post('/predict', 'NonDetParserRunController@predict');
        Route::post('/match', 'NonDetParserRunController@match');
        Route::delete('/{id}', 'NonDetParserRunController@delete');
    });

    Route::prefix('ll1')->group(function () {
        Route::post('/parse', 'LL1Controller@parse');
        Route::post('/first', 'LL1Controller@first');
        Route::post('/follow', 'LL1Controller@first');
    });

    Route::prefix('lr')->group(function () {
        Route::post('/init-parser', 'NonDetParserRunController@create')->name('lr_parsing');
        Route::post('/reduce', 'NonDetParserRunController@reduce');
        Route::post('/shift', 'NonDetParserRunController@shift');
        Route::delete('/{id}', 'NonDetParserRunController@delete');
    });
});
