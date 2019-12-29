<?php

namespace Moonlight\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Moonlight\Main\Element;
use Moonlight\Main\UserActionType;
use Moonlight\Models\UserAction;

class TrashController extends Controller
{
    const PER_PAGE = 10;

    /**
     * Return the count of element list.
     *
     * @param $item
     * @return Response
     */
    public function count($item)
    {
        $loggedUser = Auth::guard('moonlight')->user();

        if (! $loggedUser->isSuperUser()) {
            $permissionDenied = true;
            $deniedElementList = [];
            $allowedElementList = [];

            $groupList = $loggedUser->getGroups();

            foreach ($groupList as $group) {
                $itemPermission = $group->getItemPermission($item->getNameId())
                    ? $group->getItemPermission($item->getNameId())->permission
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

        $criteria = $item->getClass()->onlyTrashed();

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

        $count = $criteria->count();

        return $count;
    }

    /**
     * Show element list.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function elements(Request $request)
    {
        $scope = [];

        $loggedUser = Auth::guard('moonlight')->user();

        $class = $request->input('item');
        $order = $request->input('order');
        $direction = $request->input('direction');
        $resetorder = $request->input('resetorder');

        $site = \App::make('site');

        $currentItem = $site->getItemByName($class);

        if (! $currentItem) {
            return response()->json([]);
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

        $elements = $this->elementListView($request, $currentItem);

        return response()->json(['html' => $elements]);
    }

    protected function elementListView(Request $request, $currentItem)
    {
        $scope = [];

        $loggedUser = Auth::guard('moonlight')->user();

        $site = \App::make('site');

        // Item plugin
        $itemPluginView = null;

        $itemPlugin = $site->getItemPlugin($currentItem->getNameId());

        if ($itemPlugin) {
            $view = \App::make($itemPlugin)->index($currentItem);

            if ($view) {
                $itemPluginView = is_string($view)
                    ? $view : $view->render();
            }
        }

        $currentItemClass = $currentItem->getClass();
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

        $criteria = $currentItem->getClass()->onlyTrashed()->where(
            function ($query) use ($loggedUser, $currentItem, $propertyList, $request) {
                foreach ($propertyList as $property) {
                    $query = $property->setRequest($request)->searchQuery($query);
                }
            }
        );

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
                $scope['total'] = 0;
                $scope['mode'] = 'trash';

                return view('moonlight::elements', $scope)->render();
            }
        }

        $class = $currentItem->getNameId();
        $order = cache()->get("order_{$loggedUser->id}_{$class}");

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

        if (cache()->has("per_page_{$loggedUser->id}_{$currentItem->getNameId()}")) {
            $perPage = cache()->get("per_page_{$loggedUser->id}_{$currentItem->getNameId()}");
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

        if ($currentPage > $lastPage) {
            $total = 0;
        }

        $properties = [];
        $columns = [];
        $views = [];

        foreach ($propertyList as $property) {
            $show = cache()->get(
                "show_column_{$loggedUser->id}_{$currentItem->getNameId()}_{$property->getName()}",
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

            $show = cache()->get(
                "show_column_{$loggedUser->id}_{$currentItem->getNameId()}_{$property->getName()}",
                $property->getShow()
            );

            $columns[] = [
                'name' => $property->getName(),
                'title' => $property->getTitle(),
                'show' => $show,
            ];
        }

        foreach ($elements as $element) {
            foreach ($properties as $property) {
                $propertyScope = $property->setElement($element)->getListView();

                $views[Element::getClassId($element)][$property->getName()] = view(
                    'moonlight::properties.'.$property->getClassName().'.list', $propertyScope
                )->render();
            }
        }

        $scope['currentItem'] = $currentItem;
        $scope['itemPluginView'] = $itemPluginView;
        $scope['properties'] = $properties;
        $scope['columns'] = $columns;
        $scope['total'] = $total;
        $scope['perPage'] = $perPage;
        $scope['currentPage'] = $currentPage;
        $scope['hasMorePages'] = $hasMorePages;
        $scope['nextPage'] = $nextPage;
        $scope['lastPage'] = $lastPage;
        $scope['elements'] = $elements;
        $scope['views'] = $views;
        $scope['orderByList'] = $orderByList;
        $scope['orders'] = $orders;
        $scope['hasOrderProperty'] = false;
        $scope['mode'] = 'trash';
        $scope['copyPropertyView'] = null;
        $scope['movePropertyView'] = null;
        $scope['bindPropertyViews'] = null;
        $scope['unbindPropertyViews'] = null;
        $scope['favoriteRubrics'] = null;
        $scope['elementFavoriteRubrics'] = null;

        return view('moonlight::elements', $scope)->render();
    }

    /**
     * Restore element.
     *
     * @param \Illuminate\Http\Request $request
     * @param $classId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function restore(Request $request, $classId)
    {
        $scope = [];

        $loggedUser = Auth::guard('moonlight')->user();

        $element = Element::getByClassIdOnlyTrashed($classId);

        if (! $element) {
            $scope['error'] = 'Элемент не найден.';

            return response()->json($scope);
        }

        if (! $loggedUser->hasDeleteAccess($element)) {
            $scope['error'] = 'Нет прав на восстановление элемента.';

            return response()->json($scope);
        }

        $currentItem = Element::getItem($element);

        $element->restore();

        UserAction::log(
            UserActionType::ACTION_TYPE_RESTORE_ELEMENT_ID,
            $classId
        );

        if (cache()->has("trash_item_{$currentItem->getNameId()}")) {
            cache()->forget("trash_item_{$currentItem->getNameId()}");
        }

        $url = route('moonlight.trash.item', $currentItem->getNameId());

        $scope['restored'] = $classId;
        $scope['url'] = $url;

        return response()->json($scope);
    }

    /**
     * Delete element.
     *
     * @param \Illuminate\Http\Request $request
     * @param $classId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function delete(Request $request, $classId)
    {
        $scope = [];

        $loggedUser = Auth::guard('moonlight')->user();

        $element = Element::getByClassIdOnlyTrashed($classId);

        if (! $element) {
            $scope['error'] = 'Элемент не найден.';

            return response()->json($scope);
        }

        if (! $loggedUser->hasDeleteAccess($element)) {
            $scope['error'] = 'Нет прав на удаление элемента.';

            return response()->json($scope);
        }

        $currentItem = Element::getItem($element);

        $propertyList = $currentItem->getPropertyList();

        foreach ($propertyList as $propertyName => $property) {
            $property->setElement($element)->drop();
        }

        $element->forceDelete();

        UserAction::log(
            UserActionType::ACTION_TYPE_DROP_ELEMENT_ID,
            $classId
        );

        if (cache()->has("trash_item_{$currentItem->getNameId()}")) {
            cache()->forget("trash_item_{$currentItem->getNameId()}");
        }

        $url = route('moonlight.trash.item', $currentItem->getNameId());

        $scope['deleted'] = $classId;
        $scope['url'] = $url;

        return response()->json($scope);
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
        $scope = [];

        $loggedUser = Auth::guard('moonlight')->user();

        $site = \App::make('site');

        $element = Element::getByClassIdOnlyTrashed($classId);

        if (! $element) {
            return redirect()->route('moonlight.trash');
        }

        $currentItem = Element::getItem($element);

        $mainProperty = $currentItem->getMainProperty();
        $propertyList = $currentItem->getPropertyList();

        $properties = [];
        $views = [];

        foreach ($propertyList as $property) {
            if ($property->getHidden()) {
                continue;
            }

            $properties[] = $property;
        }

        foreach ($properties as $property) {
            $propertyScope = $property->setReadonly(true)->setElement($element)->getEditView();

            $views[$property->getName()] = view(
                'moonlight::properties.'.$property->getClassName().'.edit', $propertyScope
            )->render();
        }

        $itemList = $site->getItemList();

        foreach ($itemList as $key => $item) {
            if (! $loggedUser->hasViewDefaultAccess($item)) {
                $itemList->forget($key);
            }
        }

        $items = [];
        $totals = [];

        foreach ($itemList as $item) {
            $total = cache()->remember("trash_item_{$item->getNameId()}", 86400, function () use ($item) {
                return $this->total($item);
            });

            if ($total) {
                $items[$item->getNameId()] = $item;
                $totals[$item->getNameId()] = $total;
            }
        }

        $scope['element'] = $element;
        $scope['classId'] = $classId;
        $scope['mainProperty'] = $mainProperty;
        $scope['currentItem'] = $currentItem;
        $scope['views'] = $views;
        $scope['items'] = $items;
        $scope['totals'] = $totals;

        return view('moonlight::trashed', $scope);
    }

    /**
     * Return the total count of element list.
     *
     * @param $item
     * @return Response
     */
    public function total($item)
    {
        return $item->getClass()->onlyTrashed()->count();
    }

    public function item(Request $request, $class)
    {
        $scope = [];

        $loggedUser = Auth::guard('moonlight')->user();

        $site = \App::make('site');

        $currentItem = $site->getItemByName($class);

        if (! $currentItem) {
            return redirect()->route('moonlight.trash');
        }

        if (! $loggedUser->hasViewDefaultAccess($currentItem)) {
            return redirect()->route('moonlight.trash');
        }

        $itemList = $site->getItemList();

        foreach ($itemList as $key => $item) {
            if (! $loggedUser->hasViewDefaultAccess($item)) {
                $itemList->forget($key);
            }
        }

        $items = [];
        $totals = [];

        foreach ($itemList as $item) {
            $total = cache()->remember("trash_item_{$item->getNameId()}", 86400, function () use ($item) {
                return $this->total($item);
            });

            if ($total) {
                $items[$item->getNameId()] = $item;
                $totals[$item->getNameId()] = $total;
            }
        }

        $propertyList = $currentItem->getPropertyList();

        $properties = [];
        $actives = [];
        $links = [];
        $views = [];

        foreach ($propertyList as $property) {
            if ($property->getHidden()) {
                continue;
            }

            $propertyScope = $property->setRequest($request)->getSearchView();

            if (! $propertyScope) {
                continue;
            }

            $links[$property->getName()] = view(
                'moonlight::properties.'.$property->getClassName().'.link', $propertyScope
            )->render();

            $views[$property->getName()] = view(
                'moonlight::properties.'.$property->getClassName().'.search', $propertyScope
            )->render();

            $properties[] = $property;
        }

        $activeSearchProperties = cache()->get("search_properties_{$loggedUser->id}", []);

        $activeProperties =
            isset($activeSearchProperties[$currentItem->getNameId()])
                ? $activeSearchProperties[$currentItem->getNameId()]
                : [];

        foreach ($propertyList as $property) {
            if (isset($activeProperties[$property->getName()])) {
                $actives[$property->getName()] = $activeProperties[$property->getName()];
            }
        }

        $elements = $this->elementListView($request, $currentItem);

        $scope['currentItem'] = $currentItem;
        $scope['properties'] = $properties;
        $scope['actives'] = $actives;
        $scope['links'] = $links;
        $scope['views'] = $views;
        $scope['elements'] = $elements;
        $scope['items'] = $items;
        $scope['totals'] = $totals;

        return view('moonlight::trashItem', $scope);
    }

    public function index()
    {
        $scope = [];

        $loggedUser = Auth::guard('moonlight')->user();

        $site = \App::make('site');

        $itemList = $site->getItemList();

        foreach ($itemList as $key => $item) {
            if (! $loggedUser->hasViewDefaultAccess($item)) {
                $itemList->forget($key);
            }
        }

        $items = [];
        $totals = [];

        foreach ($itemList as $item) {
            $total = cache()->remember("trash_item_{$item->getNameId()}", 86400, function () use ($item) {
                return $this->total($item);
            });

            if ($total) {
                $items[$item->getNameId()] = $item;
                $totals[$item->getNameId()] = $total;
            }
        }

        $scope['items'] = $items;
        $scope['totals'] = $totals;

        return view('moonlight::trash', $scope);
    }
}
