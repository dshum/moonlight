<?php

namespace Moonlight\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Moonlight\Components\SidebarRubrics;
use Moonlight\Models\UserActionType;
use Moonlight\Models\FavoriteRubric;
use Moonlight\Models\Favorite;
use Moonlight\Models\UserAction;
use Moonlight\Properties\OrderProperty;
use Moonlight\Properties\FileProperty;
use Moonlight\Properties\ImageProperty;
use Moonlight\Properties\ManyToManyProperty;
use Moonlight\Properties\PasswordProperty;
use Moonlight\Properties\VirtualProperty;

class EditController extends Controller
{
    /**
     * Copy an element.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $classId
     * @return \Illuminate\Http\JsonResponse
     */
    public function copy(Request $request, string $classId)
    {
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $element = $site->getByClassId($classId);

        if (! $element) {
            return response()->json(['error' => 'Элемент не найден.']);
        } elseif (! $loggedUser->hasViewAccess($element)) {
            return response()->json(['error' => 'Нет прав на копирование элемента.']);
        }

        $currentItem = $site->getItemByElement($element);

        $clone = new $element;

        $name = $request->input('name');
        $value = $request->input('value');

        $propertyList = $currentItem->getPropertyList();

        foreach ($propertyList as $propertyName => $property) {
            if ($property instanceof OrderProperty) {
                $property->setElement($clone)->set();
                continue;
            } elseif (
                $property instanceof ManyToManyProperty
                || $property instanceof VirtualProperty
                || ($property->getReadonly() && ! $property->getRequired())
                || ($property instanceof FileProperty && ! $property->getRequired())
                || ($property instanceof ImageProperty && ! $property->getRequired())
                || $propertyName == 'created_at'
                || $propertyName == 'updated_at'
                || $propertyName == 'deleted_at'
            ) {
                continue;
            } elseif (
                $property->isOneToOne()
                && $propertyName == $name
                && ($value || ! $property->getRequired())
            ) {
                $clone->$propertyName = $value ? $value : null;
                continue;
            }

            $clone->$propertyName = $element->$propertyName;
        }

        $clone->save();

        UserAction::log(
            UserActionType::ACTION_TYPE_COPY_ELEMENT_ID,
            $site->getClassId($element).' -> '.$site->getClassId($clone)
        );

        return response()->json([
            'copied' => $site->getClassId($clone),
            'url' => $site->getEditUrl($clone),
        ]);
    }

    /**
     * Move an element.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $classId
     * @return \Illuminate\Http\JsonResponse
     */
    public function move(Request $request, string $classId)
    {
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $element = $site->getByClassId($classId);

        if (! $element) {
            return response()->json(['error' => 'Элемент не найден.']);
        } elseif (! $loggedUser->hasUpdateAccess($element)) {
            return response()->json(['error' => 'Нет прав на изменение элемента.']);
        }

        $currentItem = $site->getItemByElement($element);

        $name = $request->input('name');
        $value = $request->input('value');

        $propertyList = $currentItem->getPropertyList();
        $changes = [];
        $changed = false;

        foreach ($propertyList as $propertyName => $property) {
            if (
                ! $property->getHidden()
                && ! $property->getReadonly()
                && $property->isOneToOne()
                && $propertyName == $name
                && ($value || ! $property->getRequired())
            ) {
                $changes[] = "$propertyName={$element->$propertyName} -> $propertyName=$value";
                $element->$propertyName = $value ?: null;
                $changed = true;
            }
        }

        if ($changed) {
            $element->save();

            UserAction::log(
                UserActionType::ACTION_TYPE_MOVE_ELEMENT_ID,
                $classId.': '.implode(', ', $changes)
            );

            return response()->json([
                'moved' => $classId,
            ]);
        }

        return response()->json([]);
    }

