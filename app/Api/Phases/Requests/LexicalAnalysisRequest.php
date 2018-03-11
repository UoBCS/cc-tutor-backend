<?php

namespace App\Api\Phases\Requests;

use App\Infrastructure\Http\ApiRequest;

class LexicalAnalysisRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'content'       => 'required|string',
            'token_types'   => 'required|array',
            'dfa_minimized' => 'required|boolean'
        ];
    }
}
