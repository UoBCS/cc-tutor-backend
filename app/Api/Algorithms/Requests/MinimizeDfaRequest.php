<?php

namespace App\Api\Algorithms\Requests;

use App\Infrastructure\Http\ApiRequest;

class MinimizeDfaRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'dfa' => 'required|array',
        ];
    }
}
