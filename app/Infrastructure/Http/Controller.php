<?php
namespace App\Infrastructure\Http;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Auth;
use Optimus\Bruno\LaravelController;

/**
 * Base controller
 */
abstract class Controller extends LaravelController
{
    /**
     * Get the authenticated in user
     *
     * @return \App\Api\Users\Models\User
     */
    public function user()
    {
        return Auth::user();
    }
}
