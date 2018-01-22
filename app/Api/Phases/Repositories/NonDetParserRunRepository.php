<?php

namespace App\Api\Phases\Repositories;

use App\Api\Phases\Models\NonDetParserRun;
use App\Infrastructure\Http\Crud\Repository;

class NonDetParserRunRepository extends Repository
{
    public function getModel()
    {
        return new NonDetParserRun();
    }
}

