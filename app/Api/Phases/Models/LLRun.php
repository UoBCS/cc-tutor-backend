<?php

namespace App\Api\Phases\Models;

use Illuminate\Database\Eloquent\Model;

class LLRun extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'll_runs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'content', 'token_types', 'grammar', 'stack', 'input_index'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at',
    ];
}
