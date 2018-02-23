<?php

namespace App\Api\Users\Repositories;

use App\Api\Users\Exceptions\StudentAlreadyAttendingClass;
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

    public function relateStudentAndTeacher($uid)
    {
        $teacher = $this->user->users()->where('teacher_id', $uid)->first();

        if ($teacher !== null) {
            throw new StudentAlreadyAttendingClass();
        }

        $this->user->users()->attach($uid);
    }
}

