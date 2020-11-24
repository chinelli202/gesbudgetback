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

Route::get('/etats/section/{section}/{domaine}',"EtatsFonctionnementController@getRecapSection");



//routes for months recaps
Route::get('/etats/fonctionnement/monthsrecap/groupe/{groupename}',"EtatsFonctionnementController@getMonthsRecapRubriqueGroupe");
Route::get('/etats/fonctionnement/monthsrecap/rubrique/{rubriqueid}',"EtatsFonctionnementController@getMonthsRecapRubrique");
Route::get('/etats/fonctionnement/monthsrecap/chapitre/{chapitreid}',"EtatsFonctionnementController@getMonthsRecapChapitre");
Route::get('/etats/fonctionnement/monthsrecap/ligne/{ligneid}',"EtatsFonctionnementController@getMonthsRecapLigne");

//routes for excell exports
Route::get('/export/chapitre/{chapitreid}',"ExcellExportController@exportChapitre");
Route::get('/export/rubrique/{rubiqueid}',"ExcellExportController@exportRubrique");
Route::get('/export/section/{section}/{domaine}',"ExcellExportController@exportSection");
Route::get('/export/section/full/{section}/{domaine}',"ExcellExportController@exportSectionFull");
Route::get('/export/domaine/{domainename}',"ExcellExportController@exportDomaine");
