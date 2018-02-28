<?php

namespace Moonlight\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Moonlight\Main\UserActionType;
use Moonlight\Models\User;
use Moonlight\Models\UserAction;
use Carbon\Carbon;

class LoginController extends Controller
{
    /**
     * Login.
     * 
     * @return Response
     */
    
    public function login(Request $request)
    {
        $scope = [];

		$login = $request->input('login');
        $password = $request->input('password');
        $remember = $request->input('remember');

        $scope['login'] = $login;
        $scope['remember'] = $remember;

		if (! $login) {
			$scope['error'] = 'Введите логин.';
			return response()->json($scope);
		}

		if (! $password) {
			$scope['error'] = 'Введите пароль.';
			return response()->json($scope);
		}

		$user = User::where('login', $login)->first();

		if (! $user) {
			$scope['error'] = 'Неправильный логин или пароль.';
			return response()->json($scope);
		}
        
		if (! password_verify($password, $user->password)) {
			$scope['error'] = 'Неправильный логин или пароль.';
			return response()->json($scope);
		}

		if ($user->banned) {
			$scope['error'] = 'Пользователь заблокирован.';
			return response()->json($scope);
        }
        
        Auth::guard('moonlight')->login($user, $remember);

        $user->last_login = Carbon::now();
        $user->save();
        
        UserAction::log(
			UserActionType::ACTION_TYPE_LOGIN_ID,
			'ID '.$user->id.' ('.$user->login.')'
        );

        cookie()->forever('login', $user->login);
        cookie()->forever('remember', $remember);

        $scope['url'] = route('moonlight.home');

        return response()->json($scope);
    }
    
    /**
     * Logout.
     * 
     * @return Redirect
     */
    
    public function logout(Request $request)
    {
        $loggedUser = Auth::guard('moonlight')->user();
        
        UserAction::log(
			UserActionType::ACTION_TYPE_LOGOUT_ID,
			'ID '.$loggedUser->id.' ('.$loggedUser->login.')'
		);

        Auth::guard('moonlight')->logout();
        
        return redirect()->route('moonlight.login');
    }
    
    /**
     * Login form.
     * 
     * @return View
     */
    
    public function index(Request $request)
    {
        $scope = [];

        $scope['login'] = $request->cookie('login');
        $scope['remember'] = $request->cookie('remember');
        
        return view('moonlight::login', $scope);
    }
}