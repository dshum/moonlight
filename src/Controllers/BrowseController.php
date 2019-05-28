<?php

namespace Moonlight\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\Paginator;
use Moonlight\Main\Element;
use Moonlight\Main\Site;
use Moonlight\Main\UserActionType;
use Moonlight\Models\FavoriteRubric;
use Moonlight\Models\Favorite;
use Moonlight\Models\UserAction;
use Moonlight\Properties\MainProperty;
use Moonlight\Properties\OrderProperty;
use Moonlight\Properties\FileProperty;
use Moonlight\Properties\ImageProperty;
use Moonlight\Properties\ManyToManyProperty;
use Moonlight\Properties\PasswordProperty;
use Moonlight\Properties\VirtualProperty;
use Carbon\Carbon;

class BrowseController extends Controller
{
    const PER_PAGE = 10;

    /**
     * Show/hide column.
     *
     * @return Response
     */
    public function column(Request $request)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();

        $class = $request->input('item');
        $name = $request->input('name');
        $show = $request->input('show');

        $site = \App::make('site');
        
        $currentItem = $site->getItemByName($class);
        
        if (! $currentItem) {
            $scope['error'] = 'Класс элементов не найден.';
            
            return response()->json($scope);
        }

        $property = $currentItem->getPropertyByName($name);

        if (! $property) {
            $scope['error'] = 'Свойство элемента не найдено.';
            
            return response()->json($scope);
        }

        if ($show == 'true') {
            if ($property->getShow()) {
                cache()->forget("show_column_{$loggedUser->id}_{$class}_{$name}");
            } else {
                cache()->forever("show_column_{$loggedUser->id}_{$class}_{$name}", 1);
            }
        } else {
            if (! $property->getShow()) {
                cache()->forget("show_column_{$loggedUser->id}_{$class}_{$name}");
            } else {
                cache()->forever("show_column_{$loggedUser->id}_{$class}_{$name}", 0);
            }
        }

        $scope['column'] = $show;

