<?php namespace Moonlight\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Moonlight\Main\Item;

class User extends Authenticatable
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'admin_users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'login',
        'password',
        'first_name',
        'last_name',
        'email',
        'banned',
    ];
    /**
     * Assets folder.
     *
     * @var string
     */
    protected $assetsName = 'assets';

    public static function boot()
    {
        parent::boot();

        if (method_exists(Cache::getStore(), 'tags')) {
            static::created(function () {
                Cache::tags('admin_users')->flush();
            });

            static::saved(function () {
                Cache::tags('admin_groups')->flush();
                Cache::tags('admin_users')->flush();
            });

            static::deleting(function (User $user) {
                $user->groups()->detach();
            });

            static::deleted(function () {
                Cache::tags('admin_groups')->flush();
                Cache::tags('admin_users')->flush();
            });
        }
    }

    /**
     * @return array
     */
    public function getDates()
    {
        return ['created_at', 'updated_at', 'last_login'];
    }

    /**
     * @return bool
     */
    public function isSuperUser()
    {
        return (bool) $this->super_user;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'admin_users_groups_pivot');
    }

    /**
     * @param \Moonlight\Models\Group $group
     * @return mixed
     */
    public function inGroup(Group $group)
    {
        return $this->groups->contains($group->id);
    }

    /**
     * @return mixed|null
     */
    public function getUnserializedParameters()
    {
        try {
            return unserialize($this->parameters);
        } catch (\Exception $e) {
        }

        return null;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getParameter($name)
    {
        $unserializedParameters = $this->getUnserializedParameters();

        return $unserializedParameters[$name] ?? null;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function setParameter($name, $value)
    {
        try {
            $unserializedParameters        = $this->getUnserializedParameters();
            $unserializedParameters[$name] = $value;
            $this->parameters              = serialize($unserializedParameters);

            $this->save();
        } catch (\Exception $e) {
        }

        return $this;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasAccess($name)
    {
        if ($this->isSuperUser()) {
            return true;
        }

        foreach ($this->groups as $group) {
            if ($group->hasAccess($name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Moonlight\Main\Item $item
     * @return bool
     */
    public function hasViewDefaultAccess(Item $item)
    {
        if ($this->isSuperUser()) {
            return true;
        }

        foreach ($this->groups as $group) {
            $access = $group->getItemAccess($item);

            if (in_array($access, ['view', 'update', 'delete'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Moonlight\Main\Item $item
     * @return bool
     */
    public function hasUpdateDefaultAccess(Item $item)
    {
        if ($this->isSuperUser()) {
            return true;
        }

        foreach ($this->groups as $group) {
            $access = $group->getItemAccess($item);

            if (in_array($access, ['update', 'delete'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Moonlight\Main\Item $item
     * @return bool
     */
    public function hasDeleteDefaultAccess(Item $item)
    {
        if ($this->isSuperUser()) {
            return true;
        }

        foreach ($this->groups as $group) {
            $access = $group->getItemAccess($item);

            if ($access == 'delete') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $element
     * @return bool
     */
    public function hasViewAccess(Model $element)
    {
        if ($this->isSuperUser()) {
            return true;
        }

        foreach ($this->groups as $group) {
            $access = $group->getElementAccess($element);

            if (in_array($access, ['view', 'update', 'delete'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $element
     * @return bool
     */
    public function hasUpdateAccess(Model $element)
    {
        if ($this->isSuperUser()) {
            return true;
        }

        foreach ($this->groups as $group) {
            $access = $group->getElementAccess($element);

            if (in_array($access, ['update', 'delete'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $element
     * @return bool
     */
    public function hasDeleteAccess(Model $element)
    {
        if ($this->isSuperUser()) {
            return true;
        }

        foreach ($this->groups as $group) {
            $access = $group->getElementAccess($element);

            if ($access == 'delete') {
                return true;
            }
        }

        return false;
    }

    public function getItemList()
    {
        $site = App::make('site');

        $viewPermissions       = new Collection();
        $viewDefaultPermission = false;

        foreach ($this->groups as $group) {
            $viewPermissions = $viewPermissions->merge($group->itemPermissions->filter(function ($item) {
                return in_array($item->permission, ['view', 'update', 'delete']);
            })->transform(function ($item) {
                return $item->element_type;
            }));

            if (in_array($group->default_permission, ['view', 'update', 'delete'])) {
                $viewDefaultPermission = true;
            }
        }

        return $site->getItemList()->filter(function ($item) use ($viewPermissions, $viewDefaultPermission) {
            if ($this->isSuperUser()) {
                return true;
            } elseif ($viewPermissions->contains($item->getClass())) {
                return true;
            }

            return $viewDefaultPermission;
        });
    }

    /**
     * @return string
     */
    public function getAssetsName()
    {
        return $this->assetsName;
    }

    /**
     * @return string
     */
    public function getAssets()
    {
        return $this->getAssetsName();
    }

    /**
     * @return string
     */
    public function getAssetsPath()
    {
        return
            public_path()
            .DIRECTORY_SEPARATOR
            .$this->getAssets()
            .DIRECTORY_SEPARATOR;
    }

    /**
     * @return string
     */
    public function getFolderName()
    {
        return $this->getTable();
    }

    /**
     * @return string
     */
    public function getFolder()
    {
        return
            $this->getAssetsName()
            .DIRECTORY_SEPARATOR
            .$this->getFolderName();
    }

    /**
     * @return string
     */
    public function getFolderPath()
    {
        return
            public_path()
            .DIRECTORY_SEPARATOR
            .$this->getFolder()
            .DIRECTORY_SEPARATOR;
    }

    /**
     * @return mixed
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @return bool
     */
    public function photoExists()
    {
        return $this->getPhoto() && file_exists($this->getPhotoAbsPath());
    }

    /**
     * @return string|null
     */
    public function getPhotoAbsPath()
    {
        return $this->getPhoto()
            ? $this->getFolderPath().$this->getPhoto()
            : null;
    }

    /**
     * @return string|null
     */
    public function getPhotoSrc()
    {
        return
            $this->getPhoto()
            && file_exists($this->getPhotoAbsPath())
                ? asset($this->getFolder().DIRECTORY_SEPARATOR.$this->getPhoto())
                : null;
    }

    /**
     * @return bool|false|string|string[]|null
     */
    public function getInitialsAttribute()
    {
        return mb_strtoupper(
            mb_substr($this->first_name, 0, 1)
            .' '
            .mb_substr($this->last_name, 0, 1)
        );
    }

    /**
     * @return string
     */
    public function getHexColorAttribute()
    {
        $c = ['4', '6', '8', 'A', 'C'];

        $code = base_convert(crc32("{$this->first_name} {$this->last_name}"), 10, 5);
        $code = substr($code, -3, 3);
        $code = '#'.$c[$code[0]].$c[$code[1]].$c[$code[2]];

        return $code;
    }
}
