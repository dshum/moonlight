<?php

namespace Moonlight\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Moonlight\Main\Item;
use Moonlight\Models\Favorite;
use Moonlight\Models\FavoriteRubric;

/**
 * Class SearchController
 *
 * @package Moonlight\Controllers
 */
class SearchController extends Controller
{
    /**
     * Default number of elements per page.
     */
    const PER_PAGE = 10;

    /**
     * Sort items.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception|\Throwable
     */
    public function sort(Request $request)
    {
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $itemName = $request->input('item');
        $sort = $request->input('sort');

        $currentItem = $itemName ? $site->getItemByName($itemName) : null;

        $search = Cache::get("search_items_{$loggedUser->id}", []);

        if (in_array($sort, ['rate', 'date', 'name', 'default'])) {
            $search['sort'] = $sort;
            Cache::forever("search_items_{$loggedUser->id}", $search);
        }

        $html = $this->itemListView($currentItem);

        return response()->json(['html' => $html]);
    }

    /**
     * @param \Moonlight\Main\Item|null $currentItem
     * @return array|string
     * @throws \Throwable
     */
    protected function itemListView(Item $currentItem = null)
    {
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $itemList = $site->getItemList();

        foreach ($itemList as $key => $item) {
            if (! $loggedUser->hasViewDefaultAccess($item)) {
                $itemList->forget($key);
            }
        }

        $search = Cache::get("search_items_{$loggedUser->id}", []);
        $sort = $search['sort'] ?? 'default';

        $items = [];

        if ($sort == 'name') {
            foreach ($itemList as $item) {
                $items[$item->getTitle()] = $item;
            }

            ksort($items);
        } elseif ($sort == 'date') {
            $sortDate = $search['sortDate'] ?? [];

            arsort($sortDate);

            foreach ($sortDate as $itemName => $date) {
                if ($item = $site->getItemByName($itemName)) {
                    $items[$itemName] = $item;
                }
            }

            foreach ($itemList as $item) {
                $items[$item->getName()] = $item;
            }
        } elseif ($sort == 'rate') {
            $sortRate = isset($search['sortRate'])
                ? $search['sortRate'] : [];

            arsort($sortRate);

            foreach ($sortRate as $itemName => $rate) {
                if ($item = $site->getItemByName($itemName)) {
                    $items[$itemName] = $item;
                }
            }

            foreach ($itemList as $item) {
                $items[$item->getName()] = $item;
            }
        } else {
            foreach ($itemList as $item) {
                $items[] = $item;
            }
        }

        $sorts = [
            'rate' => 'частоте',
            'date' => 'дате',
            'name' => 'названию',
            'default' => 'умолчанию',
        ];

        if (! isset($sorts[$sort])) {
            $sort = 'default';
        }

        return view('moonlight::search.list', [
            'currentItem' => $currentItem,
            'items' => $items,
            'sorts' => $sorts,
            'sort' => $sort,
        ])->render();
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
        $resetorder = $request->input('resetorder');

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
        } elseif ($resetorder) {
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

        $criteria = $currentItem->getClass()->withoutTrashed()->withoutGlobalScopes()
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
            $orderByList = $currentItem->getOrderByList();
        }

        $orders = [];

        foreach ($orderByList as $field => $direction) {
            $property = $currentItem->getPropertyByName($field);

            if (! $property) {
                continue;
            }

            if ($property->isOrder()) {
                $criteria->orderBy('id', 'desc');
                $orders['id'] = 'порядку добавления';
            } elseif ($property->getName() == $currentItemClass->getCreatedAtColumn()) {
                $criteria->orderBy($field, $direction);
                $orders[$field] = 'дате создания';
            } elseif ($property->getName() == $currentItemClass->getUpdatedAtColumn()) {
                $criteria->orderBy($field, $direction);
                $orders[$field] = 'дате изменения';
            } elseif ($property->getName() == $currentItemClass->getDeletedAtColumn()) {
                $criteria->orderBy($field, $direction);
                $orders[$field] = 'дате удаления';
            } else {
                $criteria->orderBy($field, $direction);
                $orders[$field] = 'полю &laquo;'.$property->getTitle().'&raquo;';
            }
        }

        $orders = implode(', ', $orders);

