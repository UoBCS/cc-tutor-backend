<?php

namespace App\Api\Phases\Requests;

use App\Infrastructure\Http\ApiRequest;

class ProductionRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'run_id' => 'required|integer',
            'lhs'    => 'required|string',
            'rhs'    => 'array|nullable'
        ];
    }
}
