<?php

namespace Moonlight\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Moonlight\Main\Site;
use Moonlight\Main\Item;
use Moonlight\Main\Element;
use Moonlight\Main\Rubric;
use Moonlight\Models\FavoriteRubric;
use Moonlight\Models\Favorite;

class RubricController extends Controller
{
    /**
     * Get rubric node.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getNode(Request $request)
    {
        $scope = [];

        $loggedUser = Auth::guard('moonlight')->user();

        $rubricName = $request->input('rubric');
        $bindName = $request->input('bind');
        $classId = $request->input('classId');

        $site = \App::make('site');

        $rubric = $site->getRubricByName($rubricName);

        if (! $rubric) {
            return response()->json([]);
        }

        $html = $this->node($rubric, $bindName, $classId);

        cache()->forever("rubric_node_{$loggedUser->id}_{$rubricName}_{$classId}", true);

        return response()->json(['html' => $html]);
    }

    /**
     * Open closed rubric node.
     *
     * @return Response
     */
    public function openNode(Request $request)
    {
        $scope = [];

        $loggedUser = Auth::guard('moonlight')->user();

        $rubricName = $request->input('rubric');
        $classId = $request->input('classId');

        $site = \App::make('site');

        $rubric = $site->getRubricByName($rubricName);

        if (! $rubric) {
            return response()->json([]);
        }

        cache()->forever("rubric_node_{$loggedUser->id}_{$rubricName}_{$classId}", true);

        return response()->json([]);
    }

    /**
     * Close opened rubric node.
     *
     * @return Response
     */
    public function closeNode(Request $request)
    {
        $scope = [];

        $loggedUser = Auth::guard('moonlight')->user();

        $rubricName = $request->input('rubric');
        $classId = $request->input('classId');

        $site = \App::make('site');

        $rubric = $site->getRubricByName($rubricName);

        if (! $rubric) {
            return response()->json([]);
        }

        cache()->forget("rubric_node_{$loggedUser->id}_{$rubricName}_{$classId}");

        return response()->json([]);
    }

    /**
     * Get rubric.
     *
     * @return Response
     */
    public function rubric(Request $request)
    {
        $scope = [];

        $loggedUser = Auth::guard('moonlight')->user();

        $rubricName = $request->input('rubric');

        $site = \App::make('site');

        $rubric = $site->getRubricByName($rubricName);

        if (! $rubric) {
            $rubric = FavoriteRubric::find($rubricName);
        }

        if (! $rubric) {
            return response()->json([]);
        }

        cache()->forever("rubric_{$loggedUser->id}_{$rubricName}", true);

        $favorites = [];
        $view = null;

        if ($rubric instanceof FavoriteRubric) {
            $favoriteList = Favorite::where('rubric_id', $rubric->id)->
            orderBy('order')->
            get();

            foreach ($favoriteList as $favorite) {
                $element = $favorite->getElement();

                if ($element) {
                    $item = Element::getItem($element);
                    $mainProperty = $item->getMainProperty();

                    $favorites[] = [
                        'classId' => $favorite->class_id,
                        'name' => $element->{$mainProperty},
                    ];
                }
            }
        } else {
            $binds = $rubric->getBinds();

            foreach ($binds as $bindName => $bind) {
                $views[] = $this->node($rubric, $bindName, null);
            }

            $view = implode(PHP_EOL, $views);
        }

        $scope['rubric'] = $rubric;
        $scope['favorites'] = $favorites;
        $scope['view'] = $view;

        $html = view('moonlight::rubrics.rubric', $scope)->render();

        return response()->json(['html' => $html]);
    }

    /**
     * Open closed rubric.
     *
     * @return Response
     */
    public function open(Request $request)
    {
        $scope = [];

        $loggedUser = Auth::guard('moonlight')->user();

        $rubricName = $request->input('rubric');

        $site = \App::make('site');

        $rubric = $site->getRubricByName($rubricName);

        if (! $rubric) {
            $rubric = FavoriteRubric::find($rubricName);
        }

        if (! $rubric) {
            return response()->json([]);
        }

        cache()->forever("rubric_{$loggedUser->id}_{$rubricName}", true);

        return response()->json([]);
    }

