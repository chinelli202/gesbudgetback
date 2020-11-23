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
    Route::post("engagement/update/{id}", "EngagementController@update")->name("updateEngagement");
    Route::post("engagement/create/", "EngagementController@create")->name("createEngagement");
    Route::post("engagement/addcomment/{id}", "EngagementController@addComment")->name("addCommentEngagement");
    Route::post("engagement/close/{id}", "EngagementController@close")->name("closeEngagement");
    Route::post("engagement/restore/{id}", "EngagementController@restore")->name("restoreEngagement");
    Route::post("engagement/sendback/{id}", "EngagementController@sendBack")->name("sendBackEngagement");
    Route::post("engagement/resend/{id}", "EngagementController@resendUpdate")->name("resendUpdateEngagement");
    
    Route::post("engagement/valider/peg/{id}", "EngagementController@validerPreeng")->name("validerPreeng");
    Route::post("engagement/cancelValider/peg/{id}", "EngagementController@cancelValiderPreeng")->name("cancelValiderPreeng");
    
    Route::post("imputation/create/", "ImputationController@createImputation")->name("createImputation");
    Route::post("imputation/update/{id}", "ImputationController@update")->name("updateImputation");

    Route::post("engagement/uploadfile", "FileuploadController@uploadfile")->name("uploadfile");
    
    Route::get("getvariables", "VariableController@getvariables")->name('getvariables');
});

Route::get('/budgetsfonctionnement',"BudgetFonctionnementController@index");
Route::get('/etats/fonctionnement/depenses/groupe/{groupename}',"EtatsGroupesFonctionnementController@getGroupe");
Route::get('/etats/fonctionnement/depenses/soussection/fonctionnement',"EtatsFonctionnementController@getRecapSousSectionFonctionnement");
Route::get('/etats/fonctionnement/depenses/soussection/investissement',"EtatsFonctionnementController@getRecapSousSectionInvestissement");
Route::get('/etats/fonctionnement/recette',"EtatsFonctionnementController@getRecapRecettes");

//routes for loading the maquette
Route::get('/maquettes/fonctionnement/depenses',"MaquetteTreeController@getDepensesFonctionnementTree");
Route::get('/maquettes/fonctionnement/recettes',"MaquetteTreeController@getRecettesFonctionnementTree");
Route::get('/maquettes/mandat/depenses',"MaquetteTreeController@getDepensesMandatTree");
Route::get('/maquettes/mandat/recettes',"MaquetteTreeController@getRecettesMandatTree");
Route::get('/maquettes/fonctionnement',"MaquetteTreeController@getFonctionnementTree");
Route::get('/maquettes/mandat',"MaquetteTreeController@getMandatTree");
Route::get('/maquettes/all',"MaquetteTreeController@getGlobalTree");

//routes for recap requests
Route::get('/etats/fonctionnement/recette/rubriquegroupe/{groupename}',"EtatsFonctionnementController@getRecapRubriqueGroupe");
Route::get('/etats/fonctionnement/rubrique/{rubriqueid}',"EtatsFonctionnementController@getRecapRubrique");
Route::get('/etats/fonctionnement/chapitre/{chapitreid}',"EtatsFonctionnementController@getRecapChapitre");
Route::get('/etats/fonctionnement/ligne/{ligneid}',"EtatsFonctionnementController@getRecapLigne");
