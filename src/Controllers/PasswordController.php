<?php

namespace Moonlight\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Moonlight\Models\UserActionType;
use Moonlight\Models\UserAction;

class PasswordController extends Controller
{
    /**
     * Save passowrd of logged user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
    {
        $loggedUser = Auth::guard('moonlight')->user();

        $validator = Validator::make($request->all(), [
            'password_old' => 'required',
            'password' => 'required|min:6|max:25|confirmed',
        ], [
            'password_old.required' => 'Введите текущий пароль',
            'password.required' => 'Введите новый пароль',
            'password.min' => 'Минимальная длина пароля 6 символов',
            'password.max' => 'Максимальная длина пароля 25 символов',
            'password.confirmed' => 'Введенные пароли должны совпадать',
        ]);

        $errors = [];

        if ($validator->fails()) {
            $messages = $validator->errors();

            foreach ([
                         'password_old',
                         'password',
                     ] as $field) {
                if ($messages->has($field)) {
                    $errors[$field] = $messages->first($field);
                }
            }
        }

        $password_old = $request->input('password_old');
        $password = $request->input('password');

        if (
            $password_old
            && ! password_verify($password_old, $loggedUser->password)) {
            $errors['password_old'] = 'Неправильный текущий пароль';
        }

        if ($errors) {
            return response()->json(['errors' => $errors]);
        }

        if ($password) {
            $loggedUser->password = password_hash($password, PASSWORD_DEFAULT);
        }

        $loggedUser->save();

        UserAction::log(
            UserActionType::ACTION_TYPE_CHANGE_PASSWORD_ID,
            'User.'.$loggedUser->id.', '.$loggedUser->login
        );

        return response()->json(['saved' => $loggedUser->id]);
    }

    /**
     * Show password of logged user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        return view('moonlight::password');
    }
}
