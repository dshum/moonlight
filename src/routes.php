<?php

declare(strict_types = 1);

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
    Route::get('/login', 'LoginController@index')->name('login');
    Route::post('/login', 'LoginController@login');

    Route::get('/reset', 'ResetController@index')->name('reset');
    Route::post('/reset/send', 'ResetController@send')->name('reset.send');
    Route::get('/reset/create', 'ResetController@create')->name('reset.create');
    Route::post('/reset/save', 'ResetController@save')->name('reset.save');
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
    Route::get('/', 'HomeController@index')->name('home');

    Route::get('/logout', 'LoginController@logout')->name('logout');
    Route::get('/profile', 'ProfileController@index')->name('profile');
    Route::post('/profile', 'ProfileController@save');
    Route::get('/password', 'PasswordController@index')->name('password');
    Route::post('/password', 'PasswordController@save');

    Route::resource('groups', 'GroupController')->except(['show']);
    Route::resource('groups.items', 'ItemPermissionController')->only(['index', 'update']);
    Route::resource('groups.items.elements', 'ElementPermissionController')->only(['index', 'update']);
    Route::resource('users', 'UserController')->except(['show']);

    Route::get('/log', 'LogController@index')->name('log');
    Route::get('/log/next', 'LogController@next')->name('log.next');

    Route::get('/favorites/edit', 'HomeController@edit')->name('favorites.edit');
    Route::post('/favorites/order/rubrics', 'HomeController@orderRubrics')->name('favorites.orderRubrics');
    Route::post('/favorites/order/favorites',
        'HomeController@orderFavorites')->name('favorites.orderFavorites');
    Route::post('/favorites/delete/rubric', 'HomeController@deleteRubric')->name('favorites.deleteRubric');
    Route::post('/favorites/delete/favorite',
        'HomeController@deleteFavorite')->name('favorites.deleteFavorite');

    Route::get('/search', 'SearchController@index')->name('search');
    Route::get('/search/list', 'SearchController@elements')->name('search.list');
    Route::post('/search/active', 'SearchController@active')->name('search.active');
    Route::post('search/sort', 'SearchController@sort')->name('search.sort');

    Route::get('/trash', ['as' => 'trash', 'uses' => 'TrashController@index']);
    Route::get('/trash/count', 'TrashController@count')->name('trash.count');
    Route::get('/trash/list', 'TrashController@elements')->name('trash.list');
    Route::get('/trash/{item}', 'TrashController@item')->name('trash.item');
    Route::get('/trash/{class_id}/view', 'TrashController@view')->name('trashed.view');
    Route::post('/trash/{class_id}/delete', 'TrashController@delete')->name('trashed.delete');
    Route::post('/trash/{class_id}/restore', 'TrashController@restore')->name('trashed.restore');

    Route::get('/rubrics/get', 'RubricController@rubric')->name('rubrics.get');
    Route::get('/rubrics/node/get', 'RubricController@getNode')->name('rubrics.node.get');
    Route::post('/rubrics/open', 'RubricController@open')->name('rubrics.open');
    Route::post('/rubrics/close', 'RubricController@close')->name('rubrics.close');
    Route::post('/rubrics/node/open', 'RubricController@openNode')->name('rubrics.node.open');
    Route::post('/rubrics/node/close', 'RubricController@closeNode')->name('rubrics.node.close');

    Route::get('/elements/list', 'BrowseController@elements')->name('elements.list');
    Route::get('/elements/autocomplete', 'BrowseController@autocomplete')->name('elements.autocomplete');
    Route::post('/elements/open', 'BrowseController@open')->name('elements.open');
    Route::post('/elements/close', 'BrowseController@close')->name('elements.close');
    Route::post('/elements/order', 'BrowseController@order')->name('elements.order');
    Route::post('/elements/save', 'BrowseController@save')->name('elements.save');
    Route::post('/elements/copy', 'BrowseController@copy')->name('elements.copy');
    Route::post('/elements/move', 'BrowseController@move')->name('elements.move');
    Route::post('/elements/bind', 'BrowseController@bind')->name('elements.bind');
    Route::post('/elements/unbind', 'BrowseController@unbind')->name('elements.unbind');
    Route::post('/elements/favorite', 'BrowseController@favorite')->name('elements.favorite');
    Route::post('/elements/delete', 'BrowseController@delete')->name('elements.delete');
    Route::post('/elements/restore', 'BrowseController@restore')->name('elements.restore');
    Route::post('/elements/delete/force', 'BrowseController@forceDelete')->name('elements.delete.force');

    Route::get('/browse/{class_id}/create/{item}', 'EditController@create')->name('element.create');
    Route::get('/browse/{class_id}/edit', 'EditController@edit')->name('element.edit');
    Route::post('/browse/add/{item}', 'EditController@add')->name('element.add');
    Route::post('/browse/{class_id}/save', 'EditController@save')->name('element.save');
    Route::post('/browse/{class_id}/copy', 'EditController@copy')->name('element.copy');
    Route::post('/browse/{class_id}/move', 'EditController@move')->name('element.move');
    Route::post('/browse/{class_id}/favorite', 'EditController@favorite')->name('element.favorite');
    Route::post('/browse/{class_id}/delete', 'EditController@delete')->name('element.delete');

    Route::post('/order', 'BrowseController@order')->name('order');
    Route::post('/column', 'BrowseController@column')->name('column');
    Route::post('/perpage', 'BrowseController@perpage')->name('perpage');

    Route::group([
        'middleware' => [
            HistoryMiddleware::class,
        ],
    ], function () {
        Route::get('/search/{item}', 'SearchController@item')->name('search.item');

        Route::get('/browse', 'BrowseController@root')->name('browse');
        Route::get('/browse/root', 'BrowseController@root')->name('browse.root');
        Route::get('/browse/{class_id}', 'BrowseController@element')->name('browse.element');
    });
});

Route::get('/{url}', function () {
    return redirect()->route('home');
});
