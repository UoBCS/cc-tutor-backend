<?php

Route::get('/', 'AssignmentController@getAll');
Route::post('/', 'AssignmentController@create');
Route::get('/{id}', 'AssignmentController@getById');
Route::patch('/{id}', 'AssignmentController@update');
Route::delete('/{id}', 'AssignmentController@delete');
