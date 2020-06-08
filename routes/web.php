<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group.
|
*/

Route::get('/', 'PackageController@list')->name('packages');

Route::prefix('package/{name}')->group(function () {
    Route::get('/', 'PackageController@show')->name('package');
    Route::get('readme', 'PackageController@showReadme')->name(
        'package.readme',
    );
    Route::get('versions', 'PackageController@showVersions')->name(
        'package.versions',
    );
    Route::get('relations', 'PackageController@showRelations')->name(
        'package.relations',
    );
});

Route::get('help', fn() => view('help'))->name('help');

// Allow to un-cache any route using a PURGE request
Route::match('purge', '{query}', 'ResponseCacheController@purge')->where(
    'query',
    '.*',
);