    /**
     * Set favorite.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $classId
     * @return \Illuminate\Http\JsonResponse
     */
    public function favorite(Request $request, string $classId)
    {
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $element = $site->getByClassId($classId);

        if (! $element) {
            return response()->json(['error' => 'Элемент не найден.']);
        } elseif (! $loggedUser->hasViewAccess($element)) {
            return response()->json(['error' => 'Нет прав на добавление элемента в избранное.']);
        }

        $currentItem = $site->getItemByElement($element);

        $addRubric = $request->input('add_favorite_rubric');
        $removeRubric = $request->input('remove_favorite_rubric');
        $newRubric = $request->input('new_favorite_rubric');

        $favoriteRubrics = FavoriteRubric::where('user_id', $loggedUser->id)->orderBy('order')->get();

        $favorites = Favorite::where('user_id', $loggedUser->id)
            ->where('element_type', $currentItem->getClassName())
            ->where('element_id', $element->id)
            ->orderBy('order')
            ->get();

        $favoritesAll = Favorite::where('user_id', $loggedUser->id)->get();

        $selectedRubrics = [];
        $rubricOrders = [];
        $favoriteOrders = [];

        foreach ($favorites as $favorite) {
            $selectedRubrics[$favorite->rubric_id] = $favorite->rubric;
        }

        foreach ($favoriteRubrics as $favoriteRubric) {
            $rubricOrders[] = $favoriteRubric->order;

            if ($newRubric == $favoriteRubric->name) {
                $addRubric = $favoriteRubric->id;
                $newRubric = null;
            }
        }

        foreach ($favoritesAll as $favorite) {
            $favoriteOrders[$favorite->rubric_id][] = $favorite->order;
        }

        if ($addRubric && ! isset($selectedRubrics[$addRubric])) {
            $nextOrder = isset($favoriteOrders[$addRubric]) && sizeof($favoriteOrders[$addRubric])
                ? max($favoriteOrders[$addRubric]) + 1
                : 1;

            Favorite::create([
                'user_id' => $loggedUser->id,
                'rubric_id' => $addRubric,
                'element_type' => $currentItem->getClassName(),
                'element_id' => $element->id,
                'order' => $nextOrder,
            ]);

            return response()->json(['added' => $addRubric]);
        }

        if (
            $removeRubric
            && isset($selectedRubrics[$removeRubric])
        ) {
            foreach ($favorites as $favorite) {
                if ($favorite->rubric_id == $removeRubric) {
                    $favorite->delete();

                    return response()->json(['removed' => $removeRubric]);
                }
            }
        }

        if ($newRubric) {
            $nextOrder =
                isset($rubricOrders)
                && sizeof($rubricOrders)
                    ? max($rubricOrders) + 1
                    : 1;

            $favoriteRubric = FavoriteRubric::create([
                'user_id' => $loggedUser->id,
                'name' => $newRubric,
                'order' => $nextOrder,
            ]);

            Favorite::create([
                'user_id' => $loggedUser->id,
                'rubric_id' => $favoriteRubric->id,
                'element_type' => $currentItem->getClassName(),
                'element_id' => $element->id,
                'order' => 1,
            ]);

            return response()->json([
                'new' => [
                    'id' => $favoriteRubric->id,
                    'name' => $newRubric,
                ],
            ]);
        }

        return response()->json([]);
    }

