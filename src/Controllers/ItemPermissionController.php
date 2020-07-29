<?php

namespace Moonlight\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Moonlight\Middleware\AdminMiddleware;
use Moonlight\Models\UserActionType;
use Moonlight\Models\Group;
use Moonlight\Models\GroupItemPermission;
use Moonlight\Models\UserAction;

class ItemPermissionController extends Controller
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
     * Save item permission.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $groupId
     * @param string $itemName
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $groupId, string $itemName)
    {
        $group = Group::find($groupId);

        if (! $group) {
            return response()->json(['error' => 'Группа не найдена.'], 404);
        }

        $loggedUser = Auth::guard('moonlight')->user();

        if ($loggedUser->inGroup($group)) {
            return response()->json(['error' => 'Нельзя редактировать права группы, в которой вы состоите.'], 403);
        }

        if (! $itemName) {
            return response()->json(['error' => 'Не указан класс элемента.'], 404);
        }

        $site = App::make('site');
        $item = $site->getItemByName($itemName);

        if (! $item) {
            return response()->json(['error' => 'Класс элемента не найден.'], 404);
        }

        $permission = $request->input('permission');

        if (! $permission) {
            return response()->json(['error' => 'Не указаны права доступа.'], 422);
        }

        if (! $group->getPermissionTitle($permission)) {
            return response()->json(['error' => 'Некорректное право доступа.'], 422);
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
     * @param int $groupId
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request, int $groupId)
    {
        $group = Group::find($groupId);

        if (! $group) {
            return redirect()->route('moonlight.groups.index');
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

        return view('moonlight::permissions.items', [
            'group' => $group,
            'items' => $items,
            'permissions' => $permissionMap,
        ]);
    }
}
