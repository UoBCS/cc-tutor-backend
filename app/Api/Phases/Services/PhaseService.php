<?php

namespace App\Api\Phases\Services;

use App\Core\Inspector;

class PhaseService
{
    private $inspector;

    public function __construct()
    {
        $this->inspector = inspector();
        //$this->inspector->getState('breakpoints')
    }

    public function lexicalAnalysis($data)
    {

    }
}
