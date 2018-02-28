<?php 

namespace Moonlight\Models;

use Illuminate\Database\Eloquent\Model;

class GroupElementPermission extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'admin_group_element_permissions';

	public $timestamps = false;

	public static function boot()
	{
		parent::boot();

		if (method_exists(cache()->getStore(), 'tags')) {
			static::created(function($element) {
				cache()->tags('admin_element_permissions')->flush();
			});
	
			static::saved(function($element) {
				cache()->tags('admin_element_permissions')->flush();
			});
	
			static::deleted(function($element) {
				cache()->tags('admin_element_permissions')->flush();
			});
		}
    }
}