    /**
     * Close opened rubric.
     *
     * @return Response
     */
    public function close(Request $request)
    {
        $scope = [];

        $loggedUser = Auth::guard('moonlight')->user();

        $rubricName = $request->input('rubric');

        $site = \App::make('site');

        $rubric = $site->getRubricByName($rubricName);

        if (! $rubric) {
            $rubric = FavoriteRubric::find($rubricName);
        }

        if (! $rubric) {
            return response()->json([]);
        }

        cache()->forget("rubric_{$loggedUser->id}_{$rubricName}");

        return response()->json([]);
    }

    public function sidebar($currentClassId = null)
    {
        $scope = [];

        $loggedUser = Auth::guard('moonlight')->user();

        $site = \App::make('site');

        $favoriteRubrics = FavoriteRubric::where('user_id', $loggedUser->id)
            ->orderBy('order')
            ->get();

        $favorites = [];

        foreach ($favoriteRubrics as $favoriteRubric) {
            $open = cache()->get("rubric_{$loggedUser->id}_{$favoriteRubric->id}", false);

            if (! $open) continue;

            $favoriteList = Favorite::where('rubric_id', $favoriteRubric->id)
                ->where('user_id', $loggedUser->id)
                ->orderBy('order')
                ->get();

            foreach ($favoriteList as $favorite) {
                $element = $favorite->getElement();

                if ($element) {
                    $item = Element::getItem($element);
                    $mainProperty = $item->getMainProperty();

                    $favorites[$favoriteRubric->id][] = [
                        'classId' => $favorite->class_id,
                        'itemId' => $item->getNameId(),
                        'itemName' => $item->getTitle(),
                        'name' => $element->{$mainProperty},
                    ];
                }
            }
        }

        $views = [];

        $rubrics = $site->getRubricList();

        foreach ($rubrics as $k => $rubric) {
            $rubricName = $rubric->getName();

            $open = cache()->get("rubric_{$loggedUser->id}_{$rubricName}", false);

            if (! $open) continue;

            $binds = $rubric->getBinds();

            foreach ($binds as $bindName => $bind) {
                if ($node = $this->node($rubric, $bindName, null, $currentClassId)) {
                    $views[$rubricName][] = $node;
                }
            }

            if (empty($views[$rubricName])) {
                $rubrics->forget($k);
            }
        }

        $scope['favoriteRubrics'] = $favoriteRubrics;
        $scope['favorites'] = $favorites;
        $scope['rubrics'] = $rubrics;
        $scope['views'] = $views;
        $scope['classId'] = $currentClassId;

        return view('moonlight::rubrics.sidebar', $scope);
    }

    public function index()
    {
        $scope = [];

        $loggedUser = Auth::guard('moonlight')->user();

        $site = \App::make('site');

        $favoriteRubrics = FavoriteRubric::where('user_id', $loggedUser->id)
            ->orderBy('order')
            ->get();

        $favorites = [];

        foreach ($favoriteRubrics as $favoriteRubric) {
            $favorites[$favoriteRubric->id] = [];

            $favoriteList = Favorite::where('rubric_id', $favoriteRubric->id)
                ->where('user_id', $loggedUser->id)
                ->orderBy('order')
                ->get();

            foreach ($favoriteList as $favorite) {
                $element = $favorite->getElement();

                if ($element) {
                    $item = Element::getItem($element);
                    $mainProperty = $item->getMainProperty();

                    $favorites[$favoriteRubric->id][] = [
                        'classId' => $favorite->class_id,
                        'name' => $element->{$mainProperty},
                    ];
                }
            }
        }

        $rubricList = $site->getRubricList();

        $rubrics = [];

        foreach ($rubricList as $rubric) {
            $rubricName = $rubric->getName();
            $binds = $rubric->getBinds();

            foreach ($binds as $bindName => $bind) {
                $views[$rubricName][] = $this->node($rubric, $bindName, null);
            }

            $views[$rubricName] = implode(PHP_EOL, $views[$rubricName]);

            if ($views[$rubricName]) {
                $rubrics[] = $rubric;
            }
        }

        $scope['favoriteRubrics'] = $favoriteRubrics;
        $scope['favorites'] = $favorites;
        $scope['rubrics'] = $rubrics;
        $scope['views'] = $views;

        return view('moonlight::rubrics.index', $scope);
    }

