<?php

namespace App\Api\Algorithms\Requests;

use App\Infrastructure\Http\ApiRequest;

class CekMachineRunRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    /*
    cek_machine: {
        control: {
            type: 'VAR',
            name: 'x'
        }
    }
    */

    public function rules()
    {
        return [
            'cek_machine'              => 'required|array',
            'cek_machine.control'      => 'required|array',
            'cek_machine.environment'  => 'required|array',
            'cek_machine.continuation' => 'required|array'
        ];
    }
}
