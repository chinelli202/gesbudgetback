<?php

namespace App\Services;


use App\Models\Engagement;
use App\Models\User;
use App\Models\Variable;
use App\Models\Ligne;
use App\Models\Rubrique;
use App\Models\Chapitre;

use Illuminate\Support\Facades\Config;
use App\Services\ImputationService;
use App\Services\ApurementService;

class EngagementService {

  public function __construct(){

  }

   
  public static function enrichEngagement($engagementId) {
    $imputations = [];
    $engagement = Engagement::findOrFail($engagementId);

    /** Add all enriched imputations */
    $engagement['imputations_labelled'] = $engagement->imputations
      ->filter(function($imp) {
        return $imp->etat !== Config::get('gesbudget.variables.etat_engagement.CLOT')[1];
      })
      ->map(function ($imp) {
        return ImputationService::enrichImputation($imp->id);
      });
    
    /** Add all enriched imputations */
    $engagement['apurements_labelled'] = $engagement->apurements
      ->filter(function($apur) {
        return $apur->etat !== Config::get('gesbudget.variables.etat_engagement.CLOT')[1];
      })
      ->map(function ($apur) {
        return ApurementService::enrichApurement($apur->id);
      });
    
    /** Add pending apurements and imputations */
    $engagement['cumul_imputations_initie_ttc'] = $engagement->imputations
      ->reduce(function ($cumul, $imp) {
        return $cumul + $imp->montant_ttc;
      }, 0);
    
    $engagement['cumul_apurements_initie_ttc'] = $engagement->apurements
      ->reduce(function ($cumul, $apur) {
        return $cumul + $apur->montant_ttc;
      }, 0);

    $saisisseur = User::where('matricule', $engagement->saisisseur)->first();
    $valideurP = User::where('matricule', $engagement->valideur_first)->first();
    $valideurS = User::where('matricule', $engagement->valideur_second)->first();
    $valideurF = User::where('matricule', $engagement->valideur_final)->first();

    $devise = Variable::where([
      ['code', $engagement->devise],
      ['cle', 'DEVISE']])->first();
    $nature = Variable::where([
      ['code', $engagement->nature]
      ,['cle', 'NATURE_ENGAGEMENT']])->first();
    $type = Variable::where([
      ['code', $engagement->type]
      ,['cle', 'TYPE_ENGAGEMENT']])->first();
    $etat = Variable::where([
      ['code', $engagement->etat]
      ,['cle', 'ETAT_ENGAGEMENT']])->first();
    $statut = Variable::where([
      ['code', $engagement->statut]
      ,['cle', 'STATUT_ENGAGEMENT']])->first();

    $ligne = Ligne::where('id', $engagement->ligne_id)->first();
    $rubrique = Rubrique::where('id', $ligne->rubrique_id)->first();
    $chapitre = Chapitre::where('id', $rubrique->chapitre_id)->first();

    $engagement["saisisseur_name"] = $saisisseur->name;
    $engagement["valideurp_name"] = $valideurP->name ?? '';
    $engagement["valideurs_name"] = $valideurS->name ?? '';
    $engagement["valideurf_name"] = $valideurF->name ?? '';

    $engagement["devise_libelle"] = $devise->libelle ?? '';
    $engagement["nature_libelle"] = $nature->libelle ?? '';
    $engagement["type_libelle"] = $type->libelle ?? '';
    $engagement["etat_libelle"] = $etat->libelle ?? '';
    $engagement["statut_libelle"] = $statut->libelle ?? '';

    $engagement["chapitre_id"] = $chapitre->id;
    $engagement["rubrique_id"] = $rubrique->id;
    $engagement["domaine"] = $chapitre->domaine;
    $engagement["ligne_libelle"] = $chapitre->label . " // " . $rubrique->label . " // " . $ligne->label;

    return $engagement;
  }
}