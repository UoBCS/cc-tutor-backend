<?php

Route::get('/', 'UserController@getAll');
Route::post('/', 'UserController@create');
Route::get('/{id}', 'UserController@getById');
Route::patch('/{id}', 'UserController@update');
Route::delete('/{id}', 'UserController@delete');
