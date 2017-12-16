<?php

namespace App\Infrastructure\Auth\Requests;

use App\Infrastructure\Http\ApiRequest;

class RegisterRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'     => 'required|string',
            'email'    => 'required|email',
            'password' => 'required|string',
            'teacher'  => 'required|boolean'
        ];
    }
}
