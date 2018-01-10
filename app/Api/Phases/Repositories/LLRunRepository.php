<?php

namespace App\Api\Phases\Repositories;

use App\Api\Phases\Models\LLRun;
use App\Infrastructure\Http\Crud\Repository;

class LLRunRepository extends Repository
{
    public function getModel()
    {
        return new LLRun();
    }
}

