<?php

namespace App\Api\Phases\Requests;

use App\Infrastructure\Http\ApiRequest;

class LR0ParseRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'content'     => 'required|string',
            'token_types' => 'required|token_types',
            'grammar'     => 'required|grammar',
            'interactive' => 'boolean'
        ];
    }
}
