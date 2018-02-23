<?php

Route::get('/students', 'UserController@getStudents');
Route::get('/students/class-invitation/{token}', 'UserController@submitClassInvitation')->name('users.class_invitation_token');
Route::get('/teachers', 'UserController@getTeachers');
