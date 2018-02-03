<?php

namespace App\Api\CompilerConstructionAssistant\Repositories;

class CompilerConstructionAssistantRepository
{
    public function relateUserAndCourse($user, $cid, $data = ['lesson_id' => 1])
    {
        $user->courses()->attach($cid, $data);
    }

    public function unrelateUserAndCourse($user, $cid)
    {
        $user->courses()->detach($cid);
    }
}
