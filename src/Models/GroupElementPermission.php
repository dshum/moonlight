<?php

namespace Moonlight\Models;

use Illuminate\Database\Eloquent\Model;

class GroupElementPermission extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'admin_group_element_permissions';
    /**
     * @var bool
     */
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'group_id',
        'element_type',
        'element_id',
        'permission',
    ];

    public function element()
    {
        return $this->morphTo();
    }
}
