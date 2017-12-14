<?php

namespace App\Api\{{ pluralCapitalized }}\Repositories;

use App\Api\{{ pluralCapitalized }}\Models\{{ singularCapitalized }};
use App\Infrastructure\Http\Crud\Repository;

class {{ singularCapitalized }}Repository extends Repository
{
    public function getModel()
    {
        return new {{ singularCapitalized }}();
    }
}
