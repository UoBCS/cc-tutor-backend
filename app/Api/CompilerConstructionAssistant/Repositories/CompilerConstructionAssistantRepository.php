<?php

namespace App\Api\CompilerConstructionAssistant\Repositories;

class CompilerConstructionAssistantRepository
{
    public function relateUserAndCourse($user, $cid)
    {
        $user->courses()->attach($cid);
    }
}
