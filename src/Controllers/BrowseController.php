<?php

namespace Moonlight\Controllers;

use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Moonlight\Components\SidebarRubrics;
use Moonlight\Main\Item;
use Moonlight\Main\Site;
use Moonlight\Models\User;
use Moonlight\Models\UserActionType;
use Moonlight\Models\Favorite;
use Moonlight\Models\FavoriteRubric;
use Moonlight\Models\UserAction;
use Throwable;

/**
 * Class BrowseController
 *
 * @package Moonlight\Controllers
 */
class BrowseController extends Controller
{
    /**
     * Default number of elements per page.
     */
    const PER_PAGE = 10;
    /**
     * Maximum number of elements per page.
     */
    const MAX_PER_PAGE = 500;

    /**
     * Show/hide column.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function column(Request $request): JsonResponse
    {
        /** @var User $loggedUser */
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $itemName = $request->input('item');
        $propertyName = $request->input('name');
        $show = $request->input('show');

        $currentItem = $site->getItemByName($itemName);

        if (! $currentItem) {
            return response()->json(['error' => 'Класс элементов не найден.']);
        }

        $property = $currentItem->getPropertyByName($propertyName);

        if (! $property) {
            return response()->json(['error' => 'Свойство элемента не найдено.']);
        }

        if ($show == 'true') {
            if ($property->getShow()) {
                Cache::forget("show_column_{$loggedUser->id}_{$itemName}_{$propertyName}");
            } else {
                Cache::forever("show_column_{$loggedUser->id}_{$itemName}_{$propertyName}", 1);
            }
        } else {
            if (! $property->getShow()) {
                Cache::forget("show_column_{$loggedUser->id}_{$itemName}_{$propertyName}");
            } else {
                Cache::forever("show_column_{$loggedUser->id}_{$itemName}_{$propertyName}", 0);
            }
        }

