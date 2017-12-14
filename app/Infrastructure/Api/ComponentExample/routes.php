<?php

Route::get('/', '{{ singularCapitalized }}Controller@getAll');
Route::post('/', '{{ singularCapitalized }}Controller@create');
Route::get('/{id}', '{{ singularCapitalized }}Controller@getById');
Route::patch('/{id}', '{{ singularCapitalized }}Controller@update');
Route::delete('/{id}', '{{ singularCapitalized }}Controller@delete');
