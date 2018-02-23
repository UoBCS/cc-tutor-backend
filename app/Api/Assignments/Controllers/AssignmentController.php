<?php

namespace App\Api\Assignments\Controllers;

use App\Api\Assignments\Services\AssignmentService;
use App\Infrastructure\Http\Controller as BaseController;

class AssignmentController extends BaseController
{
    /*protected $key = 'assignment';

    protected $createRules = [
        'assignment' => 'array|required',
    ];

    protected $updateRules = [
        'assignment' => 'array|required',
    ];*/

    public function __construct(AssignmentService $service)
    {
        $this->service = $service;
    }

    public function getAll()
    {

    }
}
