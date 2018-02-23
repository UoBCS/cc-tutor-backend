<?php

namespace App\Api\Users\Requests;

use App\Infrastructure\Http\ApiRequest;
use Illuminate\Validation\Rule;

class SendClassInvitationEmailRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'emails' => 'required|array'
        ];
    }
}
