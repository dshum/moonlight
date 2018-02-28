<?php namespace Moonlight\Models;

use Illuminate\Database\Eloquent\Model;
use Moonlight\Main\Site;
use Moonlight\Main\Item;
use Moonlight\Main\Element;

class Group extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'admin_groups';

    /**
	 * The user groups pivot table name.
	 *
	 * @var string
	 */
	protected $pivotTable = 'admin_users_groups_pvot';

	/**
     * All of the relationships to be touched.
     *
     * @var array
     */
	protected $touches = ['users'];

    private $permissionTitles = [
        'deny' => 'Доступ закрыт',
        'view' => 'Просмотр элементов',
        'update' => 'Изменение элементов',
        'delete' => 'Удаление элементов',
	];
	
	public static function boot()
	{
		parent::boot();

		if (method_exists(cache()->getStore(), 'tags')) {
			static::created(function($element) {
				cache()->tags('admin_groups')->flush();
			});
	
			static::saved(function($element) {
				cache()->tags('admin_groups')->flush();
			});
	
			static::deleted(function($element) {
				cache()->tags('admin_groups')->flush();
			});
		}
    }

    public function getDates()
	{
		return array('created_at', 'updated_at');
	}

	public function users()
	{
		return $this->belongsToMany('Moonlight\Models\User', $this->pivotTable);
	}

    public function getUsers()
	{
		if (method_exists(cache()->getStore(), 'tags')) {
			$group = $this;

			return cache()->tags('admin_users')->remember("admin_group_{$group->id}_users", 1440, function() use ($group) {
				return $group->users()->get();
			});
		}

		return $this->users()->get();
	}

	public function hasAccess($name)
	{
		return $this->getPermission($name) ? true : false;
	}

	public function getUnserializedPermissions()
	{
		try {
			return unserialize($this->permissions);
		} catch (\Exception $e) {}

		return null;
	}

	public function getPermission($name)
	{
		$unserializedPermissions = $this->getUnserializedPermissions();

		return
			isset($unserializedPermissions[$name])
			? $unserializedPermissions[$name]
			: null;
	}

	public function setPermission($name, $value)
	{
		$unserializedPermissions = $this->getUnserializedPermissions();

		$unserializedPermissions[$name] = $value;

		$permissions = serialize($unserializedPermissions);

		$this->permissions = $permissions;

		return $this;
	}
    
    public function getPermissionTitle()
    {
        $name = $this->default_permission;
        
        return isset($this->permissionTitles[$name])
            ? $this->permissionTitles[$name]
            : null;
    }

	public function itemPermissions()
	{
		return $this->hasMany('Moonlight\Models\GroupItemPermission');
	}

	public function elementPermissions()
	{
		return $this->hasMany('Moonlight\Models\GroupElementPermission');
	}

	public function getItemPermission($class)
	{
		if (method_exists(cache()->getStore(), 'tags')) {
			$group = $this;

			$permissions = cache()->tags('admin_item_permissions')->remember("permission_where_group_{$group->id}_and_item_{$class}", 1440, function() use ($group, $class) {
				return $group->itemPermissions()->where('class', $class)->get();
			});

			return isset($permissions[0]) ? $permissions[0] : null;
		}

		return $this->itemPermissions()->where('class', $class)->first();
	}

	public function getElementPermissions()
	{
		if (method_exists(cache()->getStore(), 'tags')) {
			$group = $this;

			return cache()->tags('admin_element_permissions')->remember("permissions_where_group_{$group->id}", 1440, function() use ($group) {
				return $group->elementPermissions()->get();
			});
		}

		return $this->elementPermissions()->get();
	}

	public function getElementPermission($classId)
	{
		if (method_exists(cache()->getStore(), 'tags')) {
			$group = $this;

			$permissions = cache()->tags('admin_element_permissions')->remember("permission_where_group_{$group->id}_and_element_{$classId}", 1440, function() use ($group, $classId) {
				return $group->elementPermissions()->where('class_id', $classId)->get();
			});

			return isset($permissions[0]) ? $permissions[0] : null;
		}

		return $this->elementPermissions()->where('class_id', $classId)->first();
	}

	public function getItemAccess(Item $item)
	{
		$itemPermission = $this->getItemPermission($item->getNameId());

		if ($itemPermission) return $itemPermission->permission;

		return $this->default_permission;
	}

	public function getElementAccess(Model $element)
	{
		$elementPermission = $this->getElementPermission(Element::getClassId($element));

		if ($elementPermission) return $elementPermission->permission;

		$itemPermission = $this->getItemPermission(Element::getClass($element));

		if ($itemPermission) return $itemPermission->permission;

		return $this->default_permission;
	}

}