    /**
     * Delete element.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $classId
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, string $classId)
    {
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $element = $site->getByClassId($classId);

        if (! $element) {
            return response()->json(['error' => 'Элемент не найден.']);
        } elseif (! $loggedUser->hasDeleteAccess($element)) {
            return response()->json(['error' => 'Нет прав на удаление элемента.']);
        }

        $currentItem = $site->getItemByElement($element);
        $itemList = $site->getItemList();

        foreach ($itemList as $item) {
            $propertyList = $item->getPropertyList();

            foreach ($propertyList as $property) {
                if (
                    $property->isOneToOne()
                    && $property->getRelatedClass() == $currentItem->getClassName()
                ) {
                    $count = $element->hasMany($item->getClassName(), $property->getName())->count();

                    if ($count) {
                        return response()->json(['error' => 'Сначала удалите связанные элементы.']);
                    }
                }
            }
        }

        if ($element->delete()) {
            UserAction::log(
                UserActionType::ACTION_TYPE_DROP_ELEMENT_TO_TRASH_ID,
                $classId
            );

            if (Cache::has("trash_item_{$currentItem->getName()}")) {
                Cache::forget("trash_item_{$currentItem->getName()}");
            }

            $historyUrl = Cache::get("history_url_{$loggedUser->id}");
            $elementUrl = route('moonlight.browse.element', $classId);

            if (! $historyUrl || $historyUrl == $elementUrl) {
                $parent = $site->getParent($element);
                $historyUrl = $parent
                    ? $site->getBrowseUrl($parent)
                    : route('moonlight.browse');
            }

            return response()->json([
                'deleted' => $classId,
                'url' => $historyUrl,
            ]);
        }

        return response()->json(['error' => 'Не удалось удалить элемент.']);
    }

    /**
     * Add element.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $itemName
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function add(Request $request, string $itemName)
    {
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $currentItem = $site->getItemByName($itemName);

        if (! $currentItem) {
            return response()->json(['error' => 'Класс элемента не найден.']);
        }

        $element = $currentItem->getClass();
        $propertyList = $currentItem->getPropertyList();

        $inputs = [];
        $rules = [];
        $messages = [];

        foreach ($propertyList as $propertyName => $property) {
            if ($property->getHidden() || $property->getReadonly()) {
                continue;
            }

            $value = $property->setRequest($request)->buildInput();

            if ($value !== null) {
                $inputs[$propertyName] = $value;
            }

            foreach ($property->getRules() as $rule => $message) {
                $rules[$propertyName][] = $rule;

                if (strpos($rule, ':')) {
                    [$name, $value] = explode(':', $rule, 2);
                    $messages[$propertyName.'.'.$name] = $message;
                } else {
                    $messages[$propertyName.'.'.$rule] = $message;
                }
            }
        }

        $validator = Validator::make($inputs, $rules, $messages);

        if ($validator->fails()) {
            $messages = $validator->errors();
            $errors = [];

            foreach ($propertyList as $propertyName => $property) {
                if ($messages->has($propertyName)) {
                    $errors[$propertyName] = $messages->first($propertyName);
                }
            }

            return response()->json(['errors' => $errors]);
        }

        foreach ($propertyList as $propertyName => $property) {
            if (
                $property instanceof OrderProperty
                || $property instanceof PasswordProperty
                || (! $property->getHidden() && ! $property->getReadonly())
            ) {
                $property->setRequest($request)->setElement($element)->set();
            }
        }

        $element->save();

        foreach ($propertyList as $propertyName => $property) {
            if (! $property->getHidden() && ! $property->getReadonly()) {
                $property->setElement($element)->setAfterCreate();
            }
        }

        UserAction::log(
            UserActionType::ACTION_TYPE_ADD_ELEMENT_ID,
            $site->getClassId($element)
        );

        $historyUrl = Cache::get("history_url_{$loggedUser->id}");

        if (! $historyUrl) {
            $parent = $site->getParent($element);
            $historyUrl = $parent
                ? $site->getBrowseUrl($parent)
                : route('moonlight.browse');
        }

        return response()->json([
            'added' => $site->getClassId($element),
            'url' => $historyUrl,
        ]);
    }

    /**
     * Save element.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $classId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function save(Request $request, string $classId)
    {
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $element = $site->getByClassId($classId);

        if (! $element) {
            return response()->json(['error' => 'Элемент не найден.']);
        } elseif (! $loggedUser->hasUpdateAccess($element)) {
            return response()->json(['error' => 'Нет прав на изменение элемента.']);
        }

        $currentItem = $site->getItemByElement($element);
        $propertyList = $currentItem->getPropertyList();

        $inputs = [];
        $rules = [];
        $messages = [];

        foreach ($propertyList as $propertyName => $property) {
            if (
                $property->getHidden()
                || $property->getReadonly()
            ) {
                continue;
            }

            $value = $property->setRequest($request)->buildInput();

            if ($value !== null) {
                $inputs[$propertyName] = $value;
            }

            foreach ($property->getRules() as $rule => $message) {
                $rules[$propertyName][] = $rule;

                if (strpos($rule, ':')) {
                    [$name, $value] = explode(':', $rule, 2);
                    $messages[$propertyName.'.'.$name] = $message;
                } else {
                    $messages[$propertyName.'.'.$rule] = $message;
                }
            }
        }

        $validator = Validator::make($inputs, $rules, $messages);

        if ($validator->fails()) {
            $messages = $validator->errors();
            $errors = [];

            foreach ($propertyList as $propertyName => $property) {
                if ($messages->has($propertyName)) {
                    $errors[$propertyName] = $messages->first($propertyName);
                }
            }

            return response()->json(['errors' => $errors]);
        }

        foreach ($propertyList as $propertyName => $property) {
            if (! $property->getHidden() && ! $property->getReadonly()) {
                $property->setRequest($request)->setElement($element)->set();
            }
        }

        $element->save();

        UserAction::log(
            UserActionType::ACTION_TYPE_SAVE_ELEMENT_ID,
            $site->getClassId($element)
        );

        $views = [];

        foreach ($propertyList as $property) {
            if (
                $property->getHidden()
                || ! $property->refresh()
            ) {
                continue;
            }

            $propertyScope = $property->setElement($element)->getEditView();

            $views[$property->getName()] = view(
                'moonlight::properties.'.$property->getClassName().'.edit', $propertyScope
            )->render();
        }

        return response()->json([
            'saved' => $classId,
            'views' => $views,
        ]);
    }

    /**
     * Create element.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $classId
     * @param string $itemName
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * @throws \Throwable
     */
    public function create(Request $request, string $classId, string $itemName)
    {
        $site = App::make('site');

        $currentItem = $site->getItemByName($itemName);

        if (! $currentItem) {
            return redirect()->route('moonlight.browse');
        }

        $element = $currentItem->getClass();

        $parentClassId = $classId !== 'root' ? $classId : null;
        $parentElement = $parentClassId ? $site->getByClassId($parentClassId) : null;

        if ($parentClassId && ! $parentElement) {
            return redirect()->route('moonlight.browse');
        }

        $parents = [];

        if ($parentElement) {
            $parentList = $site->getParentList($parentElement);
            $parentList[] = $parentElement;

            foreach ($parentList as $parent) {
                $parentItem = $site->getItemByElement($parent);
                $parentMainProperty = $parentItem->getMainProperty();
                $parents[] = (object) [
                    'class_id' => $site->getClassId($parent),
                    'name' => $parent->$parentMainProperty,
                ];
            }
        }

        // Views
        $propertyList = $currentItem->getPropertyList();

        $properties = [];
        $views = [];

        foreach ($propertyList as $property) {
            if (
                ! $property->getHidden()
                && $property->getName() != 'deleted_at'
            ) {
                $properties[] = $property;
            }
        }

        foreach ($properties as $property) {
            $property->setElement($element);

            if ($parentElement) {
                $property->setRelation($parentElement);
            }

            $views[$property->getName()] = view(
                'moonlight::properties.'.$property->getClassName().'.edit', $property->getEditView()
            )->render();
        }

        // Rubrics
        $rubrics = (new SidebarRubrics($parentElement))->render();

        return view('moonlight::create', [
            'classId' => $parentClassId,
            'element' => $element,
            'parents' => $parents,
            'currentItem' => $currentItem,
            'views' => $views,
            'rubrics' => $rubrics,
        ]);
    }

