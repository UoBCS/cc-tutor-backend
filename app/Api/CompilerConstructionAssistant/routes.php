<?php

$controller = 'CompilerConstructionAssistantController';

Route::prefix('cca')->group(function () {
    Route::get('/courses/{cid}/subscribe', "$controller@subscribeToCourse");
    //Route::get('/lessons/{lid}', 'UserController@getCurrentLesson');
    Route::get('/courses/{cid}/current', "$controller@getCurrentLesson");
    Route::patch('/lessons/{lid}', "$controller@saveLessonProgress");

});
