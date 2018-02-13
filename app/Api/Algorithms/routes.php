<?php

Route::get('/regex2nfa/{regex}', 'AlgorithmController@regexToNfa');
Route::post('/nfa2dfa', 'AlgorithmController@nfaToDfa');
Route::post('/minimize-dfa', 'AlgorithmController@minimizeDfa');
