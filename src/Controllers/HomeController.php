<?php

namespace Moonlight\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Moonlight\Components\WelcomeRubrics;
use Moonlight\Models\FavoriteRubric;
use Moonlight\Models\Favorite;

class HomeController extends Controller
{
    /**
     * Order favorite rubrics
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function orderRubrics(Request $request)
    {
        $loggedUser = Auth::guard('moonlight')->user();

        $orders = $request->input('order');

        if (is_array($orders) && sizeof($orders) > 1) {
            foreach ($orders as $order => $id) {
                $favoriteRubric = FavoriteRubric::find($id);

                if ($favoriteRubric && $favoriteRubric->user_id == $loggedUser->id) {
                    $favoriteRubric->order = $order;
                    $favoriteRubric->save();
                }
            }
        }

        return response()->json(['ordered' => 'ok']);
    }

    /**
     * Order favorites
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function orderFavorites(Request $request)
    {
        $loggedUser = Auth::guard('moonlight')->user();

        $orders = $request->input('order');

        if (is_array($orders) && sizeof($orders) > 1) {
            foreach ($orders as $order => $id) {
                $favorite = Favorite::find($id);

                if ($favorite && $favorite->user_id == $loggedUser->id) {
                    $favorite->order = $order;
                    $favorite->save();
                }
            }
        }

        return response()->json(['ordered' => 'ok']);
    }

    /**
     * Delete favorite rubric
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteRubric(Request $request)
    {
        $loggedUser = Auth::guard('moonlight')->user();

        $rubricId = $request->input('rubric');

        if (! $rubricId) {
            return response()->json(['error' => 'Не указана рубрика.']);
        }

        $favoriteRubric = FavoriteRubric::find($rubricId);

        if (! $favoriteRubric) {
            return response()->json(['error' => 'Рубрика не найдена.']);
        }

        if ($favoriteRubric->user_id != $loggedUser->id) {
            return response()->json(['error' => 'Нет доступа к этой рубрике.']);
        }

        $favoriteCount = Favorite::where('user_id', $loggedUser->id)
            ->where('rubric_id', $favoriteRubric->id)
            ->count();

        if ($favoriteCount) {
            return response()->json(['error' => 'Сначала удалите элементы из этой рубрики.']);
        }

        $favoriteRubric->delete();

        return response()->json(['deleted' => $favoriteRubric->id]);
    }

    /**
     * Delete favorite
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteFavorite(Request $request)
    {
        $loggedUser = Auth::guard('moonlight')->user();

        $favoriteId = $request->input('favorite');

        if (! $favoriteId) {
            return response()->json(['error' => 'Не указан избранный элемент.']);
        }

        $favorite = Favorite::find($favoriteId);

        if (! $favorite) {
            return response()->json(['error' => 'Избранный элемент не найден.']);
        }

        if ($favorite->user_id != $loggedUser->id) {
            return response()->json(['error' => 'Нет доступа к этому элементу.']);
        }

        $favorite->delete();

        return response()->json(['deleted' => $favorite->id]);
    }

    /**
     * Edit favorites.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Request $request)
    {
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $favoriteRubrics = FavoriteRubric::where('user_id', $loggedUser->id)
            ->orderBy('order')
            ->get();

        $favorites = Favorite::where('user_id', $loggedUser->id)
            ->orderBy('order')
            ->get();

        $favoriteMap = [];

        foreach ($favorites as $favorite) {
            $element = $favorite->element;

            if ($element) {
                $item = $site->getItemByElement($element);
                $mainProperty = $item->getMainProperty();

                $favoriteMap[$favorite->rubric_id][] = (object) [
                    'id' => $favorite->id,
                    'classId' => $favorite->class_id,
                    'name' => $element->{$mainProperty},
                ];
            }
        }

        return view('moonlight::favorites', [
            'favoriteRubrics' => $favoriteRubrics,
            'favoriteMap' => $favoriteMap,
        ]);
    }

    /**
     * Show home view.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Throwable
     */
    public function index(Request $request)
    {
        $site = App::make('site');

        // Custom home component
        $homeComponent = $site->getHomeComponent();
        $homeComponentView = $homeComponent ? (new $homeComponent)->render() : null;

        // Rubrics view
        $rubricsView = (new WelcomeRubrics)->render();

        return view('moonlight::home', [
            'homeComponentView' => $homeComponentView,
            'rubricsView' => $rubricsView,
        ]);
    }
}
