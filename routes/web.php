<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\pages\HomePage;
use App\Http\Controllers\pages\AccountSettingsAccount;
use App\Http\Controllers\pages\AccountSettingsSecurity;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

$controller_path = 'App\Http\Controllers';


Route::group(['middleware' => ['auth']], function () {
    Route::group(['middleware' => ['auth', 'user']], function () {
        Route::get('/', [HomePage::class, 'index'])->name('pages-home');
        Route::get('/profile', [AccountSettingsAccount::class, 'index'])->name('profile-show');
        Route::post('/profile', [AccountSettingsAccount::class, 'update']);
        Route::delete('/profile', [AccountSettingsAccount::class, 'delete']);

        Route::get('/profile-security', [AccountSettingsSecurity::class, 'index'])->name('profile-security');
    });
});

// // pages
// Route::get('/pages/misc-error', $controller_path . '\pages\MiscError@index')->name('pages-misc-error');

// authentication
Route::get('/auth/login', $controller_path . '\authentications\LoginBasic@index')->name('auth-login');
Route::post('/auth/login', $controller_path . '\authentications\LoginBasic@login');
Route::get('/auth/logout', $controller_path . '\authentications\LoginBasic@logout')->name('logout');;


Route::get('/auth/register', $controller_path . '\authentications\RegisterBasic@index')->name('auth-register');
Route::post('/auth/register', $controller_path . '\authentications\RegisterBasic@register')->name('auth-register');
