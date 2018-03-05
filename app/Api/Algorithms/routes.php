<?php

Route::get('/regex2nfa/{regex}', 'AlgorithmController@regexToNfa');
Route::post('/nfa2dfa', 'AlgorithmController@nfaToDfa');
Route::get('/nfa2dfa/breakpoints', 'AlgorithmController@nfaToDfaBreakpoints');
Route::post('/minimize-dfa', 'AlgorithmController@minimizeDfa');
Route::post('/cek-machine/next-step', 'AlgorithmController@cekMachineNextStep');
Route::post('/cek-machine/run', 'AlgorithmController@cekMachineRun');
