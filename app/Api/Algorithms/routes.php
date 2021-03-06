<?php

Route::get('/regex2nfa/{regex}', 'AlgorithmController@regexToNfa');
Route::post('/nfa2dfa', 'AlgorithmController@nfaToDfa');
Route::post('/minimize-dfa', 'AlgorithmController@minimizeDfa');
Route::post('/cek-machine/next-step', 'AlgorithmController@cekMachineNextStep');
Route::post('/cek-machine/run', 'AlgorithmController@cekMachineRun');

Route::prefix('dfa-operations')->group(function () {
    Route::post('/membership', 'AlgorithmController@dfaOpsMembership');
});
