<?php

namespace App\Api\Phases\Requests;

use App\Infrastructure\Http\ApiRequest;
use Illuminate\Validation\Rule;

class AstRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'files'       => 'required|array',
            'input_type'  => ['required', Rule::in(['parse_tree', 'parsing'])],

            // Either this
            'content'     => 'string',
            'token_types' => 'token_types',
            'grammar'     => 'grammar',

            // or this
            'parse_tree'  => 'array'
        ];
    }
}
