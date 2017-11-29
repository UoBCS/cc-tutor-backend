<?php

namespace App\Infrastructure\Api\Controllers;

use App\Infrastructure\Http\Controller as BaseController;
//use App\Infrastructure\Version;

/**
 * The controller that deals with root endpoints
 */
class DefaultApiController extends BaseController
{
    public function index()
    {
        return response()->json([
            'title'   => 'CC Tutor API',
            'version' => '1.0.0' //Version::getGitTag()
        ]);
    }
}
