<?php

namespace Moonlight\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Moonlight\Models\UserActionType;
use Moonlight\Models\Group;
use Moonlight\Models\UserAction;

class GroupController extends Controller
{
    /**
     * Delete group.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, int $id)
    {
        $loggedUser = Auth::guard('moonlight')->user();

        $group = Group::find($id);

        if (! $loggedUser->hasAccess('admin')) {
            $error = 'У вас нет прав на управление пользователями.';
        } elseif (! $group) {
            $error = 'Группа не найдена.';
        } elseif ($loggedUser->inGroup($group)) {
            $error = 'Нельзя удалить группу, в которой вы состоите.';
        } else {
            $error = null;
        }

        if ($error) {
            return response()->json(['error' => $error]);
        }

        $group->users()->detach();
        $group->delete();

        UserAction::log(
            UserActionType::ACTION_TYPE_DROP_GROUP_ID,
            'Group.'.$group->id.', '.$group->name
        );

        return response()->json(['group' => $group->id]);
    }

    /**
     * Add group.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        $loggedUser = Auth::guard('moonlight')->user();

        if (! $loggedUser->hasAccess('admin')) {
            return response()->json(['error' => 'У вас нет прав на управление пользователями.']);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'default_permission' => 'required|in:deny,view,update,delete',
        ], [
            'name.required' => 'Введите название',
            'default_permission.required' => 'Укажите доступ к элементам',
            'default_permission.in' => 'Некорректный доступ',
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors();
            $errors = [];

            foreach ([
                         'name',
                         'default_permission',
                     ] as $field) {
                if ($messages->has($field)) {
                    $errors[$field] = $messages->first($field);
                }
            }

            return response()->json(['errors' => $errors]);
        }

        $group = new Group;

        $group->name = $request->input('name');
        $group->default_permission = $request->input('default_permission');

        $group->setPermission('admin', $request->has('admin'));
        $group->save();

        UserAction::log(
            UserActionType::ACTION_TYPE_ADD_GROUP_ID,
            'Group.'.$group->id.', '.$group->name
        );

        return response()->json(['added' => $group->id]);
    }

    /**
     * Save group.
     *
     * @param \Illuminate\Http\Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request, $id)
    {
        $loggedUser = Auth::guard('moonlight')->user();

        $group = Group::find($id);

        if (! $loggedUser->hasAccess('admin')) {
            $error = 'У вас нет прав на управление пользователями.';
        } elseif (! $group) {
            $error = 'Группа не найдена.';
        } elseif ($loggedUser->inGroup($group)) {
            $error = 'Нельзя редактировать группу, в которой вы состоите.';
        } else {
            $error = null;
        }

        if ($error) {
            return response()->json(['error' => $error]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'default_permission' => 'required|in:deny,view,update,delete',
        ], [
            'name.required' => 'Введите название',
            'default_permission.required' => 'Укажите доступ к элементам',
            'default_permission.in' => 'Некорректный доступ',
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors();
            $errors = [];

            foreach ([
                         'name',
                         'default_permission',
                     ] as $field) {
                if ($messages->has($field)) {
                    $errors[$field] = $messages->first($field);
                }
            }

            return response()->json(['errors' => $errors]);
        }

        $group->name = $request->input('name');
        $group->default_permission = $request->input('default_permission');

        $group->setPermission('admin', $request->has('admin'));
        $group->save();

        UserAction::log(
            UserActionType::ACTION_TYPE_SAVE_GROUP_ID,
            'Group.'.$group->id.', '.$group->name
        );

        return response()->json(['saved' => $group->id]);
    }

    /**
     * Create group.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(Request $request)
    {
        $loggedUser = Auth::guard('moonlight')->user();

        if (! $loggedUser->hasAccess('admin')) {
            return redirect()->route('moonlight.home');
        }

        return view('moonlight::groups.edit', ['group' => null]);
    }

    /**
     * Edit group.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Request $request, int $id)
    {
        $loggedUser = Auth::guard('moonlight')->user();

        if (! $loggedUser->hasAccess('admin')) {
            return redirect()->route('moonlight.home');
        }

        $group = Group::find($id);

        if (! $group) {
            return redirect()->route('users');
        }

        return view('moonlight::groups.edit', ['group' => $group]);
    }

    /**
     * Group list.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function groups(Request $request)
    {
        $loggedUser = Auth::guard('moonlight')->user();

        if (! $loggedUser->hasAccess('admin')) {
            return redirect()->route('moonlight.home');
        }

        $groups = Group::orderBy('name', 'asc')->get();

        return view('moonlight::groups.index', ['groups' => $groups]);
    }
}
