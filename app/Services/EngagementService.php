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

  public static function getLowestStatut($entitiesarray) {
    if(gettype($entitiesarray) === 'object') {
      /** $entitiesarray has only one entry. So we will put it in an array */
      $entitiesarray = array($entitiesarray[0]);
    }
    
    $lowestStatut = Config::get('gesbudget.variables.statut_engagement.VALIDF')[1];
    $statutsapurements = array_map(function($el) {
      return $el->statut;
    }, $entitiesarray);
    
    if(in_array(Config::get('gesbudget.variables.statut_engagement.SAISI')[1], $statutsapurements)) {

      $lowestStatut = Config::get('gesbudget.variables.statut_engagement.SAISI')[1];
    } else if(in_array(Config::get('gesbudget.variables.statut_engagement.VALIDP')[1], $statutsapurements)) {

      $lowestStatut = Config::get('gesbudget.variables.statut_engagement.VALIDP')[1];

    } else if(in_array(Config::get('gesbudget.variables.statut_engagement.VALIDS')[1], $statutsapurements)) {

      $lowestStatut = Config::get('gesbudget.variables.statut_engagement.VALIDS')[1];
    }

    // return $lowestStatut = json_encode([
    //   "statutsapurements" => $statutsapurements,
    //   "Configget" => Config::get('gesbudget.variables.statut_engagement.VALIDS')[1],
    //   "array_search" => in_array(Config::get('gesbudget.variables.statut_engagement.VALIDS')[1], $statutsapurements)
    //   ]);
    return $lowestStatut;
  }

  public static function getLatestEditionDate($entitiesarray) {
    if(gettype($entitiesarray) === 'object') {
      /** $entitiesarray has only one entry. So we will put it in an array */
      $entitiesarray = array($entitiesarray[0]);
    }
    usort($entitiesarray, function($a, $b) {
      if($a->updated_at == $b->updated_at) {
        return 0;
      }
      return ($a->updated_at < $b->updated_at) ? 1 : -1;
    });
  
    return $entitiesarray[0]->updated_at;
  }

  public static function enrichEngagement($engagementId) {
    $engagement = Engagement::findOrFail($engagementId);
    $imputations = $engagement->imputations;
    $apurements = $engagement->apurements;

    /** Add all enriched imputations */
    $engagement['imputations_labelled'] = $imputations
      ->filter(function($imp) {
        return $imp->etat !== Config::get('gesbudget.variables.etat_engagement.CLOT')[1];
      })
      ->map(function ($imp) {
        return ImputationService::enrichImputation($imp->id);
      });
    
    /** Add all enriched apurements */
    $engagement['apurements_labelled'] = $apurements
      ->filter(function($apur) {
        return $apur->etat !== Config::get('gesbudget.variables.etat_engagement.CLOT')[1];
      })
      ->map(function ($apur) {
        return ApurementService::enrichApurement($apur->id);
      });
    
    /** Add pending apurements and imputations */
    $engagement['cumul_imputations_initie_ttc'] = $imputations
      ->reduce(function ($cumul, $imp) {
        return $cumul + $imp->montant_ttc;
      }, 0);
    
    $engagement['cumul_apurements_initie_ttc'] = $apurements
      ->reduce(function ($cumul, $apur) {
        return $cumul + $apur->montant_ttc;
      }, 0);

    /** Add latest_statut */
    $latestStatut = Config::get('gesbudget.variables.statut_engagement.SAISI')[1];
    if(sizeof($apurements) !== 0) {
      $latestStatut = $engagement->latest_statut;
    } else if ( sizeof($imputations) !== 0) {
      if($engagement->latest_statut === Config::get('gesbudget.variables.statut_engagement.VALIDF')[1]) {
        $latestStatut = 'NEW';
      } else {
        $latestStatut = $engagement->latest_statut;
      }
    } else if($engagement->etat === Config::get('gesbudget.variables.etat_engagement.PEG')[1]) {
      $latestStatut = 'NEW';
    } else {
      $latestStatut = $engagement->latest_statut;
    }

    $engagement['latest_statut'] = $latestStatut;

    /** Add operators */
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

    $engagement["domaine"] = $chapitre->domaine;
    $engagement["ligne_libelle"] = $ligne->label;
    $engagement["rubrique_libelle"] = $rubrique->label;
    $engagement["chapitre_libelle"] = $chapitre->label;

    return $engagement;
  }
}