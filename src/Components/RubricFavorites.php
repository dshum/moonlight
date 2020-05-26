<?php

namespace Moonlight\Components;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Moonlight\Models\Favorite;
use \Moonlight\Models\FavoriteRubric;

class RubricFavorites extends Component
{
    /**
     * @var \Moonlight\Models\FavoriteRubric|null
     */
    public $rubric;
    /**
     * @var \Illuminate\Database\Eloquent\Model|null
     */
    public $currentElement;

    /**
     * Create a new component instance.
     *
     * @param \Moonlight\Models\FavoriteRubric $rubric
     * @param \Illuminate\Database\Eloquent\Model|null $currentElement
     */
    public function __construct(FavoriteRubric $rubric, ?Model $currentElement)
    {
        $this->rubric = $rubric;
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

        $favoriteList = Favorite::where('user_id', $loggedUser->id)
            ->where('rubric_id', $this->rubric->id)
            ->orderBy('order')
            ->get();

        $favorites = [];

        foreach ($favoriteList as $favorite) {
            $element = $favorite->element;

            if ($element) {
                $item = $site->getItemByElement($element);
                $mainProperty = $item->getMainProperty();

                $favorites[] = (object) [
                    'item_title' => $item->getTitle(),
                    'browse_url' => $site->getBrowseUrl($element),
                    'edit_url' => $site->getEditUrl($element),
                    'class_id' => $site->getClassId($element),
                    'name' => $element->{$mainProperty},
                ];
            }
        }

        $currentClassId = $this->currentElement ? $site->getClassId($this->currentElement) : null;

        return view('moonlight::components.rubrics.favorites', [
            'favorites' => $favorites,
            'currentClassId' => $currentClassId,
        ]);
    }
}
