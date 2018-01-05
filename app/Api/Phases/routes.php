<?php

Route::get('/', 'PhaseController@index');

Route::post('/lexical-analysis', 'PhaseController@lexicalAnalysis');
