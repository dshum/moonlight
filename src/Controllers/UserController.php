<?php

namespace Moonlight\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Moonlight\Mail\Register;
use Moonlight\Models\UserActionType;
use Moonlight\Models\Group;
use Moonlight\Models\User;
use Moonlight\Models\UserAction;

class UserController extends Controller
{
    /**
     * Delete user.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, int $id)
    {
        $loggedUser = Auth::guard('moonlight')->user();

        $user = User::find($id);

        if (! $loggedUser->hasAccess('admin')) {
            $error = 'У вас нет прав на управление пользователями.';
        } elseif (! $user) {
            $error = 'Пользователь не найден.';
        } elseif ($user->id == $loggedUser->id) {
            $error = 'Нельзя удалить свою учетную запись.';
        } elseif ($user->isSuperUser()) {
            $error = 'Нельзя удалить учетную запись суперпользователя.';
        } else {
            $error = null;
        }

        if ($error) {
            return response()->json(['error' => $error]);
        }

        if ($user->photoExists()) {
            unlink($user->getPhotoAbsPath());
        }

        $user->groups()->detach();
        $user->delete();

        UserAction::log(
            UserActionType::ACTION_TYPE_DROP_USER_ID,
            'User.'.$user->id.', '.$user->login
        );

        return response()->json(['user' => $user->id]);
    }

    /**
     * Add user.
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
            'login' => 'required|max:25',
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email',
            'groups' => 'array',
        ], [
            'login.required' => 'Введите логин',
            'login.max' => 'Слишком длинный логин',
            'first_name.required' => 'Введите имя',
            'first_name.max' => 'Слишком длинное имя',
            'last_name.required' => 'Введите фамилию',
            'last_name.max' => 'Слишком длинная фамилия',
            'email.required' => 'Введите адрес электронной почты',
            'email.email' => 'Некорректный адрес электронной почты',
            'groups.array' => 'Некорректные группы',
        ]);

        if ($validator->fails()) {
            $errors = [];
            $messages = $validator->errors();

            foreach ([
                         'login',
                         'first_name',
                         'last_name',
                         'email',
                     ] as $field) {
                if ($messages->has($field)) {
                    $errors[$field] = $messages->first($field);
                }
            }

            return response()->json(['errors' => $errors]);
        }

        $password = Str::random(8);

        $user = User::create([
            'login' => $request->input('login'),
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'email' => $request->input('email'),
            'banned' => $request->has('banned'),
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        // Set groups
        $groups = $request->input('groups');

        $user->groups()->attach($groups);

        UserAction::log(
            UserActionType::ACTION_TYPE_ADD_USER_ID,
            'User.'.$user->id.', '.$user->login
        );

        Mail::send(new Register([
            'login' => $user->login,
            'password' => $password,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
        ]));

        return response()->json(['added' => $user->id]);
    }

    /**
     * Save user.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request, int $id)
    {
        $loggedUser = Auth::guard('moonlight')->user();

        $user = User::find($id);

        if (! $loggedUser->hasAccess('admin')) {
            $error = 'У вас нет прав на управление пользователями.';
        } elseif (! $user) {
            $error = 'Пользователь не найден.';
        } elseif ($user->id == $loggedUser->id) {
            $error = 'Нельзя редактировать самого себя.';
        } elseif ($user->isSuperUser()) {
            $error = 'Нельзя редактировать суперпользователя.';
        } else {
            $error = null;
        }

        if ($error) {
            return response()->json(['error' => $error]);
        }

        $validator = Validator::make($request->all(), [
            'login' => 'required|max:25',
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email',
            'groups' => 'array',
        ], [
            'login.required' => 'Введите логин',
            'login.max' => 'Слишком длинный логин',
            'first_name.required' => 'Введите имя',
            'first_name.max' => 'Слишком длинное имя',
            'last_name.required' => 'Введите фамилию',
            'last_name.max' => 'Слишком длинная фамилия',
            'email.required' => 'Введите адрес электронной почты',
            'email.email' => 'Некорректный адрес электронной почты',
            'groups.array' => 'Некорректные группы',
        ]);

        if ($validator->fails()) {
            $errors = [];
            $messages = $validator->errors();

            foreach ([
                         'login',
                         'first_name',
                         'last_name',
                         'email',
                     ] as $field) {
                if ($messages->has($field)) {
                    $errors[$field] = $messages->first($field);
                }
            }

            return response()->json(['errors' => $errors]);
        }

        $user->update([
            'login' => $request->input('login'),
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'email' => $request->input('email'),
            'banned' => $request->has('banned'),
        ]);

        // Set groups
        $groups = $request->input('groups');

        $user->groups()->sync($groups);

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
        $loggedUser = Auth::guard('moonlight')->user();

        if (! $loggedUser->hasAccess('admin')) {
            return redirect()->route('moonlight.home');
        }

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
        $loggedUser = Auth::guard('moonlight')->user();

        if (! $loggedUser->hasAccess('admin')) {
            return redirect()->route('moonlight.home');
        }

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
    public function users(Request $request)
    {
        $loggedUser = Auth::guard('moonlight')->user();

        if (! $loggedUser->hasAccess('admin')) {
            return redirect()->route('moonlight.home');
        }

        $users = User::orderBy('login', 'asc')->with('groups')->get();

        return view('moonlight::users.index', ['users' => $users]);
    }
}
