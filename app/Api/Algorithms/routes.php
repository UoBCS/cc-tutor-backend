<?php

Route::get('/', 'AlgorithmController@index');

Route::get('/regex2nfa/{regex}', 'AlgorithmController@regexToNfa');
Route::post('/nfa2dfa', 'AlgorithmController@nfaToDfa');
