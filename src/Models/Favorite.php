<?php

namespace Moonlight\Models;

use App;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'admin_favorites';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'rubric_id',
        'order',
        'element_type',
        'element_id',
    ];

    public function element()
    {
        return $this->morphTo();
    }

    public function rubric()
    {
        return $this->belongsTo(FavoriteRubric::class);
    }
}
