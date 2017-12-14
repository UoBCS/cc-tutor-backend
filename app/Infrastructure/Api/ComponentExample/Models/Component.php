<?php

namespace App\Api\{{ pluralCapitalized }}\Models;

use App\Infrastructure\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class {{ singularCapitalized }} extends Model
{
    use Notifiable;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id', 'created_at', 'updated_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id', 'created_at', 'updated_at',
    ];
}
