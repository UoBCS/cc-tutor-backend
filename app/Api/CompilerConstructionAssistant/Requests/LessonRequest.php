<?php

namespace App\Api\CompilerConstructionAssistant\Requests;

use App\Infrastructure\Http\ApiRequest;

class LessonRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'files' => 'required|array'
        ];
    }
}
