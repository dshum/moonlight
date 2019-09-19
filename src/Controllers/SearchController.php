<?php

namespace Moonlight\Controllers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Moonlight\Main\Element;
use Moonlight\Models\FavoriteRubric;
use Moonlight\Models\Favorite;
use Moonlight\Properties\BaseProperty;
use Moonlight\Properties\MainProperty;
use Moonlight\Properties\OrderProperty;
use Moonlight\Properties\PasswordProperty;
use Moonlight\Properties\DateProperty;
use Moonlight\Properties\DatetimeProperty;
use Carbon\Carbon;

class SearchController extends Controller
{
    const PER_PAGE = 10;

    /**
     * Sort items.
     *
     * @return Response
     */
    public function sort(Request $request)
    {
        $scope = [];

        $loggedUser = Auth::guard('moonlight')->user();

        $site = \App::make('site');

        $class = $request->input('item');
        $sort = $request->input('sort');

        $currentItem = $site->getItemByName($class);

        $search = cache()->get("search_items_{$loggedUser->id}", []);

        if (in_array($sort, ['rate', 'date', 'name', 'default'])) {
            $search['sort'] = $sort;
            $search = cache()->forever("search_items_{$loggedUser->id}", $search);
        }

        $html = $this->itemListView($currentItem);

        return response()->json(['html' => $html]);
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

    public function item(Request $request, $class)
    {
        $scope = [];

        $loggedUser = Auth::guard('moonlight')->user();

        $site = \App::make('site');

        $currentItem = $site->getItemByName($class);

        if (! $currentItem) {
            return redirect()->route('moonlight.search');
        }

        if (! $loggedUser->hasViewDefaultAccess($currentItem)) {
            return redirect()->route('moonlight.search');
        }

        $propertyList = $currentItem->getPropertyList();

        $properties = [];
        $actives = [];
        $links = [];
        $views = [];
        $orderProperties = [];
        $ones = [];
        $hasOrderProperty = false;

        foreach ($propertyList as $property) {
            if ($property instanceof OrderProperty) {
                $orderProperties[] = $property;
                $hasOrderProperty = true;
            }

            if ($property->getHidden()) continue;
            if ($property->getName() == 'deleted_at') continue;

            $orderProperties[] = $property;
        }

        foreach ($propertyList as $property) {
            if ($property->getHidden()) continue;
            if ($property->getName() == 'deleted_at') continue;

            $propertyScope = $property->setRequest($request)->getSearchView();

            if (! $propertyScope) continue;

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

        $action = $request->input('action');

        if ($action == 'search') {
            $search = cache()->get("search_items_{$loggedUser->id}", []);

            $search['sortDate'][$class] = Carbon::now()->toDateTimeString();

            if (isset($search['sortRate'][$class])) {
                $search['sortRate'][$class]++;
            } else {
                $search['sortRate'][$class] = 1;
            }

            $search = cache()->forever("search_items_{$loggedUser->id}", $search);

            $elements = $this->elementListView($request, $currentItem);
        } else {
            $elements = null;
        }

        $items = $this->itemListView($currentItem);

        $styles = [];
        $scripts = [];

        // Item styles and scripts
        $styles = array_merge($styles, $site->getItemStyles($class));
        $scripts = array_merge($scripts, $site->getItemScripts($class));

        // Search styles and scripts
        $styles = array_merge($styles, $site->getSearchStyles($class));
        $scripts = array_merge($scripts, $site->getSearchScripts($class));

        $scope['items'] = $items;
        $scope['currentItem'] = $currentItem;
        $scope['properties'] = $properties;
        $scope['actives'] = $actives;
        $scope['links'] = $links;
        $scope['views'] = $views;
        $scope['orderProperties'] = $orderProperties;
        $scope['hasOrderProperty'] = $hasOrderProperty;
        $scope['action'] = $action;
        $scope['elements'] = $elements;

        view()->share([
            'styles' => $styles,
            'scripts' => $scripts,
        ]);

        return view('moonlight::searchItem', $scope);
    }

    public function active(Request $request, $class, $name)
    {
        $scope = [];

        $loggedUser = Auth::guard('moonlight')->user();

        $site = \App::make('site');

        $item = $site->getItemByName($class);

        if (! $item) {
            $scope['message'] = 'Класс не найден.';
            return response()->json($scope);
        }

        $property = $item->getPropertyByName($name);

        if (! $property) {
            $scope['message'] = 'Свойство класса не найдено.';
            return response()->json($scope);
        }

        $active = $request->input('active');

        $activeProperties = cache()->get("search_properties_{$loggedUser->id}", []);

        if (
            $active != 'true'
            && isset($activeProperties[$item->getNameId()][$property->getName()])
        ) {
            unset($activeProperties[$item->getNameId()][$property->getName()]);
        } elseif ($active) {
            $activeProperties[$item->getNameId()][$property->getName()] = 1;
        }

        $search = cache()->forever("search_properties_{$loggedUser->id}", $activeProperties);

        return response()->json($scope);
    }

    public function index(Request $request)
    {
        $scope = [];

        $loggedUser = Auth::guard('moonlight')->user();

        $items = $this->itemListView();

        $scope['items'] = $items;

        return view('moonlight::search', $scope);
    }

    protected function itemListView($currentItem = null) {
        $scope = [];

        $loggedUser = Auth::guard('moonlight')->user();

        $site = \App::make('site');

        $itemList = $site->getItemList();

        foreach ($itemList as $key => $item) {
            if (! $loggedUser->hasViewDefaultAccess($item)) {
                $itemList->forget($key);
            }
        }

        $search = cache()->get("search_items_{$loggedUser->id}", []);

        $sort = isset($search['sort'])
            ? $search['sort'] : 'default';

        $map = [];

        if ($sort == 'name') {
            foreach ($itemList as $item) {
                $map[$item->getTitle()] = $item;
            }

            ksort($map);
        } elseif ($sort == 'date') {
            $sortDate = isset($search['sortDate'])
                ? $search['sortDate'] : [];

            arsort($sortDate);

            foreach ($sortDate as $class => $date) {
                $map[$class] = $site->getItemByName($class);
            }

            foreach ($itemList as $item) {
                $map[$item->getNameId()] = $item;
            }
        } elseif ($sort == 'rate') {
            $sortRate = isset($search['sortRate'])
                ? $search['sortRate'] : array();

            arsort($sortRate);

            foreach ($sortRate as $class => $rate) {
                $map[$class] = $site->getItemByName($class);
            }

            foreach ($itemList as $item) {
                $map[$item->getNameId()] = $item;
            }
        } else {
            foreach ($itemList as $item) {
                $map[] = $item;
            }
        }

        $items = [];

        foreach ($map as $item) {
            $items[] = $item;
        }

        unset($map);

        $sorts = [
            'rate' => 'частоте',
            'date' => 'дате',
            'name' => 'названию',
            'default' => 'умолчанию',
        ];

        if (! isset($sorts[$sort])) {
            $sort = 'default';
        }

        $scope['currentItem'] = $currentItem;
        $scope['items'] = $items;
        $scope['sorts'] = $sorts;
        $scope['sort'] = $sort;

        return view('moonlight::searchList', $scope)->render();
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
            function($query) use ($loggedUser, $currentItem, $propertyList, $request) {
                foreach ($propertyList as $property) {
                    $property->setRequest($request);
                    $query = $property->searchQuery($query);
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
                $scope['mode'] = 'search';
                return view('moonlight::elements', $scope)->render();
            }
        }

        $class = $currentItem->getNameId();
        $order = cache()->get("order_{$loggedUser->id}_{$class}");

        if (isset($order['field']) && isset($order['direction'])) {
            $orderByList = [$order['field'] => $order['direction']];
        } else {
            $orderByList = $currentItem->getOrderByList();
        }

        $orders = [];

        foreach ($orderByList as $field => $direction) {
            $criteria->orderBy($field, $direction);

            $property = $currentItem->getPropertyByName($field);

            if ($property instanceof OrderProperty) {
                $orders[$field] = 'порядку';
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
                if (
                    $property->getEditable()
                    && ! $property->getReadonly()
                ) {
                    $propertyScope = $property->setElement($element)->getEditableView();

                    $views[Element::getClassId($element)][$property->getName()] = view(
                        'moonlight::properties.'.$property->getClassName().'.editable', $propertyScope
                    )->render();
                } else {
                    $propertyScope = $property->setElement($element)->getListView();

                    $views[Element::getClassId($element)][$property->getName()] = view(
                        'moonlight::properties.'.$property->getClassName().'.list', $propertyScope
                    )->render();
                }
            }
        }

        $copyPropertyView = null;
        $movePropertyView = null;
        $bindPropertyViews = [];
        $unbindPropertyViews = [];

        foreach ($propertyList as $property) {
            if ($property->getHidden()) continue;
            if (! $property->isOneToOne()) continue;
            if (! $property->getParent()) continue;

            $propertyScope = $property->dropElement()->getEditView();

            $propertyScope['mode'] = 'search';

            $copyPropertyView = view(
                'moonlight::properties.'.$property->getClassName().'.copy', $propertyScope
            )->render();

            $movePropertyView = view(
                'moonlight::properties.'.$property->getClassName().'.move', $propertyScope
            )->render();
        }

        foreach ($propertyList as $property) {
            if ($property->getHidden()) continue;
            if (! $property->isManyToMany()) continue;

            $propertyScope = $property->getEditView();

            $propertyScope['mode'] = 'search';

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
            $favoriteRubricMap[$favorite->class_id][$favorite->rubric_id] = $favorite->rubric_id;
        }

        foreach ($elements as $element) {
            $classId = Element::getClassId($element);

            $elementFavoriteRubrics[$element->id] = isset($favoriteRubricMap[$classId])
                ? implode(',', $favoriteRubricMap[$classId])
                : '';
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
        $scope['mode'] = 'search';
        $scope['copyPropertyView'] = $copyPropertyView;
        $scope['movePropertyView'] = $movePropertyView;
        $scope['bindPropertyViews'] = $bindPropertyViews;
        $scope['unbindPropertyViews'] = $unbindPropertyViews;
        $scope['favoriteRubrics'] = $favoriteRubrics;
        $scope['elementFavoriteRubrics'] = $elementFavoriteRubrics;

        return view('moonlight::elements', $scope)->render();
    }
}
