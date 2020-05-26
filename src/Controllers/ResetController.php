<?php

namespace Moonlight\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Moonlight\Models\UserActionType;
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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
    {
        $login = $request->input('login');
        $token = $request->input('token');
        $password = $request->input('password');

        if (! $login) {
            return response()->json(['error' => 'Не указан логин.']);
        }

        $record = DB::table($this->resetTable)->where('login', $login)->first();

        if (
            ! $record
            || ! Hash::check($token, $record->token)
        ) {
            return response()->json(['error' => 'Неверный код сброса пароля.']);
        }

        $user = User::where('login', $login)->first();

		if (! $user) {
            return response()->json(['error' => 'Пользователь не найден.']);
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
                return response()->json(['error' => $messages->first('password')]);
            }
        }

        DB::table($this->resetTable)->where('login', $user->login)->delete();

        $user->password = password_hash($password, PASSWORD_DEFAULT);
        $user->save();

        UserAction::log(
			UserActionType::ACTION_TYPE_RESET_PASSWORD_ID,
            'User.'.$user->id.', '.$user->login,
            $user
        );

        return response()->json(['message' => 'Пароль успешно изменен.']);
    }

    /**
     * Restore password.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function send(Request $request)
    {
        $login = $request->input('login');

        if (! $login) {
			return response()->json(['error' => 'Введите логин.']);
        }

        $user = User::where('login', $login)->first();

		if (! $user) {
            return response()->json(['error' => 'Пользователь не найден.']);
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

        Mail::send(new Reset([
            'login' => $user->login,
            'email' => $user->email,
            'token' => $token,
        ]));

        return response()->json(['message' => 'Вам отправлено письмо с дальнейшей инструкцией.']);
    }

    /**
     * New password.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(Request $request)
    {
        $login = $request->input('login');
        $token = $request->input('token');

        $record = DB::table($this->resetTable)->where('login', $login)->first();

        if (
            ! $record
            || ! Hash::check($token, $record->token)
        ) {
            return view('moonlight::password.reset', [
                'error' => 'Неверный код сброса пароля.<br>Попробуйте отправить запрос еще раз.',
                'login' => $request->cookie('login'),
            ]);
        }

        return view('moonlight::password.create', [
            'login' => $login,
            'token' => $token,
        ]);
    }

    /**
     * Restore password.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        return view('moonlight::password.reset', [
            'error' => null,
            'login' => $request->cookie('login'),
        ]);
    }
}
