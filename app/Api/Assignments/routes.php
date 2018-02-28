<?php

Route::get('/', 'AssignmentController@getAll');
Route::post('/', 'AssignmentController@create');
Route::get('/{id}', 'AssignmentController@getById');
Route::patch('/{id}', 'AssignmentController@update');
Route::delete('/{id}', 'AssignmentController@delete');
Route::get('/{id}/submissions', 'AssignmentController@getSubmissions');
Route::post('/{id}/submit', 'AssignmentController@submit');
Route::post('/{assignmentId}/students/{studentId}/run-tests', 'AssignmentController@runTests');
