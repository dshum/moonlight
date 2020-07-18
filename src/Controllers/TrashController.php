<?php

namespace Moonlight\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Moonlight\Main\Item;
use Moonlight\Models\UserActionType;
use Moonlight\Models\UserAction;

class TrashController extends Controller
{
    /**
     * Default number of elements per page.
     */
    const PER_PAGE = 10;

    /**
     * Return the count of element list.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function count(Request $request)
    {
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $itemName = $request->input('item');

        $item = $site->getItemByName($itemName);

        if (! $item) {
            return response()->json(['error' => 'Класс элемента не найден.']);
        }

        $count = Cache::remember("trash_item_{$loggedUser->id}_{$item->getName()}", 86400, function () use ($loggedUser, $item) {
            return $item->getClass()->onlyTrashed()->where(function ($query) use ($loggedUser, $item) {
                $permissionDenied = true;
                $deniedElementList = [];
                $allowedElementList = [];

                if (! $loggedUser->isSuperUser()) {
                    foreach ($loggedUser->groups as $group) {
                        $groupItemPermission = $group->getItemPermission($item);
                        $itemPermission = $groupItemPermission
                            ? $groupItemPermission->permission
                            : $group->default_permission;

                        if ($itemPermission != 'deny') {
                            $permissionDenied = false;
                            $deniedElementList = [];
                        }

                        $elementPermissions = $group->getElementPermissionsByItem($item);

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
            })->count();
        });

        return response()->json(['count' => $count]);
    }

    /**
     * Show element list.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception|\Throwable
     */
    public function elements(Request $request)
    {
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $itemName = $request->input('item');
        $order = $request->input('order');
        $direction = $request->input('direction');
        $resetOrder = $request->input('resetorder');

        $currentItem = $site->getItemByName($itemName);

        if (! $currentItem) {
            return response()->json(['error' => 'Класс элемента не найден.']);
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

        $elements = $this->elementListView($request, $currentItem);

        return response()->json(['html' => $elements]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Moonlight\Main\Item $currentItem
     * @return array|string
     * @throws \Throwable
     */
    protected function elementListView(Request $request, Item $currentItem)
    {
        $loggedUser = Auth::guard('moonlight')->user();

        $currentItemClass = $currentItem->getClass();
        $propertyList = $currentItem->getPropertyList();

        $criteria = $currentItem->getClass()->onlyTrashed()->withoutGlobalScopes()
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

                    if (
                        $permissionDenied
                        && sizeof($allowedElementList)
                    ) {
                        $query->whereIn('id', $allowedElementList);
                    } elseif (
                        ! $permissionDenied
                        && sizeof($deniedElementList)
                    ) {
                        $query->whereNotIn('id', $deniedElementList);
                    } elseif ($permissionDenied) {
                        $query->whereNull('id');
                    }
                }
            })
            ->where(
                function ($query) use ($loggedUser, $currentItem, $propertyList, $request) {
                    foreach ($propertyList as $property) {
                        $query = $property->setRequest($request)->searchQuery($query);
                    }
                }
            );

        $itemName = $currentItem->getName();
        $order = Cache::get("order_{$loggedUser->id}_{$itemName}");

        if (isset($order['field']) && isset($order['direction'])) {
            $orderByList = [$order['field'] => $order['direction']];
        } else {
            $orderByList = [$currentItemClass->getDeletedAtColumn() => 'desc'];
        }

        $orders = [];

        foreach ($orderByList as $field => $direction) {
            $property = $currentItem->getPropertyByName($field);

            if (! $property) {
                continue;
            }

            $criteria->orderBy($field, $direction);

            if ($property->getName() == $currentItemClass->getCreatedAtColumn()) {
                $orders[$field] = 'дате создания';
            } elseif ($property->getName() == $currentItemClass->getUpdatedAtColumn()) {
                $orders[$field] = 'дате изменения';
            } elseif ($property->getName() == $currentItemClass->getDeletedAtColumn()) {
                $orders[$field] = 'дате удаления';
            } else {
                $orders[$field] = 'полю &laquo;'.$property->getTitle().'&raquo;';
            }
        }

        $orders = implode(', ', $orders);

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

        if (! $total) {
            return view('moonlight::elements', [
                'total' => 0,
                'itemComponentView' => null,
                'mode' => 'trash',
            ])->render();
        }

        if ($currentPage > $lastPage) {
            $total = 0;
        }

        $properties = [];
        $columns = [];
        $views = [];

        foreach ($propertyList as $property) {
            $show = Cache::get(
                "show_column_{$loggedUser->id}_{$currentItem->getName()}_{$property->getName()}",
                $property->getShow()
            );

            if ($property->getName() == $currentItemClass->getDeletedAtColumn()) {
                $show = true;
            }

            if (
                $property->getHidden()
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
                $propertyScope = $property->setElement($element)->getListView();

                $views[$element->id][$property->getName()] = view(
                    'moonlight::properties.'.$property->getClassName().'.list', $propertyScope
                )->render();
            }
        }

        return view('moonlight::elements', [
            'currentItem' => $currentItem,
            'itemComponentView' => null,
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
            'hasOrderProperty' => false,
            'mode' => 'trash',
            'copyPropertyView' => null,
            'movePropertyView' => null,
            'bindPropertyViews' => null,
            'unbindPropertyViews' => null,
            'favoriteRubrics' => null,
            'elementFavoriteRubrics' => null,
        ])->render();
    }

