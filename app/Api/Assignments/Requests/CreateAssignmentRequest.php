<?php

namespace App\Api\Assignments\Requests;

use App\Infrastructure\Http\ApiRequest;

class CreateAssignmentRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'assignment'             => 'array|required',
            'assignment.title'       => 'required|string',
            'assignment.type'        => 'required|string',
            'assignment.description' => 'required|string'
        ];
    }
}
