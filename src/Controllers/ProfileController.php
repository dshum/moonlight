<?php

namespace Moonlight\Controllers;

use Log;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Moonlight\Main\UserActionType;
use Moonlight\Models\User;
use Moonlight\Models\UserAction;
use Moonlight\Utils\Image;

class ProfileController extends Controller
{
    const PHOTO_RESIZE_WIDTH = 100;
    const PHOTO_RESIZE_HEIGHT = 100;

    /**
     * Save profile of logged user.
     *
     * @return Response
     */
    public function save(Request $request)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();

        $inputs = $request->all();

        if ($request->hasFile('photo')) {
            $inputs['photo'] = $request->file('photo');
        } else {
            $inputs['photo'] = null;
            unset($inputs['photo']);
        }
        
		$validator = Validator::make($inputs, [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email',
            'photo' => 'mimes:jpeg,pjpeg,png,gif|dimensions:min_width=100,min_height=100|max:1024',            
        ], [
            'first_name.required' => 'Введите имя',
            'first_name.max' => 'Слишком длинное имя',
            'last_name.required' => 'Введите фамилию',
            'last_name.max' => 'Слишком длинная фамилия',
            'email.required' => 'Введите адрес электронной почты',
            'email.email' => 'Некорректный адрес электронной почты',
            'photo.mimes' => 'Допустимый формат файла: jpg, png, gif',
            'photo.dimensions' => 'Минимальный размер изображения: 100x100 пикселей',
            'photo.max' => 'Максимальный размер файла: 1024 Кб',
        ]);
        
        if ($validator->fails()) {
            $messages = $validator->errors();
            
            foreach ([
                'first_name',
                'last_name',
                'email',
                'photo',
            ] as $field) {
                if ($messages->has($field)) {
                    $scope['errors'][$field] = $messages->first($field);
                }
            }
        }
        
        if (isset($scope['errors'])) {
            return response()->json($scope);
        }
        
        $loggedUser->first_name = $request->input('first_name');
        $loggedUser->last_name = $request->input('last_name');
        $loggedUser->email = $request->input('email');

        /*
         * Upload photo
         */
        
        $assetsPath = $loggedUser->getAssetsPath();
        $folderPath = $loggedUser->getFolderPath();
        
        if ($request->hasFile('photo')) {
            if ($loggedUser->photoExists()) {
                try {
                    unlink($loggedUser->getPhotoAbsPath());
                    
                    $loggedUser->photo = null;
                } catch (\Exception $e) {}
            }
        
            $file = $request->file('photo');

            if ($file->isValid() && $file->getMimeType()) {
                $path = $file->getRealPath();
                $extension = $file->getClientOriginalExtension();
                $hash = substr(md5(rand()), 0, 8);
                $filename = sprintf('photo_%s.%s',
                    $hash,
                    $extension
                );

                if (! file_exists($assetsPath)) {
                    mkdir($assetsPath, 0755);
                }     

                if (! file_exists($folderPath)) {
                    mkdir($folderPath, 0755);
                }
                
                Image::resizeAndCopy(
                    $path,
                    $folderPath.$filename,
                    self::PHOTO_RESIZE_WIDTH,
                    self::PHOTO_RESIZE_HEIGHT,
                    100
                );
                
                $loggedUser->photo = $filename;
            }
        } elseif ($request->input('drop')) {
            if ($loggedUser->photoExists()) {
                try {
                    unlink($loggedUser->getPhotoAbsPath());
                    
                    $loggedUser->photo = null;
                } catch (\Exception $e) {}
            }
        } 
        
        $loggedUser->save();
        
        UserAction::log(
			UserActionType::ACTION_TYPE_SAVE_PROFILE_ID,
			'ID '.$loggedUser->id.' ('.$loggedUser->login.')'
		);
        
        $scope['saved'] = $loggedUser->id;
        
        if ($loggedUser->photoExists()) {
            $scope['photo'] = $loggedUser->getPhotoSrc();
        }
        
        return response()->json($scope);
    }
    
    /**
     * Show profile of logged user.
     * 
     * @return View
     */
    public function index(Request $request)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();
        
        $groups = $loggedUser->getGroups();
        
        $scope['login'] = $loggedUser->login;
        $scope['first_name'] = $loggedUser->first_name;
        $scope['last_name'] = $loggedUser->last_name;
        $scope['email'] = $loggedUser->email;
        $scope['created_at'] = $loggedUser->created_at;
        $scope['last_login'] = $loggedUser->last_login;
        $scope['groups'] = $loggedUser->groups;
        
        return view('moonlight::profile', $scope);
    }
}