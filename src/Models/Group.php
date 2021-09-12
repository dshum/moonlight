<?php namespace Moonlight\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Moonlight\Main\Item;

class Group extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'admin_groups';
    /**
     * All the relationships to be touched.
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

    public function getDates()
    {
        return ['created_at', 'updated_at'];
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'admin_users_groups_pivot');
    }

    public function hasAccess($name)
    {
        return (bool) $this->getPermission($name);
    }

    public function getDecodedPermissions()
    {
        return json_decode($this->permissions, true);
    }

    public function getPermission($name)
    {
        $permissions = $this->getDecodedPermissions();

        return $permissions[$name] ?? null;
    }

    public function setPermission($name, $value)
    {
        $permissions = json_decode($this->permissions, true);

        $permissions[$name] = $value;

        $this->permissions = json_encode($permissions);

        return $this;
    }

    public function getPermissionTitle()
    {
        $name = $this->default_permission;

        return $this->permissionTitles[$name] ?? null;
    }

    public function itemPermissions()
    {
        return $this->hasMany(GroupItemPermission::class);
    }

    public function elementPermissions()
    {
        return $this->hasMany(GroupElementPermission::class);
    }

    public function getItemPermission(Item $item)
    {
        return $this->itemPermissions()->where('element_type', $item->getClassName())->first();
    }

    public function getElementPermissions()
    {
        return $this->elementPermissions()->get();
    }

    public function getElementPermissionsByItem(Item $item)
    {
        return $this->elementPermissions()->where('element_type', $item->getClassName())->get();
    }

    public function getElementPermission(Model $element)
    {
        $site = App::make('site');

        $item = $site->getItemByElement($element);

        return $this->elementPermissions()
            ->where('element_type', $item->getClassName())
            ->where('element_id', $element->id)
            ->first();
    }

    public function getItemAccess(Item $item)
    {
        $itemPermission = $this->getItemPermission($item);

        if ($itemPermission) {
            return $itemPermission->permission;
        }

        return $this->default_permission;
    }

    public function getElementAccess(Model $element)
    {
        $site = App::make('site');

        $item = $site->getItemByElement($element);
        $elementPermission = $this->getElementPermission($element);

        if ($elementPermission) {
            return $elementPermission->permission;
        }

        return $this->getItemAccess($item);
    }
}