    /**
     * Restore element.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $classId
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore(Request $request, string $classId)
    {
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $element = $site->getByClassIdOnlyTrashed($classId);

        if (! $element) {
            return response()->json(['error' => 'Элемент не найден.']);
        } elseif (! $loggedUser->hasDeleteAccess($element)) {
            return response()->json(['error' => 'Нет прав на восстановление элемента.']);
        }

        $currentItem = $site->getItemByElement($element);

        $element->restore();

        UserAction::log(
            UserActionType::ACTION_TYPE_RESTORE_ELEMENT_ID,
            $classId
        );

        if (Cache::has("trash_item_{$currentItem->getName()}")) {
            Cache::forget("trash_item_{$currentItem->getName()}");
        }

        $url = route('moonlight.trash.item', $currentItem->getName());

        return response()->json([
            'restored' => $classId,
            'url' => $url,
        ]);
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

        $element = $site->getByClassIdOnlyTrashed($classId);

        if (! $element) {
            return response()->json(['error' => 'Элемент не найден.']);
        }

        if (! $loggedUser->hasDeleteAccess($element)) {
            return response()->json(['error' => 'Нет прав на удаление элемента.']);
        }

        $currentItem = $site->getItemByElement($element);

        $propertyList = $currentItem->getPropertyList();

        foreach ($propertyList as $propertyName => $property) {
            $property->setElement($element)->drop();
        }

        $element->forceDelete();

        UserAction::log(
            UserActionType::ACTION_TYPE_DROP_ELEMENT_ID,
            $classId
        );

        if (Cache::has("trash_item_{$currentItem->getName()}")) {
            Cache::forget("trash_item_{$currentItem->getName()}");
        }

        $url = route('moonlight.trash.item', $currentItem->getName());

        return response()->json([
            'deleted' => $classId,
            'url' => $url,
        ]);
    }

    /**
     * View element.
     *
     * @param \Illuminate\Http\Request $request
     * @param $classId
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Throwable
     */
    public function view(Request $request, $classId)
    {
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $element = $site->getByClassIdOnlyTrashed($classId);

        if (! $element) {
            return redirect()->route('moonlight.trash');
        }

        $currentItem = $site->getItemByElement($element);

        $mainProperty = $currentItem->getMainProperty();
        $propertyList = $currentItem->getPropertyList();

        $views = [];

        foreach ($propertyList as $property) {
            if (! $property->getHidden()) {
                $propertyScope = $property->setReadonly(true)->setElement($element)->getEditView();

                $views[$property->getName()] = view(
                    'moonlight::properties.'.$property->getClassName().'.edit', $propertyScope
                )->render();
            }
        }

        $itemList = $site->getItemList();

        foreach ($itemList as $key => $item) {
            if (! $loggedUser->hasViewDefaultAccess($item)) {
                $itemList->forget($key);
            }
        }

        $items = [];

        foreach ($itemList as $item) {
            $total = Cache::remember("trash_item_{$item->getName()}", 86400, function () use ($item) {
                return $item->getClass()->onlyTrashed()->count();
            });

            if ($total) {
                $items[$item->getName()] = (object) [
                    'name' => $item->getName(),
                    'class_name' => $item->getClassBaseName(),
                    'title' => $item->getTitle(),
                    'total' => $total,
                ];
            }
        }

        return view('moonlight::trash.view', [
            'element' => $element,
            'classId' => $classId,
            'mainProperty' => $mainProperty,
            'currentItem' => $currentItem,
            'views' => $views,
            'items' => $items,
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param string $itemName
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * @throws \Throwable
     */
    public function item(Request $request, string $itemName)
    {
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $currentItem = $site->getItemByName($itemName);

        if (! $currentItem) {
            return redirect()->route('moonlight.trash');
        } elseif (! $loggedUser->hasViewDefaultAccess($currentItem)) {
            return redirect()->route('moonlight.trash');
        }

        $itemList = $site->getItemList();

        foreach ($itemList as $key => $item) {
            if (! $loggedUser->hasViewDefaultAccess($item)) {
                $itemList->forget($key);
            }
        }

        $items = [];

        foreach ($itemList as $item) {
            $total = Cache::remember("trash_item_{$item->getName()}", 86400, function () use ($item) {
                return $item->getClass()->onlyTrashed()->count();
            });

            if ($total) {
                $items[$item->getName()] = (object) [
                    'name' => $item->getName(),
                    'class_name' => $item->getClassBaseName(),
                    'title' => $item->getTitle(),
                    'total' => $total,
                ];
            }
        }

        $propertyList = $currentItem->getPropertyList();

        $activeSearchProperties = Cache::get("search_properties_{$loggedUser->id}", []);
        $activeProperties = $activeSearchProperties[$currentItem->getName()] ?? [];

        $properties = [];
        $actives = [];
        $links = [];
        $views = [];

        foreach ($propertyList as $property) {
            if ($property->getHidden()) {
                continue;
            }

            $propertyScope = $property->setRequest($request)->getSearchView();

            if ($propertyScope) {
                $links[$property->getName()] = view(
                    'moonlight::properties.'.$property->getClassName().'.link', $propertyScope
                )->render();

                $views[$property->getName()] = view(
                    'moonlight::properties.'.$property->getClassName().'.search', $propertyScope
                )->render();

                $properties[] = $property;
            }

            if (isset($activeProperties[$property->getName()])) {
                $actives[$property->getName()] = $activeProperties[$property->getName()];
            }
        }

        $elements = $this->elementListView($request, $currentItem);

        return view('moonlight::trash.item', [
            'currentItem' => $currentItem,
            'properties' => $properties,
            'actives' => $actives,
            'links' => $links,
            'views' => $views,
            'elements' => $elements,
            'items' => $items,
        ]);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $itemList = $site->getItemList();

        foreach ($itemList as $key => $item) {
            if (! $loggedUser->hasViewDefaultAccess($item)) {
                $itemList->forget($key);
            }
        }

        $items = [];

        foreach ($itemList as $item) {
            $total = Cache::remember("trash_item_{$item->getName()}", 86400, function () use ($item) {
                return $item->getClass()->onlyTrashed()->count();
            });

            if ($total) {
                $items[$item->getName()] = (object) [
                    'name' => $item->getName(),
                    'class_name' => $item->getClassBaseName(),
                    'title' => $item->getTitle(),
                    'total' => $total,
                ];
            }
        }

        $scope['items'] = $items;

        return view('moonlight::trash.index', $scope);
    }
}
