<?php

namespace Moonlight\Components;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Moonlight\Models\Favorite;
use Moonlight\Models\FavoriteRubric;

class WelcomeRubrics extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        // Favorite rubrics
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
                    'class_id' => $site->getClassId($element),
                    'name' => $element->{$mainProperty},
                ];
            }
        }

        // Common rubrics
        $rubrics = $site->getRubricList();
        $rubricElementMap = [];

        foreach ($rubrics as $rubric) {
            $bindings = $rubric->getRootBindings();
            $rubricElementMap[$rubric->getName()] = [];

            foreach ($bindings as $binding) {
                $item = $site->getItemByClassName($binding->className);
                $mainProperty = $item->getMainProperty();
                $elements = $rubric->getElements($binding->className, null, $binding->clause);

                foreach ($elements as $element) {
                    $rubricElementMap[$rubric->getName()][] = (object) [
                        'item' => $item,
                        'class_id' => $site->getClassId($element),
                        'name' => $element->{$mainProperty},
                    ];
                }
            }
        }

        return view('moonlight::components.rubrics.home', [
            'favoriteRubrics' => $favoriteRubrics,
            'favoriteMap' => $favoriteMap,
            'rubrics' => $rubrics,
            'rubricElementMap' => $rubricElementMap,
        ]);
    }
}
