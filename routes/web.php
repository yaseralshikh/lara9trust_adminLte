<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Livewire\Backend\Admin\Dashboard\Dashboard;
use App\Http\Livewire\Backend\Admin\Permissions\ListPermissions;
use App\Http\Livewire\Backend\Admin\Roles\ListRoles;
use App\Http\Livewire\Backend\Admin\Users\ListUsers;

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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::group(['prefix' => 'admin', 'as' => 'admin.','middleware' => ['auth', 'role:admin|superadmin']], function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('users', ListUsers::class)->name('users');
    Route::get('roles', ListRoles::class)->name('roles');
    Route::get('permissions', ListPermissions::class)->name('permissions');
});

