<?php

namespace Moonlight\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Moonlight\Middleware\AdminMiddleware;
use Moonlight\Models\UserActionType;
use Moonlight\Models\Group;
use Moonlight\Models\UserAction;
use Moonlight\Requests\GroupRequest;

class GroupController extends Controller
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
     * Delete group.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, int $id)
    {
        $group = Group::find($id);

        if (! $group) {
            return response()->json(['error' => 'Группа не найдена.'], 404);
        }

        $loggedUser = Auth::guard('moonlight')->user();

        // Check if user belongs to the group
        if ($loggedUser->inGroup($group)) {
            return response()->json(['error' => 'Нельзя удалить группу, в которой вы состоите.'], 403);
        }

        // Detach users from group
        $group->users()->detach();

        // Delete group
        $group->delete();

        // Log user action
        UserAction::log(
            UserActionType::ACTION_TYPE_DROP_GROUP_ID,
            "Group.{$group->id}, {$group->name}"
        );

        return response()->json(['deleted' => $group->id]);
    }

    /**
     * Store group.
     *
     * @param \Moonlight\Requests\GroupRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(GroupRequest $request)
    {
        // Validate request
        $validated = $request->validated();

        // Store group
        $group = new Group;

        $group->name = $validated['name'];
        $group->default_permission = $validated['default_permission'];

        $group->setPermission('admin', $validated['admin'] ?? false);
        $group->save();

        // Log user action
        UserAction::log(
            UserActionType::ACTION_TYPE_ADD_GROUP_ID,
            "Group.{$group->id}, {$group->name}"
        );

        return response()->json([
            'added' => $group->id,
            'redirect_url' => route('moonlight.groups.index'),
        ]);
    }

    /**
     * Update group.
     *
     * @param \Moonlight\Requests\GroupRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(GroupRequest $request, int $id)
    {
        \Log::info("update $id");
        \Log::info(print_r($request->all(), true));

        $group = Group::find($id);

        if (! $group) {
            return response()->json(['error' => 'Группа не найдена.'], 404);
        }

        $loggedUser = Auth::guard('moonlight')->user();

        // Check if user belongs to the group
        if ($loggedUser->inGroup($group)) {
            return response()->json(['error' => 'Нельзя редактировать группу, в которой вы состоите.'], 403);
        }

        // Validate request
        $validated = $request->validated();

        // Update group
        $group->name = $validated['name'];
        $group->default_permission = $validated['default_permission'];

        $group->setPermission('admin', $validated['admin'] ?? false);
        $group->save();

        // Log user action
        UserAction::log(
            UserActionType::ACTION_TYPE_SAVE_GROUP_ID,
            "Group.{$group->id}, {$group->name}"
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
        $group = Group::find($id);

        if (! $group) {
            return redirect()->route('moonlight.groups.index');
        }

        return view('moonlight::groups.edit', ['group' => $group]);
    }

    /**
     * Group list.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $groups = Group::orderBy('name', 'asc')->get();

        return view('moonlight::groups.index', ['groups' => $groups]);
    }
}
