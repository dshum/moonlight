<?php

namespace Moonlight\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Moonlight\Models\UserActionType;
use Moonlight\Models\Group;
use Moonlight\Models\GroupItemPermission;
use Moonlight\Models\GroupelementPermission;
use Moonlight\Models\UserAction;

class PermissionController extends Controller
{
    /**
     * Save item permission.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @param string $itemName
     * @return \Illuminate\Http\JsonResponse
     */

    public function saveElementPermission(Request $request, int $id, string $itemName)
    {
        $loggedUser = Auth::guard('moonlight')->user();

        if (! $loggedUser->hasAccess('admin')) {
            return response()->json(['error' => 'У вас нет прав на управление пользователями.']);
        }

        $group = Group::find($id);

        if (! $group) {
            return response()->json(['error' => 'Группа не найдена.']);
        }

        if ($loggedUser->inGroup($group)) {
            return response()->json(['error' => 'Нельзя редактировать права группы, в которой вы состоите.']);
        }

        $site = App::make('site');
        $item = $site->getItemByName($itemName);

        if (! $item) {
            return response()->json(['error' => 'Класс элемента не найден.']);
        }

        $elementId = $request->input('item');

        if (! $elementId) {
            return response()->json(['error' => 'Не указан элемент.']);
        }

        $element = $item->getClass()->find($elementId);

        if (! $element) {
            return response()->json(['error' => 'Элемент не найден.']);
        }

        $permission = $request->input('permission');

        if (! $permission) {
            return response()->json(['error' => 'Не указаны права доступа.']);
        }

        if (! $group->getPermissionTitle($permission)) {
            return response()->json(['error' => 'Некорректное право доступа.']);
        }

        $defaultPermission = $group->default_permission;
        $itemPermissions = $group->itemPermissions;
        $elementPermission = $group->getElementPermission($element);

        $permissions = [];

		foreach ($itemPermissions as $itemPermission) {
			$permissions[$itemPermission->element_type] = $itemPermission->permission;
		}

        if (
            $elementPermission
            && isset($permissions[$item->getClassName()])
            && $permission == $permissions[$item->getClassName()]
        ) {
            $elementPermission->delete();
        } elseif (
            $elementPermission
            && $permission == $defaultPermission
        ) {
            $elementPermission->delete();
        } elseif ($elementPermission) {
            $elementPermission->update(['permission' => $permission]);
        } else {
            GroupElementPermission::create([
                'group_id' => $group->id,
                'element_type' => $item->getClassName(),
                'element_id' => $element->id,
                'permission' => $permission,
            ]);
        }

        $classId = $site->getClassId($element);

        UserAction::log(
            UserActionType::ACTION_TYPE_SAVE_ELEMENT_PERMISSIONS_ID,
            "Group.{$group->id}, {$group->name}, $classId, $permission"
        );

        return response()->json(['saved' => $group->id]);
    }

    /**
     * List of element permissions.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @param string $itemName
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */

    public function elementPermissions(Request $request, int $id, string $itemName)
    {
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        if (! $loggedUser->hasAccess('admin')) {
            return redirect()->route('moonlight.home');
        }

        $group = Group::find($id);

        if (! $group) {
            return redirect()->route('moonlight.groups');
        }

        $item = $site->getItemByName($itemName);

        if (! $item) {
            return redirect()->route('moonlight.groups');
        }

        $elements = $item->getClass()->orderBy('id')->get();

		$defaultPermission = $group->default_permission;
		$itemPermissions = $group->itemPermissions;
        $elementsPermissions = $group->elementPermissions;

		$permissions = [];

		foreach ($itemPermissions as $itemPermission) {
			$class = $itemPermission->element_type;
			$permission = $itemPermission->permission;
			$permissions[$class] = $permission;
		}

        foreach ($elementsPermissions as $elementsPermission) {
			$element_id = $elementsPermission->element_id;
			$permission = $elementsPermission->permission;
			$permissions[$element_id] = $permission;
		}

        foreach ($elements as $element) {
            $class = $item->getClassName();

            if (empty($permissions[$element->id])) {
                $permissions[$element->id] = $permissions[$class] ?? $defaultPermission;
            }
        }

        return view('moonlight::groups.elements', [
            'group' => $group,
            'item' => $item,
            'elements' => $elements,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Save item permission.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */

    public function saveItemPermission(Request $request, int $id)
    {
        $loggedUser = Auth::guard('moonlight')->user();

        if (! $loggedUser->hasAccess('admin')) {
            return response()->json(['error' => 'У вас нет прав на управление пользователями.']);
        }

		$group = Group::find($id);

		if (! $group) {
            return response()->json(['error' => 'Группа не найдена.']);
        }

        if ($loggedUser->inGroup($group)) {
            return response()->json(['error' => 'Нельзя редактировать права группы, в которой вы состоите.']);
        }

        $itemName = $request->input('item');

        if (! $itemName) {
            return response()->json(['error' => 'Не указан класс элемента.']);
        }

        $site = App::make('site');
        $item = $site->getItemByName($itemName);

        if (! $item) {
            return response()->json(['error' => 'Класс элемента не найден.']);
        }

        $permission = $request->input('permission');

        if (! $permission) {
            return response()->json(['error' => 'Не указаны права доступа.']);
        }

        if (! $group->getPermissionTitle($permission)) {
            return response()->json(['error' => 'Некорректное право доступа.']);
        }

        $itemPermission = $group->getItemPermission($item);

        if ($itemPermission) {
            if ($permission == $group->default_permission) {
                $itemPermission->delete();
            } elseif ($itemPermission->permission != $permission) {
                $itemPermission->update(['permission' => $permission]);
            }
        } else {
            if ($permission != $group->default_permission) {
                GroupItemPermission::create([
                    'group_id' => $group->id,
                    'element_type' => $item->getClassName(),
                    'permission' => $permission,
                ]);
            }
        }

        UserAction::log(
            UserActionType::ACTION_TYPE_SAVE_ITEM_PERMISSIONS_ID,
            "Group.{$group->id}, {$group->name}, $itemName, $permission"
        );

        return response()->json(['saved' => $group->id]);
    }

    /**
     * List of item permissions.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function itemPermissions(Request $request, int $id)
    {
        $loggedUser = Auth::guard('moonlight')->user();

        if (! $loggedUser->hasAccess('admin')) {
            return redirect()->route('moonlight.home');
        }

        $group = Group::find($id);

        if (! $group) {
            return redirect()->route('moonlight.groups');
        }

        $site = App::make('site');
        $items = $site->getItemList();

        $defaultPermission = $group->default_permission ?: 'deny';
        $itemPermissions = $group->itemPermissions;

        $permissionMap = [];

        foreach ($items as $item) {
            $permissionMap[$item->getClassName()] = $defaultPermission;
        }

        foreach ($itemPermissions as $itemPermission) {
            $class = $itemPermission->element_type;
            $permission = $itemPermission->permission;
            $permissionMap[$class] = $permission;
        }

        return view('moonlight::groups.items', [
            'group' => $group,
            'items' => $items,
            'permissions' => $permissionMap,
        ]);
    }
}
