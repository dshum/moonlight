<?php

namespace Moonlight\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Moonlight\Middleware\AdminMiddleware;
use Moonlight\Models\UserActionType;
use Moonlight\Models\Group;
use Moonlight\Models\GroupItemPermission;
use Moonlight\Models\GroupelementPermission;
use Moonlight\Models\UserAction;

class ElementPermissionController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(AdminMiddleware::class);
    }

    /**
     * Update item permission.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $groupId
     * @param string $itemName
     * @param int $elementId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $groupId, string $itemName, int $elementId)
    {
        $group = Group::find($groupId);

        if (! $group) {
            return response()->json(['error' => 'Группа не найдена.'], 404);
        }

        $loggedUser = Auth::guard('moonlight')->user();

        if ($loggedUser->inGroup($group)) {
            return response()->json(['error' => 'Нельзя редактировать права группы, в которой вы состоите.'], 403);
        }

        $site = App::make('site');
        $item = $site->getItemByName($itemName);

        if (! $item) {
            return response()->json(['error' => 'Класс элемента не найден.'], 404);
        }

        $element = $item->getClass()->find($elementId);

        if (! $element) {
            return response()->json(['error' => 'Элемент не найден.'], 404);
        }

        $permission = $request->input('permission');

        if (! $permission) {
            return response()->json(['error' => 'Не указаны права доступа.'], 422);
        }

        if (! $group->getPermissionTitle($permission)) {
            return response()->json(['error' => 'Некорректное право доступа.'], 422);
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
     * @param int $groupId
     * @param string $itemName
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request, int $groupId, string $itemName)
    {
        $group = Group::find($groupId);

        if (! $group) {
            return redirect()->route('moonlight.groups.index');
        }

        $site = App::make('site');
        $item = $site->getItemByName($itemName);

        if (! $item) {
            return redirect()->route('moonlight.groups.index');
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

        return view('moonlight::permissions.elements', [
            'group' => $group,
            'item' => $item,
            'elements' => $elements,
            'permissions' => $permissions,
        ]);
    }
}
