<?php

Route::post('/logout', 'LoginController@logout');
Route::get('/authenticated', 'AuthController@isAuthenticated');
Route::get('/user-data', 'AuthController@getUserData');
