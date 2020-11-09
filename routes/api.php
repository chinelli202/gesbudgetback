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

    
    Route::get("getengagements", "EngagementController@getEngagements")->name('getEngagements');
    Route::get("engagement/{id}", "EngagementController@getEngagement")->name('getEngagement');
    Route::post("engagement/{id}", "EngagementController@update")->name("updateEngagement");
    Route::post("engagement/addcomment/{id}", "EngagementController@addComment")->name("addCommentEngagement");
    Route::post("engagement/sendback/{id}", "EngagementController@sendBack")->name("sendBackEngagement");
    Route::post("engagement/resend/{id}", "EngagementController@resendUpdate")->name("resendUpdateEngagement");

    Route::get("getvariables", "VariableController@getvariables")->name('getvariables');
});
