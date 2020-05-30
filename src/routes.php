<?php

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Moonlight\Middleware\SessionNameMiddleware;
use Moonlight\Middleware\GuestMiddleware;
use Moonlight\Middleware\AuthMiddleware;
use Moonlight\Middleware\HistoryMiddleware;

Route::group([
    'middleware' => [
        AddQueuedCookiesToResponse::class,
        SessionNameMiddleware::class,
        StartSession::class,
        GuestMiddleware::class,
        VerifyCsrfToken::class,
    ],
], function () {
    Route::get('/login', 'LoginController@index')->name('moonlight.login');
    Route::post('/login', 'LoginController@login');

    Route::get('/reset', 'ResetController@index')->name('moonlight.reset');
    Route::post('/reset/send', 'ResetController@send')->name('moonlight.reset.send');
    Route::get('/reset/create', 'ResetController@create')->name('moonlight.reset.create');
    Route::post('/reset/save', 'ResetController@save')->name('moonlight.reset.save');
});

Route::group([
    'middleware' => [
        AddQueuedCookiesToResponse::class,
        SessionNameMiddleware::class,
        StartSession::class,
        AuthMiddleware::class,
        VerifyCsrfToken::class,
    ],
], function () {
    Route::get('/', 'HomeController@index')->name('moonlight.home');

    Route::get('/logout', 'LoginController@logout')->name('moonlight.logout');
    Route::get('/profile', 'ProfileController@index')->name('moonlight.profile');
    Route::post('/profile', 'ProfileController@save');
    Route::get('/password', 'PasswordController@index')->name('moonlight.password');
    Route::post('/password', 'PasswordController@save');

    Route::get('/users', 'UserController@users')->name('moonlight.users');
    Route::get('/users/create', 'UserController@create')->name('moonlight.user.create');
    Route::get('/users/{id}', 'UserController@edit')->name('moonlight.user')->where(['id' => '[0-9]+']);
    Route::post('/users/create', 'UserController@add')->name('moonlight.user.add');
    Route::post('/users/{id}', 'UserController@save')->name('moonlight.user.save')->where(['id' => '[0-9]+']);
    Route::post('/users/{id}/delete',
        'UserController@delete')->name('moonlight.user.delete')->where(['id' => '[0-9]+']);

    Route::get('/groups', 'GroupController@groups')->name('moonlight.groups');
    Route::get('/groups/create', 'GroupController@create')->name('moonlight.group.create');
    Route::get('/groups/{id}', 'GroupController@edit')->name('moonlight.group')->where(['id' => '[0-9]+']);
    Route::post('/groups/create', 'GroupController@add')->name('moonlight.group.add');
    Route::post('/groups/{id}', 'GroupController@save')->name('moonlight.group.save')->where(['id' => '[0-9]+']);
    Route::post('/groups/{id}/delete',
        'GroupController@delete')->name('moonlight.group.delete')->where(['id' => '[0-9]+']);

    Route::get('groups/permissions/items/{group_id}',
        'PermissionController@itemPermissions')->name('moonlight.group.items')->where('group_id', '[0-9]+');
    Route::get('groups/permissions/elements/{group_id}/{item}',
        'PermissionController@elementPermissions')->name('moonlight.group.elements')->where(['group_id' => '[0-9]+']);
    Route::post('groups/permissions/items/{group_id}', 'PermissionController@saveItemPermission')->where('group_id',
        '[0-9]+');
    Route::post('groups/permissions/elements/{group_id}/{item}',
        'PermissionController@saveElementPermission')->name('moonlight.group.elements')->where(['group_id' => '[0-9]+']);

    Route::get('/log', 'LogController@index')->name('moonlight.log');
    Route::get('/log/next', 'LogController@next')->name('moonlight.log.next');

    Route::get('/favorites/edit', 'HomeController@edit')->name('moonlight.favorites.edit');
    Route::post('/favorites/order/rubrics', 'HomeController@orderRubrics')->name('moonlight.favorites.orderRubrics');
    Route::post('/favorites/order/favorites',
        'HomeController@orderFavorites')->name('moonlight.favorites.orderFavorites');
    Route::post('/favorites/delete/rubric', 'HomeController@deleteRubric')->name('moonlight.favorites.deleteRubric');
    Route::post('/favorites/delete/favorite',
        'HomeController@deleteFavorite')->name('moonlight.favorites.deleteFavorite');

    Route::get('/search', 'SearchController@index')->name('moonlight.search');
    Route::get('/search/list', 'SearchController@elements')->name('moonlight.search.list');
    Route::post('/search/active', 'SearchController@active')->name('moonlight.search.active');
    Route::post('search/sort', 'SearchController@sort')->name('moonlight.search.sort');

    Route::get('/trash', ['as' => 'moonlight.trash', 'uses' => 'TrashController@index']);
    Route::get('/trash/count', 'TrashController@count')->name('moonlight.trash.count');
    Route::get('/trash/list', 'TrashController@elements')->name('moonlight.trash.list');
    Route::get('/trash/{item}', 'TrashController@item')->name('moonlight.trash.item');
    Route::get('/trash/{class_id}/view', 'TrashController@view')->name('moonlight.trashed.view');
    Route::post('/trash/{class_id}/delete', 'TrashController@delete')->name('moonlight.trashed.delete');
    Route::post('/trash/{class_id}/restore', 'TrashController@restore')->name('moonlight.trashed.restore');

    Route::get('/rubrics/get', 'RubricController@rubric')->name('moonlight.rubrics.get');
    Route::get('/rubrics/node/get', 'RubricController@getNode')->name('moonlight.rubrics.node.get');
    Route::post('/rubrics/open', 'RubricController@open')->name('moonlight.rubrics.open');
    Route::post('/rubrics/close', 'RubricController@close')->name('moonlight.rubrics.close');
    Route::post('/rubrics/node/open', 'RubricController@openNode')->name('moonlight.rubrics.node.open');
    Route::post('/rubrics/node/close', 'RubricController@closeNode')->name('moonlight.rubrics.node.close');

    Route::get('/elements/list', 'BrowseController@elements')->name('moonlight.elements.list');
    Route::get('/elements/autocomplete', 'BrowseController@autocomplete')->name('moonlight.elements.autocomplete');
    Route::post('/elements/open', 'BrowseController@open')->name('moonlight.elements.open');
    Route::post('/elements/close', 'BrowseController@close')->name('moonlight.elements.close');
    Route::post('/elements/order', 'BrowseController@order')->name('moonlight.elements.order');
    Route::post('/elements/save', 'BrowseController@save')->name('moonlight.elements.save');
    Route::post('/elements/copy', 'BrowseController@copy')->name('moonlight.elements.copy');
    Route::post('/elements/move', 'BrowseController@move')->name('moonlight.elements.move');
    Route::post('/elements/bind', 'BrowseController@bind')->name('moonlight.elements.bind');
    Route::post('/elements/unbind', 'BrowseController@unbind')->name('moonlight.elements.unbind');
    Route::post('/elements/favorite', 'BrowseController@favorite')->name('moonlight.elements.favorite');
    Route::post('/elements/delete', 'BrowseController@delete')->name('moonlight.elements.delete');
    Route::post('/elements/restore', 'BrowseController@restore')->name('moonlight.elements.restore');
    Route::post('/elements/delete/force', 'BrowseController@forceDelete')->name('moonlight.elements.delete.force');

    Route::get('/browse/{class_id}/create/{item}', 'EditController@create')->name('moonlight.element.create');
    Route::get('/browse/{class_id}/edit', 'EditController@edit')->name('moonlight.element.edit');
    Route::post('/browse/add/{item}', 'EditController@add')->name('moonlight.element.add');
    Route::post('/browse/{class_id}/save', 'EditController@save')->name('moonlight.element.save');
    Route::post('/browse/{class_id}/copy', 'EditController@copy')->name('moonlight.element.copy');
    Route::post('/browse/{class_id}/move', 'EditController@move')->name('moonlight.element.move');
    Route::post('/browse/{class_id}/favorite', 'EditController@favorite')->name('moonlight.element.favorite');
    Route::post('/browse/{class_id}/delete', 'EditController@delete')->name('moonlight.element.delete');

    Route::post('/order', 'BrowseController@order')->name('moonlight.order');
    Route::post('/column', 'BrowseController@column')->name('moonlight.column');
    Route::post('/perpage', 'BrowseController@perpage')->name('moonlight.perpage');

    Route::group([
        'middleware' => [
            HistoryMiddleware::class,
        ],
    ], function () {
        Route::get('/search/{item}', 'SearchController@item')->name('moonlight.search.item');

        Route::get('/browse', 'BrowseController@root')->name('moonlight.browse');
        Route::get('/browse/root', 'BrowseController@root')->name('moonlight.browse.root');
        Route::get('/browse/{class_id}', 'BrowseController@element')->name('moonlight.browse.element');
    });
});

Route::get('/{url}', function () {
    return redirect()->route('moonlight.home');
});