        if (Cache::has("per_page_{$loggedUser->id}_{$itemName}")) {
            $perPage = Cache::get("per_page_{$loggedUser->id}_{$itemName}");
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
                'mode' => 'search',
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
                "show_column_{$loggedUser->id}_{$itemName}_{$property->getName()}",
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
                "show_column_{$loggedUser->id}_{$itemName}_{$property->getName()}",
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

        $copyPropertyView = null;
        $movePropertyView = null;
        $bindPropertyViews = [];
        $unbindPropertyViews = [];

        foreach ($propertyList as $property) {
            if (
                $property->getHidden()
                || ! $property->isOneToOne()
                || ! $property->getParent()
            ) {
                continue;
            }

            $propertyScope = $property->dropElement()->getEditView();
            $propertyScope['mode'] = 'search';

            $copyPropertyView = view(
                'moonlight::properties.'.$property->getClassName().'.copy', $propertyScope
            )->render();

            $movePropertyView = view(
                'moonlight::properties.'.$property->getClassName().'.move', $propertyScope
            )->render();
        }

        if (! $copyPropertyView && $currentItem->getRoot()) {
            $copyPropertyView = 'Корень сайта';
        }

        foreach ($propertyList as $property) {
            if (
                $property->getHidden()
                || ! $property->isManyToMany()
            ) {
                continue;
            }

            $propertyScope = $property->getEditView();
            $propertyScope['mode'] = 'search';

            $bindPropertyViews[$property->getName()] = view(
                'moonlight::properties.'.$property->getClassName().'.bind', $propertyScope
            )->render();

            $unbindPropertyViews[$property->getName()] = view(
                'moonlight::properties.'.$property->getClassName().'.bind', $propertyScope
            )->render();
        }

        // Favorites
        $favoriteRubrics = FavoriteRubric::where('user_id', $loggedUser->id)
            ->orderBy('order')
            ->get();

        $favorites = Favorite::where('user_id', $loggedUser->id)
            ->where('element_type', $currentItem->getClassName())
            ->get();

        $favoriteRubricMap = [];
        $elementFavoriteRubrics = [];

        foreach ($favorites as $favorite) {
            $favoriteRubricMap[$favorite->element_id][$favorite->rubric_id] = $favorite->rubric_id;
        }

        foreach ($elements as $element) {
            $elementFavoriteRubrics[$element->id] = isset($favoriteRubricMap[$element->id])
                ? implode(',', $favoriteRubricMap[$element->id])
                : '';
        }

        $reloadUrl = route('moonlight.elements.list', [
            'item' => $currentItem->getName(),
        ]);

        return view('moonlight::elements', [
            'currentItem' => $currentItem,
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
            'openUrl' => null,
            'closeUrl' => null,
            'reloadUrl' => $reloadUrl,
            'views' => $views,
            'orderByList' => $orderByList,
            'orders' => $orders,
            'hasOrderProperty' => false,
            'mode' => 'search',
            'copyPropertyView' => $copyPropertyView,
            'movePropertyView' => $movePropertyView,
            'bindPropertyViews' => $bindPropertyViews,
            'unbindPropertyViews' => $unbindPropertyViews,
            'favoriteRubrics' => $favoriteRubrics,
            'elementFavoriteRubrics' => $elementFavoriteRubrics,
        ])->render();
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
            return redirect()->route('moonlight.search');
        }

        if (! $loggedUser->hasViewDefaultAccess($currentItem)) {
            return redirect()->route('moonlight.search');
        }

        // Item component
        $itemComponent = $site->getItemComponent($currentItem);
        $itemComponentView = $itemComponent ? (new $itemComponent($currentItem))->render() : null;

        // Filter component
        $filterComponent = $site->getFilterComponent($currentItem);
        $filterComponentView = $filterComponent ? (new $filterComponent($currentItem))->render() : null;

        $currentItemClass = $currentItem->getClass();
        $propertyList = $currentItem->getPropertyList();

        $properties = [];
        $actives = [];
        $links = [];
        $views = [];
        $orderProperties = [];
        $hasOrderProperty = false;

        foreach ($propertyList as $property) {
            if ($property->isOrder()) {
                $orderProperties[] = $property;
                $hasOrderProperty = true;
            }

            if (
                $property->getHidden()
                || $property->getName() == $currentItemClass->getDeletedAtColumn()
            ) {
                continue;
            }

            $orderProperties[] = $property;
        }

        foreach ($propertyList as $property) {
            if (
                $property->getHidden()
                || $property->getName() == $currentItemClass->getDeletedAtColumn()
            ) {
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

        $activeSearchProperties = Cache::get("search_properties_{$loggedUser->id}", []);

        $activeProperties = $activeSearchProperties[$currentItem->getName()] ?? [];

        foreach ($propertyList as $property) {
            if (isset($activeProperties[$property->getName()])) {
                $actives[$property->getName()] = $activeProperties[$property->getName()];
            }
        }

        $action = $request->input('action');

        if ($action == 'search') {
            $search = Cache::get("search_items_{$loggedUser->id}", []);

            $search['sortDate'][$itemName] = Carbon::now()->toDateTimeString();

            if (isset($search['sortRate'][$itemName])) {
                $search['sortRate'][$itemName]++;
            } else {
                $search['sortRate'][$itemName] = 1;
            }

            Cache::forever("search_items_{$loggedUser->id}", $search);

            $elements = $this->elementListView($request, $currentItem);
        } else {
            $elements = null;
        }

        $items = $this->itemListView($currentItem);

        return view('moonlight::search.item', [
            'items' => $items,
            'currentItem' => $currentItem,
            'itemComponentView' => $itemComponentView,
            'filterComponentView' => $filterComponentView,
            'properties' => $properties,
            'actives' => $actives,
            'links' => $links,
            'views' => $views,
            'orderProperties' => $orderProperties,
            'hasOrderProperty' => $hasOrderProperty,
            'action' => $action,
            'elements' => $elements,
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function active(Request $request)
    {
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $itemName = $request->input('item');
        $propertyName = $request->input('property');

        $item = $site->getItemByName($itemName);

        if (! $item) {
            return response()->json(['message' => 'Класс не найден.']);
        }

        $property = $item->getPropertyByName($propertyName);

        if (! $property) {
            return response()->json(['message' => 'Свойство класса не найдено.']);
        }

        $activeProperties = Cache::get("search_properties_{$loggedUser->id}", []);

        $active = $request->input('active');

        if (
            $active != 'true'
            && isset($activeProperties[$itemName][$property->getName()])
        ) {
            unset($activeProperties[$itemName][$property->getName()]);
        } elseif ($active) {
            $activeProperties[$itemName][$property->getName()] = 1;
        }

        Cache::forever("search_properties_{$loggedUser->id}", $activeProperties);

        return response()->json(['properties' => 'cached']);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     * @throws \Throwable
     */
    public function index(Request $request)
    {
        return view('moonlight::search.index', [
            'items' => $this->itemListView(),
        ]);
    }
}
