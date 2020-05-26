<?php

namespace Moonlight\Models;

use Illuminate\Database\Eloquent\Model;

class FavoriteRubric extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'admin_favorite_rubrics';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'order',
    ];
}
