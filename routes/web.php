<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\pages\HomePage;
use App\Http\Controllers\pages\AccountSettingsAccount;
use App\Http\Controllers\pages\AccountSettingsSecurity;
use App\Http\Controllers\pages\ProviderController;
use App\Http\Controllers\pages\SearchServicesController;
use App\Http\Controllers\pages\SearchServicesTestController;
use App\Http\Controllers\pages\MyListController;
use App\Http\Controllers\pages\OrderHistoryController;

use App\Http\Controllers\pages\PaymentController;

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
    Route::get('/home', [HomePage::class, 'index'])->name('pages-home');

    // profile
    Route::get('/profile', [AccountSettingsAccount::class, 'index'])->name('profile-show');
    Route::post('/profile', [AccountSettingsAccount::class, 'update']);
    Route::delete('/profile', [AccountSettingsAccount::class, 'delete']);
    
    Route::get('/profile-security', [AccountSettingsSecurity::class, 'index'])->name('profile-security');
    Route::post('/profile-security', [AccountSettingsSecurity::class, 'update']);

    // Provider
    Route::get('/providers', [ProviderController::class, 'index'])->name('pages-my-provider');
    Route::post('/providers/add', [ProviderController::class, 'createNewProvider']);
    Route::get('/providers/delete/{id}', [ProviderController::class, 'deleteProvider'])->where('id', '[0-9]+');
    Route::post('/providers/favorite', [ProviderController::class, 'favoriteProvider']);
    Route::post('/providers/changeAPIKey', [ProviderController::class, 'changeAPIKey']); 
    Route::post('/providers/changeBalanceAlertLimit', [ProviderController::class, 'changeBalanceAlertLimit']); 
    Route::post('/providers/import_list', [ProviderController::class, 'importList']);
    Route::get('/providers/provider_list', [ProviderController::class, 'getProviderList']);
    
    
    // Search Services
    Route::get('/search-services', [SearchServicesController::class, 'index'])->name('pages-search-services');
    Route::post('/search-services', [SearchServicesController::class, 'searchServices']);
    Route::get('/search-services/load_existing_list', [SearchServicesController::class, 'loadExistingList']);
    Route::post('/search-services/create_new_list', [SearchServicesController::class, 'createNewList']);
    Route::post('/search-services/add_services_existing_list', [SearchServicesController::class, 'addServicesExistingList']);

    
    // serarch services for test
    Route::get('/search-services-test', [SearchServicesTestController::class, 'index'])->name('pages-search-services-test');
    Route::post('/search-services-test', [SearchServicesTestController::class, 'searchServices']);

    // My Lists page
    Route::get('/my-list', [MyListController::class, 'index'])->name('pages-my-list');
    Route::post('/my-list', [MyListController::class, 'loadMyLists']);
    Route::delete('/my-list/delete_service_from_list/{id}', [MyListController::class, 'deleteServiceFromList'])->where('id', '[0-9]+');
    // Route::post('/my-list/start_order', [MyListController::class, 'startOrder']);
    Route::post('/my-list/start-test-order', [MyListController::class, 'startTestOrder']);


    // Orders History page
    Route::get('/order-history', [OrderHistoryController::class, 'index'])->name('pages-order-history');
    Route::post('/order-history', [OrderHistoryController::class, 'loadHistory']);
    
    
    // payment page
    Route::get('/payment', [PaymentController::class, 'index'])->name('pages-payment');
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
Route::get('/', $controller_path . '\pages\MiscComingSoon@index');
Route::post('/coming-soon', $controller_path . '\pages\MiscComingSoon@addSubscriber');
