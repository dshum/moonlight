<?php

namespace Moonlight\Controllers;

use Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Moonlight\Main\UserActionType;
use Moonlight\Main\Element;
use Moonlight\Models\Group;
use Moonlight\Models\GroupItemPermission;
use Moonlight\Models\GroupelementPermission;
use Moonlight\Models\User;
use Moonlight\Models\UserAction;
use Carbon\Carbon;

class PermissionController extends Controller
{
    /**
     * Save item permission.
     * 
     * @return Response
     */
    
    public function saveElementPermission(Request $request, $id, $class)
    {
        $scope = [];

        $loggedUser = Auth::guard('moonlight')->user();
        
		$group = Group::find($id);
        
        $classId = $request->input('item');
        $permission = $request->input('permission');
        
        $site = \App::make('site');
        
        $element = Element::getByClassId($classId);
        
        if (! $loggedUser->hasAccess('admin')) {
            $scope['error'] = 'У вас нет прав на управление пользователями.';
        } elseif (! $group) {
            $scope['error'] = 'Группа не найдена.';
        } elseif ($loggedUser->inGroup($group)) {
            $scope['error'] = 'Нельзя редактировать права группы, в которой вы состоите.';
        } elseif (! $element) {
            $scope['error'] = 'Элемент не найден.';
        } elseif (! $group->getPermissionTitle($permission)) {
            $scope['error'] = 'Некорректное право доступа.';
        } else {
            $scope['error'] = null;
        }
        
        if ($scope['error']) {
            return response()->json($scope);
        }
        
        $item = Element::getItem($element);
        
        $defaultPermission = $group->default_permission;
        $itemPermissions = $group->itemPermissions;
        $elementPermission = $group->getElementPermission($classId);
        
        $permissions = [];

		foreach ($itemPermissions as $itemPermission) {
			$permissions[$itemPermission->class] = $itemPermission->permission;
		}

        if (
            $elementPermission
            && isset($permissions[$item->getNameId()]) 
            && $permission == $permissions[$item->getNameId()]
        ) {
            $elementPermission->delete();
        } elseif (
            $elementPermission 
            && $permission == $defaultPermission
        ) {
            $elementPermission->delete();
        } elseif (
            $elementPermission
        ) {
            $elementPermission->permission = $permission;

            $elementPermission->save();
        } else {
            $elementPermission = new GroupElementPermission;

            $elementPermission->group_id = $group->id;
            $elementPermission->class_id = $classId;
            $elementPermission->permission = $permission;

            $elementPermission->save();
        }

        UserAction::log(
            UserActionType::ACTION_TYPE_SAVE_ELEMENT_PERMISSIONS_ID,
            $group->name.', ['.$group->id.']['.$classId.'] = '.$permission
        );
        
        $scope['saved'] = $group->id;

        return response()->json($scope);
    }
    
    /**
     * List of element permissions.
     * 
     * @return Response
     */
    
    public function elementPermissions(Request $request, $id, $class)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();

        if (! $loggedUser->hasAccess('admin')) {
            $scope['state'] = 'error_admin_access_denied';
            return response()->json($scope);
        }

        $group = Group::find($id);

        if (! $group) {
            $scope['state'] = 'error_group_not_found';
            return response()->json($scope);
        }

        $site = \App::make('site');

        $item = $site->getItemByName($class);
        
        if (! $item) {
            $scope['state'] = 'error_item_not_found';
            return response()->json($scope);
        }

        $elementList =
            $item->getClass()->
            orderBy($item->getMainProperty())->
            get();
        
        $elements = [];
        
        foreach ($elementList as $element) {
            $classId = Element::getClassId($element);
            $elements[$classId] = $element;
        }

		$defaultPermission = $group->default_permission;
		$itemPermissions = $group->itemPermissions;
        $elementsPermissions = $group->elementPermissions;

		$permissions = [];

