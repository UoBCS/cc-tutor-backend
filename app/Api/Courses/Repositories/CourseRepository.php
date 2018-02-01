<?php

namespace App\Api\Courses\Repositories;

use App\Api\Courses\Models\Course;
use App\Infrastructure\Http\Crud\Repository;

class CourseRepository extends Repository
{
    public function getModel()
    {
        return new Course();
    }
}
