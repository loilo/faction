<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| OAuth Routes
|--------------------------------------------------------------------------
|
| These routes are responsible for handling GitHub OAuth authentication.
| They are loaded by the RouteServiceProvider within a group which
| contains the "oauth" middleware group.
|
*/

Route::get('login', [
    'middleware' => 'doNotCacheResponse',
    'uses' => 'GitHubOauthController@redirectToProvider'
])->name('login');
Route::get('login/callback', [
    'middleware' => 'doNotCacheResponse',
    'uses' => 'GitHubOauthController@handleProviderCallback'
]);
