<?php

Route::post('/logout', 'LoginController@logout');
Route::get('/authenticated', 'LoginController@isAuthenticated');