        return response()->json(['column' => $show]);
    }

    /**
     * Set per page.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function perPage(Request $request): JsonResponse
    {
        /** @var User $loggedUser */
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $itemName = $request->input('item');
        $classId = $request->input('classId') ?? 'search';
        $perPage = (int) $request->input('perpage');

        if ($perPage < 1) {
            $perPage = static::PER_PAGE;
        } elseif ($perPage > static::MAX_PER_PAGE) {
            $perPage = static::PER_PAGE;
        }

        $currentItem = $site->getItemByName($itemName);

        if (! $currentItem) {
            return response()->json(['error' => 'Класс элементов не найден.']);
        }

        if ($currentItem->getPerPage() == $perPage) {
            Cache::forget("per_page_{$loggedUser->id}_{$itemName}");
        } else {
            Cache::forever("per_page_{$loggedUser->id}_{$itemName}", $perPage);
        }

        Cache::forget("page_{$loggedUser->id}_{$classId}_{$itemName}");

        return response()->json(['per_page' => $perPage]);
    }

    /**
     * Order elements.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function order(Request $request): JsonResponse
    {
        /** @var User $loggedUser */
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $itemName = $request->input('item');
        $classId = $request->input('class_id');

        $currentItem = $site->getItemByName($itemName);

        if (! $currentItem) {
            return response()->json(['error' => 'Класс элементов не найден.']);
        }

        $currentElement = $classId ? $site->getByClassId($classId) : null;
        $currentElementClass = $currentElement ? $site->getClass($currentElement) : null;
        $currentElementClassId = $classId ?: 'Root';

        $propertyList = $currentItem->getPropertyList();

        $orderProperty = null;
        $relatedMethod = null;

        foreach ($propertyList as $property) {
            if (! $property->isOrder()) {
                continue;
            }

            $relatedProperty = $property->getRelatedProperty()
                ? $currentItem->getPropertyByName($property->getRelatedProperty())
                : null;
            $relatedClass = $relatedProperty ? $relatedProperty->getRelatedClass() : null;

            if ($relatedClass && $relatedClass != $currentElementClass) {
                continue;
            }

            $orderProperty = $property->getName();

            if ($relatedProperty && $relatedProperty->isManyToMany()) {
                $relatedMethod = $relatedProperty->getRelatedMethod();

                if ($relatedProperty->getOrderField()) {
                    $orderProperty = $relatedProperty->getOrderField();
                }
            }

            break;
        }

        if (! $orderProperty) {
            return response()->json(['error' => 'Поле для ручной сортировки не найдено.']);
        }

        $elements = $request->input('elements');

        if (! is_array($elements) || sizeof($elements) < 2) {
            return response()->json(['error' => 'Недостаточно элементов для ручной сортировки.']);
        }

        $ordered = [];

        foreach ($elements as $order => $id) {
            $element = $currentItem->getClass()->find($id);

            if (! $element || ! $loggedUser->hasUpdateAccess($element)) {
                continue;
            }

            if ($currentElement && $relatedMethod) {
                $currentElement->{$relatedMethod}()->updateExistingPivot($id, [$orderProperty => $order]);
            } else {
                $element->{$orderProperty} = $order;
                $element->save();
            }

            $ordered[$order] = $site->getClassId($element);
        }

        if (sizeof($ordered)) {
            UserAction::log(
                UserActionType::ACTION_TYPE_ORDER_ELEMENT_LIST_ID,
                $currentElementClassId.': '.implode(', ', $ordered)
            );
        }

        return response()->json(['ordered' => sizeof($ordered)]);
    }

    /**
     * Save elements.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function save(Request $request): JsonResponse
    {
        /** @var User $loggedUser */
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $itemName = $request->input('item');

        $currentItem = $site->getItemByName($itemName);

        if (! $currentItem) {
            return response()->json(['error' => 'Класс элементов не найден.']);
        }

        $propertyList = $currentItem->getPropertyList();

        $editing = $request->input('editing');

        if (! is_array($editing) || ! sizeof($editing)) {
            return response()->json(['error' => 'Пустой список элементов.']);
        }

        $elements = [];
        $saved = [];
        $properties = [];
        $views = [];
        $errors = [];

        foreach ($propertyList as $property) {
            if (! $property->getHidden()) {
                $properties[] = $property;
            }
        }

        foreach ($editing as $id => $fields) {
            $element = $currentItem->getClass()->find($id);

            if (
                $element && $loggedUser->hasUpdateAccess($element)
                && is_array($fields) && sizeof($fields)
            ) {
                $elements[] = $element;
            }
        }

        if (! sizeof($elements)) {
            return response()->json(['error' => 'Нет элементов для редактирования.']);
        }

        foreach ($elements as $element) {
            $inputs = [];
            $rules = [];
            $messages = [];

            foreach ($properties as $property) {
                if (! array_key_exists($property->getName(), $editing[$element->id])) {
                    continue;
                }

                $name = $property->getName();
                $value = $editing[$element->id][$property->getName()];

                if ($value !== null) {
                    $inputs[$name] = $value;
                }

                foreach ($property->getRules() as $rule => $message) {
                    $rules[$name][] = $rule;

                    if (strpos($rule, ':')) {
                        [$name2, $value2] = explode(':', $rule, 2);
                        $messages[$name.'.'.$name2] = $message;
                    } else {
                        $messages[$name.'.'.$rule] = $message;
                    }
                }
            }

            $validator = Validator::make($inputs, $rules, $messages);

            if ($validator->fails()) {
                $messages = $validator->errors();

                foreach ($properties as $property) {
                    if ($messages->has($property->getName())) {
                        $errors[$element->id][$property->getName()] =
                            $messages->first($property->getName());
                    }
                }

                continue;
            }

            foreach ($properties as $property) {
                if (! array_key_exists($property->getName(), $editing[$element->id])) {
                    continue;
                }

                $name = $property->getName();
                $value = $editing[$element->id][$property->getName()];

                $element->$name = $value;

                if (
                    $property->getEditable()
                    && ! $property->getReadonly()
                ) {
                    $propertyScope = $property->setElement($element)->getEditableView();

                    $views[$element->id][$property->getName()] = view(
                        'moonlight::properties.'.$property->getClassName().'.editable', $propertyScope
                    )->render();
                } else {
                    $propertyScope = $property->setElement($element)->getListView();

                    $views[$element->id][$property->getName()] = view(
                        'moonlight::properties.'.$property->getClassName().'.list', $propertyScope
                    )->render();
                }
            }

            $element->save();
            $saved[] = $site->getClassId($element);
        }

        if ($saved) {
            UserAction::log(
                UserActionType::ACTION_TYPE_SAVE_ELEMENT_LIST_ID,
                implode(', ', $saved)
            );
        }

        return response()->json([
            'saved' => sizeof($saved),
            'views' => $views,
            'errors' => $errors,
        ]);
    }

    /**
     * Copy elements.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function copy(Request $request): JsonResponse
    {
        /** @var User $loggedUser */
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $itemName = $request->input('item');

        $currentItem = $site->getItemByName($itemName);

        if (! $currentItem) {
            return response()->json(['error' => 'Класс элементов не найден.']);
        }

        $checked = $request->input('checked');

        if (! is_array($checked) || ! sizeof($checked)) {
            return response()->json(['error' => 'Пустой список элементов.']);
        }

        $elements = [];

        foreach ($checked as $id) {
            $element = $currentItem->getClass()->find($id);

            if ($element && $loggedUser->hasUpdateAccess($element)) {
                $elements[] = $element;
            }
        }

        if (! sizeof($elements)) {
            return response()->json(['error' => 'Нет элементов для копирования.']);
        }

        $propertyName = $request->input('name');
        $value = $request->input('value');

        $copied = [];
        $destination = null;

        $propertyList = $currentItem->getPropertyList();

        foreach ($elements as $element) {
            $clone = new $element;

            foreach ($propertyList as $property) {
                if ($property->isOrder()) {
                    $property->setElement($clone)->set();
                    continue;
                }

                if (
                    $property->getHidden()
                    || $property->getReadonly()
                    || $property->getName() == $element->getCreatedAtColumn()
                    || $property->getName() == $element->getUpdatedAtColumn()
                    || $property->getName() == $element->getDeletedAtColumn()
                ) {
                    continue;
                }

                if (
                    $property->isOneToOne()
                    && $property->getName() == $propertyName
                    && ($value || ! $property->getRequired())
                ) {
                    $relatedClass = $property->getRelatedClass();
                    $relatedItem = $site->getItemByClassName($relatedClass);

                    if ($value) {
                        $destination = $relatedItem->getClass()->find($value);
                    }

                    $clone->{$property->getName()} = $value ? $value : null;
                    continue;
                }

                $clone->{$property->getName()} = $element->{$property->getName()};
            }

            $clone->save();
            $copied[] = $site->getClassId($clone);
        }

        if ($copied) {
            UserAction::log(
                UserActionType::ACTION_TYPE_COPY_ELEMENT_LIST_ID,
                implode(', ', $copied)
            );
        }

        $url = $destination
            ? route('moonlight.browse.element', $site->getClassId($destination))
            : route('moonlight.browse');

        return response()->json([
            'copied' => sizeof($copied),
            'url' => $url,
        ]);
    }

    /**
     * Move elements.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function move(Request $request): JsonResponse
    {
        /** @var User $loggedUser */
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $itemName = $request->input('item');

        $currentItem = $site->getItemByName($itemName);

        if (! $currentItem) {
            return response()->json(['error' => 'Класс элементов не найден.']);
        }

        $checked = $request->input('checked');

        if (! is_array($checked) || ! sizeof($checked)) {
            return response()->json(['error' => 'Пустой список элементов.']);
        }

        $elements = [];

        foreach ($checked as $id) {
            $element = $currentItem->getClass()->find($id);

            if ($element && $loggedUser->hasUpdateAccess($element)) {
                $elements[] = $element;
            }
        }

        if (! sizeof($elements)) {
            return response()->json(['error' => 'Нет элементов для переноса.']);
        }

        $propertyName = $request->input('name');
        $value = $request->input('value');

        $moved = [];
        $destination = null;

        $propertyList = $currentItem->getPropertyList();

        foreach ($propertyList as $property) {
            if (
                $property->getHidden()
                || $property->getReadonly()
                || ! $property->isOneToOne()
                || $property->getName() != $propertyName
                || (! $value && $property->getRequired())
            ) {
                continue;
            }

            $relatedClass = $property->getRelatedClass();
            $relatedItem = $site->getItemByClassName($relatedClass);

            if ($value) {
                $destination = $relatedItem->getClass()->find($value);
            }

            foreach ($elements as $element) {
                if ($element->$propertyName !== $value) {
                    $element->$propertyName = $value;

                    $element->save();
                    $moved[] = $site->getClassId($element);
                }
            }
        }

        if ($moved) {
            UserAction::log(
                UserActionType::ACTION_TYPE_MOVE_ELEMENT_LIST_ID,
                $propertyName.'='.$value.': '.implode(', ', $moved)
            );
        }

        $url = $destination
            ? route('moonlight.browse.element', $site->getClassId($destination))
            : route('moonlight.browse');

        return response()->json([
            'moved' => sizeof($moved),
            'url' => $url,
        ]);
    }

    /**
     * Bind element.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bind(Request $request): JsonResponse
    {
        /** @var User $loggedUser */
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $itemName = $request->input('item');

        $currentItem = $site->getItemByName($itemName);

        if (! $currentItem) {
            return response()->json(['error' => 'Класс элементов не найден.']);
        }

        $checked = $request->input('checked');

        if (! is_array($checked) || ! sizeof($checked)) {
            return response()->json(['error' => 'Пустой список элементов.']);
        }

        $elements = [];

        foreach ($checked as $id) {
            $element = $currentItem->getClass()->find($id);

            if ($element && $loggedUser->hasUpdateAccess($element)) {
                $elements[] = $element;
            }
        }

        if (! sizeof($elements)) {
            return response()->json(['error' => 'Нет элементов для привязывания.']);
        }

        $ones = $request->input('ones');

        if (! is_array($ones) || ! sizeof($ones)) {
            return response()->json(['error' => 'Нет полей для привязывания.']);
        }

        $attached = [];

        $propertyList = $currentItem->getPropertyList();

        foreach ($propertyList as $propertyName => $property) {
            if (
                $property->getHidden()
                || $property->getReadonly()
                || ! $property->isManyToMany()
                || ! isset($ones[$propertyName])
                || ! $ones[$propertyName]
            ) {
                continue;
            }

            $value = $ones[$propertyName];

            foreach ($elements as $element) {
                $property->setElement($element);

                if ($property->find($value)) {
                    continue;
                }

                if ($property->getOrderField()) {
                    $property->attach([
                        $value => [$property->getOrderField() => $element->id],
                    ]);
                } else {
                    $property->attach($value);
                }

                $attached[] = $site->getClassId($element);
            }
        }

        $lines = [];

        foreach ($ones as $name => $value) {
            $lines[] = $name.'+='.$value;
        }

        UserAction::log(
            UserActionType::ACTION_TYPE_BIND_ELEMENT_LIST_ID,
            implode(';', $lines).': '.implode(', ', $attached)
        );

        return response()->json(['attached' => sizeof($attached)]);
    }

    /**
     * Unbind element.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function unbind(Request $request): JsonResponse
    {
        /** @var User $loggedUser */
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $itemName = $request->input('item');

        $currentItem = $site->getItemByName($itemName);

        if (! $currentItem) {
            return response()->json(['error' => 'Класс элементов не найден.']);
        }

        $checked = $request->input('checked');

        if (! is_array($checked) || ! sizeof($checked)) {
            return response()->json(['error' => 'Пустой список элементов.']);
        }

        $elements = [];

        foreach ($checked as $id) {
            $element = $currentItem->getClass()->find($id);

            if ($element && $loggedUser->hasUpdateAccess($element)) {
                $elements[] = $element;
            }
        }

        if (! sizeof($elements)) {
            return response()->json(['error' => 'Нет элементов для отвязывания.']);
        }

        $ones = $request->input('ones');

        if (! is_array($ones) || ! sizeof($ones)) {
            return response()->json(['error' => 'Нет полей для отвязывания.']);
        }

        $detached = [];

        $propertyList = $currentItem->getPropertyList();

        foreach ($propertyList as $propertyName => $property) {
            if (
                $property->getHidden()
                || $property->getReadonly()
                || ! $property->isManyToMany()
                || ! isset($ones[$propertyName])
                || ! $ones[$propertyName]
            ) {
                continue;
            }

            $value = $ones[$propertyName];

            foreach ($elements as $element) {
                $property->setElement($element)->detach($value);

                $detached[] = $site->getClassId($element);
            }
        }

        $lines = [];

        foreach ($ones as $name => $value) {
            $lines[] = $name.'-='.$value;
        }

        UserAction::log(
            UserActionType::ACTION_TYPE_UNBIND_ELEMENT_LIST_ID,
            implode(';', $lines).': '.implode(', ', $detached)
        );

        return response()->json(['detached' => sizeof($detached)]);
    }

    /**
     * Set favorites.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function favorite(Request $request): JsonResponse
    {
        /** @var User $loggedUser */
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $itemName = $request->input('item');

        $currentItem = $site->getItemByName($itemName);

        if (! $currentItem) {
            return response()->json(['error' => 'Класс элементов не найден.']);
        }

        $checked = $request->input('checked');

        if (! is_array($checked) || ! sizeof($checked)) {
            return response()->json(['error' => 'Пустой список элементов.']);
        }

        $elements = [];

        foreach ($checked as $id) {
            $element = $currentItem->getClass()->find($id);

            if ($element && $loggedUser->hasViewAccess($element)) {
                $elements[] = $element;
            }
        }

        if (! sizeof($elements)) {
            return response()->json(['error' => 'Нет элементов для добавления в избранное.']);
        }

        $addRubric = $request->input('add_favorite_rubric');
        $removeRubric = $request->input('remove_favorite_rubric');
        $newRubric = $request->input('new_favorite_rubric');

        $favoriteRubrics = FavoriteRubric::where('user_id', $loggedUser->id)
            ->orderBy('order')
            ->get();

        $favoritesAll = Favorite::where('user_id', $loggedUser->id)
            ->where('element_type', $currentItem->getClassName())
            ->get();

        $rubricOrders = [];
        $favoriteOrders = [];
        $selectedRubrics = [];
        $favorites = [];

        foreach ($favoriteRubrics as $favoriteRubric) {
            $rubricOrders[] = $favoriteRubric->order;

            if ($newRubric == $favoriteRubric->name) {
                $addRubric = $favoriteRubric->id;
                $newRubric = null;
            }
        }

        foreach ($favoritesAll as $favorite) {
            $favorites[$favorite->rubric_id][$favorite->element_id] = $favorite;
            $favoriteOrders[$favorite->rubric_id][] = $favorite->order;
            $selectedRubrics[$favorite->element_id][$favorite->rubric_id] = $favorite->rubric;
        }

        // Add a new favorite element to the rubric
        if ($addRubric) {
            $nextOrder =
                isset($favoriteOrders[$addRubric])
                && sizeof($favoriteOrders[$addRubric])
                    ? max($favoriteOrders[$addRubric]) + 1
                    : 1;

            foreach ($elements as $element) {
                if (! isset($selectedRubrics[$element->id][$addRubric])) {
                    Favorite::create([
                        'user_id' => $loggedUser->id,
                        'rubric_id' => $addRubric,
                        'order' => $nextOrder,
                        'element_type' => $currentItem->getClassName(),
                        'element_id' => $element->id,
                    ]);

                    $nextOrder++;
                }
            }
        }

        // Remove a favorite element from the rubric
        if ($removeRubric) {
            foreach ($elements as $element) {
                if (isset($favorites[$removeRubric][$element->id])) {
                    $favorite = $favorites[$removeRubric][$element->id];
                    $favorite->delete();
                }
            }
        }

        // Add a new rubric
        if ($newRubric) {
            $nextOrder = isset($rubricOrders) && sizeof($rubricOrders)
                ? max($rubricOrders) + 1 : 1;

            $favoriteRubric = FavoriteRubric::create([
                'user_id' => $loggedUser->id,
                'name' => $newRubric,
                'order' => $nextOrder,
            ]);

            $nextOrder = 1;

            foreach ($elements as $element) {
                Favorite::create([
                    'user_id' => $loggedUser->id,
                    'rubric_id' => $favoriteRubric->id,
                    'order' => $nextOrder,
                    'element_type' => $currentItem->getClassName(),
                    'element_id' => $element->id,
                ]);

                $nextOrder++;
            }
        }

        return response()->json(['saved' => 'ok']);
    }

    /**
     * Delete elements.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        /** @var User $loggedUser */
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $itemName = $request->input('item');

        $currentItem = $site->getItemByName($itemName);

        if (! $currentItem) {
            return response()->json(['error' => 'Класс элементов не найден.']);
        }

        $mainProperty = $currentItem->getMainProperty();

        $checked = $request->input('checked');

        if (! is_array($checked) || ! sizeof($checked)) {
            return response()->json(['error' => 'Пустой список элементов.']);
        }

        $elements = [];

        foreach ($checked as $id) {
            $element = $currentItem->getClass()->find($id);

            if ($element && $loggedUser->hasDeleteAccess($element)) {
                $elements[] = $element;
            }
        }

        if (! sizeof($elements)) {
            return response()->json(['error' => 'Нет элементов для удаления.']);
        }

        $itemList = $site->getItemList();
        $restricted = [];

        foreach ($elements as $element) {
            foreach ($itemList as $item) {
                $propertyList = $item->getPropertyList();
                $count = 0;

                foreach ($propertyList as $property) {
                    if (
                        $property->isOneToOne()
                        && $property->getRelatedClass() == $currentItem->getClassName()
                    ) {
                        $count = $element->hasMany($item->getClassName(), $property->getName())->count();

                        if ($count) {
                            break;
                        }
                    } elseif (
                        $property->isManyToMany()
                        && $property->getRelatedClass() == $currentItem->getClassName()
                    ) {
                        $count = $element->{$property->getRelatedMethod()}()->count();

                        if ($count) {
                            break;
                        }
                    }
                }

                if ($count) {
                    $restricted[$element->id] =
                        '<a href="'.route('moonlight.browse.element', [$itemName, $element->id]).'" target="_blank">'
                        .$element->{$mainProperty}
                        .'</a>';
                    break;
                }
            }
        }

        if ($restricted) {
            return response()->json([
                'error' => 'Сначала удалите элементы, связанные со следующими элементами:<br>'
                    .implode('<br>', $restricted),
            ]);
        }

        $deleted = [];

        foreach ($elements as $element) {
            if ($element->delete()) {
                $deleted[] = $site->getClassId($element);
            }
        }

        if (sizeof($deleted)) {
            UserAction::log(
                UserActionType::ACTION_TYPE_DROP_ELEMENT_LIST_TO_TRASH_ID,
                implode(', ', $deleted)
            );

            if (Cache::has("trash_item_$itemName")) {
                Cache::forget("trash_item_$itemName");
            }

            return response()->json(['deleted' => sizeof($deleted)]);
        }

        return response()->json(['error' => 'Не удалось удалить элементы.']);
    }

    /**
     * Delete elements from trash.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function forceDelete(Request $request): JsonResponse
    {
        /** @var User $loggedUser */
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $itemName = $request->input('item');

        $currentItem = $site->getItemByName($itemName);

        if (! $currentItem) {
            return response()->json(['error' => 'Класс элементов не найден.']);
        }

        $propertyList = $currentItem->getPropertyList();

        $checked = $request->input('checked');

        if (! is_array($checked) || ! sizeof($checked)) {
            return response()->json(['error' => 'Пустой список элементов.']);
        }

        $elements = [];

        foreach ($checked as $id) {
            $element = $currentItem->getClass()->onlyTrashed()->find($id);

            if ($element && $loggedUser->hasDeleteAccess($element)) {
                $elements[] = $element;
            }
        }

        if (! sizeof($elements)) {
            return response()->json(['error' => 'Нет элементов для удаления.']);
        }

        $deleted = [];

        foreach ($elements as $element) {
            foreach ($propertyList as $propertyName => $property) {
                $property->setElement($element)->drop();
            }

            $element->forceDelete();
            $deleted[] = $site->getClassId($element);
        }

        if (sizeof($deleted)) {
            UserAction::log(
                UserActionType::ACTION_TYPE_DROP_ELEMENT_LIST_ID,
                implode(', ', $deleted)
            );

            if (Cache::has("trash_item_{$currentItem->getName()}")) {
                Cache::forget("trash_item_{$currentItem->getName()}");
            }

            return response()->json(['deleted' => sizeof($deleted)]);
        }

        return response()->json(['error' => 'Не удалось удалить элементы.']);
    }

    /**
     * Restore elements from trash.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function restore(Request $request): JsonResponse
    {
        /** @var User $loggedUser */
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $itemName = $request->input('item');

        $currentItem = $site->getItemByName($itemName);

        if (! $currentItem) {
            return response()->json(['error' => 'Класс элементов не найден.']);
        }

        $checked = $request->input('checked');

        if (! is_array($checked) || ! sizeof($checked)) {
            return response()->json(['error' => 'Пустой список элементов.']);
        }

        $elements = [];

        foreach ($checked as $id) {
            $element = $currentItem->getClass()->onlyTrashed()->find($id);

            if ($element && $loggedUser->hasDeleteAccess($element)) {
                $elements[] = $element;
            }
        }

        if (! sizeof($elements)) {
            return response()->json(['error' => 'Нет элементов для восстановления.']);
        }

        $restored = [];

        foreach ($elements as $element) {
            $element->restore();
            $restored[] = $site->getClassId($element);
        }

        if (sizeof($restored)) {
            UserAction::log(
                UserActionType::ACTION_TYPE_RESTORE_ELEMENT_LIST_ID,
                implode(', ', $restored)
            );

            if (Cache::has("trash_item_{$currentItem->getName()}")) {
                Cache::forget("trash_item_{$currentItem->getName()}");
            }

            return response()->json(['deleted' => sizeof($restored)]);
        }

        return response()->json(['error' => 'Не удалось восстановить элементы.']);
    }

    /**
     * Open closed item.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function open(Request $request): JsonResponse
    {
        /** @var User $loggedUser */
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $itemName = $request->input('item');
        $classId = $request->input('class_id');

        $currentItem = $site->getItemByName($itemName);

        if (! $currentItem) {
            return response()->json(['error' => 'Класс элементов не найден.']);
        }

        $cid = $classId ?: Site::ROOT;

        Cache::forever("open_{$loggedUser->id}_{$cid}_{$itemName}", true);

        return response()->json(['cached' => 'ok']);
    }

    /**
     * Close opened item.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function close(Request $request): JsonResponse
    {
        /** @var User $loggedUser */
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $itemName = $request->input('item');
        $classId = $request->input('class_id');

        $currentItem = $site->getItemByName($itemName);

        if (! $currentItem) {
            return response()->json(['error' => 'Класс элементов не найден.']);
        }

        $cid = $classId ?: Site::ROOT;

        Cache::forever("open_{$loggedUser->id}_{$cid}_{$itemName}", false);

        return response()->json(['cached' => 'ok']);
    }

    /**
     * Show element list.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function elements(Request $request): JsonResponse
    {
        /** @var User $loggedUser */
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $itemName = $request->input('item');
        $classId = $request->input('class_id');
        $open = $request->input('open');
        $order = $request->input('order');
        $direction = $request->input('direction');
        $resetOrder = $request->input('reset_order');
        $page = (int) $request->input('page');

        $currentItem = $site->getItemByName($itemName);

        if (! $currentItem) {
            return response()->json(['error' => 'Класс не найден']);
        }

        $parent = $classId ? $site->getByClassId($classId) : null;
        $cid = $classId ?: Site::ROOT;

        if ($open) {
            Cache::forever("open_{$loggedUser->id}_{$cid}_{$itemName}", true);
        }

        if ($page) {
            Cache::put("page_{$loggedUser->id}_{$cid}_{$itemName}", $page, 3600);
        }

        if ($order && in_array($direction, ['asc', 'desc'])) {
            $propertyList = $currentItem->getPropertyList();

            foreach ($propertyList as $property) {
                if ($order == $property->getName()) {
                    Cache::put("order_{$loggedUser->id}_{$itemName}", [
                        'field' => $order,
                        'direction' => $direction,
                    ], 3600);
                    break;
                }
            }
        } elseif ($resetOrder) {
            Cache::forget("order_{$loggedUser->id}_{$itemName}");
        }

        $html = $this->elementListView($currentItem, $parent);

        return response()->json(['html' => $html]);
    }

    /**
     * @param Item $currentItem
     * @param Model|null $parent
     * @return string
     * @throws Throwable
     */
    protected function elementListView(Item $currentItem, Model $parent = null): string
    {
        /** @var User $loggedUser */
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        // Item component
        $itemComponent = $site->getItemComponent($currentItem);
        $itemComponentView = $itemComponent ? (new $itemComponent($currentItem))->render() : null;

        $parentClass = $parent ? $site->getClass($parent) : null;
        $parentItem = $parent ? $site->getItemByElement($parent) : null;
        $parentCid = $parent ? $site->getClassId($parent) : Site::ROOT;

        $currentItemClass = $currentItem->getClass();
        $propertyList = $currentItem->getPropertyList();

        $criteria = $currentItem->getClass();

        foreach ($propertyList as $property) {
            if (
                $parent
                && $property->isManyToMany()
                && $property->getRelatedClass() == $parentClass
            ) {
                $criteria = $parent->{$property->getRelatedMethod()}();
                break;
            } elseif (
                $parent
                && $property->isOneToOne()
                && $property->getRelatedClass() == $parentClass
            ) {
                if ($property->getRelatedMethod()) {
                    $criteria = $parent->{$property->getRelatedMethod()}();
                } else {
                    $criteria = $currentItem->getClass()->where($property->getName(), $parent->id);
                }
                break;
            } elseif (
                ! $parent
                && $property->isOneToOne()
                && $property->getParent()
            ) {
                $criteria = $currentItem->getClass()->whereNull($property->getName());
                break;
            }
        }

        $criteria->where(function ($query) use ($currentItem, $loggedUser) {
            if (! $loggedUser->isSuperUser()) {
                $permissionDenied = true;
                $deniedElementList = [];
                $allowedElementList = [];

                foreach ($loggedUser->groups as $group) {
                    $groupItemPermission = $group->getItemPermission($currentItem);
                    $itemPermission = $groupItemPermission
                        ? $groupItemPermission->permission
                        : $group->default_permission;

                    if ($itemPermission != 'deny') {
                        $permissionDenied = false;
                        $deniedElementList = [];
                    }

                    $elementPermissions = $group->getElementPermissionsByItem($currentItem);

                    foreach ($elementPermissions as $elementPermission) {
                        $elementId = $elementPermission->element_id;
                        $permission = $elementPermission->permission;

                        if ($permission == 'deny') {
                            $deniedElementList[$elementId] = $elementId;
                        } else {
                            $allowedElementList[$elementId] = $elementId;
                        }
                    }
                }

                if ($permissionDenied && sizeof($allowedElementList)) {
                    $query->whereIn('id', $allowedElementList);
                } elseif (! $permissionDenied && sizeof($deniedElementList)) {
                    $query->whereNotIn('id', $deniedElementList);
                } elseif ($permissionDenied) {
                    $query->whereNull('id');
                }
            }
        });

        $open = false;

        if ($parent) {
            foreach ($propertyList as $property) {
                if (
                    ($property->isOneToOne() || $property->isManyToMany())
                    && $property->getRelatedClass() == $parentClass
                ) {
                    $defaultOpen = $property->getOpenItem();
                    $open = Cache::get("open_{$loggedUser->id}_{$parentCid}_{$currentItem->getName()}", $defaultOpen);
                    break;
                }
            }
        } else {
            $open = Cache::get("open_{$loggedUser->id}_{$parentCid}_{$currentItem->getName()}", false);
        }

        if (! $open) {
            $total = $criteria->count();

            $scope['currentItem'] = $currentItem;
            $scope['total'] = $total;

            return view('moonlight::count', $scope)->render();
        }

        $class = $currentItem->getName();
        $order = Cache::get("order_{$loggedUser->id}_{$class}");

        if (isset($order['field']) && isset($order['direction'])) {
            $orderByList = [$order['field'] => $order['direction']];
        } else {
            $orderByList = $currentItem->getOrderByList();
        }

        $orders = [];
        $hasOrderProperty = false;

        foreach ($orderByList as $field => $direction) {
            $property = $currentItem->getPropertyByName($field);

            if (! $property) {
                continue;
            }

            if ($property->isOrder()) {
                $relatedProperty = $property->getRelatedProperty()
                    ? $currentItem->getPropertyByName($property->getRelatedProperty())
                    : null;

                $relatedClass = $relatedProperty ? $relatedProperty->getRelatedClass() : null;

                if (! $relatedClass || $relatedClass == $parentClass) {
                    $criteria = $criteria->orderBy($field, $direction);
                    $orders[$field] = 'порядку';
                    $hasOrderProperty = true;
                }
            } elseif ($property->getName() == $currentItemClass->getCreatedAtColumn()) {
                $criteria = $criteria->orderBy($field, $direction);
                $orders[$field] = 'дате создания';
            } elseif ($property->getName() == $currentItemClass->getUpdatedAtColumn()) {
                $criteria = $criteria->orderBy($field, $direction);
                $orders[$field] = 'дате изменения';
            } elseif ($property->getName() == $currentItemClass->getDeletedAtColumn()) {
                $criteria = $criteria->orderBy($field, $direction);
                $orders[$field] = 'дате удаления';
            } else {
                $criteria = $criteria->orderBy($field, $direction);
                $orders[$field] = 'полю &laquo;'.$property->getTitle().'&raquo;';
            }
        }

        $orders = implode(', ', $orders);

        if ($hasOrderProperty) {
            $elements = $criteria->get();

            $total = sizeof($elements);

            $perPage = static::PER_PAGE;
            $currentPage = 1;
            $hasMorePages = false;
            $nextPage = null;
            $lastPage = null;
        } else {
            $page = Cache::get("page_{$loggedUser->id}_{$parentCid}_{$currentItem->getName()}", 1);

            Paginator::currentPageResolver(function () use ($page) {
                return $page;
            });

            if (Cache::has("per_page_{$loggedUser->id}_{$currentItem->getName()}")) {
                $perPage = Cache::get("per_page_{$loggedUser->id}_{$currentItem->getName()}");
            } elseif ($currentItem->getPerPage()) {
                $perPage = $currentItem->getPerPage();
            } else {
                $perPage = static::PER_PAGE;
            }

            $elements = $criteria->paginate($perPage);

            $total = $elements->total();
            $currentPage = $elements->currentPage();
            $hasMorePages = $elements->hasMorePages();
            $nextPage = $elements->currentPage() + 1;
            $lastPage = $elements->lastPage();
        }

        // Views
        $properties = [];
        $columns = [];
        $views = [];

        foreach ($propertyList as $property) {
            $show = Cache::get(
                "show_column_{$loggedUser->id}_{$currentItem->getName()}_{$property->getName()}",
                $property->getShow()
            );

            if (
                $property->getHidden()
                || $property->getName() == $currentItemClass->getDeletedAtColumn()
                || ! $show
            ) {
                continue;
            }

            $properties[] = $property;
        }

        foreach ($propertyList as $property) {
            if (
                $property->getHidden()
                || ! $property->isShowEditable()
                || $property->getName() == $currentItemClass->getDeletedAtColumn()
            ) {
                continue;
            }

            $show = Cache::get(
                "show_column_{$loggedUser->id}_{$currentItem->getName()}_{$property->getName()}",
                $property->getShow()
            );

            $columns[] = [
                'name' => $property->getName(),
                'title' => $property->getTitle(),
                'show' => $show,
            ];
        }

        if (sizeof($columns) >= 30) {
            $columnsCount = 3;
        } elseif (sizeof($columns) >= 20) {
            $columnsCount = 2;
        } else {
            $columnsCount = 1;
        }

        foreach ($elements as $element) {
            foreach ($properties as $property) {
                if (
                    $property->getEditable()
                    && ! $property->getReadonly()
                ) {
                    $propertyScope = $property->setElement($element)->getEditableView();
                    $suffix = 'editable';
                } else {
                    $propertyScope = $property->setElement($element)->getListView();
                    $suffix = 'list';
                }

                $views[$element->id][$property->getName()] = view(
                    "moonlight::properties.{$property->getClassName()}.$suffix",
                    $propertyScope
                )->render();
            }
        }

        // Copy and move views
        $copyPropertyView = null;
        $movePropertyView = null;
        $bindPropertyViews = [];
        $unbindPropertyViews = [];

        foreach ($propertyList as $property) {
            if (
                $property->getHidden()
                || ! $property->isOneToOne()
            ) {
                continue;
            }

            if (
                ($parentItem && $property->getRelatedClass() == $parentItem->getName())
                || (! $parentItem && $property->getParent())
            ) {
                $element = $currentItem->getClass();

                if ($parent) {
                    $site->setParent($element, $parent);
                }

                $propertyScope = $property->setElement($element)->getEditView();

                $propertyScope['mode'] = 'browse';

                $copyPropertyView = view(
                    'moonlight::properties.'.$property->getClassName().'.copy', $propertyScope
                )->render();

                $movePropertyView = view(
                    'moonlight::properties.'.$property->getClassName().'.move', $propertyScope
                )->render();

                break;
            }
        }

        foreach ($propertyList as $property) {
            if (
                $property->getHidden()
                || ! $property->isManyToMany()
            ) {
                continue;
            }

            $propertyScope = $property->getEditView();
            $propertyScope['mode'] = 'browse';

            $bindPropertyViews[$property->getName()] = view(
                'moonlight::properties.'.$property->getClassName().'.bind', $propertyScope
            )->render();

            $unbindPropertyViews[$property->getName()] = view(
                'moonlight::properties.'.$property->getClassName().'.bind', $propertyScope
            )->render();
        }

        if (! $copyPropertyView && $currentItem->getRoot()) {
            $copyPropertyView = 'Корень сайта';
        }

        // Favorites
        $favoriteRubrics = FavoriteRubric::where('user_id', $loggedUser->id)
            ->orderBy('order')
            ->get();

        $favorites = Favorite::where('user_id', $loggedUser->id)->get();

        $favoriteRubricMap = [];
        $elementFavoriteRubrics = [];

        foreach ($favorites as $favorite) {
            $favoriteRubricMap[$favorite->element_id][$favorite->rubric_id] = $favorite->rubric_id;
        }

        foreach ($elements as $element) {
            $elementFavoriteRubrics[$element->id] = isset($favoriteRubricMap[$element->id])
                ? implode(',', $favoriteRubricMap[$element->id])
                : null;
        }

        $scope = [
            'currentItem' => $currentItem,
            'itemComponentView' => $itemComponentView,
            'properties' => $properties,
            'columns' => $columns,
            'columnsCount' => $columnsCount,
            'total' => $total,
            'perPage' => $perPage,
            'currentPage' => $currentPage,
            'hasMorePages' => $hasMorePages,
            'nextPage' => $nextPage,
            'lastPage' => $lastPage,
            'elements' => $elements,
            'views' => $views,
            'orderByList' => $orderByList,
            'orders' => $orders,
            'hasOrderProperty' => $hasOrderProperty,
            'mode' => 'browse',
            'copyPropertyView' => $copyPropertyView,
            'movePropertyView' => $movePropertyView,
            'bindPropertyViews' => $bindPropertyViews,
            'unbindPropertyViews' => $unbindPropertyViews,
            'favoriteRubrics' => $favoriteRubrics,
            'elementFavoriteRubrics' => $elementFavoriteRubrics,
        ];

        return view('moonlight::elements', $scope)->render();
    }

    /**
     * Show element list for autocomplete.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function autocomplete(Request $request): JsonResponse
    {
        /** @var User $loggedUser */
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $itemName = $request->input('item');
        $mode = $request->input('mode');

        $currentItem = $site->getItemByName($itemName);

        if (! $currentItem) {
            return response()->json(['error' => 'Класс элементов не найден.']);
        }

        $itemClass = $currentItem->getClass();
        $mainProperty = $currentItem->getMainProperty();

        $term = $request->input('query');
        $term_id = (int) $term;
        $term = mb_strtolower($term);

        $suggestions = [];

        if ($term_id) {
            $element = $itemClass->find($term_id);

            if ($element && $loggedUser->hasViewAccess($element)) {
                $suggestions[] = [
                    'value' => $element->$mainProperty,
                    'class_id' => $site->getClassId($element),
                    'id' => $element->id,
                ];
            }
        }

        $criteria = $currentItem->getClass()->query();

        if ($mode == 'onlyTrashed') {
            $criteria->onlyTrashed();
        } elseif ($mode == 'withTrashed') {
            $criteria->withTrashed();
        }

        $criteria
            ->where(function ($query) use ($loggedUser, $currentItem) {
                $permissionDenied = true;
                $deniedElementList = [];
                $allowedElementList = [];

                if (! $loggedUser->isSuperUser()) {
                    foreach ($loggedUser->groups as $group) {
                        $groupItemPermission = $group->getItemPermission($currentItem);
                        $itemPermission = $groupItemPermission
                            ? $groupItemPermission->permission
                            : $group->default_permission;

                        if ($itemPermission != 'deny') {
                            $permissionDenied = false;
                            $deniedElementList = [];
                        }

                        $elementPermissions = $group->getElementPermissionsByItem($currentItem);

                        foreach ($elementPermissions as $elementPermission) {
                            $element_id = $elementPermission->element_id;
                            $permission = $elementPermission->permission;

                            if ($permission == 'deny') {
                                $deniedElementList[$element_id] = $element_id;
                            } else {
                                $allowedElementList[$element_id] = $element_id;
                            }
                        }
                    }

                    if ($permissionDenied && sizeof($allowedElementList)) {
                        $query->whereIn('id', $allowedElementList);
                    } elseif (! $permissionDenied && sizeof($deniedElementList)) {
                        $query->whereNotIn('id', $deniedElementList);
                    } elseif ($permissionDenied) {
                        $query->whereNull('id');
                    }
                }
            })
            ->where(function ($query) use ($term, $term_id, $mainProperty) {
                if ($term) {
                    $query->where(DB::raw("lower($mainProperty)"), 'like', "%$term%");
                }

                if ($term_id) {
                    $query->orWhere('id', 'like', "%$term_id%");
                }
            });

        $elements = $criteria->orderBy('id', 'asc')->limit(static::PER_PAGE)->get();

        foreach ($elements as $element) {
            if ($element->id != $term_id) {
                $suggestions[] = [
                    'value' => $element->$mainProperty,
                    'class_id' => $site->getClassId($element),
                    'id' => $element->id,
                ];
            }
        }

        return response()->json(['suggestions' => $suggestions]);
    }

    /**
     * Show browse component.
     *
     * @param Request $request
     * @param string $classId
     * @return Factory|View|null
     */
    public function component(Request $request, string $classId): Factory|View|null
    {
        $site = App::make('site');

        $element = $site->getByClassId($classId);
        $browseComponent = $element ? $site->getBrowseComponent($element) : null;

        return $browseComponent ? new $browseComponent($request, $element)->render() : null;
    }

    /**
     * Show browse element.
     *
     * @param Request $request
     * @param string $classId
     * @return RedirectResponse|Factory|View
     */
    public function element(Request $request, string $classId): Factory|View|RedirectResponse
    {
        $site = App::make('site');

        $element = $site->getByClassId($classId);

        if (! $element) {
            return redirect()->route('moonlight.browse');
        }

        $currentItem = $site->getItemByElement($element);
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

        $mainProperty = $currentItem->getMainProperty();

        // Browse component
        $browseComponent = $site->getBrowseComponent($element);

        $items = [];
        $creates = [];

        $bonds = $site->getBindings($element);

        foreach ($bonds as $bond) {
            $item = $site->getItemByClassName($bond);

            if (! $item) {
                continue;
            }

            $propertyList = $item->getPropertyList();

            foreach ($propertyList as $property) {
                if (
                    ($property->isOneToOne() || $property->isManyToMany())
                    && $property->getRelatedClass() == $site->getClass($element)
                ) {
                    $items[] = $item;

                    if ($item->getCreate()) {
                        $creates[] = $item;
                    }

                    break;
                }
            }
        }

        $rubrics = new SidebarRubrics($element)->render();

        return view('moonlight::element', [
            'element' => $element,
            'classId' => $classId,
            'mainProperty' => $mainProperty,
            'parents' => $parents,
            'currentItem' => $currentItem,
            'browseComponent' => $browseComponent,
            'items' => $items,
            'creates' => $creates,
            'rubrics' => $rubrics,
        ]);
    }

    /**
     * Show browse root.
     *
     * @param Request $request
     * @return Factory|View
     */
    public function root(Request $request): Factory|View
    {
        $site = App::make('site');

        $items = [];
        $creates = [];

        $bindings = $site->getRootBindings();

        foreach ($bindings as $binding) {
            $item = $site->getItemByClassName($binding);

            if ($item) {
                $items[] = $item;

                if ($item->getCreate()) {
                    $creates[] = $item;
                }
            }
        }

        $rubrics = new SidebarRubrics()->render();

        return view('moonlight::root', [
            'items' => $items,
            'creates' => $creates,
            'rubrics' => $rubrics,
        ]);
    }
}