    protected function node($rubric, $bindName, $parent, $currentClassId = null)
    {
        $views = [];

        $loggedUser = Auth::guard('moonlight')->user();

        $site = \App::make('site');

        $bind = $rubric->getBindByName($bindName);

        if (! $bind) {
            return null;
        }

        if (strpos($parent, Element::ID_SEPARATOR)) {
            $parts = explode(Element::ID_SEPARATOR, $parent);
            $id = array_pop($parts);
            $class = implode(Element::ID_SEPARATOR, $parts);
        } else {
            $class = $parent;
        }

        if (! $parent) {
            $bindItem = $bind['first'];
        } elseif (isset($bind['first'][$parent])) {
            $bindItem = $bind['first'][$parent];
        } elseif (isset($bind['first'][$class])) {
            $bindItem = $bind['first'][$class];
        } elseif (isset($bind['addition'][$parent])) {
            $bindItem = $bind['addition'][$parent];
        } elseif (isset($bind['addition'][$class])) {
            $bindItem = $bind['addition'][$class];
        } else {
            $bindItem = null;
        }

        if ($bindItem && is_string($bindItem)) {
            $elements = $this->getElements($parent, $bindItem);

            if (sizeof($elements)) {
                $scope['name'] = $rubric->getName();
                $scope['bind'] = $bindName;
                $scope['classId'] = $currentClassId;
                $scope['parent'] = $parent;
                $scope['elements'] = $elements;

                if (isset($bind['addition'][$bindItem])) {
                    foreach ($elements as $element) {
                        $count = $this->count($bind, $element['classId']);

                        $open = cache()->get("rubric_node_{$loggedUser->id}_{$rubric->getName()}_{$element['classId']}", false);

                        if ($count && $open) {
                            $scope['children'][$element['classId']] = $this->node($rubric, $bindName, $element['classId'], $currentClassId);;
                        } elseif ($count) {
                            $scope['haschildren'][$element['classId']] = $count;
                        }
                    }
                }

                $views[] = view('moonlight::rubrics.node', $scope)->render();
            }
        } elseif (is_array($bindItem)) {
            foreach ($bindItem as $key => $value) {
                if (! $parent) {
                    if ($key && $key == Site::ROOT) {
                        $parent = $key;
                    } elseif ($key) {
                        $element = Element::getByClassId($key);

                        if ($element) {
                            $parent = $key;
                        }
                    }
                }

                $elements = $this->getElements($parent, $value);

                if (sizeof($elements)) {
                    $scope['name'] = $rubric->getName();
                    $scope['bind'] = $bindName;
                    $scope['classId'] = $currentClassId;
                    $scope['parent'] = $parent;
                    $scope['elements'] = $elements;

                    if (isset($bind['addition'][$value])) {
                        foreach ($elements as $element) {
                            $count = $this->count($bind, $element['classId']);

                            $open = cache()->get("rubric_node_{$loggedUser->id}_{$rubric->getName()}_{$element['classId']}", false);

                            if ($count && $open) {
                                $scope['children'][$element['classId']] = $this->node($rubric, $bindName, $element['classId'], $currentClassId);
                            } elseif ($count) {
                                $scope['haschildren'][$element['classId']] = $count;
                            }
                        }
                    }

                    $views[] = view('moonlight::rubrics.node', $scope)->render();
                }
            }
        }

        $html = implode(PHP_EOL, $views);

        return $html;
    }

    protected function count($bind, $parent)
    {
        $count = 0;

        $loggedUser = Auth::guard('moonlight')->user();

        $site = \App::make('site');

        if (strpos($parent, Element::ID_SEPARATOR)) {
            $parts = explode(Element::ID_SEPARATOR, $parent);
            $id = array_pop($parts);
            $class = implode(Element::ID_SEPARATOR, $parts);
        } else {
            $class = $parent;
        }

        if (! $parent) {
            $bindItem = $bind['first'];
        } elseif (isset($bind['first'][$parent])) {
            $bindItem = $bind['first'][$parent];
        } elseif (isset($bind['first'][$class])) {
            $bindItem = $bind['first'][$class];
        } elseif (isset($bind['addition'][$parent])) {
            $bindItem = $bind['addition'][$parent];
        } elseif (isset($bind['addition'][$class])) {
            $bindItem = $bind['addition'][$class];
        } else {
            $bindItem = null;
        }

        if ($bindItem && is_string($bindItem)) {
            $count += $this->getElementsCount($parent, $bindItem);
        } elseif (is_array($bindItem)) {
            foreach ($bindItem as $key => $value) {
                if ($parent) {
                    $count += $this->getElementsCount($parent, $value);
                } elseif ($key && $key == Site::ROOT) {
                    $count += $this->getElementsCount($key, $value);
                } else {
                    $element = Element::getByClassId($key);

                    if ($element) {
                        $count += $this->getElementsCount($key, $value);
                    } else {
                        $count += $this->getElementsCount(null, $value);
                    }
                }
            }
        }

        return $count;
    }

