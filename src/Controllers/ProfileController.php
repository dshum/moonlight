<?php

namespace Moonlight\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Moonlight\Models\UserActionType;
use Moonlight\Models\UserAction;
use Moonlight\Utils\Image;

class ProfileController extends Controller
{
    const PHOTO_RESIZE_WIDTH = 100;
    const PHOTO_RESIZE_HEIGHT = 100;

    /**
     * Save profile of logged user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
    {
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
            $errors = [];

            foreach ([
                         'first_name',
                         'last_name',
                         'email',
                         'photo',
                     ] as $field) {
                if ($messages->has($field)) {
                    $errors[$field] = $messages->first($field);
                }
            }

            return response()->json(['errors' => $errors]);
        }

        $loggedUser->first_name = $request->input('first_name');
        $loggedUser->last_name = $request->input('last_name');
        $loggedUser->email = $request->input('email');

        // Upload photo
        $assetsPath = $loggedUser->getAssetsPath();
        $folderPath = $loggedUser->getFolderPath();

        if ($request->hasFile('photo')) {
            if ($loggedUser->photoExists()) {
                unlink($loggedUser->getPhotoAbsPath());
                $loggedUser->photo = null;
            }

            $file = $request->file('photo');

            if ($file->isValid() && $file->getMimeType()) {
                $path = $file->getRealPath();
                $extension = $file->getClientOriginalExtension();
                $hash = substr(md5(rand()), 0, 8);
                $filename = sprintf('photo_%s.%s', $hash, $extension);

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
                unlink($loggedUser->getPhotoAbsPath());
                $loggedUser->photo = null;
            }
        }

        $loggedUser->save();

        UserAction::log(
            UserActionType::ACTION_TYPE_SAVE_PROFILE_ID,
            'User.'.$loggedUser->id.', '.$loggedUser->login
        );

        return response()->json([
            'saved' => $loggedUser->id,
            'photo' => $loggedUser->photoExists() ? $loggedUser->getPhotoSrc() : null,
        ]);
    }

    /**
     * Show profile of logged user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $loggedUser = Auth::guard('moonlight')->user();

        return view('moonlight::profile', ['user' => $loggedUser]);
    }
}
