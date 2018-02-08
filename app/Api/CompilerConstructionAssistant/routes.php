<?php

Route::prefix('cca')->group(function () {
    Route::get('/courses', 'CompilerConstructionAssistantController@getCourses');
    Route::post('/courses/{cid}/subscribe', 'CompilerConstructionAssistantController@subscribeToCourse');
    Route::post('/courses/{cid}/unsubscribe', 'CompilerConstructionAssistantController@unsubscribeFromCourse');
    Route::get('/courses/{cid}/lessons', 'CompilerConstructionAssistantController@getCourseLessons');
    Route::get('/courses/{cid}/current-lesson', 'CompilerConstructionAssistantController@getCurrentLesson');
    Route::patch('/courses/{cid}/lessons/{lid}', 'CompilerConstructionAssistantController@saveLessonProgress');
    Route::get('/courses/{cid}/lessons/next', 'CompilerConstructionAssistantController@nextLesson');
    Route::post('/courses/{cid}/lessons/{lid}/submit', 'CompilerConstructionAssistantController@submitLesson');
});
