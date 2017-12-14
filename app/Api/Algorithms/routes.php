<?php

Route::get('/', 'AlgorithmController@index');

// Regex to NFA
Route::get('/regex2nfa/{regex}', 'AlgorithmController@regexToNFA');

