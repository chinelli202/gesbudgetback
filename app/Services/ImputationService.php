<?php

namespace App\Services;


use App\Models\Imputation;
use App\Models\User;
use App\Models\Variable;

class ImputationService {

  public function __construct(){

  }
  public const ImputationCreateValidator = [
      'engagement_id'     =>          'required|exists:engagements,code',
      'reference'         =>          'required',
      'montant_ttc'       =>          'required',
      'devise'            =>          'required|exists:variables,code',
      'observations'      =>          'required'
  ];

  public static function enrichImputation($imputationId) {
      $imputation = Imputation::findOrFail($imputationId);
      $saisisseur = User::where('matricule', $imputation->saisisseur)->first();
      $valideurP = User::where('matricule', $imputation->valideur_first)->first();
      $valideurS = User::where('matricule', $imputation->valideur_second)->first();
      $valideurF = User::where('matricule', $imputation->valideur_final)->first();

      $devise = Variable::where('code', $imputation->devise)->first();
      $type = Variable::where('code', $imputation->type)->first();
      $etat = Variable::where('code', $imputation->etat)->first();
      $statut = Variable::where('code', $imputation->statut)->first();

      $imputation["saisisseur_name"] = $saisisseur->name;
      $imputation["valideurp_name"] = $valideurP->name ?? '';
      $imputation["valideurs_name"] = $valideurS->name ?? '';
      $imputation["valideurf_name"] = $valideurF->name ?? '';

      $imputation["devise_libelle"] = $devise->libelle ?? '';
      $imputation["etat_libelle"] = $etat->libelle ?? '';
      $imputation["statut_libelle"] = $statut->libelle ?? '';
      return $imputation;
  }
}