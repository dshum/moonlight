<?php

use Illuminate\Support\Facades\Log;
use \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Moonlight\Middleware\SessionNameMiddleware;
use Moonlight\Middleware\GuestMiddleware;
use Moonlight\Middleware\AuthMiddleware;
use Moonlight\Middleware\HistoryMiddleware;
use Moonlight\Middleware\QueryLogMiddleware;
use Moonlight\Main\Element;
use Moonlight\Models\User;

Route::group(['prefix' => 'moonlight'], function() {
    Route::group(['middleware' => [
        AddQueuedCookiesToResponse::class,
        SessionNameMiddleware::class,
        StartSession::class, 
        GuestMiddleware::class, 
        VerifyCsrfToken::class,
    ]], function () {
        Route::get('/login', ['as' => 'moonlight.login', 'uses' => 'Moonlight\Controllers\LoginController@index']);
        
        Route::post('/login', ['as' => 'moonlight.login', 'uses' => 'Moonlight\Controllers\LoginController@login']);

        Route::get('/reset', ['as' => 'moonlight.reset', 'uses' => 'Moonlight\Controllers\ResetController@index']);

        Route::post('/reset/send', ['as' => 'moonlight.reset.send', 'uses' => 'Moonlight\Controllers\ResetController@send']);

        Route::get('/reset/create', ['as' => 'moonlight.reset.create', 'uses' => 'Moonlight\Controllers\ResetController@create']);

        Route::post('/reset/save', ['as' => 'moonlight.reset.save', 'uses' => 'Moonlight\Controllers\ResetController@save']);
    });
    
    Route::group(['middleware' => [
        AddQueuedCookiesToResponse::class,
        SessionNameMiddleware::class,
        StartSession::class, 
        AuthMiddleware::class,
        VerifyCsrfToken::class,
        QueryLogMiddleware::class,
    ]], function () {
        Route::get('/', ['as' => 'moonlight.home', 'uses' => 'Moonlight\Controllers\HomeController@index']);

        Route::get('/logout', ['as' => 'moonlight.logout', 'uses' => 'Moonlight\Controllers\LoginController@logout']);
       
        Route::get('/profile', ['as' => 'moonlight.profile', 'uses' => 'Moonlight\Controllers\ProfileController@index']);
       
        Route::post('/profile', ['as' => 'moonlight.profile', 'uses' => 'Moonlight\Controllers\ProfileController@save']);
        
        Route::get('/password', ['as' => 'moonlight.password', 'uses' => 'Moonlight\Controllers\PasswordController@index']);
        
        Route::post('/password', ['as' => 'moonlight.password', 'uses' => 'Moonlight\Controllers\PasswordController@save']);

        Route::get('/users', ['as' => 'moonlight.users', 'uses' => 'Moonlight\Controllers\UserController@users']);
        
        Route::get('/users/create', ['as' => 'moonlight.user.create', 'uses' => 'Moonlight\Controllers\UserController@create']);
        
        Route::post('/users/create', ['as' => 'moonlight.user.add', 'uses' => 'Moonlight\Controllers\UserController@add']);
        
        Route::get('/users/{id}', ['as' => 'moonlight.user', 'uses' => 'Moonlight\Controllers\UserController@edit'])->
            where(['id' => '[0-9]+']);
        
        Route::post('/users/{id}', ['as' => 'moonlight.user.save', 'uses' => 'Moonlight\Controllers\UserController@save'])->
            where(['id' => '[0-9]+']);
        
        Route::post('/users/{id}/delete', ['as' => 'moonlight.user.delete', 'uses' => 'Moonlight\Controllers\UserController@delete'])->
            where(['id' => '[0-9]+']);

        Route::get('/groups', ['as' => 'moonlight.groups', 'uses' => 'Moonlight\Controllers\GroupController@groups']);
        
        Route::get('/groups/create', ['as' => 'moonlight.group.create', 'uses' => 'Moonlight\Controllers\GroupController@create']);
        
        Route::post('/groups/create', ['as' => 'moonlight.group.add', 'uses' => 'Moonlight\Controllers\GroupController@add']);
        
        Route::get('/groups/{id}', ['as' => 'moonlight.group', 'uses' => 'Moonlight\Controllers\GroupController@edit'])->
            where(['id' => '[0-9]+']);
        
        Route::post('/groups/{id}', ['as' => 'moonlight.group.save', 'uses' => 'Moonlight\Controllers\GroupController@save'])->
            where(['id' => '[0-9]+']);
        
        Route::post('/groups/{id}/delete', ['as' => 'moonlight.group.delete', 'uses' => 'Moonlight\Controllers\GroupController@delete'])->
            where(['id' => '[0-9]+']);
        
        Route::get('groups/permissions/items/{id}', ['as' => 'moonlight.group.items', 'uses' => 'Moonlight\Controllers\PermissionController@itemPermissions'])->
            where('id', '[0-9]+');
        
        Route::post('groups/permissions/items/{id}', ['as' => 'moonlight.group.items', 'uses' => 'Moonlight\Controllers\PermissionController@saveItemPermission'])->
            where('id', '[0-9]+');
        
        Route::get('groups/permissions/elements/{id}/{class}', ['as' => 'moonlight.group.elements', 'uses' => 'Moonlight\Controllers\PermissionController@elementPermissions'])->
            where('id', '[0-9]+');
        
        Route::post('groups/permissions/elements/{id}/{class}', ['as' => 'moonlight.group.elements', 'uses' => 'Moonlight\Controllers\PermissionController@saveElementPermission'])->
            where('id', '[0-9]+'); 
        
        Route::get('/log', ['as' => 'moonlight.log', 'uses' => 'Moonlight\Controllers\LogController@index']);
        
        Route::get('/log/next', ['as' => 'moonlight.log.next', 'uses' => 'Moonlight\Controllers\LogController@next']);

        Route::get('/favorites/edit', ['as' => 'moonlight.favorites.edit', 'uses' => 'Moonlight\Controllers\HomeController@edit']);

        Route::post('/favorites/order/rubrics', ['as' => 'moonlight.favorites.orderRubrics', 'uses' => 'Moonlight\Controllers\HomeController@orderRubrics']);

        Route::post('/favorites/order/favorites', ['as' => 'moonlight.favorites.orderFavorites', 'uses' => 'Moonlight\Controllers\HomeController@orderFavorites']);

        Route::post('/favorites/delete/rubric', ['as' => 'moonlight.favorites.deleteRubric', 'uses' => 'Moonlight\Controllers\HomeController@deleteRubric']);

        Route::post('/favorites/delete/favorite', ['as' => 'moonlight.favorites.deleteFavorite', 'uses' => 'Moonlight\Controllers\HomeController@deleteFavorite']);
        
        Route::get('/search', ['as' => 'moonlight.search', 'uses' => 'Moonlight\Controllers\SearchController@index']);

        Route::post('/search/active/{class}/{name}', ['as' => 'moonlight.search.active', 'uses' => 'Moonlight\Controllers\SearchController@active']); 
        
        Route::get('/search/list', ['as' => 'moonlight.search.list', 'uses' => 'Moonlight\Controllers\SearchController@elements']);

        Route::post('search/sort', ['as' => 'moonlight.search.sort', 'uses' => 'Moonlight\Controllers\SearchController@sort']);
        
        Route::get('/trash', ['as' => 'moonlight.trash', 'uses' => 'Moonlight\Controllers\TrashController@index']);
        
        Route::get('/trash/count', ['as' => 'moonlight.trash.count', 'uses' => 'Moonlight\Controllers\TrashController@count']);
        
        Route::get('/trash/list', ['as' => 'moonlight.trash.list', 'uses' => 'Moonlight\Controllers\TrashController@elements']);
        
        Route::get('/trash/{item}', ['as' => 'moonlight.trash.item', 'uses' => 'Moonlight\Controllers\TrashController@item'])->
            where(['item' => '[A-Za-z0-9\.]+']);

        Route::get('/trash/{classId}/view', ['as' => 'moonlight.trashed.view', 'uses' => 'Moonlight\Controllers\TrashController@view'])->
            where(['classId' => '[A-Za-z0-9\.]+']);

        Route::post('/trash/{classId}/delete', ['as' => 'moonlight.trashed.delete', 'uses' => 'Moonlight\Controllers\TrashController@delete'])->
            where(['classId' => '[A-Za-z0-9\.]+']);

        Route::post('/trash/{classId}/restore', ['as' => 'moonlight.trashed.restore', 'uses' => 'Moonlight\Controllers\TrashController@restore'])->
            where(['classId' => '[A-Za-z0-9\.]+']);

        Route::get('/rubrics/get', ['as' => 'moonlight.rubrics.get', 'uses' => 'Moonlight\Controllers\RubricController@rubric']);
        
        Route::post('/rubrics/open', ['as' => 'moonlight.rubrics.open', 'uses' => 'Moonlight\Controllers\RubricController@open']);

        Route::post('/rubrics/close', ['as' => 'moonlight.rubrics.close', 'uses' => 'Moonlight\Controllers\RubricController@close']);

        Route::get('/rubrics/node/get', ['as' => 'moonlight.rubrics.node.get', 'uses' => 'Moonlight\Controllers\RubricController@getNode']);
        
        Route::post('/rubrics/node/open', ['as' => 'moonlight.rubrics.node.open', 'uses' => 'Moonlight\Controllers\RubricController@openNode']);

        Route::post('/rubrics/node/close', ['as' => 'moonlight.rubrics.node.close', 'uses' => 'Moonlight\Controllers\RubricController@closeNode']);
        
        Route::get('/elements/list', ['as' => 'moonlight.elements.list', 'uses' => 'Moonlight\Controllers\BrowseController@elements']);
        
        Route::post('/elements/open', ['as' => 'moonlight.elements.open', 'uses' => 'Moonlight\Controllers\BrowseController@open']);

        Route::post('/elements/close', ['as' => 'moonlight.elements.close', 'uses' => 'Moonlight\Controllers\BrowseController@close']);
        
        Route::get('/elements/autocomplete', ['as' => 'moonlight.elements.autocomplete', 'uses' => 'Moonlight\Controllers\BrowseController@autocomplete']);

        Route::post('/elements/order', ['as' => 'moonlight.elements.order', 'uses' => 'Moonlight\Controllers\BrowseController@order']);
        
        Route::post('/elements/save', ['as' => 'moonlight.elements.save', 'uses' => 'Moonlight\Controllers\BrowseController@save']);

        Route::post('/elements/copy', ['as' => 'moonlight.elements.copy', 'uses' => 'Moonlight\Controllers\BrowseController@copy']);
        
        Route::post('/elements/move', ['as' => 'moonlight.elements.move', 'uses' => 'Moonlight\Controllers\BrowseController@move']);

        Route::post('/elements/bind', ['as' => 'moonlight.elements.move', 'uses' => 'Moonlight\Controllers\BrowseController@bind']);

        Route::post('/elements/unbind', ['as' => 'moonlight.elements.move', 'uses' => 'Moonlight\Controllers\BrowseController@unbind']);

        Route::post('/elements/favorite', ['as' => 'moonlight.elements.favorite', 'uses' => 'Moonlight\Controllers\BrowseController@favorite']);

        Route::post('/elements/delete', ['as' => 'moonlight.elements.delete', 'uses' => 'Moonlight\Controllers\BrowseController@delete']);

        Route::post('/elements/restore', ['as' => 'moonlight.elements.restore', 'uses' => 'Moonlight\Controllers\BrowseController@restore']);

        Route::post('/elements/delete/force', ['as' => 'moonlight.elements.delete.force', 'uses' => 'Moonlight\Controllers\BrowseController@forceDelete']);

        Route::get('/browse/{classId}/create/{item}', ['as' => 'moonlight.element.create', 'uses' => 'Moonlight\Controllers\EditController@create'])->
            where(['classId' => '[A-Za-z0-9\.]+', 'item' => '[A-Za-z0-9\.]+']);

        Route::get('/browse/{classId}/edit', ['as' => 'moonlight.element.edit', 'uses' => 'Moonlight\Controllers\EditController@edit'])->
            where(['classId' => '[A-Za-z0-9\.]+']);
        
        Route::post('/browse/add/{item}', ['as' => 'moonlight.element.add', 'uses' => 'Moonlight\Controllers\EditController@add'])->
            where(['item' => '[A-Za-z0-9\.]+']);
        
        Route::post('/browse/{classId}/save', ['as' => 'moonlight.element.save', 'uses' => 'Moonlight\Controllers\EditController@save'])->
            where(['classId' => '[A-Za-z0-9\.]+']);
        
        Route::post('/browse/{classId}/copy', ['as' => 'moonlight.element.copy', 'uses' => 'Moonlight\Controllers\EditController@copy'])->
            where(['classId' => '[A-Za-z0-9\.]+']);
        
        Route::post('/browse/{classId}/move', ['as' => 'moonlight.element.move', 'uses' => 'Moonlight\Controllers\EditController@move'])->
            where(['classId' => '[A-Za-z0-9\.]+']);

        Route::post('/browse/{classId}/favorite', ['as' => 'moonlight.element.favorite', 'uses' => 'Moonlight\Controllers\EditController@favorite']);
        
        Route::post('/browse/{classId}/delete', ['as' => 'moonlight.element.delete', 'uses' => 'Moonlight\Controllers\EditController@delete'])->
            where(['classId' => '[A-Za-z0-9\.]+']);
        
        Route::post('/browse/{classId}/plugin/{method}', ['as' => 'moonlight.browse.plugin', 'uses' => 'Moonlight\Controllers\BrowseController@plugin'])->
            where(['classId' => '[A-Za-z0-9\.]+', 'method' => '[A-Za-z0-9]+']);
        
        Route::post('/order', ['as' => 'moonlight.order', 'uses' => 'Moonlight\Controllers\BrowseController@order']);

        Route::post('/column', ['as' => 'moonlight.column', 'uses' => 'Moonlight\Controllers\BrowseController@column']);
        
        Route::group(['middleware' => [
            HistoryMiddleware::class,
        ]], function () {
            Route::get('/search/{item}', ['as' => 'moonlight.search.item', 'uses' => 'Moonlight\Controllers\SearchController@item'])->
                where(['item' => '[A-Za-z0-9\.]+']);
            
            Route::get('/browse', ['as' => 'moonlight.browse', 'uses' => 'Moonlight\Controllers\BrowseController@root']);
        
            Route::get('/browse/root', ['as' => 'moonlight.browse.root', 'uses' => 'Moonlight\Controllers\BrowseController@root']);
            
            Route::get('/browse/{classId}', ['as' => 'moonlight.browse.element', 'uses' => 'Moonlight\Controllers\BrowseController@element'])->
                where(['classId' => '[A-Za-z0-9\.]+']);
        });
    });

    Route::get('/{url}', function() {
        return redirect()->route('moonlight.home');
    });
});