    protected function getElementsCount($parentId, $className)
    {
        $loggedUser = Auth::guard('moonlight')->user();

        $site = \App::make('site');

        $parent = null;

        if ($parentId && $parentId != Site::ROOT) {
            $parent = Element::getByClassId($parentId);

            if (! $parent) return 0;
        }

        $item = $site->getItemByName($className);

        if (! $item) return 0;

        $mainProperty = $item->getMainProperty();

        if (! $loggedUser->isSuperUser()) {
            $permissionDenied = true;
            $deniedElementList = [];
            $allowedElementList = [];

            $groupList = $loggedUser->getGroups();

            foreach ($groupList as $group) {
                $groupItemPermission = $group->getItemPermission($item->getNameId());
                $itemPermission = $groupItemPermission
                    ? $groupItemPermission->permission
                    : $group->default_permission;

                if ($itemPermission != 'deny') {
                    $permissionDenied = false;
                    $deniedElementList = [];
                }

                $elementPermissionList = $group->getElementPermissions();

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

        if ($parentId) {
            $propertyList = $item->getPropertyList();

            $criteria = $item->getClass()->where(
                function($query) use ($propertyList, $parent) {
                    if ($parent) {
                        $query->orWhere('id', null);
                    }

                    foreach ($propertyList as $property) {
                        if (
                            $parent
                            && $property->isOneToOne()
                            && $property->getRelatedClass() == Element::getClass($parent)
                        ) {
                            $query->orWhere(
                                $property->getName(), $parent->id
                            );
                        } elseif (
                            ! $parent
                            && $property->isOneToOne()
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
                    $parent
                    && $property->isManyToMany()
                    && $property->getRelatedClass() == Element::getClass($parent)
                ) {
                    $criteria = $parent->{$property->getRelatedMethod()}();
                    break;
                }
            }
        } else {
            $criteria = $item->getClass()->query();
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
                return 0;
            }
        }

        return $criteria->count();
    }

    protected function getElements($parentId, $className)
    {
        $loggedUser = Auth::guard('moonlight')->user();

        $site = \App::make('site');

        $parent = null;

        if ($parentId && $parentId != Site::ROOT) {
            $parent = Element::getByClassId($parentId);

            if (! $parent) return [];
        }

        $item = $site->getItemByName($className);

        if (! $item) return [];

        $mainProperty = $item->getMainProperty();

        if (! $loggedUser->isSuperUser()) {
            $permissionDenied = true;
            $deniedElementList = [];
            $allowedElementList = [];

            $groupList = $loggedUser->getGroups();

            foreach ($groupList as $group) {
                $groupItemPermission = $group->getItemPermission($item->getNameId());
                $itemPermission = $groupItemPermission
                    ? $groupItemPermission->permission
                    : $group->default_permission;

                if ($itemPermission != 'deny') {
                    $permissionDenied = false;
                    $deniedElementList = [];
                }

                $elementPermissionList = $group->getElementPermissions();

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

        if ($parentId) {
            $propertyList = $item->getPropertyList();

            $criteria = $item->getClass()->where(
                function($query) use ($propertyList, $parent) {
                    if ($parent) {
                        $query->orWhere('id', null);
                    }

                    foreach ($propertyList as $property) {
                        if (
                            $parent
                            && $property->isOneToOne()
                            && $property->getRelatedClass() == Element::getClass($parent)
                        ) {
                            $query->orWhere(
                                $property->getName(), $parent->id
                            );
                        } elseif (
                            ! $parent
                            && $property->isOneToOne()
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
                    $parent
                    && $property->isManyToMany()
                    && $property->getRelatedClass() == Element::getClass($parent)
                ) {
                    $criteria = $parent->{$property->getRelatedMethod()}();
                    break;
                }
            }
        } else {
            $criteria = $item->getClass()->query();
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
                return [];
            }
        }

        $orderByList = $item->getOrderByList();

        foreach ($orderByList as $field => $direction) {
            $criteria->orderBy($field, $direction);
        }

        $elementList = $criteria->get();

        $elements = [];

        foreach ($elementList as $element) {
            $elements[] = [
                'classId' => class_id($element),
                'itemId' => $item->getNameId(),
                'itemName' => $item->getTitle(),
                'name' => $element->{$mainProperty},
            ];
        }

        return $elements;
    }
}
