<?php

namespace App\Api\Assignments\Repositories;

use App\Api\Assignments\Models\Assignment;
use App\Infrastructure\Http\Crud\Repository;

class AssignmentRepository extends Repository
{
    public function getModel()
    {
        return new Assignment();
    }
}
