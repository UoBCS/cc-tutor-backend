<?php

Route::get('/', 'CourseController@getAll');
Route::post('/', 'CourseController@create');
Route::get('/{id}', 'CourseController@getById');
Route::patch('/{id}', 'CourseController@update');
Route::delete('/{id}', 'CourseController@delete');
