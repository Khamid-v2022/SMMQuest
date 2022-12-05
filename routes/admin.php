<?php 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\Login;
use App\Http\Controllers\admin\Dashboard;
use App\Http\Controllers\admin\ProviderManagement;
use App\Http\Controllers\admin\UserManagement;

Route::namespace('admin')->prefix('admin')->group(function(){

    Route::middleware('guest:admin')->group(function(){
        //login route
        Route::get('/login', [Login::class, 'index'])->name('admin-login');
        Route::post('/login', [Login::class, 'signin']);
    });

    Route::middleware('auth:admin')->group(function(){
        Route::get('/', [Dashboard::class, 'index']);
        Route::get('/logout', [Login::class, 'signout'])->name('admin-logout');

        Route::get('/provider-management', [ProviderManagement::class, 'index']);
        Route::post('/provider-management', [ProviderManagement::class, 'addProvider']);
        Route::delete('/provider-management', [ProviderManagement::class, 'deleteProvider']);
        Route::post('/provider-management/import_list', [ProviderManagement::class, 'importList']);
        Route::get('/provider-management/importOne/{id}', [ProviderManagement::class, 'importOneProviderServiceList'])->where('id', '[0-9]+');
        

       
        Route::get('/user-management', [UserManagement::class, 'index']);
        Route::post('/user-management', [UserManagement::class, 'addUser']);
        // Route::delete('/user-management', [UserManagement::class, 'deleteUser']);
        Route::post('/user-management/reset-password', [UserManagement::class, 'resetPassword']);
    });

});
