<?php

namespace Moonlight\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Moonlight\Models\UserActionType;
use Moonlight\Models\User;
use Moonlight\Models\UserAction;

class LoginController extends Controller
{
    /**
     * Login.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function login(Request $request)
    {
        $login = $request->input('login');
        $password = $request->input('password');
        $remember = $request->input('remember');

        $scope['login'] = $login;
        $scope['remember'] = $remember;

		if (! $login) {
			return response()->json(['error' => 'Введите логин.']);
		}

		if (! $password) {
            return response()->json(['error' => 'Введите пароль.']);
		}

		$user = User::where('login', $login)->first();

		if (! $user) {
            return response()->json(['error' => 'Неправильный логин или пароль.']);
		}

		if (! password_verify($password, $user->password)) {
            return response()->json(['error' => 'Неправильный логин или пароль.']);
		}

		if ($user->banned) {
            return response()->json(['error' => 'Пользователь заблокирован.']);
        }

        Auth::guard('moonlight')->login($user, $remember);

        $user->last_login = Carbon::now();
        $user->save();

        UserAction::log(
			UserActionType::ACTION_TYPE_LOGIN_ID,
			'User.'.$user->id.', '.$user->login
        );

        Cookie::forever('moonlight_login', $user->login);
        Cookie::forever('moonlight_remember', $remember);

        return response()->json(['url' => route('moonlight.home')]);
    }

    /**
     * Logout.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */

    public function logout(Request $request)
    {
        $loggedUser = Auth::guard('moonlight')->user();

        UserAction::log(
			UserActionType::ACTION_TYPE_LOGOUT_ID,
			'User.'.$loggedUser->id.', '.$loggedUser->login
		);

        Auth::guard('moonlight')->logout();

        return redirect()->route('moonlight.login');
    }

    /**
     * Login form.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */

    public function index(Request $request)
    {
        return view('moonlight::login', [
            'login' => $request->cookie('moonlight_login'),
            'remember' => $request->cookie('moonlight_remember'),
        ]);
    }
}
