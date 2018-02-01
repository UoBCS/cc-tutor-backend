<?php

Route::get('/', 'LessonController@getAll');
Route::post('/', 'LessonController@create');
Route::get('/{id}', 'LessonController@getById');
Route::patch('/{id}', 'LessonController@update');
Route::delete('/{id}', 'LessonController@delete');
