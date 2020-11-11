<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post("create-user", "UserController@createUser")->name('createUser');

Route::post("user-login", "UserController@userLogin")->name('userLogin');

Route::get("user-login", "UserController@getLogin")->name('getLogin');

Route::group(['middleware' => 'auth:api'], function () {
    Route::get("user-detail", "UserController@userDetail");
    Route::get("user-logout", "UserController@userLogout")->name('userLogout');
});

Route::get('/budgetsfonctionnement',"BudgetFonctionnementController@index");