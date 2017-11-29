<?php

namespace App\Infrastructure\Http;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Base class to deal with API form requests
 */
abstract class ApiRequest extends FormRequest
{
    /**
     * Manage failed validation
     *
     * @param  Validator $validator
     * @return void
     */
    protected function failedValidation(Validator $validator)
    {
        throw new UnprocessableEntityHttpException($validator->errors()->toJson());
    }

    /**
     * Manage failed authorization
     *
     * @return void
     */
    protected function failedAuthorization()
    {
        throw new HttpException(403);
    }
}
