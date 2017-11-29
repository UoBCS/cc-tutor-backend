<?php

namespace App\Api\Users\Repositories;

use App\Api\Users\Models\User;
use App\Infrastructure\Http\Crud\Repository;

class UserRepository extends Repository
{
    public function getModel()
    {
        return new User();
    }

    public function create($data)
    {
        $user = $this->getModel();

        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        $user->fill($data);

        $user->save();

        return $user;
    }
}

