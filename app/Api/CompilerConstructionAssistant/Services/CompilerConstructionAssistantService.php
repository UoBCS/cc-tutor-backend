<?php

namespace App\Api\CompilerConstructionAssistant\Services;

use App\Api\CompilerConstructionAssistant\Repositories\CompilerConstructionAssistantRepository;
use HerokuClient\Client as HerokuClient;

class CompilerConstructionAssistantService extends Service
{
    private $repository;
    private $heroku;
    private $herokuAppName;
    private $herokuGitUrl;

    public function __construct(CompilerConstructionAssistantRepository $repository)
    {
        $this->repository = $repository;

        $this->heroku = new HerokuClient([
            'apiKey' => config('cc_tutor.heroku.api_key')
        ]);

        $this->herokuAppName = config('cc_tutor.heroku.app_name');
        $this->herokuGitUrl = $this->heroku->get('apps/' . $this->herokuAppName)->git_url;
    }

    public function subscribeToCourse($user, $cid)
    {
        $this->repository->relateUserAndCourse($user, $cid);

        // Create course directory for user in Heroku

    }
}
