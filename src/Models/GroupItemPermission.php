<?php

namespace Moonlight\Models;

use Cache;
use Illuminate\Database\Eloquent\Model;

/**
 * Class GroupItemPermission
 *
 * @package Moonlight\Models
 */
class GroupItemPermission extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'admin_group_item_permissions';
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
        'permission',
    ];
}
