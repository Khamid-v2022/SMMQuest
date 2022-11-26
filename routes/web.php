<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\pages\HomePage;
use App\Http\Controllers\pages\AccountSettingsAccount;
use App\Http\Controllers\pages\AccountSettingsSecurity;
use App\Http\Controllers\pages\ProviderController;

// use App\Http\Controllers\admin\LoginPage;
// use App\Http\Controllers\admin\DashboardPage;

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


Route::group(['middleware' => ['auth', 'user']], function () {
    Route::get('/', [HomePage::class, 'index'])->name('pages-home');

    Route::get('/profile', [AccountSettingsAccount::class, 'index'])->name('profile-show');
    Route::post('/profile', [AccountSettingsAccount::class, 'update']);
    Route::delete('/profile', [AccountSettingsAccount::class, 'delete']);
    
    Route::get('/profile-security', [AccountSettingsSecurity::class, 'index'])->name('profile-security');
    Route::post('/profile-security', [AccountSettingsSecurity::class, 'update']);

    // Provider
    Route::get('/providers', [ProviderController::class, 'index']);
    Route::post('/providers/add', [ProviderController::class, 'createNewProvider']);
    Route::get('/providers/delete/{id}', [ProviderController::class, 'deleteProvider'])->where('id', '[0-9]+');
    Route::post('/providers/favorite', [ProviderController::class, 'favoriteProvider']);
    Route::post('/providers/changeAPIKey', [ProviderController::class, 'changeAPIKey']);   
});

// error pages
Route::get('/pages/misc-error', $controller_path . '\pages\MiscError@index')->name('pages-misc-error');

// authentication
Route::get('/auth/login', $controller_path . '\authentications\LoginBasic@index')->name('auth-login');
Route::post('/auth/login', $controller_path . '\authentications\LoginBasic@login');
Route::get('/auth/logout', $controller_path . '\authentications\LoginBasic@logout')->name('logout');

Route::get('/forgot-password', $controller_path . '\authentications\LoginBasic@forgotPassword');
Route::post('/forgot-password', $controller_path . '\authentications\LoginBasic@sendMailToResetPasswordLink');

Route::get('/reset-password/{unique_str}', $controller_path . '\authentications\LoginBasic@resetPasswordPage')->name('reset-password');
Route::post('/reset-password', $controller_path . '\authentications\LoginBasic@resetPassword');


Route::get('/auth/register', $controller_path . '\authentications\RegisterBasic@index')->name('auth-register');
Route::post('/auth/register', $controller_path . '\authentications\RegisterBasic@register');

// verification 
Route::get('/auth/register/send-verify-email/{unique_str}', $controller_path . '\authentications\RegisterBasic@sendVerifyEmail');
Route::get('/auth/register/resend-verify-email/{unique_str}', $controller_path . '\authentications\RegisterBasic@resendVerifyEmail');
Route::get('/email-verify/{unique_str}', $controller_path . '\authentications\RegisterBasic@verifyEmail')->name('email-verify');
Route::get('/failed-email-verify', $controller_path . '\authentications\RegisterBasic@failedVerify')->name('failed-email-verify');

// Coming Soon page
Route::get('/coming-soon', $controller_path . '\pages\MiscComingSoon@index');
Route::post('/coming-soon', $controller_path . '\pages\MiscComingSoon@addSubscriber');
