<?php

namespace Moonlight\Components;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;
use Moonlight\Models\Rubric;

class RubricNode extends Component
{
    /**
     * @var \Moonlight\Models\Rubric|null
     */
    public $rubric;
    /**
     * @var \Illuminate\Database\Eloquent\Model|null
     */
    public $parentNode;
    /**
     * @var \Illuminate\Database\Eloquent\Model|null
     */
    public $currentElement;

    /**
     * Create a new component instance.
     *
     * @param \Moonlight\Models\Rubric|null $rubric
     * @param \Illuminate\Database\Eloquent\Model|null $parentNode
     * @param \Illuminate\Database\Eloquent\Model|null $currentElement
     */
    public function __construct(Rubric $rubric, ?Model $parentNode, ?Model $currentElement)
    {
        $this->rubric = $rubric;
        $this->parentNode = $parentNode;
        $this->currentElement = $currentElement;
    }

    protected function getCount(Model $parentNode)
    {
        $site = App::make('site');

        $bindings = $this->rubric->getBindingsByClass($site->getClass($parentNode));

        $count = 0;

        foreach ($bindings as $binding) {
            $count += $this->rubric->getElementsCount($binding->className, $parentNode, $binding->clause);
        }

        return $count;
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

        $currentClassId = $this->currentElement ? $site->getClassId($this->currentElement) : null;
        $parentClassId = $this->parentNode ? $site->getClassId($this->parentNode) : null;

        $bindings = $this->parentNode
            ? $this->rubric->getBindingsByClass($site->getClass($this->parentNode))
            : $this->rubric->getRootBindings();
        $elements = [];

        foreach ($bindings as $binding) {
            $item = $site->getItemByClassName($binding->className);
            $mainProperty = $item->getMainProperty();
            $rubricElements = $this->rubric->getElements($binding->className, $this->parentNode, $binding->clause);

            foreach ($rubricElements as $element) {
                $elementClassId = $site->getClassId($element);
                $open = Cache::get("rubric_node_{$loggedUser->id}_{$this->rubric->getName()}_{$elementClassId}", false);
                $count = $this->getCount($element);

                $children = $open && $count
                    ? (new RubricNode($this->rubric, $element, $this->currentElement))->render()
                    : null;

                $elements[] = (object) [
                    'item_name' => $item->getName(),
                    'item_title' => $item->getTitle(),
                    'browse_url' => $site->getBrowseUrl($element),
                    'edit_url' => $site->getEditUrl($element),
                    'class_id' => $site->getClassId($element),
                    'name' => $element->{$mainProperty},
                    'children' => $children,
                    'has_children' => $count,
                ];
            }
        }

        return view('moonlight::components.rubrics.node', [
            'elements' => $elements,
            'name' => $this->rubric->getName(),
            'bind' => 0,
            'currentClassId' => $currentClassId,
            'parentClassId' => $parentClassId,
        ]);
    }
}
