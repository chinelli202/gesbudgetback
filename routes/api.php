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

    Route::prefix('engagement')->name('engagement')->group(function () {
        Route::post("addcomment/{id}", "ImputationController@addcomment")->name("addcomment");
        Route::post("create/", "ImputationController@create")->name("create");
        Route::post("update/{id}", "ImputationController@update")->name("update");
        Route::post("close/{id}", "ImputationController@close")->name("close");
        Route::post("restore/{id}", "ImputationController@restore")->name("restore");
        Route::post("sendback/{id}", "ImputationController@sendback")->name("sendback");
        Route::post("resend/{id}", "ImputationController@resendupdate")->name("resendupdate");
        Route::post("valider/{id}", "ImputationController@valider")->name("valider");
        Route::post("cancelvalider/{id}", "ImputationController@cancelvalider")->name("cancelvalider");
        
        Route::post("uploadfile", "FileuploadController@uploadfile")->name("uploadfile");
    });

    Route::prefix('imputation')->name('imputation')->group(function () {
        Route::post("addcomment/{id}", "ImputationController@addcomment")->name("addcomment");
        Route::post("create/", "ImputationController@create")->name("create");
        Route::post("update/{id}", "ImputationController@update")->name("update");
        Route::post("close/{id}", "ImputationController@close")->name("close");
        Route::post("restore/{id}", "ImputationController@restore")->name("restore");
        Route::post("sendback/{id}", "ImputationController@sendback")->name("sendback");
        Route::post("resend/{id}", "ImputationController@resendupdate")->name("resendupdate");
        Route::post("valider/{id}", "ImputationController@valider")->name("valider");
        Route::post("cancelvalider/{id}", "ImputationController@cancelvalider")->name("cancelvalider");
    });

    Route::prefix('apurement')->name('apurement')->group(function () {
        Route::post("addcomment/{id}", "ApurementController@addcomment")->name("addcomment");
        Route::post("create/", "ApurementController@create")->name("create");
        Route::post("update/{id}", "ApurementController@update")->name("update");
        Route::post("close/{id}", "ApurementController@close")->name("close");
        Route::post("restore/{id}", "ApurementController@restore")->name("restore");
        Route::post("sendback/{id}", "ApurementController@sendback")->name("sendback");
        Route::post("resend/{id}", "ApurementController@resendupdate")->name("resendupdate");
        Route::post("valider/{id}", "ApurementController@valider")->name("valider");
        Route::post("cancelvalider/{id}", "ApurementController@cancelvalider")->name("cancelvalider");
    });
    
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
Route::get('/etats/fonctionnement/groupe/{groupename}',"EtatsFonctionnementController@getRecapRubriqueGroupe");
Route::get('/etats/fonctionnement/rubrique/{rubriqueid}',"EtatsFonctionnementController@getRecapRubrique");
Route::get('/etats/fonctionnement/chapitre/{chapitreid}',"EtatsFonctionnementController@getRecapChapitre");
Route::get('/etats/fonctionnement/ligne/{ligneid}',"EtatsFonctionnementController@getRecapLigne");

//routes for months recaps
Route::get('/etats/fonctionnement/monthsrecap/groupe/{groupename}',"EtatsFonctionnementController@getMonthsRecapRubriqueGroupe");
Route::get('/etats/fonctionnement/monthsrecap/rubrique/{rubriqueid}',"EtatsFonctionnementController@getMonthsRecapRubrique");
Route::get('/etats/fonctionnement/monthsrecap/chapitre/{chapitreid}',"EtatsFonctionnementController@getMonthsRecapChapitre");
Route::get('/etats/fonctionnement/monthsrecap/ligne/{ligneid}',"EtatsFonctionnementController@getMonthsRecapLigne");

//routes for excell exports
Route::get('/export/chapitre/{chapitreid}',"ExcellExportController@exportChapitre");
Route::get('/export/rubrique/{rubiqueid}',"ExcellExportController@exportRubrique");