    /**
     * Edit element.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $classId
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * @throws \Throwable
     */
    public function edit(Request $request, string $classId)
    {
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $element = $site->getByClassId($classId);

        if (! $element) {
            return redirect()->route('moonlight.browse');
        } elseif (! $loggedUser->hasViewAccess($element)) {
            return redirect()->route('moonlight.browse');
        }

        $currentItem = $site->getItemByElement($element);

        $parentElement = $site->getParent($element);
        $parentClass = $parentElement ? $site->getClass($parentElement) : null;

        $parentList = $site->getParentList($element);
        $parents = [];

        foreach ($parentList as $parent) {
            $parentItem = $site->getItemByElement($parent);
            $parentMainProperty = $parentItem->getMainProperty();
            $parents[] = (object) [
                'class_id' => $site->getClassId($parent),
                'name' => $parent->$parentMainProperty,
            ];
        }

        // Views
        $mainProperty = $currentItem->getMainProperty();
        $propertyList = $currentItem->getPropertyList();

        $properties = [];
        $views = [];

        foreach ($propertyList as $property) {
            if ($property->getHidden()) {
                continue;
            }
            if ($property->getName() == 'deleted_at') {
                continue;
            }

            $properties[] = $property;
        }

        foreach ($properties as $property) {
            $propertyScope = $property->setElement($element)->getEditView();

            $views[$property->getName()] = view(
                'moonlight::properties.'.$property->getClassName().'.edit', $propertyScope
            )->render();
        }

        // Copy and move views
        $copyPropertyView = null;
        $movePropertyView = null;

        foreach ($propertyList as $property) {
            if (
                $property->getHidden()
                || ! $property->isOneToOne()
            ) {
                continue;
            }

            if (
                ($parentClass && $property->getRelatedClass() == $parentClass)
                || (! $parentClass && $property->getParent())
            ) {
                $propertyScope = $property->setElement($element)->getEditView();
                $propertyScope['mode'] = 'edit';

                $copyPropertyView = view(
                    'moonlight::properties.'.$property->getClassName().'.copy', $propertyScope
                )->render();

                $movePropertyView = view(
                    'moonlight::properties.'.$property->getClassName().'.move', $propertyScope
                )->render();

                break;
            }
        }

        if (! $copyPropertyView && $currentItem->getRoot()) {
            $copyPropertyView = 'Корень сайта';
        }

        // Rubrics
        $rubrics = (new SidebarRubrics($element))->render();

        // Favorites
        $favoriteRubrics = FavoriteRubric::where('user_id', $loggedUser->id)
            ->orderBy('order')
            ->get();

        $favorites = Favorite::where('user_id', $loggedUser->id)
            ->where('element_type', $currentItem->getClassName())
            ->where('element_id', $element->id)
            ->get();

        $elementFavoriteRubrics = [];

        foreach ($favorites as $favorite) {
            $elementFavoriteRubrics[$favorite->rubric_id] = $favorite->rubric_id;
        }

        return view('moonlight::edit', [
            'element' => $element,
            'classId' => $classId,
            'mainProperty' => $mainProperty,
            'parents' => $parents,
            'currentItem' => $currentItem,
            'views' => $views,
            'movePropertyView' => $movePropertyView,
            'copyPropertyView' => $copyPropertyView,
            'rubrics' => $rubrics,
            'favoriteRubrics' => $favoriteRubrics,
            'elementFavoriteRubrics' => $elementFavoriteRubrics,
        ]);
    }
}
