<?php

namespace Moonlight\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Moonlight\Main\UserActionType;
use Moonlight\Models\User;
use Moonlight\Models\UserAction;
use Moonlight\Mail\Reset;
use Carbon\Carbon;

class ResetController extends Controller
{
    protected $resetTable = 'admin_password_resets';

    /**
     * Save password.
     *
     * @return Response
     */
    public function save(Request $request)
    {
        $scope = [];

        $login = $request->input('login');
        $token = $request->input('token');

        if (! $login) {
            $scope['error'] = 'Не указан логин.';
            return response()->json($scope);
        }

        $record = DB::table($this->resetTable)->where('login', $login)->first();

        if (
            ! $record 
            || ! Hash::check($token, $record->token)
        ) {
            $scope['error'] = 'Неверный код сброса пароля.';
            return response()->json($scope);
        }

        $user = User::where('login', $login)->first();

		if (! $user) {
			$scope['error'] = 'Пользователь не найден.';
			return response()->json($scope);
        }
        
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:6|max:25|confirmed',
        ], [
            'password.required' => 'Введите новый пароль.',
            'password.min' => 'Минимальная длина пароля 6 символов.',
            'password.max' => 'Максимальная длина пароля 25 символов.',
            'password.confirmed' => 'Введенные пароли должны совпадать.',
        ]);
        
        if ($validator->fails()) {
            $messages = $validator->errors();

            if ($messages->has('password')) {
                $scope['error'] = $messages->first('password');
                return response()->json($scope);
            }
        }

        DB::table($this->resetTable)->where('login', $user->login)->delete();

        $password = $request->input('password');

        $user->password = password_hash($password, PASSWORD_DEFAULT);
        
        $user->save();
        
        UserAction::log(
			UserActionType::ACTION_TYPE_RESET_PASSWORD_ID,
            'ID '.$user->id.' ('.$user->login.')',
            $user
        );
        
        $scope['ok'] = 'Пароль успешно изменен';
        
        return response()->json($scope);
    }

    /**
     * Restore password.
     *
     * @return Response
     */
    public function send(Request $request)
    {
        $scope = [];
        
        $login = $request->input('login');

        $scope['login'] = $login;

        if (! $login) {
			$scope['error'] = 'Введите логин.';
			return response()->json($scope);
        }
        
        $user = User::where('login', $login)->first();

		if (! $user) {
			$scope['error'] = 'Пользователь не найден.';
			return response()->json($scope);
        }

        $key = Config::get('app.key');

        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        $token = hash_hmac('sha256', Str::random(40), $key);

        DB::table($this->resetTable)->where('login', $user->login)->delete();

        DB::table($this->resetTable)->insert([
            'login' => $user->login,
            'token' => Hash::make($token),
            'created_at' => Carbon::now(),
        ]);
        
        $mailScope = [
            'login' => $user->login,
            'email' => $user->email,
            'token' => $token,
        ];
        
        Mail::send(new Reset($mailScope));

        $scope['ok'] = 'Вам отправлено письмо с дальнейшей инструкцией.';
        
        return response()->json($scope);
    }

    /**
     * New password.
     * 
     * @return View
     */
    public function create(Request $request)
    {
        $scope = [];

        $login = $request->input('login');
        $token = $request->input('token');

        $record = DB::table($this->resetTable)->where('login', $login)->first();

        if (
            ! $record 
            || ! Hash::check($token, $record->token)
        ) {
            $scope['error'] = 'Неверный код сброса пароля.<br>Попробуйте отправить запрос еще раз.';
            $scope['login'] = $request->cookie('login');

            return view('moonlight::password.reset', $scope);
        }

        $scope['login'] = $login;
        $scope['token'] = $token;
        
        return view('moonlight::password.create', $scope);
    }
    
    /**
     * Restore password.
     * 
     * @return View
     */
    public function index(Request $request)
    {
        $scope = [];

        $scope['login'] = $request->cookie('login');
        $scope['error'] = null;
        
        return view('moonlight::password.reset', $scope);
    }
}