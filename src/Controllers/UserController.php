<?php

namespace Moonlight\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Moonlight\Mail\Register;
use Moonlight\Middleware\AdminMiddleware;
use Moonlight\Models\UserActionType;
use Moonlight\Models\Group;
use Moonlight\Models\User;
use Moonlight\Models\UserAction;
use Moonlight\Requests\UserRequest;

class UserController extends Controller
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
     * Delete user.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, int $id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['error' => 'Пользователь не найден.'], 404);
        }

        $loggedUser = Auth::guard('moonlight')->user();

        // Check if user tries to delete himself
        if ($user->id === $loggedUser->id) {
            return response()->json(['error' => 'Нельзя удалить свою учетную запись.'], 403);
        }

        // Check if user tries to delete super user
        if ($user->isSuperUser()) {
            return response()->json(['error' => 'Нельзя удалить учетную запись суперпользователя.'], 403);
        }

        // Delete user avatar
        if ($user->photoExists()) {
            unlink($user->getPhotoAbsPath());
        }

        // Detach user from groups
        $user->groups()->detach();

        // Delete user
        $user->delete();

        // Log user action
        UserAction::log(
            UserActionType::ACTION_TYPE_DROP_USER_ID,
            'User.'.$user->id.', '.$user->login
        );

        return response()->json(['deleted' => $user->id]);
    }

    /**
     * Store user.
     *
     * @param \Moonlight\Requests\UserRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(UserRequest $request)
    {
        // Validate request
        $validated = $request->validated();

        // Generate password
        $password = Str::random(8);

        // Store user
        $user = User::create([
            'login' => $validated['login'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'banned' => $validated['banned'] ?? false,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        // Set groups
        $user->groups()->attach($validated['groups'] ?? []);

        // Log user action
        UserAction::log(
            UserActionType::ACTION_TYPE_ADD_USER_ID,
            'User.'.$user->id.', '.$user->login
        );

        // Send mail to the stored user
        Mail::send(new Register([
            'login' => $user->login,
            'password' => $password,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
        ]));

        return response()->json([
            'added' => $user->id,
            'redirect_url' => route('moonlight.users.index'),
        ]);
    }

    /**
     * Update user.
     *
     * @param \Moonlight\Requests\UserRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UserRequest $request, int $id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['error' => 'Пользователь не найден.'], 404);
        }

        $loggedUser = Auth::guard('moonlight')->user();

        // Check if user tries to update himself
        if ($user->id === $loggedUser->id) {
            return response()->json(['error' => 'Нельзя редактировать самого себя.'], 403);
        }

        // Check if user tries to update super user
        if ($user->isSuperUser()) {
            return response()->json(['error' => 'Нельзя редактировать суперпользователя.'], 403);
        }

        // Validate request
        $validated = $request->validated();

        // Update user
        $user->update([
            'login' => $validated['login'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'banned' => $validated['banned'] ?? false,
        ]);

        // Set groups
        $user->groups()->sync($validated['groups'] ?? []);

        // Log user action
        UserAction::log(
            UserActionType::ACTION_TYPE_SAVE_USER_ID,
            'User.'.$user->id.', '.$user->login
        );

        return response()->json(['saved' => $user->id]);
    }

    /**
     * Create user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(Request $request)
    {
        $groups = Group::orderBy('name', 'asc')->get();

        return view('moonlight::users.edit', [
            'user' => null,
            'groups' => $groups,
        ]);
    }

    /**
     * Edit user.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Request $request, int $id)
    {
        $user = User::find($id);

        if (! $user) {
            return redirect()->route('moonlight.users');
        }

        $groups = Group::orderBy('name', 'asc')->get();

        return view('moonlight::users.edit', [
            'user' => $user,
            'groups' => $groups,
        ]);
    }

    /**
     * User list.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $users = User::orderBy('login', 'asc')->with('groups')->get();

        return view('moonlight::users.index', ['users' => $users]);
    }
}
