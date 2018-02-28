<?php

namespace Moonlight\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Moonlight\Main\Element;
use Moonlight\Models\FavoriteRubric;
use Moonlight\Models\Favorite;

class HomeController extends Controller
{
    /**
     * Order favorite rubrics
     *
     * @return Response
     */
    public function orderRubrics(Request $request)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();
        
        $orders = $request->input('order');

        if (
            is_array($orders) 
            && sizeof($orders) > 1
        ) {
            foreach ($orders as $order => $id) {
                $favoriteRubric = FavoriteRubric::find($id);

                if ($favoriteRubric) {
                    $favoriteRubric->order = $order;

                    $favoriteRubric->save();
                }
            }
        }
        
        $scope['ordered'] = 'ok';
        
        return response()->json($scope);
    }

    /**
     * Order favorites
     *
     * @return Response
     */
    public function orderFavorites(Request $request)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();
        
        $orders = $request->input('order');

        if (
            is_array($orders) 
            && sizeof($orders) > 1
        ) {
            foreach ($orders as $order => $id) {
                $favorite = Favorite::find($id);

                if ($favorite) {
                    $favorite->order = $order;

                    $favorite->save();
                }
            }
        }
        
        $scope['ordered'] = 'ok';
        
        return response()->json($scope);
    }

    /**
     * Delete favorite rubric
     *
     * @return Response
     */
    public function deleteRubric(Request $request)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();
        
        $rubricId = $request->input('rubric');
        
        if (! $rubricId) {
            $scope['error'] = 'Не указана рубрика.';
            return response()->json($scope);
        }

        $favoriteRubric = FavoriteRubric::find($rubricId);

        if (! $favoriteRubric) {
            $scope['error'] = 'Рубрика не найдена.';
            return response()->json($scope);
        }

        if ($favoriteRubric->user_id != $loggedUser->id) {
            $scope['error'] = 'Рубрика не найдена.';
            return response()->json($scope);
        }

        $favoriteCount = Favorite::where('user_id', $loggedUser->id)->
            where('rubric_id', $favoriteRubric->id)->
            count();

        if ($favoriteCount) {
            $scope['error'] = 'Сначала удалите элементы из этой рубрики.';
            return response()->json($scope);
        }

        $favoriteRubric->delete();

        $scope['deleted'] = $favoriteRubric->id;
        
        return response()->json($scope);
    }

    /**
     * Delete favorite
     *
     * @return Response
     */
    public function deleteFavorite(Request $request)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();
        
        $favoriteId = $request->input('favorite');
        
        if (! $favoriteId) {
            $scope['error'] = 'Не указан избранный элемент.';
            return response()->json($scope);
        }

        $favorite = Favorite::find($favoriteId);

        if (! $favorite) {
            $scope['error'] = 'Избранный элемент не найден.';
            return response()->json($scope);
        }

        if ($favorite->user_id != $loggedUser->id) {
            $scope['error'] = 'Избранный элемент не найден.';
            return response()->json($scope);
        }

        $favorite->delete();

        $scope['deleted'] = $favorite->id;
        
        return response()->json($scope);
    }

    /**
     * Edit favorites.
     *
     * @return View
     */
    public function edit(Request $request)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();

        $favoriteRubrics = FavoriteRubric::orderBy('order')->get();
        $favorites = [];

        foreach ($favoriteRubrics as $favoriteRubric) {
            $favorites[$favoriteRubric->id] = [];
            
            $favoriteList = Favorite::where('rubric_id', $favoriteRubric->id)->
                orderBy('order')->
                get();

            foreach ($favoriteList as $favorite) {
                $element = $favorite->getElement();

                if ($element) {
                    $item = Element::getItem($element);
                    $mainProperty = $item->getMainProperty();

                    $favorites[$favoriteRubric->id][] = [
                        'id' => $favorite->id,
                        'classId' => $favorite->class_id,
                        'name' => $element->{$mainProperty},
                    ];
                }
            }
        }

        $scope['favoriteRubrics'] = $favoriteRubrics;
        $scope['favorites'] = $favorites;
            
        return view('moonlight::favorites', $scope);
    }

    /**
     * Show home view.
     *
     * @return View
     */
    public function index(Request $request)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();

        $site = \App::make('site');

        /*
         * Home styles and scripts
         */

        $styles = $site->getHomeStyles();
        $scripts = $site->getHomeScripts();

        /*
         * Home plugin
         */
        
        $homePluginView = null;
        
        $homePlugin = $site->getHomePlugin();

        if ($homePlugin) {
            $view = \App::make($homePlugin)->index();

            if ($view) {
                $homePluginView = is_string($view)
                    ? $view : $view->render();
            }
        }
        
        $rubricController = new RubricController;
        
        $rubrics = $rubricController->index();

        $scope['homePluginView'] = $homePluginView;
        $scope['rubrics'] = $rubrics;

        view()->share([
            'styles' => $styles,
            'scripts' => $scripts,
        ]);
            
        return view('moonlight::home', $scope);
    }
}