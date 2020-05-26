<?php

namespace Moonlight\Components;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;
use Moonlight\Models\FavoriteRubric;

class SidebarRubrics extends Component
{
    /**
     * @var \Illuminate\Database\Eloquent\Model|null
     */
    public $currentElement;

    /**
     * Create a new component instance.
     *
     * @param \Illuminate\Database\Eloquent\Model|null $currentElement
     */
    public function __construct(Model $currentElement = null)
    {
        $this->currentElement = $currentElement;
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

        $views = [];

        // Favorite rubrics
        $favoriteRubrics = FavoriteRubric::where('user_id', $loggedUser->id)
            ->orderBy('order')
            ->get();

        foreach ($favoriteRubrics as $favoriteRubric) {
            $open = Cache::get("rubric_{$loggedUser->id}_{$favoriteRubric->id}", false);

            if ($open) {
                $views[$favoriteRubric->id] = (new RubricFavorites($favoriteRubric, $this->currentElement))->render();
            };
        }

        // Common rubrics
        $rubrics = $site->getRubricList();

        foreach ($rubrics as $rubric) {
            $open = Cache::get("rubric_{$loggedUser->id}_{$rubric->getName()}", false);

            if ($open) {
                $views[$rubric->getName()] = (new RubricNode($rubric, null, $this->currentElement))->render();
            }
        }

        $currentClassId = $this->currentElement ? $site->getClassId($this->currentElement) : null;

        return view('moonlight::components.rubrics.sidebar', [
            'favoriteRubrics' => $favoriteRubrics,
            'rubrics' => $rubrics,
            'views' => $views,
            'currentClassId' => $currentClassId,
        ]);
    }
}
