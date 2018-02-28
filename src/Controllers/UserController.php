<?php

namespace Moonlight\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Moonlight\Main\UserActionType;
use Moonlight\Models\Group;
use Moonlight\Models\User;
use Moonlight\Models\UserAction;
use Moonlight\Mail\Register;

class UserController extends Controller
{
    /**
     * Delete user.
     *
     * @return Response
     */
    public function delete(Request $request, $id)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();
        
		$user = User::find($id);
        
        if ( ! $loggedUser->hasAccess('admin')) {
            $scope['error'] = 'У вас нет прав на управление пользователями.';
        } elseif ( ! $user) {
            $scope['error'] = 'Пользователь не найден.';
        } elseif ($user->id == $loggedUser->id) {
            $scope['error'] = 'Нельзя удалить самого себя.';
        } elseif ($user->isSuperUser()) {
            $scope['error'] = 'Нельзя удалить суперпользователя.';
        } else {
            $scope['error'] = null;
        }
        
        if ($scope['error']) {
            return response()->json($scope);
        }

        if ($user->photoExists()) {
            try {
                unlink($user->getPhotoAbsPath());
            } catch (\Exception $e) {}
        }
        
        $user->delete();

        UserAction::log(
			UserActionType::ACTION_TYPE_DROP_USER_ID,
			'ID '.$user->id.' ('.$user->login.')'
		);
        
        $scope['user'] = $user->id;
        
        return response()->json($scope);
    }
    
    /**
     * Add user.
     *
     * @return Response
     */
    public function add(Request $request)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();
        
        if ( ! $loggedUser->hasAccess('admin')) {
            $scope['error'] = 'У вас нет прав на управление пользователями.';
        } else {
            $scope['error'] = null;
        }
        
        if ($scope['error']) {
            return response()->json($scope);
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
            $messages = $validator->errors();
            
            foreach ([
                'login',
                'first_name',
                'last_name',
                'email',
            ] as $field) {
                if ($messages->has($field)) {
                    $scope['errors'][$field] = $messages->first($field);
                }
            }
        }
        
        if (isset($scope['errors'])) {
            return response()->json($scope);
        }   
        
        $user = new User;

        $user->login = $request->input('login');
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->email = $request->input('email');
        
        $banned = $request->has('banned') ? true : false;
        $user->banned = $banned;

        /*
         * Set password
         */
        
        $password = Str::random(8);
        $user->password = password_hash($password, PASSWORD_DEFAULT);
        
        $user->save();
        
        /*
         * Set groups
         */
        
        $groups = $request->input('groups');

        if ($groups) {
            foreach ($groups as $id) {
                $group = Group::find($id); 
                
                if ($group) {
                    $user->addGroup($group);
                }
            }
        }
        
        UserAction::log(
			UserActionType::ACTION_TYPE_ADD_USER_ID,
			'ID '.$user->id.' ('.$user->login.')'
        );

        $mailScope = [
            'login' => $user->login,
            'password' => $password,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
        ];
        
        Mail::send(new Register($mailScope));
        
        $scope['added'] = $user->id;
        
        return response()->json($scope);
    }
    
    /**
     * Save user.
     *
     * @return Response
     */
    public function save(Request $request, $id)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();
        
		$user = User::find($id);
        
        if ( ! $loggedUser->hasAccess('admin')) {
            $scope['error'] = 'У вас нет прав на управление пользователями.';
        } elseif ( ! $user) {
            $scope['error'] = 'Пользователь не найден.';
        } elseif ($user->id == $loggedUser->id) {
            $scope['error'] = 'Нельзя редактировать самого себя.';
        } elseif ($user->isSuperUser()) {
            $scope['error'] = 'Нельзя редактировать суперпользователя.';
        } else {
            $scope['error'] = null;
        }
        
        if ($scope['error']) {
            return response()->json($scope);
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
            $messages = $validator->errors();
            
            foreach ([
                'login',
                'first_name',
                'last_name',
                'email',
            ] as $field) {
                if ($messages->has($field)) {
                    $scope['errors'][$field] = $messages->first($field);
                }
            }
        }
        
        if (isset($scope['errors'])) {
            return response()->json($scope);
        }

        $user->login = $request->input('login');
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->email = $request->input('email');

        $banned = $request->has('banned') ? true : false;
        $user->banned = $banned;
        
        /*
         * Set groups
         */
        
        $groups = $request->input('groups');
        
        $userGroups = $user->getGroups();
        
        foreach ($userGroups as $group) {
			if ( ! $groups || ! in_array($group->id, $groups)) {
				$user->removeGroup($group);
			}
		}

        if ($groups) {
            foreach ($groups as $id) {
                $group = Group::find($id); 
                
                if ($group) {
                    $user->addGroup($group);
                }
            }
        }
        
        $user->save();
        
        UserAction::log(
			UserActionType::ACTION_TYPE_SAVE_USER_ID,
			'ID '.$user->id.' ('.$user->login.')'
		);
        
        $scope['saved'] = $user->id;
        
        return response()->json($scope);
    }
    
    /**
     * Create user.
     * 
     * @return View
     */
    public function create(Request $request)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();
        
        if ( ! $loggedUser->hasAccess('admin')) {
            return redirect()->route('moonlight.home');
        }
        
        $groups = Group::orderBy('name', 'asc')->get();
        
        $scope['user'] = null;
        $scope['groups'] = $groups;
        $scope['userGroups'] = [];
        
        return view('moonlight::user', $scope);
    }
    
    /**
     * Edit user.
     * 
     * @return View
     */
    public function edit(Request $request, $id)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();
        
        if ( ! $loggedUser->hasAccess('admin')) {
            return redirect()->route('moonlight.home');
        }
        
        $user = User::find($id);
        
        if ( ! $user) {
            return redirect()->route('moonlight.users');
        }
        
        $groups = Group::orderBy('name', 'asc')->get();

        $userGroups = [];
        
        foreach ($user->getGroups() as $group) {
            $userGroups[$group->id] = $group->id;
        }
        
        $scope['user'] = $user;
        $scope['groups'] = $groups;
        $scope['userGroups'] = $userGroups;
        
        return view('moonlight::user', $scope);
    }

    /**
     * User list.
     * 
     * @return View
     */
    public function users(Request $request)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();
        
        if ( ! $loggedUser->hasAccess('admin')) {
            return redirect()->route('moonlight.home');
        }
        
        $groups = Group::orderBy('name', 'asc')->get();
        $users = User::orderBy('login', 'asc')->get();
        
        foreach ($users as $user) {
            $userGroups[$user->id] = $user->getGroups();
        }
        
        $scope['groups'] = $groups;
        $scope['users'] = $users;
        $scope['userGroups'] = $userGroups;
        
        return view('moonlight::users', $scope);
    }
}