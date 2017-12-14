<?php

namespace App\Api\{{ pluralCapitalized }}\Controllers;

use App\Api\{{ pluralCapitalized }}\Services\{{ singularCapitalized }}Service;
use App\Infrastructure\Http\Crud\Controller;

class {{ singularCapitalized }}Controller extends Controller
{
    protected $key = '{{ singular }}';

    protected $createRules = [
        '{{ singular }}' => 'array|required',
    ];

    protected $updateRules = [
        '{{ singular }}' => 'array|required',
    ];

    public function __construct({{ singularCapitalized }}Service $service)
    {
        $this->service = $service;
    }
}
