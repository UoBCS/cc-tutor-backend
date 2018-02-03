<?php

Route::prefix('cca')->group(function () {
    Route::post('/courses/{cid}/subscribe', 'CompilerConstructionAssistantController@subscribeToCourse');
    Route::post('/courses/{cid}/unsubscribe', 'CompilerConstructionAssistantController@unsubscribeFromCourse');
    //Route::get('/lessons/{lid}', 'UserController@getCurrentLesson');
    Route::get('/courses/{cid}/current-lesson', 'CompilerConstructionAssistantController@getCurrentLesson');
    Route::patch('/lessons/{lid}', 'CompilerConstructionAssistantController@saveLessonProgress');
    Route::post('/courses/{cid}/lessons/next', 'CompilerConstructionAssistantController@nextLesson');
    Route::post('/courses/{cid}/lessons/{lid}/submit', 'CompilerConstructionAssistantController@submitLesson');
});
