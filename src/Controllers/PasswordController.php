<?php

namespace Moonlight\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Moonlight\Main\UserActionType;
use Moonlight\Models\User;
use Moonlight\Models\UserAction;

class PasswordController extends Controller
{
    /**
     * Save passowrd of logged user.
     *
     * @return Response
     */
    public function save(Request $request)
    {
        $scope = [];
        
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
        
        if ($validator->fails()) {
            $messages = $validator->errors();
            
            foreach ([
                'password_old',
                'password',
            ] as $field) {
                if ($messages->has($field)) {
                    $scope['errors'][$field] = $messages->first($field);
                }
            }
        }
        
        $password_old = $request->input('password_old');
        $password = $request->input('password');
        
        if (
            $password_old
            && ! password_verify($password_old, $loggedUser->password)) {
            $scope['errors']['password_old'] = 'Неправильный текущий пароль';
        }
        
        if (isset($scope['errors'])) {
            return response()->json($scope);
        }
        
        if ($password) {
            $loggedUser->password = password_hash($password, PASSWORD_DEFAULT);
        }
        
        $loggedUser->save();
        
        UserAction::log(
			UserActionType::ACTION_TYPE_CHANGE_PASSWORD_ID,
			'ID '.$loggedUser->id.' ('.$loggedUser->login.')'
		);
        
        $scope['saved'] = $loggedUser->id;
        
        return response()->json($scope);
    }
    
    /**
     * Show password of logged user.
     * 
     * @return View
     */
    public function index(Request $request)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();
        
        return view('moonlight::password', $scope);
    }
}