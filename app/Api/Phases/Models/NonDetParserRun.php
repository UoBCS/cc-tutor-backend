<?php

namespace App\Api\Phases\Models;

use Illuminate\Database\Eloquent\Model;

class NonDetParserRun extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'non_det_parser_runs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'content', 'token_types', 'grammar', 'stack', 'input_index', 'parse_tree'
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
