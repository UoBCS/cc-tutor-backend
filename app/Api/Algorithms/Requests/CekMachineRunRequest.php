<?php

namespace App\Api\Algorithms\Requests;

use App\Infrastructure\Http\ApiRequest;

class CekMachineRunRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'cek_machine'              => 'required|array',
            'cek_machine.control'      => 'required',
            'cek_machine.environment'  => 'array',
            'cek_machine.continuation' => 'array'
        ];
    }
}