        return response()->json($scope);
    }

    /**
     * Order elements.
     *
     * @return Response
     */
    public function order(Request $request)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();

        $class = $request->input('item');

        $site = \App::make('site');
        
        $currentItem = $site->getItemByName($class);
        
        if (! $currentItem) {
            $scope['error'] = 'Класс элементов не найден.';
            
            return response()->json($scope);
        }

        if (! $currentItem->getOrderProperty()) {
            $scope['error'] = 'Поле для ручной сортировки не найдено.';
            
            return response()->json($scope);
        }

        $orderProperty = $currentItem->getOrderProperty();
        
        $elements = $request->input('elements');

        $ordered = [];

        if (is_array($elements) && sizeof($elements) > 1) {
            foreach ($elements as $order => $id) {
                $element = $currentItem->getClass()->find($id);
            
                if ($element && $loggedUser->hasUpdateAccess($element)) {
                    $element->{$orderProperty} = $order;

                    $element->save();

                    $ordered[$order] = $id;
                }
            }

            if ($ordered) {
                UserAction::log(
                    UserActionType::ACTION_TYPE_ORDER_ELEMENT_LIST_ID,
                    $class.': '.implode(', ', $ordered)
                );

                $scope['ordered'] = 'ok';
            }
        }

        return response()->json($scope);
    }

    /**
     * Save elements.
     *
     * @return Response
     */
    public function save(Request $request)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();

        $class = $request->input('item');

        $site = \App::make('site');

        $currentItem = $site->getItemByName($class);
        
        if (! $currentItem) {
            $scope['error'] = 'Класс элементов не найден.';
            
            return response()->json($scope);
        }

        $propertyList = $currentItem->getPropertyList();

        $editing = $request->input('editing');

        if (! is_array($editing) || ! sizeof($editing)) {
            $scope['error'] = 'Пустой список элементов.';
            
            return response()->json($scope);
        }
        
        $elements = [];
        $saved = [];
        $properties = [];
        $views = [];
        $errors = [];

        foreach ($propertyList as $property) {
            if ($property->getHidden()) continue;
            if (! $property->getShow()) continue;

            $properties[] = $property;
        }

        foreach ($editing as $id => $fields) {
            $element = $currentItem->getClass()->find($id);
            
            if (! $element) continue;
            if (! $loggedUser->hasUpdateAccess($element)) continue;
            if (! is_array($fields)) continue;
            if (! sizeof($fields)) continue;

            $elements[] = $element;
        }

        if (! sizeof($elements)) {
            $scope['error'] = 'Нет элементов для редактирования.';
            
            return response()->json($scope);
        }

        foreach ($elements as $element) {
            $inputs = [];
            $rules = [];
            $messages = [];

            foreach ($properties as $property) {
                if (! array_key_exists($property->getName(), $editing[$element->id])) continue;

                $name = $property->getName();
                $value = $editing[$element->id][$property->getName()];

                if ($value !== null) $inputs[$name] = $value;

                foreach ($property->getRules() as $rule => $message) {
                    $rules[$name][] = $rule;

                    if (strpos($rule, ':')) {
                        list($name2, $value2) = explode(':', $rule, 2);
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
                if (! array_key_exists($property->getName(), $editing[$element->id])) continue;

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

            $saved[] = Element::getClassId($element);
        }
        
        if ($saved) {
            UserAction::log(
                UserActionType::ACTION_TYPE_SAVE_ELEMENT_LIST_ID,
                implode(', ', $saved)
            );
        }

        $scope['saved'] = 'ok';
        $scope['views'] = $views;
        $scope['errors'] = $errors;
        
        return response()->json($scope);
    }
    
    /**
     * Copy elements.
     *
     * @return Response
     */
    public function copy(Request $request)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();

        $class = $request->input('item');

        $site = \App::make('site');
        
        $currentItem = $site->getItemByName($class);
        
        if (! $currentItem) {
            $scope['error'] = 'Класс элементов не найден.';
            
            return response()->json($scope);
        }

        $checked = $request->input('checked');

        if (! is_array($checked) || ! sizeof($checked)) {
            $scope['error'] = 'Пустой список элементов.';
            
            return response()->json($scope);
        }
        
        $elements = [];
        
        foreach ($checked as $id) {
            $element = $currentItem->getClass()->find($id);
            
            if ($element && $loggedUser->hasUpdateAccess($element)) {
                $elements[] = $element;
            }
        }
        
        if (! sizeof($elements)) {
            $scope['error'] = 'Нет элементов для копирования.';
            
            return response()->json($scope);
        }

        $name = $request->input('name');
        $value = $request->input('value');

        $copied = [];
        $destination = null;

        $propertyList = $currentItem->getPropertyList();

        foreach ($elements as $element) {
            $clone = new $element;
            
            foreach ($propertyList as $propertyName => $property) {
                if ($property instanceof OrderProperty) {
                    $property->setElement($clone)->set();
                    continue;
                }

                if (
                    $property instanceof ManyToManyProperty
                    || $property instanceof VirtualProperty
                ) {
                    continue;
                }
    
                if (
                    $property->getReadonly()
                    && ! $property->getRequired()
                ) continue;
    
                if (
                    $property instanceof FileProperty
                    && ! $property->getRequired()
                ) continue;
                
                if (
                    $property instanceof ImageProperty
                    && ! $property->getRequired()
                ) continue;

                if (
                    $propertyName == 'created_at'
                    || $propertyName == 'updated_at'
                    || $propertyName == 'deleted_at'
                ) continue;
    
                if (
                    $property->isOneToOne()
                    && $propertyName == $name
                    && ($value || ! $property->getRequired())
                ) {
                    $relatedClass = $property->getRelatedClass();
                    $relatedItem = $site->getItemByName($relatedClass);
                    
                    if ($value) {
                        $destination = $relatedItem->getClass()->find($value);
                    }

                    $clone->$propertyName = $value ? $value : null;
                    continue;
                }
                
                $clone->$propertyName = $element->$propertyName;
            }

            $clone->save();
            
            $copied[] = Element::getClassId($clone);
        }
        
        if ($copied) {
            UserAction::log(
                UserActionType::ACTION_TYPE_COPY_ELEMENT_LIST_ID,
                implode(', ', $copied)
            );
        }

        $url = $destination
            ? route('moonlight.browse.element', Element::getClassId($destination))
            : route('moonlight.browse');

        $scope['copied'] = 'ok';
        $scope['url'] = $url;
        
        return response()->json($scope);
    }
    
    /**
     * Move elements.
     *
     * @return Response
     */
    public function move(Request $request)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();

        $class = $request->input('item');

        $site = \App::make('site');
        
        $currentItem = $site->getItemByName($class);
        
        if (! $currentItem) {
            $scope['error'] = 'Класс элементов не найден.';
            
            return response()->json($scope);
        }

        $checked = $request->input('checked');

        if (! is_array($checked) || ! sizeof($checked)) {
            $scope['error'] = 'Пустой список элементов.';
            
            return response()->json($scope);
        }
        
        $elements = [];
        
        foreach ($checked as $id) {
            $element = $currentItem->getClass()->find($id);
            
            if ($element && $loggedUser->hasUpdateAccess($element)) {
                $elements[] = $element;
            }
        }
        
        if (! sizeof($elements)) {
            $scope['error'] = 'Нет элементов для переноса.';
            
            return response()->json($scope);
        }

        $name = $request->input('name');
        $value = $request->input('value');

        $moved = [];
        $destination = null;

        $propertyList = $currentItem->getPropertyList();

        foreach ($propertyList as $propertyName => $property) {
            if ($property->getHidden()) continue;
            if ($property->getReadonly()) continue;
            if (! $property->isOneToOne()) continue;
            if ($propertyName != $name) continue;
            if (! $value && $property->getRequired()) continue;

            $relatedClass = $property->getRelatedClass();
            $relatedItem = $site->getItemByName($relatedClass);
            
            if ($value) {
                $destination = $relatedItem->getClass()->find($value);
            }

            foreach ($elements as $element) {
                if ($element->$propertyName !== $value) {
                    $element->$propertyName = $value;

                    $element->save();

                    $moved[] = Element::getClassId($element);
                }
            }
        }
        
        if ($moved) {
            UserAction::log(
                UserActionType::ACTION_TYPE_MOVE_ELEMENT_LIST_ID,
                $name.'='.$value.': '.implode(', ', $moved)
            );

            $url = $destination
                ? route('moonlight.browse.element', Element::getClassId($destination))
                : route('moonlight.browse');

            $scope['moved'] = 'ok';
            $scope['url'] = $url;
        }

        return response()->json($scope);
    }

    /**
     * Bind element.
     *
     * @return Response
     */
    public function bind(Request $request)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();

        $class = $request->input('item');

        $site = \App::make('site');
        
        $currentItem = $site->getItemByName($class);
        
        if (! $currentItem) {
            $scope['error'] = 'Класс элементов не найден.';
            
            return response()->json($scope);
        }

        $checked = $request->input('checked');

        if (! is_array($checked) || ! sizeof($checked)) {
            $scope['error'] = 'Пустой список элементов.';
            
            return response()->json($scope);
        }
        
        $elements = [];
        
        foreach ($checked as $id) {
            $element = $currentItem->getClass()->find($id);
            
            if ($element && $loggedUser->hasUpdateAccess($element)) {
                $elements[] = $element;
            }
        }
        
        if (! sizeof($elements)) {
            $scope['error'] = 'Нет элементов для переноса.';
            
            return response()->json($scope);
        }

        $ones = $request->input('ones');

        $attached = [];

        $propertyList = $currentItem->getPropertyList();

        foreach ($propertyList as $propertyName => $property) {
            if ($property->getHidden()) continue;
            if ($property->getReadonly()) continue;
            if (! $property->isManyToMany()) continue;
            if (! isset($ones[$propertyName])) continue;
            if (! $ones[$propertyName]) continue;

            $value = $ones[$propertyName];
            
            $relatedClass = $property->getRelatedClass();
            $relatedItem = $site->getItemByName($relatedClass);

            foreach ($elements as $element) {
                $property->setElement($element);

                $property->attach($value);

                $element->save();

                $attached[] = Element::getClassId($element);
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

        $scope['attached'] = 'ok';

        return response()->json($scope);
    }

    /**
     * Unbind element.
     *
     * @return Response
     */
    public function unbind(Request $request)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();

        $class = $request->input('item');

        $site = \App::make('site');
        
        $currentItem = $site->getItemByName($class);
        
        if (! $currentItem) {
            $scope['error'] = 'Класс элементов не найден.';
            
            return response()->json($scope);
        }

        $checked = $request->input('checked');

        if (! is_array($checked) || ! sizeof($checked)) {
            $scope['error'] = 'Пустой список элементов.';
            
            return response()->json($scope);
        }
        
        $elements = [];
        
        foreach ($checked as $id) {
            $element = $currentItem->getClass()->find($id);
            
            if ($element && $loggedUser->hasUpdateAccess($element)) {
                $elements[] = $element;
            }
        }
        
        if (! sizeof($elements)) {
            $scope['error'] = 'Нет элементов для переноса.';
            
            return response()->json($scope);
        }

        $ones = $request->input('ones');

        $detached = [];

        $propertyList = $currentItem->getPropertyList();

        foreach ($propertyList as $propertyName => $property) {
            if ($property->getHidden()) continue;
            if ($property->getReadonly()) continue;
            if (! $property->isManyToMany()) continue;
            if (! isset($ones[$propertyName])) continue;
            if (! $ones[$propertyName]) continue;

            $value = $ones[$propertyName];
            
            $relatedClass = $property->getRelatedClass();
            $relatedItem = $site->getItemByName($relatedClass);

            foreach ($elements as $element) {
                $property->setElement($element);

                $property->detach($value);

                $element->save();

                $detached[] = Element::getClassId($element);
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

        $scope['detached'] = 'ok';

        return response()->json($scope);
    }

    /**
     * Set favorites.
     *
     * @return Response
     */
    public function favorite(Request $request)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();
        
		$class = $request->input('item');

        $site = \App::make('site');
        
        $currentItem = $site->getItemByName($class);
        
        if (! $currentItem) {
            $scope['error'] = 'Класс элементов не найден.';
            
            return response()->json($scope);
        }

        $mainProperty = $currentItem->getMainProperty();

        $checked = $request->input('checked');
        
        if (! is_array($checked) || ! sizeof($checked)) {
            $scope['error'] = 'Пустой список элементов.';
            
            return response()->json($scope);
        }
        
        $elements = [];
        
        foreach ($checked as $id) {
            $element = $currentItem->getClass()->find($id);
            
            if ($element && $loggedUser->hasViewAccess($element)) {
                $elements[] = $element;
            }
        }
        
        if ( ! sizeof($elements)) {
            $scope['error'] = 'Нет элементов для добавление в избранное.';
            
            return response()->json($scope);
        }
        
        $addRubric = $request->input('add_favorite_rubric');
        $removeRubric = $request->input('remove_favorite_rubric');
        $newRubric = $request->input('new_favorite_rubric');

        $favoriteRubrics = FavoriteRubric::where('user_id', $loggedUser->id)->
            orderBy('order')->
            get();

        $favoritesAll = Favorite::where('user_id', $loggedUser->id)->
            get();
           
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
            $favorites[$favorite->rubric_id][$favorite->class_id] = $favorite;
            $favoriteOrders[$favorite->rubric_id][] = $favorite->order;
            $selectedRubrics[$favorite->class_id][$favorite->rubric_id] = $favorite->rubric;
        }

        if (
            $addRubric
        ) {
            $nextOrder = 
                isset($favoriteOrders[$addRubric])
                && sizeof($favoriteOrders[$addRubric])
                ? max($favoriteOrders[$addRubric]) + 1
                : 1;

            foreach ($elements as $element) {
                $classId = Element::getClassId($element);
    
                if (isset($selectedRubrics[$classId][$addRubric])) continue;

                $favorite = new Favorite;

                $favorite->user_id = $loggedUser->id;
                $favorite->rubric_id = $addRubric;
                $favorite->class_id = $classId;
                $favorite->order = $nextOrder;
                $favorite->created_at = Carbon::now();

                $favorite->save();

                $nextOrder++;
            }
        }

        if (
            $removeRubric
        ) {
            foreach ($elements as $element) {
                $classId = Element::getClassId($element);

                if (! isset($favorites[$removeRubric][$classId])) continue;

                $favorite = $favorites[$removeRubric][$classId];

                $favorite->delete();
            }
        }

        if (
            $newRubric
        ) {
            $nextOrder = 
                isset($rubricOrders)
                && sizeof($rubricOrders)
                ? max($rubricOrders) + 1
                : 1;

            $favoriteRubric = new FavoriteRubric;

            $favoriteRubric->user_id = $loggedUser->id;
            $favoriteRubric->name = $newRubric;
            $favoriteRubric->order = $nextOrder;
            $favoriteRubric->created_at = Carbon::now();

            $favoriteRubric->save();

            $nextOrder = 1;

            foreach ($elements as $element) {
                $classId = Element::getClassId($element);

                $favorite = new Favorite;
    
                $favorite->user_id = $loggedUser->id;
                $favorite->rubric_id = $favoriteRubric->id;
                $favorite->class_id = $classId;
                $favorite->order = $nextOrder;
                $favorite->created_at = Carbon::now();
    
                $favorite->save();

                $nextOrder++;
            }
        }
        
        $scope['saved'] = 'ok';

        return response()->json($scope);
    }
    
    /**
     * Delete elements.
     *
     * @return Response
     */
    public function delete(Request $request)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();
        
        $class = $request->input('item');

        $site = \App::make('site');
        
        $currentItem = $site->getItemByName($class);
        
        if (! $currentItem) {
            $scope['error'] = 'Класс элементов не найден.';
            
            return response()->json($scope);
        }

        $mainProperty = $currentItem->getMainProperty();

        $checked = $request->input('checked');
        
        if (! is_array($checked) || ! sizeof($checked)) {
            $scope['error'] = 'Пустой список элементов.';
            
            return response()->json($scope);
        }
        
        $elements = [];
        
        foreach ($checked as $id) {
            $element = $currentItem->getClass()->find($id);
            
            if ($element && $loggedUser->hasDeleteAccess($element)) {
                $elements[] = $element;
            }
        }
        
        if ( ! sizeof($elements)) {
            $scope['error'] = 'Нет элементов для удаления.';
            
            return response()->json($scope);
        }
        
        $itemList = $site->getItemList();
        
        foreach ($elements as $element) {
            $classId = Element::getClassId($element);

            foreach ($itemList as $item) {
                $itemName = $item->getName();
                $propertyList = $item->getPropertyList();
                $count = 0;

                foreach ($propertyList as $property) {
                    if (
                        $property->isOneToOne()
                        && $property->getRelatedClass() == $currentItem->getName()
                    ) {
                        $count = $element->
                            hasMany($itemName, $property->getName())->
                            count();

                        if ($count) break;
                    } elseif (
                        $property->isManyToMany()
                        && $property->getRelatedClass() == $currentItem->getName()
                    ) {
                        $count = $element->
                            {$property->getRelatedMethod()}()->
                            count();

                        if ($count) break;
                    }
                }

                if ($count) {
                    $scope['restricted'][$classId] = 
                        '<a href="'.route('moonlight.browse.element', $classId).'" target="_blank">'
                        .$element->{$mainProperty}
                        .'</a>';

                    break;
                }
            }
        }

		if (isset($scope['restricted'])) {
            $scope['error'] = 'Сначала удалите элементы, связанные со следующими элементами:<br>'
                .implode('<br>', $scope['restricted']);
            
            return response()->json($scope);
        }

        $deleted = [];
        
        foreach ($elements as $element) {
            $classId = Element::getClassId($element);

            if ($element->delete()) {
                $deleted[] = $classId;
                $scope['deleted'][] = $element->id;
            }
        }
        
        if (isset($scope['deleted'])) {
            UserAction::log(
                UserActionType::ACTION_TYPE_DROP_ELEMENT_LIST_TO_TRASH_ID,
                implode(', ', $deleted)
            );

            if (cache()->has("trash_item_{$currentItem->getNameId()}")) {
                cache()->forget("trash_item_{$currentItem->getNameId()}");
            }
        } else {
            $scope['error'] = 'Не удалось удалить элемент.';
        }
        
        return response()->json($scope);
    }
    
    /**
     * Delete elements from trash.
     *
     * @return Response
     */
    public function forceDelete(Request $request)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();
        
        $class = $request->input('item');

        $site = \App::make('site');
        
        $currentItem = $site->getItemByName($class);
        
        if (! $currentItem) {
            $scope['error'] = 'Класс элементов не найден.';
            
            return response()->json($scope);
        }

        $mainProperty = $currentItem->getMainProperty();
        $propertyList = $currentItem->getPropertyList();

        $checked = $request->input('checked');
        
        if (! is_array($checked) || ! sizeof($checked)) {
            $scope['error'] = 'Пустой список элементов.';
            
            return response()->json($scope);
        }
        
        $elements = [];
        
        foreach ($checked as $id) {
            $element = $currentItem->getClass()->onlyTrashed()->find($id);
            
            if ($element && $loggedUser->hasDeleteAccess($element)) {
                $elements[] = $element;
            }
        }
        
        if (! sizeof($elements)) {
            $scope['error'] = 'Нет элементов для удаления.';
            
            return response()->json($scope);
        }

        $deleted = [];
        
        foreach ($elements as $element) {
            $classId = Element::getClassId($element);

            foreach ($propertyList as $propertyName => $property) {
                $property->setElement($element)->drop();
            }

            $element->forceDelete();

            $deleted[] = $classId;
            $scope['deleted'][] = $element->id;
        }
        
        if (sizeof($deleted)) {
            UserAction::log(
                UserActionType::ACTION_TYPE_DROP_ELEMENT_LIST_ID,
                implode(', ', $deleted)
            );

            if (cache()->has("trash_item_{$currentItem->getNameId()}")) {
                cache()->forget("trash_item_{$currentItem->getNameId()}");
            }
        }
        
        return response()->json($scope);
    }
    
    /**
     * Restore elements from trash.
     *
     * @return Response
     */
    public function restore(Request $request)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();
        
        $class = $request->input('item');

        $site = \App::make('site');
        
        $currentItem = $site->getItemByName($class);
        
        if (! $currentItem) {
            $scope['error'] = 'Класс элементов не найден.';
            
            return response()->json($scope);
        }

        $mainProperty = $currentItem->getMainProperty();

        $checked = $request->input('checked');
        
        if (! is_array($checked) || ! sizeof($checked)) {
            $scope['error'] = 'Пустой список элементов.';
            
            return response()->json($scope);
        }
        
        $elements = [];
        
        foreach ($checked as $id) {
            $element = $currentItem->getClass()->onlyTrashed()->find($id);
            
            if ($element && $loggedUser->hasDeleteAccess($element)) {
                $elements[] = $element;
            }
        }
        
        if (! sizeof($elements)) {
            $scope['error'] = 'Нет элементов для восстановления.';
            
            return response()->json($scope);
        }

        $restored = [];
        
        foreach ($elements as $element) {
            $classId = Element::getClassId($element);

            $element->restore();

            $restored[] = $classId;
            $scope['restored'][] = $element->id;
        }
        
        if (sizeof($restored)) {
            UserAction::log(
                UserActionType::ACTION_TYPE_RESTORE_ELEMENT_LIST_ID,
                implode(', ', $restored)
            );

            if (cache()->has("trash_item_{$currentItem->getNameId()}")) {
                cache()->forget("trash_item_{$currentItem->getNameId()}");
            }
        }
        
        return response()->json($scope);
    }

    /**
     * Open closed item.
     *
     * @return Response
     */
    public function open(Request $request)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();
        
        $class = $request->input('item');
        $classId = $request->input('classId');
        
        $site = \App::make('site');
        
        $currentItem = $site->getItemByName($class);
        
        if (! $currentItem) {
            return response()->json([]);
        }
        
        $cid = $classId ?: Site::ROOT;
        cache()->forever("open_{$loggedUser->id}_{$cid}_{$class}", true);

        return response()->json([]);
    }
    
    /**
     * Close opened item.
     *
     * @return Response
     */
    public function close(Request $request)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();
        
        $class = $request->input('item');
        $classId = $request->input('classId');
        
        $site = \App::make('site');
        
        $currentItem = $site->getItemByName($class);
        
        if (! $currentItem) {
            return response()->json([]);
        }
        
        $cid = $classId ?: Site::ROOT;
        cache()->forever("open_{$loggedUser->id}_{$cid}_{$class}", false);

        return response()->json([]);
    }
    
    /**
     * Show element list.
     *
     * @return Response
     */
    public function elements(Request $request)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();
        
        $open = $request->input('open');
        $class = $request->input('item');
        $classId = $request->input('classId');
        $order = $request->input('order');
        $direction = $request->input('direction');
        $resetorder = $request->input('resetorder');
        $page = (int)$request->input('page');
        
        $site = \App::make('site');
        
        $currentItem = $site->getItemByName($class);
        
        if (! $currentItem) {
            return response()->json([]);
        }

        $cid = $classId ?: Site::ROOT;

        if ($open) {
            cache()->forever("open_{$loggedUser->id}_{$cid}_{$class}", true);
        }

        if ($page) {
            cache()->put("page_{$loggedUser->id}_{$cid}_{$class}", $page, 3600);
        }

        if ($order && in_array($direction, ['asc', 'desc'])) {
            $propertyList = $currentItem->getPropertyList();

            foreach ($propertyList as $property) {
                if ($order == $property->getName()) {
                    cache()->put("order_{$loggedUser->id}_{$class}", [
                        'field' => $order,
                        'direction' => $direction,
                    ], 3600);

                    break;
                }
            }
        } elseif ($resetorder) {
            cache()->forget("order_{$loggedUser->id}_{$class}");
        }
        
        $element = $classId 
            ? Element::getByClassId($classId) : null;
        
        $html = $this->elementListView($element, $currentItem);
        
        return response()->json(['html' => $html]);
    }
    
    protected function elementListView($currentElement, $currentItem)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();

        $site = \App::make('site');
        
        /*
         * Item plugin
         */
        
        $itemPluginView = null;
         
        $itemPlugin = $site->getItemPlugin($currentItem->getNameId());

        if ($itemPlugin) {
            $view = \App::make($itemPlugin)->index($currentItem);

            if ($view) {
                $itemPluginView = is_string($view)
                    ? $view : $view->render();
            }
        }

        $currentClassId = $currentElement ? Element::getClassId($currentElement) : null;
        $currentClass = $currentElement ? Element::getClass($currentElement) : null;
        
        $propertyList = $currentItem->getPropertyList();

		if (! $loggedUser->isSuperUser()) {
			$permissionDenied = true;
			$deniedElementList = [];
			$allowedElementList = [];

			$groupList = $loggedUser->getGroups();

			foreach ($groupList as $group) {
				$itemPermission = $group->getItemPermission($currentItem->getNameId())
					? $group->getItemPermission($currentItem->getNameId())->permission
					: $group->default_permission;

				if ($itemPermission != 'deny') {
					$permissionDenied = false;
					$deniedElementList = [];
				}

				$elementPermissionList = $group->elementPermissions;

				$elementPermissionMap = [];

				foreach ($elementPermissionList as $elementPermission) {
					$classId = $elementPermission->class_id;
					$permission = $elementPermission->permission;
                    
					$array = explode(Element::ID_SEPARATOR, $classId);
                    $id = array_pop($array);
                    $class = implode(Element::ID_SEPARATOR, $array);
					
                    if ($class == $currentItem->getNameId()) {
						$elementPermissionMap[$id] = $permission;
					}
				}

				foreach ($elementPermissionMap as $id => $permission) {
					if ($permission == 'deny') {
						$deniedElementList[$id] = $id;
					} else {
						$allowedElementList[$id] = $id;
					}
				}
			}
		}

        $criteria = $currentItem->getClass()->where(
            function($query) use ($propertyList, $currentElement, $currentClass) {
                if ($currentElement) {
                    $query->orWhere('id', null);
                }

                foreach ($propertyList as $property) {
                    if (
                        $currentElement
                        && $property->isOneToOne()
                        && $property->getRelatedClass() == $currentClass
                    ) {
                        $query->orWhere(
                            $property->getName(), $currentElement->id
                        );
                    } elseif (
                        ! $currentElement
                        && $property->isOneToOne()
                        && $property->getParent()
                    ) {
                        $query->orWhere(
                            $property->getName(), null
                        );
                    }
                }
            }
        );

        foreach ($propertyList as $property) {
            if (
                $currentElement
                && $property->isManyToMany()
                && $property->getRelatedClass() == $currentClass
            ) {
                $criteria = $currentElement->{$property->getRelatedMethod()}();
                break;
            }
        }

		if (! $loggedUser->isSuperUser()) {
			if (
				$permissionDenied
				&& sizeof($allowedElementList)
			) {
				$criteria->whereIn('id', $allowedElementList);
			} elseif (
				! $permissionDenied
				&& sizeof($deniedElementList)
			) {
				$criteria->whereNotIn('id', $deniedElementList);
			} elseif ($permissionDenied) {
                return response()->json(['count' => 0]);
			}
        }

        /*
         * Browse filter
         */
        
        $browseFilterView = null;

        $browseFilter = $site->getBrowseFilter($currentItem->getNameId());

        if ($browseFilter) {
            $view = \App::make($browseFilter)->index($currentItem);
            $criteria = \App::make($browseFilter)->handle($criteria);

            if ($view) {
                $browseFilterView = is_string($view)
                    ? $view : $view->render();
            }

            $scope['hasBrowseFilter'] = true;
        }

        $open = false;

        if ($currentElement) {
            foreach ($propertyList as $property) {
                if (
                    ($property->isOneToOne() || $property->isManyToMany())
                    && $property->getRelatedClass() == $currentClass
                ) {
                    $defaultOpen = $property->getOpenItem();

                    $cid = $currentClassId ?: Site::ROOT;
                    $open = cache()->get("open_{$loggedUser->id}_{$cid}_{$currentItem->getNameId()}", $defaultOpen);
                    
                    break;
                }
            }
        } else {
            $cid = Site::ROOT;
            $open = cache()->get("open_{$loggedUser->id}_{$cid}_{$currentItem->getNameId()}", false);
        }
        
        if (! $open) {
            $total = $criteria->count();

            $scope['currentItem'] = $currentItem;
            $scope['total'] = $total;
            
            return view('moonlight::count', $scope)->render();
        }
        
        $class = $currentItem->getNameId();
        $order = Cache()->get("order_{$loggedUser->id}_{$class}");

        if (isset($order['field']) && isset($order['direction'])) {
            $orderByList = [$order['field'] => $order['direction']];
        } else {
            $orderByList = $currentItem->getOrderByList();
        }
        
        $orders = [];
        $hasOrderProperty = false;

		foreach ($orderByList as $field => $direction) {
            $criteria->orderBy($field, $direction);

            $property = $currentItem->getPropertyByName($field);

            if ($property instanceof OrderProperty) {
                $orders[$field] = 'порядку';

                if (
                    ! $currentElement 
                    || ! $property->getRelatedClass()
                    || $property->getRelatedClass() == $currentClass
                ) {
                    $hasOrderProperty = true;
                }
            } elseif ($property->getName() == 'created_at') {
                $orders[$field] = 'дате создания';
            } elseif ($property->getName() == 'updated_at') {
                $orders[$field] = 'дате изменения';
            } elseif ($property->getName() == 'deleted_at') {
                $orders[$field] = 'дате удаления';
            } else {
                $orders[$field] = 'полю &laquo;'.$property->getTitle().'&raquo;';
            }
        }
        
        $orders = implode(', ', $orders);
        
        if ($hasOrderProperty) {
            $elements = $criteria->get();

            $total = sizeof($elements);

            $currentPage = 1;
            $hasMorePages = false;
            $nextPage = null;
            $lastPage = null;
        } else {
            $cid = $currentClassId ?: Site::ROOT;

            $page = cache()->get("page_{$loggedUser->id}_{$cid}_{$currentItem->getNameId()}", 1);

            Paginator::currentPageResolver(function() use ($page) {
                return $page;
            });

            $perpage = $currentItem->getPerPage() ?: static::PER_PAGE;
            
            $elements = $criteria->paginate($perpage);

            $total = $elements->total();
            $currentPage = $elements->currentPage();
            $hasMorePages = $elements->hasMorePages();
            $nextPage = $elements->currentPage() + 1;
            $lastPage = $elements->lastPage();
        }

        /*
         * Views
         */
        
        $properties = [];
        $columns = [];
        $views = [];

        foreach ($propertyList as $property) {
            if ($property instanceof PasswordProperty) continue;
            if ($property->getHidden()) continue;

            $show = cache()->get(
                "show_column_{$loggedUser->id}_{$currentItem->getNameId()}_{$property->getName()}",
                $property->getShow()
            );

            if (! $show) continue;

            $properties[] = $property;
        }

        foreach ($propertyList as $property) {
            if ($property instanceof MainProperty) continue;
            if ($property instanceof PasswordProperty) continue;
            if ($property->getHidden()) continue;
            if ($property->getName() == 'deleted_at') continue;

            $show = cache()->get(
                "show_column_{$loggedUser->id}_{$currentItem->getNameId()}_{$property->getName()}",
                $property->getShow()
            );

            $columns[] = [
                'name' => $property->getName(),
                'title' => $property->gettitle(),
                'show' => $show,
            ];
        }

        foreach ($elements as $element) {
            foreach ($properties as $property) {
                $classId = Element::getClassId($element);

                if (
                    $property->getEditable()
                    && ! $property->getReadonly()
                ) {
                    $propertyScope = $property->setElement($element)->getEditableView();
                
                    $views[$classId][$property->getName()] = view(
                        'moonlight::properties.'.$property->getClassName().'.editable', $propertyScope
                    )->render();
                } else {
                    $propertyScope = $property->setElement($element)->getListView();
                
                    $views[$classId][$property->getName()] = view(
                        'moonlight::properties.'.$property->getClassName().'.list', $propertyScope
                    )->render();
                }
            }
        }

        /*
         * Copy and move views
         */

        $copyPropertyView = null;
        $movePropertyView = null;
        $bindPropertyViews = [];
        $unbindPropertyViews = [];

        $currentElementItem = $currentElement ? Element::getItem($currentElement) : null;

        foreach ($propertyList as $property) {
            if ($property->getHidden()) continue;
            if (! $property->isOneToOne()) continue;

            if (
                ($currentElementItem && $property->getRelatedClass() == $currentElementItem->getName())
                || (! $currentElementItem && $property->getParent())
            ) {
                $element = $currentItem->getClass();

                if ($currentElement) {
                    Element::setParent($element, $currentElement);
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
            if ($property->getHidden()) continue;
            if (! $property->isManyToMany()) continue;

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

        /*
         * Favorites
         */

        $favoriteRubrics = FavoriteRubric::where('user_id', $loggedUser->id)->
            orderBy('order')->
            get();

        $favorites = Favorite::where('user_id', $loggedUser->id)->
            get();

        $favoriteRubricMap = [];
        $elementFavoriteRubrics = [];

        foreach ($favorites as $favorite) {
            $favoriteRubricMap[$favorite->class_id][$favorite->rubric_id] = $favorite->rubric_id;
        }

        foreach ($elements as $element) {
            $classId = Element::getClassId($element);

            $elementFavoriteRubrics[$element->id] = isset($favoriteRubricMap[$classId])
                ? implode(',', $favoriteRubricMap[$classId])
                : '';
        }

        $scope['classId'] = $currentClassId;
        $scope['currentElement'] = $currentElement;
        $scope['currentItem'] = $currentItem;
        $scope['itemPluginView'] = $itemPluginView;
        $scope['browseFilterView'] = $browseFilterView;
        $scope['properties'] = $properties;
        $scope['columns'] = $columns;
        $scope['total'] = $total;
        $scope['currentPage'] = $currentPage;
        $scope['hasMorePages'] = $hasMorePages;
        $scope['nextPage'] = $nextPage;
        $scope['lastPage'] = $lastPage;
        $scope['elements'] = $elements;
        $scope['views'] = $views;
        $scope['orderByList'] = $orderByList;
        $scope['orders'] = $orders;
        $scope['hasOrderProperty'] = $hasOrderProperty;
        $scope['mode'] = 'browse';
        $scope['copyPropertyView'] = $copyPropertyView;
        $scope['movePropertyView'] = $movePropertyView;
        $scope['bindPropertyViews'] = $bindPropertyViews;
        $scope['unbindPropertyViews'] = $unbindPropertyViews;
        $scope['favoriteRubrics'] = $favoriteRubrics;
        $scope['elementFavoriteRubrics'] = $elementFavoriteRubrics;
        
        return view('moonlight::elements', $scope)->render();
    }
    
    /**
     * Show element list for autocomplete.
     *
     * @return Response
     */
    public function autocomplete(Request $request)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();
        
        $class = $request->input('item');
        $query = $request->input('query');
        
        $site = \App::make('site');
        
        $currentItem = $site->getItemByName($class);
        
        if (! $currentItem) {
            return response()->json($scope);
        }
        
        $mainProperty = $currentItem->getMainProperty();

		if (! $loggedUser->isSuperUser()) {
			$permissionDenied = true;
			$deniedElementList = [];
			$allowedElementList = [];

			$groupList = $loggedUser->getGroups();

			foreach ($groupList as $group) {
				$itemPermission = $group->getItemPermission($currentItem->getNameId())
					? $group->getItemPermission($currentItem->getNameId())->permission
					: $group->default_permission;

				if ($itemPermission != 'deny') {
					$permissionDenied = false;
					$deniedElementList = [];
				}

				$elementPermissionList = $group->elementPermissions;

				$elementPermissionMap = [];

				foreach ($elementPermissionList as $elementPermission) {
					$classId = $elementPermission->class_id;
					$permission = $elementPermission->permission;
                    
					$array = explode(Element::ID_SEPARATOR, $classId);
                    $id = array_pop($array);
                    $class = implode(Element::ID_SEPARATOR, $array);
					
                    if ($class == $item->getNameId()) {
						$elementPermissionMap[$id] = $permission;
					}
				}

				foreach ($elementPermissionMap as $id => $permission) {
					if ($permission == 'deny') {
						$deniedElementList[$id] = $id;
					} else {
						$allowedElementList[$id] = $id;
					}
				}
			}
		}

        $criteria = $currentItem->getClass()->query();
        
        if ($query) {
            $criteria->
                where('id', (int)$query)->
                orWhere($mainProperty, 'ilike', "%$query%");
        }

		if (! $loggedUser->isSuperUser()) {
			if (
				$permissionDenied
				&& sizeof($allowedElementList)
			) {
				$criteria->whereIn('id', $allowedElementList);
			} elseif (
				! $permissionDenied
				&& sizeof($deniedElementList)
			) {
				$criteria->whereNotIn('id', $deniedElementList);
			} elseif ($permissionDenied) {
                return response()->json(['count' => 0]);
			}
		}
        
        $orderByList = $currentItem->getOrderByList();

		foreach ($orderByList as $field => $direction) {
            $criteria->orderBy($field, $direction);
        }

		$elements = $criteria->limit(static::PER_PAGE)->get();
        
        $scope['suggestions'] = [];
        
        foreach ($elements as $element) {
            $scope['suggestions'][] = [
                'value' => $element->$mainProperty,
                'classId' => Element::getClassId($element),
                'id' => $element->id,
            ];
        }
        
        return response()->json($scope);
    }
    
    /**
     * Show browse element.
     *
     * @return View
     */
    public function element(Request $request, $classId)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();
        
        $element = Element::getByClassId($classId);
        
        if ( ! $element) {
            return redirect()->route('moonlight.browse');
        }
        
        $currentItem = Element::getItem($element);
        
        $parentList = Element::getParentList($element);

        $parents = [];

        foreach ($parentList as $parent) {
            $parentItem = Element::getItem($parent);
            $parentMainProperty = $parentItem->getMainProperty();
            $parents[] = [
                'classId' => Element::getClassId($parent),
                'name' => $parent->$parentMainProperty,
            ];
        }

        $mainProperty = $currentItem->getMainProperty();
        
        $site = \App::make('site');

        $styles = [];
        $scripts = [];

        /*
         * Browse styles and scripts
         */

        $styles = array_merge($styles, $site->getBrowseStyles($classId));
        $scripts = array_merge($scripts, $site->getBrowseScripts($classId));

        /*
         * Browse plugin
         */
        
        $browsePluginView = null;
         
        $browsePlugin = $site->getBrowsePlugin($classId);

        if ($browsePlugin) {
            $view = \App::make($browsePlugin)->index($element);

            if ($view) {
                $browsePluginView = is_string($view)
                    ? $view : $view->render();
            }
        }
        
        $itemList = $site->getItemList();
        
        $binds = [];
		$items = [];
        $creates = [];
        
        foreach ($site->getBinds() as $name => $classes) {
            if (
                $name == Element::getClassId($element) 
                || $name == $currentItem->getNameId()
            ) {
                foreach ($classes as $class) {
                    $binds[] = $class;
                }
            }
        }

        foreach ($binds as $bind) {
            $item = $site->getItemByName($bind);

            if (! $item) continue;

            $propertyList = $item->getPropertyList();

            $mainPropertyTitle = $item->getMainPropertyTitle();

            $hasOrderProperty = false;

            foreach ($propertyList as $property) {
                if (
                    $property instanceof OrderProperty
                    && (
                        ! $property->getRelatedClass()
                        || $property->getRelatedClass() == Element::getClass($element)
                    )
                ) {
                    $hasOrderProperty = true;
                    break;
                }
            }

            foreach ($propertyList as $property) {
                if (
                    $property->isOneToOne()
                    && $property->getRelatedClass() == Element::getClass($element)
                ) {
                    $items[] = [
                        'id' => $item->getNameId(),
                        'name' => $item->getTitle(),
                    ];

                    if ($item->getCreate()) {
                        $creates[] = [
                            'id' => $item->getNameId(),
                            'name' => $item->getTitle(),
                        ];
                    }

                    /*
                     * Item styles and scripts
                     */

                    $styles = array_merge($styles, $site->getItemStyles($bind));
                    $scripts = array_merge($scripts, $site->getItemScripts($bind));
                    
                    break;
                } elseif (
                    $property->isManyToMany()
                    && $property->getRelatedClass() == Element::getClass($element)
                ) {
                    $items[] = [
                        'id' => $item->getNameId(),
                        'name' => $item->getTitle(),
                    ];

                    if ($item->getCreate()) {
                        $creates[] = [
                            'id' => $item->getNameId(),
                            'name' => $item->getTitle(),
                        ];
                    }

                    /*
                     * Item styles and scripts
                     */

                    $styles = array_merge($styles, $site->getItemStyles($bind));
                    $scripts = array_merge($scripts, $site->getItemScripts($bind));
                    
                    break;
                }
            }
        }

        $rubricController = new RubricController;
        
        $rubrics = $rubricController->sidebar($classId);

        $scope['element'] = $element;
        $scope['classId'] = $classId;
        $scope['mainProperty'] = $mainProperty;
        $scope['parents'] = $parents;
        $scope['currentItem'] = $currentItem;
        $scope['browsePluginView'] = $browsePluginView;
		$scope['items'] = $items;
        $scope['creates'] = $creates;
        $scope['rubrics'] = $rubrics;

        view()->share([
            'styles' => $styles,
            'scripts' => $scripts,
        ]);
            
        return view('moonlight::element', $scope);
    }
    
    /**
     * Show browse root.
     *
     * @return View
     */
    public function root(Request $request)
    {
        $scope = [];

        $loggedUser = Auth::guard('moonlight')->user();

        $site = \App::make('site');

        $styles = [];
        $scripts = [];
        
        $itemList = $site->getItemList();
        $binds = $site->getBinds();        
        
        $plugin = null;
		$items = [];
        $creates = [];

        if (isset($binds[Site::ROOT])) {
            foreach ($binds[Site::ROOT] as $bind) {
                $item = $site->getItemByName($bind);

                if (! $item) continue;

                $items[] = [
                    'id' => $item->getNameId(),
                    'name' => $item->getTitle(),
                ];

                if ($item->getCreate()) {
                    $creates[] = [
                        'id' => $item->getNameId(),
                        'name' => $item->getTitle(),
                    ];
                }

                /*
                 * Item styles and scripts
                 */

                $styles = array_merge($styles, $site->getItemStyles($bind));
                $scripts = array_merge($scripts, $site->getItemScripts($bind));
            }
        }

        $rubricController = new RubricController;

        $rubrics = $rubricController->sidebar();

        $scope['plugin'] = $plugin;
		$scope['items'] = $items;
        $scope['creates'] = $creates;
        $scope['rubrics'] = $rubrics;

        view()->share([
            'styles' => $styles,
            'scripts' => $scripts,
        ]);
            
        return view('moonlight::root', $scope);
    }
}