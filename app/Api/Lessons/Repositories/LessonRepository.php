<?php

namespace App\Api\Lessons\Repositories;

use App\Api\Lessons\Models\Lesson;
use App\Infrastructure\Http\Crud\Repository;

class LessonRepository extends Repository
{
    public function getModel()
    {
        return new Lesson();
    }
}