		foreach ($itemPermissions as $itemPermission) {
			$class = $itemPermission->class;
			$permission = $itemPermission->permission;
			$permissions[$class] = $permission;
		}
        
        foreach ($elementsPermissions as $elementsPermission) {
			$classId = $elementsPermission->class_id;
			$permission = $elementsPermission->permission;
			$permissions[$classId] = $permission;
		}
        
        foreach ($elementList as $element) {
            $class = $item->getNameId();
            $classId = Element::getClassId($element);
            
            if (isset($permissions[$classId])) continue;
            
            $permissions[$classId] = isset($permissions[$class])
                ? $permissions[$class] : $defaultPermission;
        }
        
        $scope['group'] = $group;
        $scope['item'] = $item;
        $scope['elements'] = $elements;
		$scope['permissions'] = $permissions;

        return view('moonlight::groupElements', $scope);
    }
    
    /**
     * Save item permission.
     * 
     * @return Response
     */
    
    public function saveItemPermission(Request $request, $id)
    {
        $scope = [];

        $loggedUser = Auth::guard('moonlight')->user();
        
		$group = Group::find($id);
        
        $class = $request->input('item');
        $permission = $request->input('permission');
        
        $site = \App::make('site');
        
        $item = $class ? $site->getItemByName($class) : null;
        
        if ( ! $loggedUser->hasAccess('admin')) {
            $scope['error'] = 'У вас нет прав на управление пользователями.';
        } elseif (! $group) {
            $scope['error'] = 'Группа не найдена.';
        } elseif ($loggedUser->inGroup($group)) {
            $scope['error'] = 'Нельзя редактировать права группы, в которой вы состоите.';
        } elseif (! $item) {
            $scope['error'] = 'Класс элемента не найден.';
        } elseif (! $group->getPermissionTitle($permission)) {
            $scope['error'] = 'Некорректное право доступа.';
        } else {
            $scope['error'] = null;
        }
        
        if ($scope['error']) {
            return response()->json($scope);
        }
        
        $itemPermission = $group->getItemPermission($class);
        
        if ($itemPermission) {
            if ($permission == $group->default_permission) {
                $itemPermission->delete();
            } elseif ($itemPermission->permission != $permission) {
                $itemPermission->permission = $permission;
                
                $itemPermission->save();
            }
        } else {
            if ($permission != $group->default_permission) {
                $itemPermission = new GroupItemPermission();
                
                $itemPermission->group_id = $group->id;
                $itemPermission->class = $class;
                $itemPermission->permission = $permission;
                
                $itemPermission->save();
            }
        }

        UserAction::log(
            UserActionType::ACTION_TYPE_SAVE_ITEM_PERMISSIONS_ID,
            $group->name.', ['.$group->id.']['.$class.'] = '.$permission
        );
        
        $scope['saved'] = $group->id;

        return response()->json($scope);
    }
    
    /**
     * List of item permissions.
     * 
     * @return Response
     */
    public function itemPermissions(Request $request, $id)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();

        if (! $loggedUser->hasAccess('admin')) {
            return redirect()->route('moonlight.home');
        }

        $group = Group::find($id);

        if (! $group) {
            return redirect()->route('moonlight.group');
        }

        $site = \App::make('site');

        $defaultPermission = $group->default_permission
            ? $group->default_permission
            : 'deny';

        $permissionMap = [];

        $items = $site->getItemList();

        foreach ($items as $item) {
            $permissionMap[$item->getNameId()] = $defaultPermission;
        }

        $itemPermissions = $group->itemPermissions;

        foreach ($itemPermissions as $itemPermission) {
            $class = $itemPermission->class;
            $permission = $itemPermission->permission;
            $permissionMap[$class] = $permission;
        }

        $scope['group'] = $group;
        $scope['items'] = $items;
        $scope['permissions'] = $permissionMap;

        return view('moonlight::groupItems', $scope);
    }
}